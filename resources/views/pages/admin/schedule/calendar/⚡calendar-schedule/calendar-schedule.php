<?php

namespace App\Livewire\Admin;

// use App\Models\Schedule;
use App\Models\RequestType;
use App\Models\Request as ResourceRequest;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public int    $year;
    public int    $month;
    public string $selectedDate = '';
    public string $activeTab    = 'reservation'; // ✅ default to reservation since schedule is disabled

    public function mount()
    {
        $this->year         = now()->year;
        $this->month        = now()->month;
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function previousMonth()
    {
        if ($this->month === 1) {
            $this->month = 12;
            $this->year--;
        } else {
            $this->month--;
        }
    }

    public function nextMonth()
    {
        if ($this->month === 12) {
            $this->month = 1;
            $this->year++;
        } else {
            $this->month++;
        }
    }

    public function selectDate(string $date)
    {
        $this->selectedDate = $date;
    }

    #[Computed]
    public function calendarDays()
    {
        $start       = \Carbon\Carbon::create($this->year, $this->month, 1);
        $startDay    = $start->dayOfWeek;
        $daysInMonth = $start->daysInMonth;

        $days = [];

        for ($i = 0; $i < $startDay; $i++) {
            $days[] = null;
        }

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $days[] = $d;
        }

        return $days;
    }

    /**
     * Which days of the shown month have approved facility / material requests.
     * ONE query for the whole month (it used to be 2 queries per day ≈ 62 per page load).
     */
    #[Computed]
    public function daysWithEvents(): array
    {
        $start = \Carbon\Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $rows = \Illuminate\Support\Facades\DB::table('request_items as ri')
            ->join('requests as r', 'r.id', '=', 'ri.request_id')
            ->where('r.status', 'approved')
            ->whereIn('r.request_type_id', [RequestType::FACILITY, RequestType::MATERIAL])
            ->whereBetween('ri.request_date', [$start->toDateString(), $end->toDateString()])
            ->select('ri.request_date', 'r.request_type_id')
            ->distinct()
            ->get();

        $days = [];

        foreach ($rows as $row) {
            $key = \Carbon\Carbon::parse($row->request_date)->format('Y-m-d');
            $days[$key] ??= ['reservation' => false, 'material' => false];

            if ((int) $row->request_type_id === RequestType::FACILITY) {
                $days[$key]['reservation'] = true;
            } else {
                $days[$key]['material'] = true;
            }
        }

        return $days;
    }

#[Computed]
public function selectedDateEvents()
{
    if (!$this->selectedDate) return [];

    $reservations = ResourceRequest::with(['user.department', 'items'])
        ->where('request_type_id', RequestType::FACILITY)
        ->where('status', 'approved')
        ->whereHas('items', fn($q) =>
            $q->whereDate('request_date', $this->selectedDate)
        )
        ->get();

    $materials = ResourceRequest::with(['user.department', 'items'])
        ->where('request_type_id', RequestType::MATERIAL)
        ->where('status', 'approved')
        ->whereHas('items', fn($q) =>
            $q->whereDate('request_date', $this->selectedDate)
        )
        ->get();

    return [
        'reservations' => $reservations,
        'materials'    => $materials,
    ];
}

    // ===== SCHEDULE DISABLED =====
    // private function getDayCode(int $dayOfWeek): string
    // {
    //     return match ($dayOfWeek) {
    //         0 => 'SUN',
    //         1 => 'MON',
    //         2 => 'TUE',
    //         3 => 'WED',
    //         4 => 'THU',
    //         5 => 'FRI',
    //         6 => 'SAT',
    //         default => '',
    //     };
    // }

    // private function expandDayCode(string $code): array
    // {
    //     return match ($code) {
    //         'MW'  => ['MON', 'WED'],
    //         'TTH' => ['TUE', 'THU'],
    //         'F'   => ['FRI'],
    //         'SAT' => ['SAT'],
    //         'M'   => ['MON'],
    //         'T'   => ['TUE'],
    //         'W'   => ['WED'],
    //         'TH'  => ['THU'],
    //         default => [],
    //     };
    // }
    // ===== END SCHEDULE DISABLED =====
};
