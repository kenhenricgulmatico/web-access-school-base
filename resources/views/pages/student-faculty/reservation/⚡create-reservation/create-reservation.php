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

    protected function rules()
    {
        return [
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
        return array_merge($this->messages, BookingDate::messages('used_date'));
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

        $facilityType = ResourceType::where('type_name', 'Facility')->first();

        if ($facilityType) {
            $this->facilityOptions = Resource::where('resource_type_id', $facilityType->id)
                ->where('status', 'available')
                ->orderBy('resource_name')
                ->pluck('resource_name')
                ->toArray();
        } else {
            $this->facilityOptions = Resource::whereHas('resourceType', fn($q) =>
                $q->where('type_name', 'like', '%facility%')
                  ->orWhere('type_name', 'like', '%Facility%')
            )
            ->where('status', 'available')
            ->orderBy('resource_name')
            ->pluck('resource_name')
            ->toArray();
        }

        $departmentId = Auth::user()->department_id;

        $this->availableResources = $departmentId
            ? DB::table('resource_all_locations')
                ->join('resources', 'resources.id', '=', 'resource_all_locations.resource_id')
                ->join('resource_types', 'resource_types.id', '=', 'resources.resource_type_id')
                ->where('resource_all_locations.department_id', $departmentId)
                ->where('resource_all_locations.allocated_quantity', '>', 0)
                ->where('resources.status', 'available')
                ->where('resource_types.type_name', '!=', 'Facility')
                ->select(
                    'resources.id',
                    'resources.resource_name',
                    'resource_all_locations.allocated_quantity as quantity_available'
                )
                ->orderBy('resources.resource_name')
                ->get()
            : collect();
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
            $allocation = DB::table('resource_all_locations')
                ->join('resources', 'resources.id', '=', 'resource_all_locations.resource_id')
                ->where('resource_all_locations.resource_id', $material['resource_id'])
                ->where('resource_all_locations.department_id', $departmentId)
                ->select('resource_all_locations.allocated_quantity', 'resources.resource_name')
                ->first();

            if (!$allocation) {
                $this->addError("materials.$i.resource_id", 'This material is not allocated to your department.');
                return;
            }

            if ((int) $material['quantity'] > $allocation->allocated_quantity) {
                $this->addError("materials.$i.quantity", "Only {$allocation->allocated_quantity} {$allocation->resource_name} available.");
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

    public function cancel()
    {
        return redirect()->route('portal.reservation');
    }
};
