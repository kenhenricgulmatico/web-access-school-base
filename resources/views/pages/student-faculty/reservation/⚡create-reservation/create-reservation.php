<?php

namespace App\Livewire\Portal;

use App\Support\BookingDate;
use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\Notification;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.student-faculty')] class extends Component
{
    public $facility_name = '';
    public $used_date;
    public $start_time = '09:00';
    public $end_time   = '10:00';
    public $purpose    = '';

    public array $facilityOptions    = [];
    public array $materials          = [];
    public $availableResources       = [];

    // ✅ When the borrowed Pcs items will be returned (max 1 month)
    public $return_by = '';

    protected function rules()
    {
        return $this->returnByRules() + [
            'facility_name'           => 'required|string|max:255',
            'used_date'               => BookingDate::rules(),
            'start_time'              => 'required',
            'end_time'                => 'required|after:start_time',
            'purpose'                 => 'required|string|min:10|max:500',
            'materials.*.resource_id' => 'nullable|exists:resources,id',
            'materials.*.quantity'    => 'nullable|integer|min:1',
        ];
    }

    protected $messages = [
        'facility_name.required'         => 'Please select a facility',
        'start_time.required'            => 'Please select start time',
        'end_time.required'              => 'Please select end time',
        'end_time.after'                 => 'End time must be after start time',
        'purpose.required'               => 'Please state your purpose',
        'purpose.min'                    => 'Purpose must be at least 10 characters',
        'materials.*.resource_id.exists' => 'Selected material is invalid.',
        'materials.*.quantity.min'       => 'Quantity must be at least 1.',
    ];

    protected function messages(): array
    {
        // ✅ Booking date: today up to ~6 months ahead (see App\Support\BookingDate)
        return array_merge($this->messages, BookingDate::messages('used_date'), $this->returnByMessages());
    }

    public function mount(): void
    {
        // ✅ Matches seeder: faculty/student get facility-requests.create + material-requests.create
        abort_unless(
            auth()->user()->canAny([
                'facility-requests.create',
                'material-requests.create',
            ]),
            403
        );

        $this->used_date = date('Y-m-d');

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
                  ->orWhere('type_name', 'like', '%Facility%')
            )
            ->where('status', 'available')
            ->orderBy('resource_name')
            ->pluck('resource_name')
            ->toArray();
        }

        // ✅ Only Pcs items (chairs…) — see borrowableResources()
        $this->availableResources = $this->borrowableResources();
    }

    /**
     * ✅ Items a student / faculty may add to a reservation:
     *    - Ream / Set items (bond paper, markers…): ONLY what their department has.
     *    - Pcs items (chairs…): what the department has + what the Admin can still lend.
     *      The department does not need to have them yet — the Program Head can borrow
     *      them from the Admin first (Request to Admin), then approve.
     */
    protected function borrowableResources()
    {
        $departmentId = Auth::user()->department_id;

        if (! $departmentId) {
            return collect();
        }

        return \Illuminate\Support\Facades\DB::table('resources')
            ->join('resource_types', 'resource_types.id', '=', 'resources.resource_type_id')
            ->leftJoin('resource_all_locations', function ($join) use ($departmentId) {
                $join->on('resource_all_locations.resource_id', '=', 'resources.id')
                     ->where('resource_all_locations.department_id', '=', $departmentId);
            })
            ->where('resources.status', 'available')
            ->where('resource_types.type_name', 'not like', '%facilit%')
            ->orderBy('resources.resource_name')
            ->select(
                'resources.id',
                'resources.resource_name',
                'resources.unit',
                'resources.is_returnable',
                // Material: department + Admin  |  Supplies: department only
                \Illuminate\Support\Facades\DB::raw("CASE WHEN resources.is_returnable = 1
                      THEN COALESCE(resource_all_locations.allocated_quantity, 0) + resources.quantity_available
                      ELSE COALESCE(resource_all_locations.allocated_quantity, 0)
                 END as quantity_available")
            )
            ->get()
            ->filter(fn ($resource) => (int) $resource->quantity_available > 0)
            ->values();
    }

    /** The item the user picked (null when it is not available for the department). */
    protected function borrowableResource($resourceId)
    {
        return collect($this->availableResources)->firstWhere('id', (int) $resourceId);
    }

    public function getMaterialOptionsForRow(int $currentIndex)
    {
        $selectedElsewhere = collect($this->materials)
            ->except($currentIndex)
            ->pluck('resource_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        return collect($this->availableResources)
            ->reject(fn ($resource) => in_array((int) $resource->id, $selectedElsewhere))
            ->values();
    }

    public function addMaterial(): void
    {
        $this->materials[] = ['resource_id' => '', 'quantity' => 1];
    }

    public function removeMaterial(int $index): void
    {
        unset($this->materials[$index]);
        $this->materials = array_values($this->materials);
    }

    // ─────────────────────────────────────────────────────────────────
    // LIVE CONFLICT FEEDBACK
    // ─────────────────────────────────────────────────────────────────

    public function updatedFacilityName(): void
    {
        $this->validateFacilityConflictLive();
    }

    public function updatedUsedDate(): void
    {
        $this->validateFacilityConflictLive();
    }

    public function updatedStartTime(): void
    {
        $this->validateTimeRangeLive();
        $this->validateFacilityConflictLive();
    }

    public function updatedEndTime(): void
    {
        $this->validateTimeRangeLive();
        $this->validateFacilityConflictLive();
    }

    protected function validateTimeRangeLive(): void
    {
        $this->resetErrorBag('end_time');

        if ($this->start_time && $this->end_time && $this->end_time <= $this->start_time) {
            $this->addError('end_time', 'End time must be after start time.');
        }
    }

    protected function validateFacilityConflictLive(): void
    {
        $this->resetErrorBag('facility_name');

        if ($message = $this->facilityConflict()) {
            $this->addError('facility_name', $message);
        }
    }

    /**
     * Returns a human-readable conflict message if the chosen
     * facility/date/time overlaps an already-approved reservation.
     */
    protected function facilityConflict(): ?string
    {
        if (!$this->facility_name || !$this->used_date || !$this->start_time || !$this->end_time) {
            return null;
        }

        $conflict = RequestItem::whereNull('resource_id')
            ->where('item_name', $this->facility_name)
            ->whereDate('request_date', $this->used_date)
            ->whereHas('request', fn ($q) => $q->where('status', 'approved'))
            ->where('start_time', '<', $this->end_time)
            ->where('end_time', '>', $this->start_time)
            ->first();

        if ($conflict) {
            $from = \Carbon\Carbon::parse($conflict->start_time)->format('g:i A');
            $to   = \Carbon\Carbon::parse($conflict->end_time)->format('g:i A');
            $date = \Carbon\Carbon::parse($this->used_date)->format('M d, Y');

            return "Sorry, {$this->facility_name} is already booked on {$date} from {$from} to {$to}. Please select another time or date.";
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────
    // SUBMIT
    // ─────────────────────────────────────────────────────────────────

    public function submit()
    {
        // ✅ Server-side guard — creating requires create permission on either type
        abort_unless(
            auth()->user()->canAny([
                'facility-requests.create',
                'material-requests.create',
            ]),
            403
        );

        $this->validate();

        if (is_null(Auth::user()->department_id)) {
            session()->flash('error', 'Your account is not linked to any department. Please contact the admin.');
            return;
        }

        // ── Server-side block — cannot rely on live check alone ──
        if ($message = $this->facilityConflict()) {
            $this->addError('facility_name', $message);
            return;
        }

        $departmentId = Auth::user()->department_id;

        $selectedMaterials = collect($this->materials)
            ->filter(fn($m) => !empty($m['resource_id']))
            ->values();

        foreach ($selectedMaterials as $i => $material) {
            // ✅ Not more than what is available (Pcs: department + Admin, Ream / Set: department)
            $choice = $this->borrowableResource($material['resource_id']);

            if (! $choice) {
                $this->addError("materials.$i.resource_id", 'This material is not available for your department.');
                return;
            }

            if ((int) $material['quantity'] > (int) $choice->quantity_available) {
                $this->addError("materials.$i.quantity", "Not enough {$choice->resource_name} in stock for this quantity. Please request less.");
                return;
            }
        }

        $request = ResourceRequest::create([
            'user_id'                          => Auth::id(),
            'department_id'                    => $departmentId,
            'request_type_id'                  => 1,
            'purpose'                          => $this->purpose,
            'status'                           => 'pending',
            'current_responsibility_center_id' => Auth::user()->responsibility_center_id,
        ]);

        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => null,
            'item_name'    => $this->facility_name,
            'quantity'     => 1,
            'request_date' => $this->used_date,
            'start_time'   => $this->start_time,
            'end_time'     => $this->end_time,
        ]);

        foreach ($selectedMaterials as $material) {
            $resource = Resource::find($material['resource_id']);

            RequestItem::create([
                'request_id'   => $request->id,
                'resource_id'  => $resource->id,
                'item_name'    => $resource->resource_name,
                'quantity'     => $material['quantity'],
                'request_date' => $this->used_date,
                'start_time'   => $this->start_time,
                'end_time'     => $this->end_time,
                // ✅ Only Pcs items get the return date. Ream / Set: none.
                'due_back_at'  => ($resource->is_returnable && $this->return_by) ? $this->return_by : null,
            ]);
        }

        $programHeads = User::role('program head')
            ->where('department_id', $departmentId)
            ->get();

        $materialsSummary = $selectedMaterials->isNotEmpty()
            ? ' with materials: ' . $selectedMaterials->map(function ($m) {
                $resource = Resource::find($m['resource_id']);
                return $resource->resource_name . ' (x' . $m['quantity'] . ')';
            })->implode(', ')
            : '';

        foreach ($programHeads as $programHead) {
            Notification::create([
                'user_id'    => $programHead->id,
                'request_id' => $request->id,
                'message'    => Auth::user()->name . ' submitted a facility reservation for ' . $this->facility_name . ' on ' . $this->used_date . ' (' . $this->start_time . ' - ' . $this->end_time . ')' . $materialsSummary,
                'type'       => 'Gmail',
                'status'     => 'pending',
            ]);
        }

        session()->flash('success', 'Reservation submitted! Waiting for program head approval.');
        return redirect()->route('portal.reservation');
    }

    // ============================================================
    // ✅ RETURN DEADLINE — ONLY for items counted in pieces (Pcs, e.g. chairs)
    //    Ream and Set items are used up, so they never get a return date.
    // ============================================================

    /** Longest a borrower may keep Pcs items: 1 month. */
    public const MAX_BORROW_MONTHS = 1;

    /** True when at least one selected item is a Material (it must be returned). */
    public function hasBorrowItems(): bool
    {
        $ids = collect($this->materials)
            ->pluck('resource_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return $ids && \App\Models\Resource::whereIn('id', $ids)->where('is_returnable', true)->exists();
    }

    /** Earliest return date: the reservation day (never before today). */
    public function returnByMin(): string
    {
        $start = $this->used_date ? \Carbon\Carbon::parse($this->used_date) : today();

        return ($start->lt(today()) ? today() : $start)->format('Y-m-d');
    }

    /** Latest return date: 1 month after the earliest date. */
    public function returnByMax(): string
    {
        return \Carbon\Carbon::parse($this->returnByMin())
            ->addMonthsNoOverflow(self::MAX_BORROW_MONTHS)
            ->format('Y-m-d');
    }

    /** Validation for the return date (only when Pcs items are selected). */
    protected function returnByRules(): array
    {
        return $this->hasBorrowItems()
            ? ['return_by' => 'required|date|after_or_equal:' . $this->returnByMin() . '|before_or_equal:' . $this->returnByMax()]
            : [];
    }

    protected function returnByMessages(): array
    {
        $min = \Carbon\Carbon::parse($this->returnByMin())->format('M d, Y');
        $max = \Carbon\Carbon::parse($this->returnByMax())->format('M d, Y');

        return [
            'return_by.required'        => 'Please choose when you will return the borrowed items.',
            'return_by.date'            => 'Please enter a valid return date.',
            'return_by.after_or_equal'  => "The return date cannot be before {$min}.",
            'return_by.before_or_equal' => "You can borrow items for 1 month only. Please return them by {$max}.",
        ];
    }

    public function cancel()
    {
        return redirect()->route('portal.reservation');
    }
};
