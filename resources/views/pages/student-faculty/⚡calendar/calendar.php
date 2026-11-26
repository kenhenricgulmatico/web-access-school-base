<?php

use App\Models\Department;
use App\Models\Request as ResourceRequest;
use App\Models\RequestType;
use App\Support\BookingDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * ✅ Faculty calendar (same as the Program Head calendar)
 *  - Facility: approved reservations of ALL departments (like the Admin calendar), so rooms can be
 *    planned across departments; a department picker narrows them down.
 *  - Material: approved material requests of YOUR OWN department only.
 *  - Each room has a Print bar (all departments or one) — same printout as the Admin calendar.
 *  Endorsed copies sent to the Admin are skipped — the original faculty request already shows it.
 */
new #[Layout('layouts.student-faculty')] class extends Component
{
    public int    $year;
    public int    $month;
    public string $selectedDate = '';
    public string $activeTab    = 'facility'; // facility | material
    public string $department   = '';         // '' = all departments, otherwise a department id
    public string $room         = '';         // ✅ '' = every room, otherwise one facility's bookings only

    public function mount(): void
    {
        $this->goToToday();
    }

    // ============================================================
    // NAVIGATION
    // ============================================================

    public function goToToday(): void
    {
        $this->year         = now()->year;
        $this->month        = now()->month;
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function previousMonth(): void
    {
        $this->showMonth(Carbon::create($this->year, $this->month, 1)->subMonth());
    }

    public function nextMonth(): void
    {
        // ✅ Nothing can be booked past the booking window (BookingDate::max), so stop there
        if (! $this->canGoNext) {
            return;
        }

        $this->showMonth(Carbon::create($this->year, $this->month, 1)->addMonth());
    }

    /** False on the month that holds the last bookable day. */
    public function getCanGoNextProperty(): bool
    {
        return Carbon::create($this->year, $this->month, 1)->endOfMonth()->toDateString() < BookingDate::max();
    }

    /** Switch month and pick a day inside it (today if it's this month, otherwise the 1st). */
    protected function showMonth(Carbon $d): void
    {
        [$this->year, $this->month] = [$d->year, $d->month];
        $this->selectedDate = $d->isSameMonth(now()) ? now()->format('Y-m-d') : $d->format('Y-m-d');
    }

    // ✅ The tab and the picked day are switched in the browser (no server trip);
    //    these only keep the values safe when they're sent back with the next request.
    public function updatedSelectedDate($value): void
    {
        $date = rescue(fn () => Carbon::createFromFormat('Y-m-d', (string) $value), null, false);

        // Not a real date, or past the booking window → back to today
        $this->selectedDate = $date && $date->format('Y-m-d') <= BookingDate::max()
            ? $date->format('Y-m-d')
            : now()->format('Y-m-d');
    }

    /** Only a real facility name can be picked (anything else = all rooms). */
    public function updatedRoom($value): void
    {
        $this->room = in_array($value, $this->roomOptions, true) ? $value : '';
    }

    public function updatedActiveTab($value): void
    {
        $this->activeTab = in_array($value, ['facility', 'material'], true) ? $value : 'facility';
    }

    // ============================================================
    // DATA
    // ============================================================

    /** ✅ Facilities that can be reserved (for the room filter and "Book this room"). */
    #[Computed]
    public function roomOptions(): array
    {
        return \App\Models\Resource::whereHas('resourceType', fn ($q) => $q->where('type_name', \App\Models\ResourceType::FACILITY))
            ->where('status', 'available')
            ->orderBy('resource_name')
            ->pluck('resource_name')
            ->all();
    }

    /** ✅ Can a new reservation be made for this day? (today up to the booking window) */
    public function canBook(string $date): bool
    {
        return $date >= BookingDate::min() && $date <= BookingDate::max();
    }

    /** Link to Create Reservation with the room / date filled in. */
    public function bookUrl(string $date, ?string $room = null): string
    {
        return route('portal.create-reservation', array_filter(['facility' => $room, 'date' => $date]));
    }

    #[Computed]
    public function departments(): array
    {
        return Department::orderBy('department_name')->pluck('department_name', 'id')->all();
    }

    #[Computed]
    public function myDepartmentId(): ?int
    {
        return Auth::user()->department_id;
    }

    /**
     * Approved requests, no endorsed copies:
     *  - facility → all departments, or the one picked in the department picker
     *  - material → only the user's own department (the picker doesn't apply)
     */
    protected function scoped($query)
    {
        $myDept = $this->myDepartmentId;

        return $query
            ->where('status', 'approved')
            ->whereNull('forwarded_from_request_id')
            ->where(function ($q) use ($myDept) {
                $q->where(fn ($f) => $f
                        ->where('request_type_id', RequestType::FACILITY)
                        ->when($this->department !== '', fn ($d) => $d->where('department_id', (int) $this->department))
                        // ✅ Room filter: only this facility's bookings
                        ->when($this->room !== '', fn ($r) => $r->whereHas('items', fn ($i) => $i
                            ->whereNull('resource_id')->where('item_name', $this->room))))
                  ->orWhere(fn ($m) => $m
                        ->where('request_type_id', RequestType::MATERIAL)
                        ->where('department_id', $myDept ?? 0));
            });
    }

    /**
     * ✅ Everything for the shown month, loaded in ONE go so that switching
     *    Facility / Material and picking a day happen instantly in the browser.
     *
     * date => [
     *   'rooms'     => [ ['room' => 'Room 101', 'slots' => [ [start, end, department, mine, user, purpose, extras], ... ]], ... ],
     *   'materials' => [ [id, department, mine, user, items, purpose], ... ],
     * ]
     */
    #[Computed]
    public function monthDays(): array
    {
        $start = Carbon::create($this->year, $this->month, 1)->toDateString();
        $end   = Carbon::create($this->year, $this->month, 1)->endOfMonth()->toDateString();

        $requests = $this->scoped(ResourceRequest::query())
            ->whereHas('items', fn ($q) => $q->whereBetween('request_date', [$start, $end]))
            ->with(['user:id,name', 'department:id,department_name', 'items'])
            ->get();

        $days = [];

        foreach ($requests as $request) {
            $dates = $request->items
                ->filter(fn ($i) => $i->request_date)
                ->map(fn ($i) => Carbon::parse($i->request_date)->toDateString())
                ->filter(fn ($d) => $d >= $start && $d <= $end)
                ->unique();

            $common = [
                'department' => $request->department->department_name ?? 'N/A',
                'mine'       => (int) $request->department_id === (int) $this->myDepartmentId,
                'user'       => $request->user->name ?? 'N/A',
                'purpose'    => $request->purpose,
            ];

            foreach ($dates as $date) {
                if ((int) $request->request_type_id === RequestType::FACILITY) {
                    $dayItems = $request->items->filter(fn ($i) =>
                        $i->request_date && Carbon::parse($i->request_date)->toDateString() === $date);
                    $pool = $dayItems->isNotEmpty() ? $dayItems : $request->items;
                    $room = $pool->first(fn ($i) => ! $i->resource_id && $i->start_time)
                         ?? $pool->firstWhere('resource_id', null)
                         ?? $pool->first();

                    $name = $room->item_name ?? 'Unknown room';

                    $days[$date]['rooms'][$name]['room']    = $name;
                    $days[$date]['rooms'][$name]['slots'][] = [
                        'start'  => $room?->start_time ? Carbon::parse($room->start_time)->format('g:i A') : null,
                        'end'    => $room?->end_time   ? Carbon::parse($room->end_time)->format('g:i A')   : null,
                        'sort'   => $room?->start_time ? Carbon::parse($room->start_time)->format('H:i')   : '99:99',
                        'extras' => $request->items->whereNotNull('resource_id')
                                        ->map(fn ($i) => "{$i->item_name} ×{$i->quantity}")->implode(', '),
                    ] + $common;
                } else {
                    $days[$date]['materials'][] = [
                        'id'    => $request->id,
                        'items' => $request->items->whereNotNull('resource_id')
                                       ->map(fn ($i) => "{$i->item_name} ×{$i->quantity}")->values()->all(),
                    ] + $common;
                }
            }
        }

        foreach ($days as &$day) {
            $rooms = $day['rooms'] ?? [];
            foreach ($rooms as &$r) {
                usort($r['slots'], fn ($a, $b) => strcmp($a['sort'], $b['sort']));
            }
            unset($r);
            uksort($rooms, 'strnatcasecmp');
            $day['rooms'] = array_values($rooms);

            $day['materials'] = collect($day['materials'] ?? [])
                ->sortBy([['department', 'asc'], ['user', 'asc']])
                ->values()
                ->all();
        }
        unset($day);

        return $days;
    }

    /** Month grid: null for padding, otherwise ['date', 'day', 'facility', 'material']. */
    #[Computed]
    public function calendarCells(): array
    {
        $first = Carbon::create($this->year, $this->month, 1);
        $cells = array_fill(0, $first->dayOfWeek, null);
        $days  = $this->monthDays;

        for ($d = 1; $d <= $first->daysInMonth; $d++) {
            $date    = $first->copy()->day($d)->format('Y-m-d');
            $cells[] = [
                'date'     => $date,
                'day'      => $d,
                'facility' => collect($days[$date]['rooms'] ?? [])->sum(fn ($r) => count($r['slots'])),
                'material' => count($days[$date]['materials'] ?? []),
            ];
        }

        return $cells;
    }
};
