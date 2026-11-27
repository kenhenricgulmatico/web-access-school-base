<?php

namespace App\Livewire\Admin;

use App\Models\RequestItem;
use App\Models\RequestType;
use App\Models\Request as ResourceRequest;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Support\TimeSlots;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public int    $year;
    public int    $month;
    public string $selectedDate = '';
    public string $activeTab    = 'reservation'; // reservation | material | returns
    #[\Livewire\Attributes\Url(as: 'room', except: '')]
    public string $roomFilter   = '';             // ✅ one room only ('' = all) — Facilities links here with ?room=
    public bool   $showPending  = true;           // ✅ pending requests shown faded
    public string $roomSearch   = '';             // ✅ find a room when there are many
    public bool   $showAllFree  = false;          // free rooms: first few chips, or all

    public function mount()
    {
        abort_unless(auth()->user()->can('calendar.view'), 403);
        $this->year         = now()->year;
        $this->month        = now()->month;
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function previousMonth()
    {
        [$this->year, $this->month] = $this->month === 1 ? [$this->year - 1, 12] : [$this->year, $this->month - 1];
    }

    public function nextMonth()
    {
        [$this->year, $this->month] = $this->month === 12 ? [$this->year + 1, 1] : [$this->year, $this->month + 1];
    }

    /** ✅ Month / year pickers: keep them in range. */
    public function updatedMonth($value): void
    {
        $this->month = min(max((int) $value, 1), 12);
    }

    public function updatedYear($value): void
    {
        $this->year = min(max((int) $value, now()->year - 2), now()->year + 2);
    }

    /** Years offered in the picker. */
    public function years(): array
    {
        return range(now()->year - 2, now()->year + 2);
    }

    public function updatedActiveTab($value): void
    {
        if (! in_array($value, ['reservation', 'material', 'returns'], true)) {
            $this->activeTab = 'reservation';
        }
    }

    public function selectDate(string $date)
    {
        $this->selectedDate = rescue(fn () => Carbon::parse($date)->format('Y-m-d'), now()->format('Y-m-d'), false);
    }

    /** ✅ Jump the calendar back to today's month and select today. */
    public function goToday(): void
    {
        $this->year         = now()->year;
        $this->month        = now()->month;
        $this->selectedDate = now()->format('Y-m-d');
    }

    /**
     * ✅ Friendly label for a date compared to today:
     *    Today, Tomorrow, Yesterday, In 3 days, 2 days ago,
     *    In 1 week, 3 weeks ago, In 2 months, 1 year ago …
     */
    public function relativeDayLabel(?string $date): string
    {
        if (! $date) {
            return '';
        }

        $today = Carbon::today();
        $day   = Carbon::parse($date)->startOfDay();
        $diff  = (int) $today->diffInDays($day, false); // + future, - past
        $abs   = abs($diff);

        if ($diff === 0)  return 'Today';
        if ($diff === 1)  return 'Tomorrow';
        if ($diff === -1) return 'Yesterday';

        if ($abs < 7) {
            $text = "{$abs} days";
        } elseif ($abs < 30) {
            $n    = intdiv($abs, 7);
            $text = $n . ' ' . ($n === 1 ? 'week' : 'weeks');
        } elseif ($abs < 365) {
            $n    = intdiv($abs, 30);
            $text = $n . ' ' . ($n === 1 ? 'month' : 'months');
        } else {
            $n    = intdiv($abs, 365);
            $text = $n . ' ' . ($n === 1 ? 'year' : 'years');
        }

        return $diff > 0 ? "In {$text}" : "{$text} ago";
    }

    #[Computed]
    public function calendarDays()
    {
        $start = Carbon::create($this->year, $this->month, 1);
        $days  = array_fill(0, $start->dayOfWeek, null);

        for ($d = 1; $d <= $start->daysInMonth; $d++) {
            $days[] = $d;
        }

        return $days;
    }

    /* ------------------------------------------------------------------ */
    /*  Queries — the Admin calendar shows Program Head requests only      */
    /* ------------------------------------------------------------------ */

    /**
     * ✅ Requests filed by a Program Head — checked inside the same query
     *    (no separate role lookup, so the calendar runs a fixed, small number of queries).
     */
    protected function byProgramHead($requestQuery)
    {
        return $requestQuery->whereExists(fn ($sub) => $sub->selectRaw('1')
            ->from('model_has_roles as mhr')
            ->join('roles as ro', 'ro.id', '=', 'mhr.role_id')
            ->whereColumn('mhr.model_id', 'requests.user_id')
            ->where('mhr.model_type', (new \App\Models\User)->getMorphClass())
            ->where('ro.name', 'program head'));
    }

    /** Statuses shown: approved, plus pending when "Show pending" is on. */
    protected function statuses(): array
    {
        return $this->showPending ? ['approved', 'pending'] : ['approved'];
    }

    /** Room bookings (facility request lines without a resource_id). */
    protected function bookingItems()
    {
        return RequestItem::query()
            ->whereNull('resource_id')
            ->whereHas('request', fn ($q) => $q
                ->where('request_type_id', RequestType::FACILITY)
                ->whereIn('status', $this->statuses())
                ->where(fn ($ph) => $this->byProgramHead($ph)))
            ->when($this->roomFilter !== '', fn ($q) => $q->where('item_name', $this->roomFilter));
    }

    protected function materialRequests()
    {
        return ResourceRequest::query()
            ->where('request_type_id', RequestType::MATERIAL)
            ->whereIn('status', $this->statuses())
            ->where(fn ($ph) => $this->byProgramHead($ph));
    }

    /** Borrowed Material items (chairs, projectors…) still out with a Program Head. */
    protected function outstandingLoans()
    {
        return RequestItem::query()
            ->whereNull('returned_at')
            ->whereNotNull('due_back_at')
            ->whereNotNull('resource_id')
            ->whereHas('resource', fn ($r) => $r->where('is_returnable', true))
            ->whereHas('request', fn ($q) => $q->where('status', 'approved')
                ->where(fn ($ph) => $this->byProgramHead($ph)));
    }

    /**
     * ✅ What each day of the shown month holds:
     *    date => [rooms, roomsPending, materials, materialsPending, due, overdue]
     */
    #[Computed]
    public function dayCounts(): array
    {
        $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();
        $days  = [];
        $blank = ['rooms' => 0, 'roomsPending' => 0, 'materials' => 0, 'materialsPending' => 0, 'due' => 0, 'overdue' => false];

        // status read in the same query (join) — one query per kind, never one per day
        $bookings = $this->bookingItems()
            ->join('requests as rq', 'rq.id', '=', 'request_items.request_id')
            ->whereBetween('request_items.request_date', [$start->toDateString(), $end->toDateString()])
            ->get(['request_items.request_date', 'rq.status']);

        foreach ($bookings as $b) {
            $key = Carbon::parse($b->request_date)->format('Y-m-d');
            $days[$key] ??= $blank;
            $b->status === 'pending' ? $days[$key]['roomsPending']++ : $days[$key]['rooms']++;
        }

        if ($this->roomFilter === '') {
            $materials = RequestItem::query()
                ->join('requests as rq', 'rq.id', '=', 'request_items.request_id')
                ->whereBetween('request_items.request_date', [$start->toDateString(), $end->toDateString()])
                ->whereIn('rq.id', $this->materialRequests()->select('requests.id'))
                ->get(['request_items.request_id', 'request_items.request_date', 'rq.status'])
                ->unique(fn ($i) => $i->request_id . '|' . Carbon::parse($i->request_date)->format('Y-m-d'));

            foreach ($materials as $m) {
                $key = Carbon::parse($m->request_date)->format('Y-m-d');
                $days[$key] ??= $blank;
                $m->status === 'pending' ? $days[$key]['materialsPending']++ : $days[$key]['materials']++;
            }

            $due = $this->outstandingLoans()
                ->whereBetween('due_back_at', [$start->toDateString(), $end->toDateString()])
                ->get(['id', 'due_back_at']);

            foreach ($due as $d) {
                $key = Carbon::parse($d->due_back_at)->format('Y-m-d');
                $days[$key] ??= $blank;
                $days[$key]['due']++;
                $days[$key]['overdue'] = $days[$key]['overdue'] || Carbon::parse($d->due_back_at)->lt(today());
            }
        }

        return $days;
    }

    /** ✅ Overdue borrowed items (all of them, any date) — shown on the Returns tab. */
    #[Computed]
    public function overdueCount(): int
    {
        return $this->outstandingLoans()->whereDate('due_back_at', '<', today())->count();
    }

    /** Pending Program Head requests in the shown month (header hint). */
    #[Computed]
    public function pendingThisMonth(): int
    {
        $c = $this->dayCounts;
        return array_sum(array_column($c, 'roomsPending')) + array_sum(array_column($c, 'materialsPending'));
    }

    /** Material requests needed on the selected day. */
    #[Computed]
    public function selectedMaterials()
    {
        if (! $this->selectedDate) {
            return collect();
        }

        return $this->materialRequests()
            ->with(['user.department', 'items'])
            ->whereHas('items', fn ($q) => $q->whereDate('request_date', $this->selectedDate))
            ->orderByRaw("status = 'pending' desc")
            ->get();
    }

    /** ✅ Borrowed items due back on the selected day (+ every overdue item when it's today). */
    #[Computed]
    public function selectedReturns(): array
    {
        if (! $this->selectedDate) {
            return ['due' => collect(), 'overdue' => collect()];
        }

        $with = ['request.user.department'];

        return [
            'due'     => $this->outstandingLoans()->with($with)->whereDate('due_back_at', $this->selectedDate)->orderBy('item_name')->get(),
            'overdue' => $this->selectedDate === today()->toDateString()
                ? $this->outstandingLoans()->with($with)->whereDate('due_back_at', '<', today())->orderBy('due_back_at')->get()
                : collect(),
        ];
    }

    /** Every room (for the filter and the availability list). */
    #[Computed]
    public function rooms()
    {
        return Resource::whereHas('resourceType', fn ($q) => $q->where('type_name', ResourceType::FACILITY))
            ->get(['id', 'resource_name', 'status', 'maintenance_reason', 'maintenance_until'])
            ->sortBy('resource_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * ✅ Every room on the selected day, booked or free:
     *    [ ['room', 'status', 'slots' => [...], 'free' => ['7:00 AM – 9:00 AM', …], 'busy' => bool], … ]
     *    Each slot: start, end, left/width (% of 7 AM–8 PM), department, user, purpose,
     *    status (approved|pending), clash (pending that overlaps an approved booking), request_id.
     */
    #[Computed]
    public function roomDay(): array
    {
        if (! $this->selectedDate) {
            return [];
        }

        $open  = Carbon::createFromFormat('H:i', TimeSlots::OPEN);
        $close = Carbon::createFromFormat('H:i', TimeSlots::CLOSE);
        $span  = $open->diffInMinutes($close);
        $pos   = fn (string $hm) => max(0, min(100, $open->diffInMinutes(Carbon::createFromFormat('H:i', $hm), false) / $span * 100));

        $items = $this->bookingItems()
            ->whereDate('request_date', $this->selectedDate)
            ->with('request.user.department')
            ->get();

        // all rooms (filtered), plus any booked name that isn't a room anymore
        $search = mb_strtolower(trim($this->roomSearch));
        $items  = $items->filter(fn ($i) => $search === '' || str_contains(mb_strtolower($i->item_name), $search))->values();

        $names = $this->rooms
            ->when($this->roomFilter !== '', fn ($c) => $c->where('resource_name', $this->roomFilter))
            ->when($search !== '', fn ($c) => $c->filter(fn ($r) => str_contains(mb_strtolower($r->resource_name), $search)))
            ->mapWithKeys(fn ($r) => [$r->resource_name => $r->status])
            ->all();
        foreach ($items->pluck('item_name')->unique() as $n) {
            $names[$n] ??= 'removed';
        }

        $out = [];
        foreach ($names as $name => $status) {
            $slots = $items->where('item_name', $name)
                ->map(function ($i) use ($pos) {
                    $s = TimeSlots::normalize($i->start_time);
                    $e = TimeSlots::normalize($i->end_time);
                    return [
                        'start'      => $s ? Carbon::createFromFormat('H:i', $s)->format('g:i A') : null,
                        'end'        => $e ? Carbon::createFromFormat('H:i', $e)->format('g:i A') : null,
                        's'          => $s,
                        'e'          => $e,
                        'left'       => $s ? round($pos($s), 2) : 0,
                        'width'      => $s && $e ? max(round($pos($e) - $pos($s), 2), 1.5) : 100,
                        'department' => $i->request?->user?->department?->department_name ?? 'N/A',
                        'user'       => $i->request?->user?->name ?? 'N/A',
                        'purpose'    => $i->request?->purpose,
                        'status'     => $i->request?->status ?? 'approved',
                        'request_id' => $i->request_id,
                        'clash'      => false,
                    ];
                })
                ->sortBy(fn ($x) => $x['s'] ?? '99:99')
                ->values()
                ->all();

            // a pending booking that overlaps an approved one
            $approved = array_values(array_filter($slots, fn ($x) => $x['status'] === 'approved' && $x['s'] && $x['e']));
            foreach ($slots as &$x) {
                if ($x['status'] === 'pending' && $x['s'] && $x['e']) {
                    $x['clash'] = TimeSlots::overlaps($x['s'], $x['e'], array_map(fn ($a) => ['start' => $a['s'], 'end' => $a['e']], $approved));
                }
            }
            unset($x);

            $roomModel = $this->rooms->firstWhere('resource_name', $name);

            $out[] = [
                'room'   => $name,
                'status' => $status,
                'note'   => $roomModel && $status === 'maintenance' ? \App\Support\Facilities::maintenanceNote($roomModel) : '',
                'slots'  => $slots,
                'free'   => $this->freeTimes($slots),
                'busy'   => count($slots) > 0,
            ];
        }

        // booked rooms first, then by name
        usort($out, fn ($a, $b) => ($b['busy'] <=> $a['busy']) ?: strnatcasecmp($a['room'], $b['room']));

        return $out;
    }

    /** "7:00 AM – 9:00 AM" gaps between bookings, within opening hours. */
    protected function freeTimes(array $slots): array
    {
        $taken = collect($slots)->filter(fn ($x) => $x['s'] && $x['e'])->sortBy('s')->values();
        $free  = [];
        $from  = TimeSlots::OPEN;

        foreach ($taken as $t) {
            if ($t['s'] > $from) {
                $free[] = [$from, $t['s']];
            }
            $from = max($from, $t['e']);
        }
        if ($from < TimeSlots::CLOSE) {
            $free[] = [$from, TimeSlots::CLOSE];
        }

        return array_map(fn ($g) => Carbon::createFromFormat('H:i', $g[0])->format('g:i A') . ' – ' . Carbon::createFromFormat('H:i', $g[1])->format('g:i A'), $free);
    }

    /** Hour marks for the time bar: 7 AM … 8 PM. */
    public function hourMarks(): array
    {
        $open  = Carbon::createFromFormat('H:i', TimeSlots::OPEN);
        $close = Carbon::createFromFormat('H:i', TimeSlots::CLOSE);
        $span  = $open->diffInMinutes($close);
        $marks = [];

        for ($t = $open->copy(); $t->lte($close); $t->addHours(3)) {
            $marks[] = ['label' => $t->format('ga'), 'left' => round($open->diffInMinutes($t) / $span * 100, 2)];
        }

        return $marks;
    }
};
