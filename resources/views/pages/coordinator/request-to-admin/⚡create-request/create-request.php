<?php

namespace App\Livewire\ProgramHead\RequestToAdmin;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Models\RequestType;
use App\Models\Resource;
use App\Models\Notification;
use App\Models\User;
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

    // Materials — used for BOTH: optional add-on to a Facility Reservation,
    // AND as the main list when request_type_id == 2 (Material Request)
    public array $materials = [];
    public $availableResources = [];

    protected function rules()
    {
        $rules = [
            'request_type_id' => 'required|exists:request_types,id',
            'purpose'         => 'required|string|min:10|max:500',
            'request_date'    => 'required|date|after_or_equal:today',
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
        'request_type_id.required' => 'Please select a request type',
        'purpose.required'         => 'Please state your purpose',
        'purpose.min'              => 'Purpose must be at least 10 characters',
        'request_date.required'    => 'Please select a date',
        'request_date.after_or_equal' => 'Date must be today or later',
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

    public function mount()
    {
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

    #[Computed]
    public function requestTypes()
    {
        return RequestType::all();
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

    public function updatedRequestTypeId()
    {
        $this->reset(['facility_name', 'start_time', 'end_time', 'materials']);
        $this->start_time = '09:00';
        $this->end_time   = '10:00';
    }

    public function submit()
    {
        $this->validate();

        $user = Auth::user();

        if (!$user->department_id) {
            session()->flash('error', 'Your account has no department assigned. Please contact the admin to assign your department before submitting a request.');
            return;
        }

        $selectedMaterials = collect($this->materials)
            ->filter(fn ($m) => !empty($m['resource_id']))
            ->values();

        // Validate stock for any materials being requested (facility add-on or standalone)
        foreach ($selectedMaterials as $i => $material) {
            $resource = Resource::find($material['resource_id']);

            if (!$resource) {
                $this->addError("materials.$i.resource_id", 'This material is no longer available.');
                return;
            }

            if ((int) $material['quantity'] > $resource->quantity_available) {
                $this->addError("materials.$i.quantity", "Only {$resource->quantity_available} {$resource->resource_name} available.");
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

        if ($this->request_type_id == 1) {
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

        // Materials: applies whether it's an add-on to Facility (type 1)
        // or the main content of a Material Request (type 2)
        foreach ($selectedMaterials as $material) {
            $resource = Resource::find($material['resource_id']);

            RequestItem::create([
                'request_id'   => $request->id,
                'resource_id'  => $resource->id,
                'item_name'    => $resource->resource_name,
                'quantity'     => $material['quantity'],
                'request_date' => $this->request_date,
                'start_time'   => $this->request_type_id == 1 ? $this->start_time : null,
                'end_time'     => $this->request_type_id == 1 ? $this->end_time : null,
            ]);
        }

        $typeName = $this->request_type_id == 1 ? 'Facility Reservation' : 'Material Request';
        $admins   = User::role('admin')->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'message' => $user->name . ' (Program Head) submitted a new ' . $typeName . '.',
                'type'    => 'Gmail',
                'status'  => 'pending',
            ]);
        }

        session()->flash('success', 'Request submitted successfully!');
        return redirect()->route('coordinator.request-to-admin.view-request');
    }

    public function cancel()
    {
        return redirect()->route('coordinator.request-to-admin.view-request');
    }
};
