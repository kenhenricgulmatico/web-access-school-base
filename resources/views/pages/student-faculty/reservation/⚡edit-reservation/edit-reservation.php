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

new #[Layout('layouts.student-faculty')] class extends Component
{
    public $reservation_id;
    public $facility_name = '';
    public $used_date;
    public $start_time = '09:00';
    public $end_time = '10:00';
    public $purpose = '';
    public array $facilityOptions = [];
    public $availableResources = [];
    public array $materials = [];

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
        'facility_name.required'   => 'Please select a facility',
        'start_time.required'      => 'Please select start time',
        'end_time.required'        => 'Please select end time',
        'end_time.after'           => 'End time must be after start time',
        'purpose.required'         => 'Please state your purpose',
        'purpose.min'              => 'Purpose must be at least 10 characters',
        'materials.*.resource_id.exists' => 'Selected material is invalid.',
        'materials.*.quantity.min' => 'Quantity must be at least 1.',
    ];

    protected function messages(): array
    {
        // ✅ Booking date: today up to ~6 months ahead (see App\Support\BookingDate)
        return array_merge($this->messages, BookingDate::messages('used_date'), $this->returnByMessages());
    }

    public function mount($id)
    {
        // ✅ Matches seeder: faculty/student get facility-requests.update + material-requests.update
        abort_unless(
            auth()->user()->canAny([
                'facility-requests.update',
                'material-requests.update',
            ]),
            403
        );

        $request = ResourceRequest::with('items')->findOrFail($id);

        if ($request->user_id !== Auth::id()) {
            session()->flash('error', 'You are not authorized to edit this reservation.');
            return redirect()->route('portal.reservation');
        }

        if ($request->status !== 'pending') {
            session()->flash('error', 'Only pending reservations can be edited.');
            return redirect()->route('portal.reservation');
        }

        $this->reservation_id = $request->id;

        $facilityItem  = $request->items->firstWhere('resource_id', null);
        $materialItems = $request->items->whereNotNull('resource_id');

        $this->facility_name = $facilityItem->item_name ?? '';
        $this->used_date     = optional($facilityItem)->request_date ?? date('Y-m-d');
        $this->start_time    = optional($facilityItem)->start_time ?? '09:00';
        $this->end_time      = optional($facilityItem)->end_time ?? '10:00';
        $this->purpose       = $request->purpose;

        $this->materials = $materialItems->map(fn ($item) => [
            'item_id'     => $item->id,
            'resource_id' => $item->resource_id,
            'quantity'    => $item->quantity,
        ])->values()->toArray();

        // ✅ Return date chosen earlier (saved on the Pcs items only)
        $saved = $materialItems->whereNotNull('due_back_at')->first()?->due_back_at;
        $this->return_by = $saved ? $saved->format('Y-m-d') : '';

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

        if ($this->facility_name && !in_array($this->facility_name, $this->facilityOptions)) {
            $this->facilityOptions[] = $this->facility_name;
            sort($this->facilityOptions);
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
            ->where('resource_types.type_name', 'not like', '%facility%')
            ->orderBy('resources.resource_name')
            ->select(
                'resources.id',
                'resources.resource_name',
                'resources.unit',
                // Pcs: department + Admin  |  Ream / Set: department only
                \Illuminate\Support\Facades\DB::raw("CASE WHEN resources.unit = 'Pcs'
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

    public function addMaterial()
    {
        $this->materials[] = ['item_id' => null, 'resource_id' => '', 'quantity' => 1];
    }

    public function removeMaterial(int $index)
    {
        unset($this->materials[$index]);
        $this->materials = array_values($this->materials);
    }

    public function update()
    {
        // ✅ Server-side guard — updating requires update permission on either type
        abort_unless(
            auth()->user()->canAny([
                'facility-requests.update',
                'material-requests.update',
            ]),
            403
        );

        $this->validate();

        $request = ResourceRequest::with('items')->findOrFail($this->reservation_id);

        if ($request->user_id !== Auth::id() || $request->status !== 'pending') {
            session()->flash('error', 'This reservation can no longer be edited.');
            return redirect()->route('portal.reservation');
        }

        if ($this->facility_name && $this->used_date && $this->start_time && $this->end_time) {
            $conflict = \DB::table('request_items as ri')
                ->join('requests as req', 'req.id', '=', 'ri.request_id')
                ->where('req.status', 'approved')
                ->where('req.id', '!=', $request->id)
                ->whereNull('ri.resource_id')
                ->where('ri.item_name', $this->facility_name)
                ->whereDate('ri.request_date', $this->used_date)
                ->where('ri.start_time', '<', $this->end_time)
                ->where('ri.end_time', '>', $this->start_time)
                ->exists();

            if ($conflict) {
                $this->addError('start_time', 'This facility is already booked for an overlapping time. Please choose another slot.');
                return;
            }
        }

        $selectedMaterials = collect($this->materials)
            ->filter(fn ($m) => !empty($m['resource_id']))
            ->values();

        foreach ($selectedMaterials as $i => $material) {
            $resource = Resource::find($material['resource_id']);

            if (!$resource) {
                $this->addError("materials.$i.resource_id", 'This material is no longer available.');
                return;
            }

            // ✅ Not more than what is available (Pcs: department + Admin, Ream / Set: department)
            $choice = $this->borrowableResource($material['resource_id']);

            if (! $choice) {
                $this->addError("materials.$i.resource_id", 'This material is not available for your department.');
                return;
            }

            if ((int) $material['quantity'] > (int) $choice->quantity_available) {
                $this->addError("materials.$i.quantity", "Only {$choice->quantity_available} {$choice->resource_name} available.");
                return;
            }
        }

        $facilityItem = $request->items->firstWhere('resource_id', null);
        if ($facilityItem) {
            $facilityItem->update([
                'item_name'    => $this->facility_name,
                'request_date' => $this->used_date,
                'start_time'   => $this->start_time,
                'end_time'     => $this->end_time,
            ]);
        }

        $keptItemIds = [];

        foreach ($selectedMaterials as $material) {
            $resource = Resource::find($material['resource_id']);

            if (!empty($material['item_id'])) {
                $item = RequestItem::find($material['item_id']);
                if ($item) {
                    $item->update([
                        'resource_id'  => $resource->id,
                        'item_name'    => $resource->resource_name,
                        'quantity'     => $material['quantity'],
                        'request_date' => $this->used_date,
                        'start_time'   => $this->start_time,
                        'end_time'     => $this->end_time,
                        // ✅ Only Pcs items get the return date. Ream / Set: none.
                        'due_back_at'  => ($resource->unit === 'Pcs' && $this->return_by) ? $this->return_by : null,
                    ]);
                    $keptItemIds[] = $item->id;
                }
            } else {
                $newItem = RequestItem::create([
                    'request_id'   => $request->id,
                    'resource_id'  => $resource->id,
                    'item_name'    => $resource->resource_name,
                    'quantity'     => $material['quantity'],
                    'request_date' => $this->used_date,
                    'start_time'   => $this->start_time,
                    'end_time'     => $this->end_time,
                    // ✅ Only Pcs items get the return date. Ream / Set: none.
                    'due_back_at'  => ($resource->unit === 'Pcs' && $this->return_by) ? $this->return_by : null,
                ]);
                $keptItemIds[] = $newItem->id;
            }
        }

        $request->items()
            ->whereNotNull('resource_id')
            ->whereNotIn('id', $keptItemIds)
            ->delete();

        $request->update(['purpose' => $this->purpose]);

        $programHeads = User::role('program head')
            ->where('department_id', Auth::user()->department_id)
            ->get();

        foreach ($programHeads as $programHead) {
            Notification::create([
                'user_id'    => $programHead->id,
                'request_id' => $request->id,
                'message'    => Auth::user()->name . ' updated their facility reservation for ' . $this->facility_name . ' on ' . $this->used_date . ' (' . $this->start_time . ' - ' . $this->end_time . ')',
                'type'       => 'Gmail',
                'status'     => 'pending',
            ]);
        }

        session()->flash('success', 'Reservation updated successfully!');

        return redirect()->route('portal.reservation');
    }

    // ============================================================
    // ✅ RETURN DEADLINE — ONLY for items counted in pieces (Pcs, e.g. chairs)
    //    Ream and Set items are used up, so they never get a return date.
    // ============================================================

    /** Longest a borrower may keep Pcs items: 1 month. */
    public const MAX_BORROW_MONTHS = 1;

    /** True when at least one selected item has unit "Pcs" (it must be returned). */
    public function hasBorrowItems(): bool
    {
        $ids = collect($this->materials)
            ->pluck('resource_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return $ids && \App\Models\Resource::whereIn('id', $ids)->where('unit', 'Pcs')->exists();
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
