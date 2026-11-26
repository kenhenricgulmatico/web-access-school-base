<?php

use App\Models\Department;
use App\Models\Request as ResourceRequest;
use App\Models\RequestType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * ✅ Faculty calendar (same as the Program Head calendar)
 *  - Approved facility reservations and material requests of ALL departments (like the Admin calendar),
 *    so rooms and materials can be planned across departments.
 *  - Facility / Material are separate tabs; a department picker narrows everything down.
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
        $d = Carbon::create($this->year, $this->month, 1)->subMonth();
        [$this->year, $this->month] = [$d->year, $d->month];
    }

    public function nextMonth(): void
    {
        $d = Carbon::create($this->year, $this->month, 1)->addMonth();
        [$this->year, $this->month] = [$d->year, $d->month];
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = Carbon::parse($date)->format('Y-m-d');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['facility', 'material'], true) ? $tab : 'facility';
    }

    // ============================================================
    // DATA
    // ============================================================

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

    /** Approved facility / material requests (all departments, or the picked one), no endorsed copies. */
    protected function scoped($query, string $prefix = '')
    {
        return $query
            ->where("{$prefix}status", 'approved')
            ->whereIn("{$prefix}request_type_id", [RequestType::FACILITY, RequestType::MATERIAL])
            ->whereNull("{$prefix}forwarded_from_request_id")
            ->when($this->department !== '', fn ($q) => $q->where("{$prefix}department_id", (int) $this->department));
    }

    /** date => ['facility' => n, 'material' => n] for the shown month. */
    #[Computed]
    public function monthCounts(): array
    {
        $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $rows = $this->scoped(
                DB::table('request_items as ri')->join('requests as r', 'r.id', '=', 'ri.request_id'),
                'r.'
            )
            ->whereBetween('ri.request_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('DATE(ri.request_date) as d, r.request_type_id as t, COUNT(DISTINCT r.id) as n')
            ->groupBy('d', 't')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $key = (int) $row->t === RequestType::FACILITY ? 'facility' : 'material';
            $counts[$row->d][$key] = (int) $row->n;
        }

        return $counts;
    }

    /** Month grid: null for padding, otherwise ['date', 'day', 'facility', 'material']. */
    #[Computed]
    public function calendarCells(): array
    {
        $first  = Carbon::create($this->year, $this->month, 1);
        $cells  = array_fill(0, $first->dayOfWeek, null);
        $counts = $this->monthCounts;

        for ($d = 1; $d <= $first->daysInMonth; $d++) {
            $date    = $first->copy()->day($d)->format('Y-m-d');
            $cells[] = [
                'date'     => $date,
                'day'      => $d,
                'facility' => $counts[$date]['facility'] ?? 0,
                'material' => $counts[$date]['material'] ?? 0,
            ];
        }

        return $cells;
    }

    /** Approved requests that have an item on the selected day. */
    protected function dayRequests(int $type)
    {
        return $this->scoped(ResourceRequest::query())
            ->where('request_type_id', $type)
            ->whereHas('items', fn ($q) => $q->whereDate('request_date', $this->selectedDate))
            ->with(['user', 'department', 'items'])
            ->get();
    }

    /**
     * Rooms booked on the selected day:
     * [ ['room' => 'Room 101', 'slots' => [ [start, end, department, mine, user, purpose, extras], ... ]], ... ]
     */
    #[Computed]
    public function rooms(): array
    {
        $rooms = [];

        foreach ($this->dayRequests(RequestType::FACILITY) as $request) {
            $dayItems = $request->items->filter(fn ($i) =>
                $i->request_date && Carbon::parse($i->request_date)->toDateString() === $this->selectedDate);
            $pool = $dayItems->isNotEmpty() ? $dayItems : $request->items;
            $room = $pool->first(fn ($i) => ! $i->resource_id && $i->start_time)
                 ?? $pool->firstWhere('resource_id', null)
                 ?? $pool->first();

            $name = $room->item_name ?? 'Unknown room';

            $rooms[$name]['room']    = $name;
            $rooms[$name]['slots'][] = [
                'start'      => $room?->start_time ? Carbon::parse($room->start_time)->format('g:i A') : null,
                'end'        => $room?->end_time   ? Carbon::parse($room->end_time)->format('g:i A')   : null,
                'sort'       => $room?->start_time ? Carbon::parse($room->start_time)->format('H:i')   : '99:99',
                'department' => $request->department->department_name ?? 'N/A',
                'mine'       => (int) $request->department_id === (int) $this->myDepartmentId,
                'user'       => $request->user->name ?? 'N/A',
                'purpose'    => $request->purpose,
                'extras'     => $request->items->whereNotNull('resource_id')
                                    ->map(fn ($i) => "{$i->item_name} ×{$i->quantity}")->implode(', '),
            ];
        }

        foreach ($rooms as &$r) {
            usort($r['slots'], fn ($a, $b) => strcmp($a['sort'], $b['sort']));
        }
        unset($r);
        uksort($rooms, 'strnatcasecmp');

        return array_values($rooms);
    }

    /** Material requests on the selected day. */
    #[Computed]
    public function materials(): array
    {
        return $this->dayRequests(RequestType::MATERIAL)
            ->map(fn ($r) => [
                'id'         => $r->id,
                'department' => $r->department->department_name ?? 'N/A',
                'mine'       => (int) $r->department_id === (int) $this->myDepartmentId,
                'user'       => $r->user->name ?? 'N/A',
                'items'      => $r->items->whereNotNull('resource_id')->map(fn ($i) => "{$i->item_name} ×{$i->quantity}")->values()->all(),
                'purpose'    => $r->purpose,
            ])
            ->sortBy([['department', 'asc'], ['user', 'asc']])
            ->values()
            ->all();
    }
};
