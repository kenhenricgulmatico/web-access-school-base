<?php

namespace App\Livewire\Admin\Schedule;

use App\Models\Resource;
use App\Models\ResourceType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public string $search = '';

    public function updatingSearch() {}

    /** ✅ Bookings of this room from today on (approved or still waiting for a decision). */
    protected function upcomingBookings(Resource $facility)
    {
        return \App\Models\RequestItem::whereNull('resource_id')
            ->where('item_name', $facility->resource_name)
            ->whereDate('request_date', '>=', today())
            ->whereHas('request', fn ($q) => $q->whereIn('status', ['pending', 'approved']))
            ->with('request.user')
            ->orderBy('request_date')
            ->orderBy('start_time')
            ->get();
    }

    /** How many upcoming bookings each facility has (shown next to Delete / Maintenance). */
    #[Computed]
    public function upcomingCounts(): array
    {
        return \App\Models\RequestItem::whereNull('resource_id')
            ->whereDate('request_date', '>=', today())
            ->whereHas('request', fn ($q) => $q->whereIn('status', ['pending', 'approved']))
            ->selectRaw('item_name, COUNT(*) as n')
            ->groupBy('item_name')
            ->pluck('n', 'item_name')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    /** ✅ A facility with upcoming bookings can't be deleted. */
    public function delete(int $id)
    {
        abort_unless(auth()->user()->can('facility-schedule.delete'), 403);

        $facility = Resource::findOrFail($id);
        $upcoming = $this->upcomingBookings($facility);

        if ($upcoming->isNotEmpty()) {
            $first = \Carbon\Carbon::parse($upcoming->first()->request_date)->format('M d, Y');
            session()->flash('error', "Can't delete {$facility->resource_name}: it has {$upcoming->count()} upcoming "
                . \Illuminate\Support\Str::plural('booking', $upcoming->count()) . " (the next on {$first}). "
                . 'Put it in maintenance or wait until they have passed.');
            return;
        }

        $facility->delete();
        session()->flash('success', 'Facility deleted successfully.');
    }

    /**
     * ✅ Available ↔ maintenance. Going into maintenance tells everyone with an upcoming
     *    booking of this room (and the Admin sees how many were affected).
     */
    public function toggleStatus(int $id)
    {
        abort_unless(auth()->user()->can('facility-schedule.update'), 403);

        $facility = Resource::findOrFail($id);
        $facility->status = $facility->status === 'available' ? 'maintenance' : 'available';
        $facility->save();

        if ($facility->status !== 'maintenance') {
            session()->flash('success', "{$facility->resource_name} is available again.");
            return;
        }

        $upcoming = $this->upcomingBookings($facility);

        foreach ($upcoming->groupBy(fn ($i) => $i->request?->user_id) as $userId => $items) {
            if (! $userId) {
                continue;
            }

            $dates = $items->map(fn ($i) => \Carbon\Carbon::parse($i->request_date)->format('M d'))->unique()->implode(', ');

            \App\Models\Notification::create([
                'user_id'    => $userId,
                'request_id' => $items->first()->request_id,
                'message'    => "{$facility->resource_name} is now under maintenance. Your booking on {$dates} may be affected — please contact the Admin or book another room.",
                'type'       => 'Gmail',
                'status'     => 'pending',
            ]);
        }

        session()->flash('success', $upcoming->isEmpty()
            ? "{$facility->resource_name} is now under maintenance. It had no upcoming bookings."
            : "{$facility->resource_name} is now under maintenance. {$upcoming->count()} upcoming "
              . \Illuminate\Support\Str::plural('booking', $upcoming->count()) . ' affected — the people who booked were notified.');
    }

    /**
     * All facilities (resources tagged under the "Facility" type),
     * matching the search filter, sorted by name.
     */
    #[Computed]
public function facilities()
{
    $facilityType = ResourceType::where('type_name', \App\Models\ResourceType::FACILITY)->first();

    // No "Facility" type exists yet -> there can be no facilities. Return empty, not everything.
    if (! $facilityType) {
        return collect();
    }

    return Resource::query()
        ->where('resource_type_id', $facilityType->id)
        ->when(
            $this->search,
            fn($q) => $q->where('resource_name', 'like', '%' . $this->search . '%')
        )
        ->orderBy('resource_name')
        ->get();
}
};
