<?php

namespace App\Livewire\Portal;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Models\Resource;
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

    // ['item_id' => existing RequestItem id or null, 'resource_id' => .., 'quantity' => ..]
    public array $materials = [];

    protected function rules()
    {
        return [
            'facility_name'           => 'required|string|max:255',
            'used_date'               => 'required|date|after_or_equal:today',
            'start_time'              => 'required',
            'end_time'                => 'required|after:start_time',
            'purpose'                 => 'required|string|min:10|max:500',
            'materials.*.resource_id' => 'nullable|exists:resources,id',
            'materials.*.quantity'    => 'nullable|integer|min:1',
        ];
    }

    protected $messages = [
        'facility_name.required'   => 'Please select a facility',
        'used_date.required'       => 'Please select a date',
        'used_date.after_or_equal' => 'Date must be today or later',
        'start_time.required'      => 'Please select start time',
        'end_time.required'        => 'Please select end time',
        'end_time.after'           => 'End time must be after start time',
        'purpose.required'         => 'Please state your purpose',
        'purpose.min'              => 'Purpose must be at least 10 characters',
        'materials.*.resource_id.exists' => 'Selected material is invalid.',
        'materials.*.quantity.min' => 'Quantity must be at least 1.',
    ];

    public function mount($id)
    {
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

        // Same facility list as Create form
        $facilities = [];

        for ($floor = 1; $floor <= 4; $floor++) {
            for ($room = 1; $room <= 5; $room++) {
                $facilities[] = 'ROOM ' . (($floor * 100) + $room);
            }
        }

        for ($floor = 1; $floor <= 4; $floor++) {
            for ($room = 1; $room <= 5; $room++) {
                $facilities[] = 'NBR ' . (($floor * 100) + $room);
            }
        }

        for ($i = 1; $i <= 3; $i++) {
            $facilities[] = 'LAB ' . $i;
        }

        $facilities[] = 'LISC';

        $this->facilityOptions = $facilities;

        $this->availableResources = Resource::where('status', 'available')
            ->where('quantity_available', '>', 0)
            ->orderBy('resource_name')
            ->get();
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
        $this->validate();

        $request = ResourceRequest::with('items')->findOrFail($this->reservation_id);

        if ($request->user_id !== Auth::id() || $request->status !== 'pending') {
            session()->flash('error', 'This reservation can no longer be edited.');
            return redirect()->route('portal.reservation');
        }

        $selectedMaterials = collect($this->materials)
            ->filter(fn ($m) => !empty($m['resource_id']))
            ->values();

        // Validate stock — but credit back whatever this item already holds,
        // so editing an existing material line doesn't wrongly fail against itself.
        foreach ($selectedMaterials as $i => $material) {
            $resource = Resource::find($material['resource_id']);

            if (!$resource) {
                $this->addError("materials.$i.resource_id", 'This material is no longer available.');
                return;
            }

            $existingQty = 0;
            if (!empty($material['item_id'])) {
                $existingItem = $request->items->firstWhere('id', $material['item_id']);
                if ($existingItem && $existingItem->resource_id == $resource->id) {
                    $existingQty = $existingItem->quantity;
                }
            }

            $effectivelyAvailable = $resource->quantity_available + $existingQty;

            if ((int) $material['quantity'] > $effectivelyAvailable) {
                $this->addError("materials.$i.quantity", "Only {$effectivelyAvailable} {$resource->resource_name} available.");
                return;
            }
        }

        // Update the facility line item
        $facilityItem = $request->items->firstWhere('resource_id', null);
        if ($facilityItem) {
            $facilityItem->update([
                'item_name'    => $this->facility_name,
                'request_date' => $this->used_date,
                'start_time'   => $this->start_time,
                'end_time'     => $this->end_time,
            ]);
        }

        // Sync material line items: update existing, create new, delete removed
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
                ]);
                $keptItemIds[] = $newItem->id;
            }
        }

        // Remove material items that were deleted in the form
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
                'user_id' => $programHead->id,
                'message' => Auth::user()->name . ' updated their facility reservation for ' . $this->facility_name . ' on ' . $this->used_date . ' (' . $this->start_time . ' - ' . $this->end_time . ')',
                'type'    => 'Gmail',
                'status'  => 'pending',
            ]);
        }

        session()->flash('success', 'Reservation updated successfully!');

        return redirect()->route('portal.reservation');
    }

    public function cancel()
    {
        return redirect()->route('portal.reservation');
    }
};
