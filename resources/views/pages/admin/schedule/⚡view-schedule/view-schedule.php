<?php

namespace App\Livewire\Admin\Schedule;

use App\Models\RequestItem;
use App\Models\Resource;
use App\Support\Facilities;
use App\Support\TimeSlots;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search       = '';
    #[Url(as: 'status', except: '')]
    public string $statusFilter = '';   // '' | available | maintenance | booked-today
    #[Url(as: 'kind', except: '')]
    public string $kindFilter   = '';
    #[Url(as: 'building', except: '')]
    public string $buildingFilter = '';

    // ✅ "Put under maintenance" form (reason + until)
    public ?int   $maintenanceId      = null;
    public string $maintenance_reason = '';
    public string $maintenance_until  = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('facility-schedule.view') || auth()->user()->can('facility-schedule.update'), 403);
    }

    public function updatingSearch()         { $this->resetPage(); }
    public function updatingStatusFilter()   { $this->resetPage(); }
    public function updatingKindFilter()     { $this->resetPage(); }
    public function updatingBuildingFilter() { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset('search', 'statusFilter', 'kindFilter', 'buildingFilter');
        $this->resetPage();
    }

    /** Card click: filter by it (click again to show all). */
    public function filterStatus(string $status): void
    {
        $this->statusFilter = $status === $this->statusFilter ? '' : $status;
        $this->resetPage();
    }

    /** Room names booked today (approved). */
    #[Computed]
    public function bookedTodayNames(): array
    {
        return RequestItem::whereNull('resource_id')
            ->whereDate('request_date', today())
            ->whereHas('request', fn ($q) => TimeSlots::withoutDuplicateCopies($q->where('status', 'approved'), ['approved']))
            ->distinct()
            ->pluck('item_name')
            ->map(fn ($n) => mb_strtolower($n))
            ->all();
    }

    /** ✅ Summary cards. */
    #[Computed]
    public function counts(): array
    {
        $rooms = Facilities::query()->get(['resource_name', 'status']);

        return [
            'all'          => $rooms->count(),
            'available'    => $rooms->where('status', 'available')->count(),
            'maintenance'  => $rooms->where('status', 'maintenance')->count(),
            'booked-today' => $rooms->filter(fn ($r) => in_array(mb_strtolower($r->resource_name), $this->bookedTodayNames, true))->count(),
        ];
    }

    #[Computed]
    public function kinds(): array
    {
        return Facilities::query()->whereNotNull('room_kind')->distinct()->orderBy('room_kind')->pluck('room_kind')->all();
    }

    #[Computed]
    public function buildings(): array
    {
        return Facilities::query()->whereNotNull('location')->distinct()->orderBy('location')->pluck('location')->all();
    }

    /** Facilities, filtered, by name, 12 per page. */
    #[Computed]
    public function facilities()
    {
        return Facilities::query()
            ->when($this->search !== '', fn ($q) => $q->where(fn ($s) => $s
                ->where('resource_name', 'like', '%' . $this->search . '%')
                ->orWhere('location', 'like', '%' . $this->search . '%')
                ->orWhere('room_kind', 'like', '%' . $this->search . '%')))
            ->when(in_array($this->statusFilter, ['available', 'maintenance'], true), fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->statusFilter === 'booked-today', fn ($q) => $q->whereIn('resource_name', $this->bookedTodayNames ?: ['']))
            ->when($this->kindFilter !== '', fn ($q) => $q->where('room_kind', $this->kindFilter))
            ->when($this->buildingFilter !== '', fn ($q) => $q->where('location', $this->buildingFilter))
            ->orderBy('resource_name')
            ->paginate(12);
    }

    /** How many upcoming bookings each facility has (shown next to Delete / Maintenance). */
    #[Computed]
    public function upcomingCounts(): array
    {
        return RequestItem::whereNull('resource_id')
            ->whereDate('request_date', '>=', today())
            ->whereHas('request', fn ($q) => TimeSlots::withoutDuplicateCopies($q->whereIn('status', ['pending', 'approved']), ['pending', 'approved']))
            ->selectRaw('item_name, COUNT(*) as n')
            ->groupBy('item_name')
            ->pluck('n', 'item_name')
            ->mapWithKeys(fn ($n, $name) => [mb_strtolower($name) => (int) $n])
            ->all();
    }

    public function upcomingFor(Resource $room): int
    {
        return $this->upcomingCounts[mb_strtolower($room->resource_name)] ?? 0;
    }

    /** ✅ Next approved booking of each room on this page: lower(name) => item. */
    #[Computed]
    public function nextBookings(): array
    {
        $names = $this->facilities->pluck('resource_name')->all();
        if (! $names) {
            return [];
        }

        $now = now();

        return RequestItem::whereNull('resource_id')
            ->whereIn('item_name', $names)
            ->whereDate('request_date', '>=', today())
            ->whereHas('request', fn ($q) => TimeSlots::withoutDuplicateCopies($q->where('status', 'approved'), ['approved']))
            ->with('request.user.department')
            ->orderBy('request_date')
            ->orderBy('start_time')
            ->get()
            // today's bookings that have already ended don't count
            ->reject(fn ($i) => Carbon::parse($i->request_date)->isToday() && $i->end_time && Carbon::parse($i->end_time)->format('H:i') <= $now->format('H:i'))
            ->groupBy(fn ($i) => mb_strtolower($i->item_name))
            ->map->first()
            ->all();
    }

    /* ------------------------------ maintenance ------------------------------ */

    public function openMaintenance(int $id): void
    {
        abort_unless(auth()->user()->can('facility-schedule.update'), 403);

        $this->resetErrorBag();
        $this->maintenanceId      = Facilities::query()->findOrFail($id)->id;
        $this->maintenance_reason = '';
        $this->maintenance_until  = '';
    }

    public function cancelMaintenance(): void
    {
        $this->reset('maintenanceId', 'maintenance_reason', 'maintenance_until');
        $this->resetErrorBag();
    }

    /** ✅ Under maintenance, with a reason and an "until" date; people who booked are told. */
    public function saveMaintenance(): void
    {
        abort_unless(auth()->user()->can('facility-schedule.update'), 403);

        $this->validate([
            'maintenance_reason' => 'nullable|string|max:255',
            'maintenance_until'  => 'nullable|date|after_or_equal:today',
        ], ['maintenance_until.after_or_equal' => 'The "until" date cannot be in the past.']);

        $room = Facilities::query()->findOrFail($this->maintenanceId);
        $room->update([
            'status'             => 'maintenance',
            'maintenance_reason' => $this->maintenance_reason !== '' ? $this->maintenance_reason : null,
            'maintenance_until'  => $this->maintenance_until !== '' ? $this->maintenance_until : null,
        ]);

        $upcoming = Facilities::notifyMaintenance($room->fresh());

        session()->flash('success', $upcoming->isEmpty()
            ? "{$room->resource_name} is now under maintenance. It had no upcoming bookings."
            : "{$room->resource_name} is now under maintenance. {$upcoming->count()} upcoming "
              . \Illuminate\Support\Str::plural('booking', $upcoming->count()) . ' affected — the people who booked were notified.');

        $this->cancelMaintenance();
        unset($this->counts, $this->facilities);
    }

    /** Back to available (clears the reason and date). */
    public function setAvailable(int $id): void
    {
        abort_unless(auth()->user()->can('facility-schedule.update'), 403);

        $room = Facilities::query()->findOrFail($id);
        $room->update(['status' => 'available', 'maintenance_reason' => null, 'maintenance_until' => null]);

        session()->flash('success', "{$room->resource_name} is available again.");
        unset($this->counts, $this->facilities);
    }

    /** Kept for older links/tests: available → opens the maintenance form; maintenance → available. */
    public function toggleStatus(int $id)
    {
        $room = Facilities::query()->findOrFail($id);

        if ($room->status === 'maintenance') {
            $this->setAvailable($id);
            return;
        }

        $this->maintenanceId = $room->id;
        $this->saveMaintenance();
    }

    /** ✅ A facility with upcoming bookings can't be deleted. */
    public function delete(int $id)
    {
        abort_unless(auth()->user()->can('facility-schedule.delete'), 403);

        $facility = Facilities::query()->findOrFail($id);
        $upcoming = Facilities::upcomingBookings($facility);

        if ($upcoming->isNotEmpty()) {
            $first = Carbon::parse($upcoming->first()->request_date)->format('M d, Y');
            session()->flash('error', "Can't delete {$facility->resource_name}: it has {$upcoming->count()} upcoming "
                . \Illuminate\Support\Str::plural('booking', $upcoming->count()) . " (the next on {$first}). "
                . 'Put it in maintenance or wait until they have passed.');
            return;
        }

        $facility->delete();
        session()->flash('success', 'Facility deleted successfully.');
        unset($this->counts, $this->facilities);
    }
};
