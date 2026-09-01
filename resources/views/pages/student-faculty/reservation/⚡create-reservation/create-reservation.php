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
    public $facility_name = '';
    public $used_date;
    public $start_time = '09:00';
    public $end_time = '10:00';
    public $purpose = '';
    public array $facilityOptions = [];

    // Materials picker: array of ['resource_id' => ?, 'quantity' => 1]
    public array $materials = [];
    public $availableResources = [];

    protected function rules()
    {
        return [
            'facility_name'            => 'required|string|max:255',
            'used_date'                => 'required|date|after_or_equal:today',
            'start_time'               => 'required',
            'end_time'                 => 'required|after:start_time',
            'purpose'                  => 'required|string|min:10|max:500',
            'materials.*.resource_id'  => 'nullable|exists:resources,id',
            'materials.*.quantity'     => 'nullable|integer|min:1',
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

    public function mount()
    {
        $this->used_date = date('Y-m-d');

        $facilities = [];

        for ($floor = 1; $floor <= 4; $floor++) {
            for ($room = 1; $room <= 5; $room++) {
                $roomNumber = ($floor * 100) + $room;
                $facilities[] = 'ROOM ' . $roomNumber;
            }
        }

        for ($floor = 1; $floor <= 4; $floor++) {
            for ($room = 1; $room <= 5; $room++) {
                $roomNumber = ($floor * 100) + $room;
                $facilities[] = 'NBR ' . $roomNumber;
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
        $this->materials[] = ['resource_id' => '', 'quantity' => 1];
    }

    public function removeMaterial(int $index)
    {
        unset($this->materials[$index]);
        $this->materials = array_values($this->materials);
    }

    public function submit()
    {
        $this->validate();

        if (is_null(Auth::user()->department_id)) {
            session()->flash('error', 'Your account is not linked to any department. Please contact the admin.');
            return;
        }

        // Filter out empty material rows (user added a row but left it blank)
        $selectedMaterials = collect($this->materials)
            ->filter(fn ($m) => !empty($m['resource_id']))
            ->values();

        // Validate requested quantity against actual stock
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
            'user_id'                          => Auth::id(),
            'department_id'                    => Auth::user()->department_id,
            'request_type_id'                  => 1, // Facility Reservation (request stays one record; materials tagged per-item via resource_id)
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

        // Notify only program heads from the same department
        $programHeads = User::role('program head')
            ->where('department_id', Auth::user()->department_id)
            ->get();

        $materialsSummary = $selectedMaterials->isNotEmpty()
            ? ' with materials: ' . $selectedMaterials->map(function ($m) {
                $resource = Resource::find($m['resource_id']);
                return $resource->resource_name . ' (x' . $m['quantity'] . ')';
            })->implode(', ')
            : '';

        foreach ($programHeads as $programHead) {
            Notification::create([
                'user_id' => $programHead->id,
                'message' => Auth::user()->name . ' submitted a facility reservation for ' . $this->facility_name . ' on ' . $this->used_date . ' (' . $this->start_time . ' - ' . $this->end_time . ')' . $materialsSummary,
                'type'    => 'Gmail',
                'status'  => 'pending',
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
