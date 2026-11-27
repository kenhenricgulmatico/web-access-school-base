<?php

namespace App\Livewire\Admin\Schedule;

use App\Models\Resource;
use App\Models\ResourceType;
use App\Support\Facilities;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public string $facility_name      = '';
    public string $room_kind          = '';
    public        $capacity           = '';
    public string $location           = '';
    public string $status             = 'available';
    public string $maintenance_reason = '';
    public string $maintenance_until  = '';

    protected array $messages = [
        'facility_name.required'           => 'Please enter the facility name',
        'status.required'                  => 'Please select a status',
        'maintenance_until.after_or_equal' => 'The "until" date cannot be in the past.',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('facility-schedule.create'), 403);
    }

    /** Buildings already used (suggestions). */
    #[Computed]
    public function locations(): array
    {
        return Facilities::query()->whereNotNull('location')->distinct()->orderBy('location')->pluck('location')->all();
    }

    public function submit()
    {
        abort_unless(auth()->user()->can('facility-schedule.create'), 403);

        $this->facility_name = Facilities::cleanName($this->facility_name);
        $this->location      = Facilities::cleanName($this->location);
        $this->validate(Facilities::rules());

        // ✅ no second "Room 101" (upper/lower case and spaces ignored)
        if (Facilities::nameTaken($this->facility_name)) {
            $this->addError('facility_name', "There is already a facility called \"{$this->facility_name}\".");
            return;
        }

        $facilityType = ResourceType::firstOrCreate(['type_name' => ResourceType::FACILITY]);
        $isMaintenance = $this->status === 'maintenance';

        Resource::create([
            'resource_type_id'   => $facilityType->id,
            'resource_name'      => $this->facility_name,
            'description'        => '',
            'quantity_available' => 1,
            'capacity'           => $this->capacity !== '' && $this->capacity !== null ? (int) $this->capacity : null,
            'location'           => $this->location !== '' ? $this->location : null,
            'room_kind'          => $this->room_kind !== '' ? $this->room_kind : null,
            'status'             => $this->status,
            'maintenance_reason' => $isMaintenance && $this->maintenance_reason !== '' ? $this->maintenance_reason : null,
            'maintenance_until'  => $isMaintenance && $this->maintenance_until !== '' ? $this->maintenance_until : null,
        ]);

        session()->flash('success', "{$this->facility_name} added.");
        return redirect()->route('admin.schedule');
    }

    public function cancel()
    {
        return redirect()->route('admin.schedule');
    }
};
