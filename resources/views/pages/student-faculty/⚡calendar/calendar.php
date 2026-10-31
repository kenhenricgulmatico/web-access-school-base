<?php

use App\Models\RequestType;
use App\Models\RequestItem;
use App\Models\Resource;
use App\Support\BookingDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Student / Faculty calendar (Objective 4 of the study).
 *  - "My Requests": the user's own facility reservations and material requests (any status).
 *  - "Facility Availability": every APPROVED facility booking shown as a room timeline,
 *    so users can see free time slots before reserving. Requester names are hidden.
 */
new #[Layout('layouts.student-faculty')] class extends Component
{
    public int    $year;
    public int    $month;
    public string $selectedDate = '';
    public string $activeTab    = 'mine'; // 'mine' | 'availability'

    /** Timeline range shown in the room schedule (24h clock). */
    public const DAY_START = 7;
    public const DAY_END   = 19;

    public function mount(): void
    {
        $this->goToToday();
    }

    public function goToToday(): void
    {
        $this->year         = now()->year;
        $this->month        = now()->month;
        $this->selectedDate = now()->format('Y-m-d');
    }

    /** Earliest month the calendar can show: 12 months back (for history). */
    protected function firstMonth(): Carbon
    {
        return now()->startOfMonth()->subMonths(12);
    }

    /** Latest month the calendar can show: the last month users can book in. */
    protected function lastMonth(): Carbon
    {
        return Carbon::parse(BookingDate::max())->startOfMonth();
    }

    protected function currentMonth(): Carbon
    {
        return Carbon::create($this->year, $this->month, 1)->startOfMonth();
    }

    #[Computed]
    public function canGoPrevious(): bool
    {
        return $this->currentMonth()->gt($this->firstMonth());
    }

    #[Computed]
    public function canGoNext(): bool
    {
        return $this->currentMonth()->lt($this->lastMonth());
    }

    public function previousMonth(): void
    {
        if (! $this->canGoPrevious()) {
            return;
        }
        $d = $this->currentMonth()->subMonth();
        $this->year  = $d->year;
        $this->month = $d->month;
    }

    public function nextMonth(): void
    {
        if (! $this->canGoNext()) {
            return;
        }
        $d = $this->currentMonth()->addMonth();
        $this->year  = $d->year;
        $this->month = $d->month;
    }

    public function selectDate(string $date): void
    {
        // Ignore anything that is not a real Y-m-d date inside the shown range.
        $d = Carbon::createFromFormat('!Y-m-d', $date) ?: null;
        if (! $d || $d->lt($this->firstMonth()) || $d->gt($this->lastMonth()->endOfMonth())) {
            return;
        }
        $this->selectedDate = $d->format('Y-m-d');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab === 'availability' ? 'availability' : 'mine';
    }

    // ------------------------------------------------------------------
    // Queries
    // ------------------------------------------------------------------

    /** Items of the logged-in user's own requests. */
    protected function myItemsQuery()
    {
        return RequestItem::query()
            ->with(['request.approvals'])
            ->whereHas('request', fn ($q) => $q
                ->where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'approved', 'rejected']));
    }

    /** Approved facility bookings of everyone (facility rows have no resource_id). */
    protected function bookedFacilitiesQuery()
    {
        return RequestItem::query()
            ->whereNull('resource_id')
            ->whereNotNull('start_time')
            ->whereHas('request', fn ($q) => $q->where('status', 'approved'));
    }

    /** Turns a request's items into one display row. */
    protected function toRow($items): array
    {
        $request   = $items->first()->request;
        $facility  = $items->firstWhere('resource_id', null);
        $materials = $items->whereNotNull('resource_id');

        if ($facility) {
            $title = $facility->item_name;
        } else {
            $first = $materials->first();
            $title = "{$first->item_name} (x{$first->quantity})";
            if ($materials->count() > 1) {
                $title .= ' +' . ($materials->count() - 1) . ' more';
            }
        }

        $reason = $request->status === 'rejected'
            ? optional($request->approvals->where('status', 'rejected')->sortByDesc('id')->first())->remarks
            : null;

        return [
            'id'         => $request->id,
            'status'     => $request->status,
            'isFacility' => (int) $request->request_type_id === RequestType::FACILITY,
            'title'      => $title,
            'time'       => ($facility && $facility->start_time)
                ? Carbon::parse($facility->start_time)->format('g:i A') . ' – ' . Carbon::parse($facility->end_time)->format('g:i A')
                : null,
            'materials'  => $facility
                ? $materials->map(fn ($m) => "{$m->item_name} (x{$m->quantity})")->implode(', ')
                : null,
            'purpose'    => $request->purpose,
            'reason'     => $reason,
            // ✅ Borrowed items still to be returned, e.g. "Chair (x2) — return by Nov 03 (Due in 3 days)"
            'returns'    => $materials
                ->filter(fn ($m) => $m->due_back_at && ! $m->returned_at)
                ->map(fn ($m) => [
                    'text'    => "{$m->item_name} (x{$m->quantity}) — return by " . $m->due_back_at->format('M d') . ' (' . \App\Support\ReturnTracking::label($m) . ')',
                    'overdue' => $m->isOverdue(),
                ])->values()->all(),
        ];
    }

    // ------------------------------------------------------------------
    // Month grid
    // ------------------------------------------------------------------

    /** Every cell of the month grid, with the chips to show inside it. */
    #[Computed]
    public function calendarCells(): array
    {
        $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();
        $range = [$start->toDateString(), $end->toDateString()];

        // My requests this month, grouped by date → request
        $mine = $this->myItemsQuery()
            ->whereBetween('request_date', $range)
            ->get()
            ->groupBy(fn ($i) => Carbon::parse($i->request_date)->format('Y-m-d'))
            ->map(fn ($dayItems) => $dayItems->groupBy('request_id')->map(fn ($items) => $this->toRow($items))->values());

        // Number of approved facility bookings per date
        $booked = $this->bookedFacilitiesQuery()
            ->whereBetween('request_date', $range)
            ->pluck('request_date')
            ->countBy(fn ($d) => Carbon::parse($d)->format('Y-m-d'));

        $cells = array_fill(0, $start->dayOfWeek, null);

        for ($day = 1; $day <= $start->daysInMonth; $day++) {
            $key  = $start->copy()->day($day)->format('Y-m-d');
            $rows = $mine->get($key, collect());

            $cells[] = [
                'day'    => $day,
                'date'   => $key,
                'rows'   => $rows,
                'booked' => (int) $booked->get($key, 0),
            ];
        }

        while (count($cells) % 7 !== 0) {
            $cells[] = null;
        }

        return $cells;
    }

    // ------------------------------------------------------------------
    // Selected day panel
    // ------------------------------------------------------------------

    #[Computed]
    public function myRequests()
    {
        return $this->myItemsQuery()
            ->whereDate('request_date', $this->selectedDate)
            ->get()
            ->groupBy('request_id')
            ->map(fn ($items) => $this->toRow($items))
            ->values();
    }

    /** One row per facility, with booked bars positioned on a 7 AM – 7 PM timeline. */
    #[Computed]
    public function roomSchedule(): array
    {
        $bookings = $this->bookedFacilitiesQuery()
            ->whereDate('request_date', $this->selectedDate)
            ->orderBy('start_time')
            ->get(['item_name', 'start_time', 'end_time']);

        $facilityNames = Resource::whereHas('resourceType', fn ($q) => $q->where('type_name', 'like', '%facility%'))
            ->orderBy('resource_name')
            ->pluck('resource_name');

        $names = $facilityNames->merge($bookings->pluck('item_name'))->unique()->values();

        $span = self::DAY_END - self::DAY_START;
        $toHour = function ($time) {
            $t = Carbon::parse($time);
            return $t->hour + $t->minute / 60;
        };

        return $names->map(function ($name) use ($bookings, $span, $toHour) {
            $bars = $bookings->where('item_name', $name)->map(function ($b) use ($span, $toHour) {
                $from = max(self::DAY_START, $toHour($b->start_time));
                $to   = min(self::DAY_END, $toHour($b->end_time));
                if ($to <= $from) {
                    return null;
                }

                return [
                    'left'  => round(($from - self::DAY_START) / $span * 100, 2),
                    'width' => round(($to - $from) / $span * 100, 2),
                    'label' => Carbon::parse($b->start_time)->format('g:i') . '–' . Carbon::parse($b->end_time)->format('g:i A'),
                ];
            })->filter()->values();

            return ['name' => $name, 'bars' => $bars];
        })->all();
    }

    #[Computed]
    public function bookedCount(): int
    {
        return collect($this->roomSchedule)->sum(fn ($r) => count($r['bars']));
    }
};
