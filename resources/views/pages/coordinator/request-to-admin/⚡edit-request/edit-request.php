<?php

namespace App\Livewire\ProgramHead\RequestToAdmin;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Models\RequestType;
use App\Models\Resource;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.coordinator')] class extends Component
{
    public ResourceRequest $requestModel;

    public $request_type_id = '';
    public $purpose = '';
    public $request_date = '';

    // Facility fields
    public $facility_name = '';
    public $start_time = '09:00';
    public $end_time = '10:00';
    public array $facilityOptions = [];

    // Materials — used for BOTH: optional add-on to a Facility Reservation,
    // AND as the main list when request_type_id == 2 (Material Request)
    public array $materials = [];
    public $availableResources = [];

    public function mount(int $id)
    {
        $request = ResourceRequest::with('items')->findOrFail($id);

        if ($request->user_id !== Auth::id()) {
            abort(403, 'You do not own this request.');
        }

        if ($request->status !== 'pending') {
            session()->flash('error', 'This request can no longer be edited.');
            redirect()->route('coordinator.request-to-admin.view-request');
            return;
        }

        $this->requestModel = $request;

        $this->request_type_id = $request->request_type_id;
        $this->purpose = $request->purpose;

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

        if ($this->request_type_id == 1) {
            $facilityItem = $request->items->firstWhere('resource_id', null);

            if ($facilityItem) {
                $this->facility_name = $facilityItem->item_name;
                $this->start_time    = $facilityItem->start_time;
                $this->end_time      = $facilityItem->end_time;
                $this->request_date  = $facilityItem->request_date;
            }

            $this->materials = $request->items
                ->whereNotNull('resource_id')
                ->map(fn ($item) => [
                    'id'          => $item->id,
                    'resource_id' => $item->resource_id,
                    'quantity'    => $item->quantity,
                ])
                ->values()
                ->toArray();
        }

        if ($this->request_type_id == 2) {
            $firstItem = $request->items->first();
            $this->request_date = $firstItem?->request_date;

            $this->materials = $request->items
                ->map(fn ($item) => [
                    'id'          => $item->id,
                    'resource_id' => $item->resource_id,
                    'quantity'    => $item->quantity,
                ])
                ->values()
                ->toArray();
        }
    }

    protected function rules()
    {
        $rules = [
            'purpose'         => 'required|string|min:10|max:500',
            'request_date'    => 'required|date',
            'materials.*.resource_id' => 'nullable|exists:resources,id',
            'materials.*.quantity'    => 'nullable|integer|min:1',
        ];

        if ($this->request_type_id == 1) {
            $rules['facility_name'] = 'required|string|max:255';
            $rules['start_time']    = 'required';
            $rules['end_time']      = 'required|after:start_time';
        }

        if ($this->request_type_id == 2) {
            $rules['materials'] = 'required|array|min:1';
            $rules['materials.*.resource_id'] = 'required|exists:resources,id';
            $rules['materials.*.quantity']    = 'required|integer|min:1';
        }

        return $rules;
    }

    protected $messages = [
        'purpose.required'         => 'Please state your purpose',
        'purpose.min'              => 'Purpose must be at least 10 characters',
        'request_date.required'    => 'Please select a date',
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

    #[Computed]
    public function requestTypes()
    {
        return RequestType::all();
    }

    public function addMaterial()
    {
        $this->materials[] = ['id' => null, 'resource_id' => '', 'quantity' => 1];
    }

    public function removeMaterial(int $index)
    {
        unset($this->materials[$index]);
        $this->materials = array_values($this->materials);
    }

    public function update()
    {
        $this->validate();

        $selectedMaterials = collect($this->materials)
            ->filter(fn ($m) => !empty($m['resource_id']))
            ->values();

        // Validate stock for any materials being requested
        foreach ($selectedMaterials as $i => $material) {
            $resource = Resource::find($material['resource_id']);

            if (!$resource) {
                $this->addError("materials.$i.resource_id", 'This material is no longer available.');
                return;
            }

            // Allow up to (currently available + whatever this item already holds)
            $alreadyHeld = ($material['id'] ?? null)
                ? RequestItem::find($material['id'])?->quantity ?? 0
                : 0;

            if ((int) $material['quantity'] > ($resource->quantity_available + $alreadyHeld)) {
                $this->addError("materials.$i.quantity", "Only {$resource->quantity_available} {$resource->resource_name} available.");
                return;
            }
        }

        $this->requestModel->update([
            'purpose' => $this->purpose,
        ]);

        if ($this->request_type_id == 1) {
            $facilityItem = $this->requestModel->items()->whereNull('resource_id')->first();

            $facilityData = [
                'item_name'    => $this->facility_name,
                'quantity'     => 1,
                'request_date' => $this->request_date,
                'start_time'   => $this->start_time,
                'end_time'     => $this->end_time,
            ];

            if ($facilityItem) {
                $facilityItem->update($facilityData);
            } else {
                RequestItem::create(array_merge($facilityData, [
                    'request_id'  => $this->requestModel->id,
                    'resource_id' => null,
                ]));
            }
        }

        if ($this->request_type_id == 2) {
            // Update request_date on all material items
            $this->requestModel->items()->update(['request_date' => $this->request_date]);
        }

        // Materials: delete removed rows, update existing, create new ones
        $keptIds = $selectedMaterials->pluck('id')->filter()->values();

        $this->requestModel->items()
            ->whereNotNull('resource_id')
            ->whereNotIn('id', $keptIds)
            ->delete();

        foreach ($selectedMaterials as $material) {
            $resource = Resource::find($material['resource_id']);

            $itemData = [
                'resource_id'  => $resource->id,
                'item_name'    => $resource->resource_name,
                'quantity'     => $material['quantity'],
                'request_date' => $this->request_date,
                'start_time'   => $this->request_type_id == 1 ? $this->start_time : null,
                'end_time'     => $this->request_type_id == 1 ? $this->end_time : null,
            ];

            if (!empty($material['id'])) {
                RequestItem::where('id', $material['id'])->update($itemData);
            } else {
                RequestItem::create(array_merge($itemData, ['request_id' => $this->requestModel->id]));
            }
        }

        session()->flash('success', 'Request updated successfully!');
        return redirect()->route('coordinator.request-to-admin.view-request');
    }

    public function cancel()
    {
        return redirect()->route('coordinator.request-to-admin.view-request');
    }
};
