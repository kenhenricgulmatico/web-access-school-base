<?php

use App\Support\BookingDate;
use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Models\RequestType;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\ProgramHeadRequestNotification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.coordinator')] class extends Component
{
    public $request_type_id = '';
    public $purpose = '';
    public $request_date = '';

    // Facility fields
    public $facility_name = '';
    public $start_time = '09:00';
    public $end_time = '10:00';
    public array $facilityOptions = [];

    // Materials
    public array $materials = [];
    public $availableResources = [];

    // ✅ When the borrowed Pcs items will be returned (max 1 month)
    public $return_by = '';

    // ✅ Student / faculty request this was forwarded from ("Forward to Admin")
    public ?int $forwardedFromId = null;
    public string $forwardedFromName = '';

    public function mount(): void
    {
        // ✅ Server-side guard — creating a request to admin requires `program-head-requests.create`
        abort_unless(auth()->user()->can('program-head-requests.create'), 403);

        $facilityType = ResourceType::where('type_name', \App\Models\ResourceType::FACILITY)->first();

        if ($facilityType) {
            $this->facilityOptions = Resource::where('resource_type_id', $facilityType->id)
                ->where('status', 'available')
                ->orderBy('resource_name')
                ->pluck('resource_name')
                ->toArray();
        } else {
            $this->facilityOptions = Resource::whereHas('resourceType', fn($q) =>
                $q->where('type_name', 'like', '%facilit%')
            )
            ->where('status', 'available')
            ->orderBy('resource_name')
            ->pluck('resource_name')
            ->toArray();
        }

        $this->availableResources = Resource::where('status', 'available')
            ->where('quantity_available', '>', 0)
            ->where(function ($q) {
                $q->whereHas('resourceType', function ($q2) {
                    $q2->where('type_name', '!=', \App\Models\ResourceType::FACILITY)
                       ->where('type_name', 'not like', '%facilit%');
                })->orWhereDoesntHave('resourceType');
            })
            ->orderBy('resource_name')
            ->get()
            ->map(function ($resource) {
                $resource->available_formatted = $this->formatQuantity(
                    (int) $resource->quantity_available,
                    $resource->unit,
                    $resource->pieces_per_pack
                );
                return $resource;
            });

        // ✅ "Forward to Admin": fill the form with what the student's request still needs
        if ($from = (int) request()->query('from')) {
            $this->prefillFromStudentRequest($from);
        }
    }

    /**
     * ✅ "Forward to Admin": pre-fill this form with a pending student / faculty request
     *    of the same department (facility + materials, quantities, return date).
     *    When the Admin approves this request, the student's request is approved automatically.
     */
    protected function prefillFromStudentRequest(int $id): void
    {
        $source = ResourceRequest::with(['user', 'items.resource'])
            ->where('department_id', Auth::user()->department_id)
            ->where('status', 'pending')
            ->whereHas('user', fn ($u) => $u->whereDoesntHave('roles', fn ($r) => $r->where('name', 'program head')))
            ->find($id);

        if (! $source) {
            session()->flash('error', 'That request can no longer be forwarded (it may already be approved or rejected).');
            return;
        }

        if (\App\Support\ForwardedRequests::isWaitingForAdmin($source)) {
            session()->flash('error', 'This request was already forwarded and is waiting for the Admin.');
            return;
        }

        $facility  = $source->items->firstWhere('resource_id', null);
        $materials = $source->items->whereNotNull('resource_id');

        $this->forwardedFromId   = $source->id;
        $this->forwardedFromName = $source->user->name ?? 'a faculty member';
        // ✅ The faculty member's own purpose, as they wrote it (no "For <name>:" in front)
        $this->purpose           = \Illuminate\Support\Str::limit((string) $source->purpose, 500, '');

        if ($facility) {
            // Facility reservation → forward the same facility, date and time
            $this->request_type_id = RequestType::FACILITY;
            $this->facility_name   = $facility->item_name;
            $this->request_date    = \Carbon\Carbon::parse($facility->request_date)->format('Y-m-d');
            $this->start_time      = substr((string) $facility->start_time, 0, 5);
            $this->end_time        = substr((string) $facility->end_time, 0, 5);
        } else {
            $this->request_type_id = RequestType::MATERIAL;
            $needed = $materials->min('request_date');
            $this->request_date = ($needed && \Carbon\Carbon::parse($needed)->gte(today()))
                ? \Carbon\Carbon::parse($needed)->format('Y-m-d')
                : today()->format('Y-m-d');
        }

        // Every material, with the quantity the student asked for (only what the Admin has)
        $this->materials = $materials
            ->groupBy('resource_id')
            ->filter(fn ($rows, $rid) => $this->getMaterialResource($rid))
            ->map(fn ($rows, $rid) => ['resource_id' => (int) $rid, 'quantity' => (int) $rows->sum('quantity')])
            ->values()
            ->toArray();

        $notAtAdmin = $materials->reject(fn ($i) => $this->getMaterialResource($i->resource_id))->pluck('item_name')->unique();
        if ($notAtAdmin->isNotEmpty()) {
            session()->flash('error', 'The Admin has none left of: ' . $notAtAdmin->implode(', ') . '. Those items were left out.');
        }

        // Use the student's return date when it fits the Program Head's 1-month limit
        $studentReturn = $source->items->whereNotNull('due_back_at')->first()?->due_back_at?->format('Y-m-d');
        if ($studentReturn && $studentReturn >= $this->returnByMin() && $studentReturn <= $this->returnByMax()) {
            $this->return_by = $studentReturn;
        }
    }

    protected function rules()
    {
        $rules = [
            'request_type_id' => 'required|exists:request_types,id',
            'purpose'         => 'required|string|min:10|max:500',
            'request_date'    => BookingDate::rules(),
            'materials.*.resource_id' => 'nullable|exists:resources,id',
            'materials.*.quantity'    => 'nullable|integer|min:1',
        ];

        if ($this->request_type_id == RequestType::FACILITY) {
            $rules['facility_name'] = 'required|string|max:255';
            $rules['start_time']    = 'required';
            $rules['end_time']      = 'required|after:start_time';
        }

        if ($this->request_type_id == RequestType::MATERIAL) {
            $rules['materials'] = 'required|array|min:1';
            $rules['materials.*.resource_id'] = 'required|exists:resources,id';
            $rules['materials.*.quantity']    = 'required|integer|min:1';
        }

        // ✅ Borrowing Pcs items → a return date is required (max 1 month). Ream / Set: not asked.
        if ($this->hasBorrowItems()) {
            $rules['return_by'] = 'required|date|after_or_equal:' . $this->returnByMin() . '|before_or_equal:' . $this->returnByMax();
        }

        return $rules;
    }

    protected $messages = [
        'request_type_id.required' => 'Please select a request type',
        'purpose.required'         => 'Please state your purpose',
        'purpose.min'              => 'Purpose must be at least 10 characters',
        'facility_name.required'   => 'Please select a facility',
        'start_time.required'      => 'Please select start time',
        'end_time.required'        => 'Please select end time',
        'end_time.after'           => 'End time must be after start time',
        'materials.required'       => 'Please add at least one material',
        'materials.*.resource_id.required' => 'Please select a material',
        'materials.*.resource_id.exists'   => 'Selected material is invalid.',
        'materials.*.quantity.required'    => 'Please enter quantity',
        'materials.*.quantity.min'         => 'Quantity must be at least 1',
    ];

    protected function messages(): array
    {
        // ✅ Booking date: today up to ~6 months ahead (see App\Support\BookingDate)
        $min = \Carbon\Carbon::parse($this->returnByMin())->format('M d, Y');
        $max = \Carbon\Carbon::parse($this->returnByMax())->format('M d, Y');

        return array_merge($this->messages, BookingDate::messages('request_date'), [
            'return_by.required'        => 'Please choose when you will return the borrowed items.',
            'return_by.date'            => 'Please enter a valid return date.',
            'return_by.after_or_equal'  => "The return date cannot be before {$min} (the day you need the items).",
            'return_by.before_or_equal' => "You can borrow items for 1 month only. Please return them by {$max}.",
        ]);
    }

    protected function formatQuantity(int $qtyInPcs, ?string $unit, ?int $piecesPerPack): string
    {
        if ($unit === 'Pack' && $piecesPerPack) {
            $packs     = intdiv($qtyInPcs, $piecesPerPack);
            $remainder = $qtyInPcs % $piecesPerPack;

            if ($remainder === 0) {
                return "{$packs} Pack" . ($packs === 1 ? '' : 's');
            }

            return "{$packs} Pack" . ($packs === 1 ? '' : 's') . " + {$remainder} pcs";
        }

        return "{$qtyInPcs} ". ($unit ?: 'Ream');
    }

    // ============================================================
    // ✅ RETURN DEADLINE — ONLY for "Material" items (they must be returned)
    //    Ream and Set items are used up, so they never get a return date.
    // ============================================================

    /** Longest a Program Head may keep borrowed Pcs items: 1 month after the day they need them. */
    public const MAX_BORROW_MONTHS = 1;

    /** True when at least one selected material is a Material (it must be returned). */
    public function hasBorrowItems(): bool
    {
        return collect($this->materials)
            ->pluck('resource_id')
            ->filter()
            ->contains(fn ($id) => ($this->getMaterialResource($id)?->is_returnable) === true);
    }

    /** Earliest return date: the day the items are needed (never before today). */
    public function returnByMin(): string
    {
        $needed = $this->request_date ? \Carbon\Carbon::parse($this->request_date) : today();

        return ($needed->lt(today()) ? today() : $needed)->format('Y-m-d');
    }

    /** Latest return date: 1 month after the day the items are needed. */
    public function returnByMax(): string
    {
        return \Carbon\Carbon::parse($this->returnByMin())
            ->addMonthsNoOverflow(self::MAX_BORROW_MONTHS)
            ->format('Y-m-d');
    }

    #[Computed]
    public function requestTypes()
    {
        return RequestType::all();
    }

    public function getMaterialResource($resourceId)
    {
        if (!$resourceId) return null;

        return collect($this->availableResources)->firstWhere('id', (int) $resourceId);
    }

    public function getAvailableStock($resourceId): ?string
    {
        $resource = $this->getMaterialResource($resourceId);

        if (!$resource) {
            return null;
        }

        return $resource->available_formatted;
    }

    public function getQuantityBreakdown($resourceId, $quantity): ?string
    {
        $resource = $this->getMaterialResource($resourceId);

        if (!$resource) {
            return null;
        }

        $qty = (int) $quantity;

        if ($qty <= 0) {
            return null;
        }

        return $this->formatQuantity($qty, $resource->unit, $resource->pieces_per_pack);
    }

    public function getStockWarning($resourceId, $quantity): ?string
    {
        $resource = $this->getMaterialResource($resourceId);

        if (!$resource || $quantity === '' || $quantity === null) {
            return null;
        }

        $qty = (int) $quantity;

        if ($qty > (int) $resource->quantity_available) {
            return "Exceeds available stock ({$resource->available_formatted})";
        }

        return null;
    }

    public function getResourcesForRow(int $currentIndex)
    {
        $selectedElsewhere = collect($this->materials)
            ->except($currentIndex)
            ->pluck('resource_id')
            ->filter(fn ($id) => $id !== '' && $id !== null)
            ->map(fn ($id) => (int) $id)
            ->toArray();

        return collect($this->availableResources)
            ->reject(fn ($resource) => in_array($resource->id, $selectedElsewhere, true))
            ->values();
    }

    public function addMaterial()
    {
        $this->materials[] = ['resource_id' => '', 'quantity' => 1];
    }

    public function removeMaterial(int $index)
    {
        unset($this->materials[$index]);
        $this->materials = array_values($this->materials);
    }

    public function updatedMaterials($value, $key)
    {
        if (! str_ends_with($key, '.resource_id') || $value === '' || $value === null) {
            return;
        }

        $index = (int) explode('.', $key)[0];

        $duplicateExists = collect($this->materials)
            ->except($index)
            ->pluck('resource_id')
            ->filter(fn ($id) => $id !== '' && $id !== null)
            ->map(fn ($id) => (int) $id)
            ->contains((int) $value);

        if ($duplicateExists) {
            $this->materials[$index]['resource_id'] = '';
            $this->addError("materials.$index.resource_id", 'This material is already added in another row.');
        }
    }

    public function updatedRequestTypeId()
    {
        $this->reset(['facility_name', 'start_time', 'end_time', 'materials', 'return_by']);
        $this->start_time = '09:00';
        $this->end_time   = '10:00';
    }

    public function updatedFacilityName()
    {
        $this->validateFacilityConflictLive();
    }

    public function updatedStartTime()
    {
        $this->validateTimeRangeLive();
        $this->validateFacilityConflictLive();
    }

    public function updatedEndTime()
    {
        $this->validateTimeRangeLive();
        $this->validateFacilityConflictLive();
    }

    public function updatedRequestDate()
    {
        $this->validateFacilityConflictLive();
    }

    protected function validateTimeRangeLive(): void
    {
        $this->resetErrorBag('end_time');

        if ($this->request_type_id == RequestType::FACILITY && $this->start_time && $this->end_time) {
            if ($this->end_time <= $this->start_time) {
                $this->addError('end_time', 'End time must be after start time.');
            }
        }
    }

    protected function validateFacilityConflictLive(): void
    {
        $this->resetErrorBag('facility_name');

        if ($this->request_type_id == RequestType::FACILITY && $conflictMessage = $this->facilityConflict()) {
            $this->addError('facility_name', $conflictMessage);
        }
    }

    protected function facilityConflict(): ?string
    {
        if (!$this->facility_name || !$this->request_date || !$this->start_time || !$this->end_time) {
            return null;
        }

        $conflict = RequestItem::whereNull('resource_id')
            ->where('item_name', $this->facility_name)
            ->where('request_date', $this->request_date)
            ->whereHas('request', fn ($q) => $q->where('status', 'approved'))
            ->where('start_time', '<', $this->end_time)
            ->where('end_time', '>', $this->start_time)
            ->first();

        if ($conflict) {
            $from = \Carbon\Carbon::parse($conflict->start_time)->format('h:i A');
            $to   = \Carbon\Carbon::parse($conflict->end_time)->format('h:i A');

            return "Sorry, {$this->facility_name} is already booked on "
                . \Carbon\Carbon::parse($this->request_date)->format('M d, Y')
                . " from {$from} to {$to}. Please choose a different time or date.";
        }

        return null;
    }

    public function submit()
    {
        // ✅ Server-side guard — creating a request to admin requires `program-head-requests.create`
        abort_unless(auth()->user()->can('program-head-requests.create'), 403);

        $this->validate();

        $user = Auth::user();

        if (!$user->department_id) {
            session()->flash('error', 'Your account has no department assigned. Please contact the admin to assign your department before submitting a request.');
            return;
        }

        if ($this->request_type_id == RequestType::FACILITY) {
            if ($conflictMessage = $this->facilityConflict()) {
                $this->addError('facility_name', $conflictMessage);
                return;
            }
        }

        $selectedMaterials = collect($this->materials)
            ->filter(fn ($m) => !empty($m['resource_id']))
            ->values();

        $duplicateIds = $selectedMaterials
            ->pluck('resource_id')
            ->map(fn ($id) => (int) $id)
            ->duplicates();

        if ($duplicateIds->isNotEmpty()) {
            foreach ($selectedMaterials as $i => $material) {
                if ($duplicateIds->contains((int) $material['resource_id'])) {
                    $this->addError("materials.$i.resource_id", 'This material is selected more than once. Please combine the quantity into a single row instead.');
                }
            }
            return;
        }

        foreach ($selectedMaterials as $i => $material) {
            $resource = Resource::find($material['resource_id']);

            if (!$resource) {
                $this->addError("materials.$i.resource_id", 'This material is no longer available.');
                return;
            }

            if ((int) $material['quantity'] > $resource->quantity_available) {
                $available = $this->formatQuantity(
                    (int) $resource->quantity_available,
                    $resource->unit,
                    $resource->pieces_per_pack
                );
                $this->addError("materials.$i.quantity", "Only {$available} of {$resource->resource_name} available.");
                return;
            }
        }

        $request = ResourceRequest::create([
            'user_id'                          => $user->id,
            'department_id'                    => $user->department_id,
            'request_type_id'                  => $this->request_type_id,
            'purpose'                          => $this->purpose,
            'status'                           => 'pending',
            'current_responsibility_center_id' => $user->responsibility_center_id ?? null,
        ]);

        // ✅ Remember which student / faculty request this was forwarded from
        if ($this->forwardedFromId) {
            $request->forceFill(['forwarded_from_request_id' => $this->forwardedFromId])->save();
        }

        if ($this->request_type_id == RequestType::FACILITY) {
            RequestItem::create([
                'request_id'   => $request->id,
                'resource_id'  => null,
                'item_name'    => $this->facility_name,
                'quantity'     => 1,
                'request_date' => $this->request_date,
                'start_time'   => $this->start_time,
                'end_time'     => $this->end_time,
            ]);
        }

        foreach ($selectedMaterials as $material) {
            $resource = Resource::find($material['resource_id']);

            RequestItem::create([
                'request_id'   => $request->id,
                'resource_id'  => $resource->id,
                'item_name'    => $resource->resource_name,
                'quantity'     => $material['quantity'],
                'request_date' => $this->request_date,
                'start_time'   => $this->request_type_id == RequestType::FACILITY ? $this->start_time : null,
                'end_time'     => $this->request_type_id == RequestType::FACILITY ? $this->end_time : null,
                // ✅ Only Pcs items get the return date (the Admin can still adjust it). Ream / Set: none.
                'due_back_at'  => $resource->is_returnable && $this->return_by ? $this->return_by : null,
            ]);
        }

        $typeName = $this->request_type_id == RequestType::FACILITY ? 'Facility Reservation' : 'Material Request';
        $admins   = User::role('admin')->get();

        if ($this->request_type_id == RequestType::FACILITY) {
            $materialsSummary = $selectedMaterials->isNotEmpty()
                ? ' Materials: ' . $selectedMaterials->map(function ($m) {
                    $resource = Resource::find($m['resource_id']);
                    return $resource->resource_name . ' (x' . $m['quantity'] . ')';
                })->implode(', ')
                : '';

            $details = "Facility: {$this->facility_name} on {$this->request_date} ({$this->start_time} - {$this->end_time}).{$materialsSummary}";
        } else {
            $itemsList = $selectedMaterials->map(function ($m) {
                $resource = Resource::find($m['resource_id']);
                return $resource->resource_name . ' (x' . $m['quantity'] . ')';
            })->implode(', ');

            $details = "Items: {$itemsList}";
        }

        // ✅ Tell the Admin when the Program Head will return the Pcs items
        if ($this->hasBorrowItems() && $this->return_by) {
            $details .= ' Will return borrowed items by ' . \Carbon\Carbon::parse($this->return_by)->format('M d, Y') . '.';
        }

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'message' => $user->name . ' (Program Head) submitted a new ' . $typeName . '.',
                'type'    => 'Gmail',
                'status'  => 'pending',
            ]);

            $admin->notify(new ProgramHeadRequestNotification(
                $user->name,
                $typeName,
                $details
            ));
        }

        // ✅ Forwarded: tell the student their Program Head asked the Admin for the items
        if ($this->forwardedFromId) {
            $source = ResourceRequest::find($this->forwardedFromId);

            if ($source) {
                Notification::create([
                    'user_id'    => $source->user_id,
                    'request_id' => $source->id,
                    'message'    => "{$user->name} (Program Head) forwarded your request to the Admin. It will be approved automatically once the Admin approves it.",
                    'type'       => 'Gmail',
                    'status'     => 'pending',
                ]);
            }
        }

        session()->flash('success', 'Request submitted successfully!');
        return redirect()->route('coordinator.request-to-admin.view-request');
    }

    public function cancel()
    {
        return redirect()->route('coordinator.request-to-admin.view-request');
    }
};
