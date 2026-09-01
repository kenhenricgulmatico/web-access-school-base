<?php

namespace App\Livewire\Admin;

// use App\Models\Schedule;
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

    #[Computed]
    public function daysWithEvents(): array
    {
        $carbon = \Carbon\Carbon::create($this->year, $this->month, 1);

        // ===== SCHEDULE DISABLED =====
        // $schedules = Schedule::all();
        // Uncomment above and the schedule blocks below to re-enable class schedule dots.
        // ===== END SCHEDULE DISABLED =====

        $days = [];

        for ($d = 1; $d <= $carbon->daysInMonth; $d++) {
            $date = \Carbon\Carbon::create($this->year, $this->month, $d);

            // ===== SCHEDULE DISABLED =====
            // $dayCode = $this->getDayCode($date->dayOfWeek);
            // $hasSchedule = $schedules->contains(
            //     fn($s) => in_array($dayCode, $this->expandDayCode($s->day_type))
            // );
            // ===== END SCHEDULE DISABLED =====

            $hasReservation = ResourceRequest::with('items')
                ->where('request_type_id', 1)
                ->where('status', 'approved')
                ->whereHas('items', fn($q) =>
                    $q->whereDate('request_date', $date->format('Y-m-d'))
                )
                ->exists();

            $hasMaterial = ResourceRequest::where('request_type_id', 2)
                ->where('status', 'approved')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->exists();

            if ($hasReservation || $hasMaterial) {
                $days[$date->format('Y-m-d')] = [
                    // 'schedule'    => $hasSchedule, // ===== SCHEDULE DISABLED =====
                    'reservation' => $hasReservation,
                    'material'    => $hasMaterial,
                ];
            }
        }

        return $days;
    }

    #[Computed]
    public function selectedDateEvents()
    {
        if (!$this->selectedDate) return [];

        // ===== SCHEDULE DISABLED =====
        // $date    = \Carbon\Carbon::parse($this->selectedDate);
        // $dayCode = $this->getDayCode($date->dayOfWeek);
        // $schedules = Schedule::all()
        //     ->filter(fn($s) => in_array($dayCode, $this->expandDayCode($s->day_type)))
        //     ->sortBy(fn($s) => \Carbon\Carbon::parse($s->start_time)->format('Hi'))
        //     ->values();
        // ===== END SCHEDULE DISABLED =====

        $reservations = ResourceRequest::with(['user.department', 'items'])
            ->where('request_type_id', 1)
            ->where('status', 'approved')
            ->whereHas('items', fn($q) =>
                $q->whereDate('request_date', $this->selectedDate)
            )
            ->get();

        $materials = ResourceRequest::with(['user.department', 'items'])
            ->where('request_type_id', 2)
            ->where('status', 'approved')
            ->whereDate('created_at', $this->selectedDate)
            ->get();

        return [
            // 'schedules'    => $schedules, // ===== SCHEDULE DISABLED =====
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
