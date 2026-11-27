<?php

namespace App\Livewire\Admin\Schedule;

use App\Models\Resource;
use App\Support\Facilities;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public Resource $resource;

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

    public function mount(int $id)
    {
        abort_unless(auth()->user()->can('facility-schedule.update'), 403);

        // ✅ Only facility rooms can be edited here (not Chair, Bond Paper…)
        $this->resource = Facilities::query()->findOrFail($id);

        $this->facility_name      = $this->resource->resource_name ?? '';
        $this->room_kind          = $this->resource->room_kind ?? '';
        $this->capacity           = $this->resource->capacity ?? '';
        $this->location           = $this->resource->location ?? '';
        $this->status             = $this->resource->status ?? 'available';
        $this->maintenance_reason = $this->resource->maintenance_reason ?? '';
        $this->maintenance_until  = $this->resource->maintenance_until?->toDateString() ?? '';
    }

    #[Computed]
    public function locations(): array
    {
        return Facilities::query()->whereNotNull('location')->distinct()->orderBy('location')->pluck('location')->all();
    }

    /** Bookings that keep following this room when it is renamed. */
    #[Computed]
    public function upcomingCount(): int
    {
        return Facilities::upcomingBookings($this->resource)->count();
    }

    public function submit()
    {
        abort_unless(auth()->user()->can('facility-schedule.update'), 403);

        $this->facility_name = Facilities::cleanName($this->facility_name);
        $this->location      = Facilities::cleanName($this->location);

        // an "until" date that has already passed may stay as it was
        $rules = Facilities::rules();
        if ($this->maintenance_until !== '' && $this->maintenance_until === $this->resource->maintenance_until?->toDateString()) {
            $rules['maintenance_until'] = 'nullable|date';
        }
        $this->validate($rules);

        if (Facilities::nameTaken($this->facility_name, $this->resource->id)) {
            $this->addError('facility_name', "There is already a facility called \"{$this->facility_name}\".");
            return;
        }

        $wasAvailable  = $this->resource->status !== 'maintenance';
        $isMaintenance = $this->status === 'maintenance';
        $oldName       = $this->resource->resource_name;

        $moved = DB::transaction(function () use ($isMaintenance) {
            // ✅ rename first — its bookings move to the new name
            $moved = Facilities::rename($this->resource, $this->facility_name);

            $this->resource->update([
                'capacity'           => $this->capacity !== '' && $this->capacity !== null ? (int) $this->capacity : null,
                'location'           => $this->location !== '' ? $this->location : null,
                'room_kind'          => $this->room_kind !== '' ? $this->room_kind : null,
                'status'             => $this->status,
                'maintenance_reason' => $isMaintenance && $this->maintenance_reason !== '' ? $this->maintenance_reason : null,
                'maintenance_until'  => $isMaintenance && $this->maintenance_until !== '' ? $this->maintenance_until : null,
            ]);

            return $moved;
        });

        $message = "{$this->resource->resource_name} updated.";
        if ($moved) {
            $message .= " Renamed from \"{$oldName}\" — its {$moved} " . \Illuminate\Support\Str::plural('booking', $moved) . ' moved with it.';
        }

        // ✅ Going under maintenance here tells the people who booked it, like the list does
        if ($wasAvailable && $isMaintenance) {
            $affected = Facilities::notifyMaintenance($this->resource->fresh());
            $message .= $affected->isEmpty() ? ' It had no upcoming bookings.'
                : " {$affected->count()} upcoming " . \Illuminate\Support\Str::plural('booking', $affected->count()) . ' affected — the people who booked were notified.';
        }

        session()->flash('success', $message);
        return redirect()->route('admin.schedule');
    }

    public function cancel()
    {
        return redirect()->route('admin.schedule');
    }
};
