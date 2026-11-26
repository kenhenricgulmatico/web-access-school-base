<?php

namespace App\Livewire\Admin\Schedule;

use App\Models\Resource;
use App\Models\ResourceType;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public string $facility_name = '';
    public string $status        = 'available';

    protected array $rules = [
        'facility_name' => 'required|string|max:255',
        'status'        => 'required|in:available,maintenance',
    ];

    protected array $messages = [
        'facility_name.required' => 'Please enter the facility name',
        'status.required'        => 'Please select a status',
    ];

    public function submit()
    {
        $this->validate();

        // Facilities are tagged under a shared "Facility" resource type so
        // Student/Faculty and Program Head pickers pull from the same list.
        $facilityType = ResourceType::firstOrCreate(
            ['type_name' => \App\Models\ResourceType::FACILITY]
        );

        Resource::create([
            'resource_type_id'   => $facilityType->id,
            'resource_name'      => $this->facility_name,
            'description'        => '',
            'quantity_available' => 1,
            'status'             => $this->status,
        ]);

        session()->flash('success', 'Facility added successfully.');
        return redirect()->route('admin.schedule');
    }

    public function cancel()
    {
        return redirect()->route('admin.schedule');
    }
};
