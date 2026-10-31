<?php

namespace App\Livewire\Admin;

use App\Models\RequestType;
use App\Models\Department;
use App\Models\Request as ResourceRequest;
use App\Models\Resource;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public ?int $historyUserId = null;

    // Year filter for the Monthly Requests chart
    public int $chartYear;

    public function mount()
    {
        $this->chartYear = (int) now()->year;
    }

    /**
     * Livewire hook — fires automatically when chartYear is updated.
     * Dispatches a browser event that the chart script listens for.
     */
    public function updatedChartYear()
    {
        // Force recompute of monthlyData (it caches by $this->chartYear)
        unset($this->monthlyData);

        // Send a signal to the front-end with fresh data
        $this->dispatch('chartYearChanged',
            labels: $this->monthlyData['labels'],
            series: $this->monthlyData['departments'],
            year:   $this->chartYear,
        );
    }

    public function showHistory($userId)
    {
        $isProgramHead = User::role('program head')->whereKey($userId)->exists();

        if (! $isProgramHead) {
            return;
        }

        $this->historyUserId = (int) $userId;
    }

    public function closeHistory()
    {
        $this->historyUserId = null;
    }

    private function adminVisibleRequests()
    {
        return ResourceRequest::whereIn('status', [
            'pending',
            'approved',
            'rejected',
            'cancelled',
        ])
        ->whereHas('user', function ($q) {
                $q->role('program head');
            });
    }

    #[Computed]
    public function totalRequests()
    {
        return $this->adminVisibleRequests()->count();
    }

    #[Computed]
    public function pendingRequests()
    {
        return $this->adminVisibleRequests()
            ->whereIn('status', ['pending'])
            ->count();
    }

    #[Computed]
    public function approvedRequests()
    {
        return $this->adminVisibleRequests()
            ->where('status', 'approved')
            ->count();
    }

    #[Computed]
    public function rejectedRequests()
    {
        return $this->adminVisibleRequests()
            ->where('status', 'rejected')
            ->count();
    }

    #[Computed]
    public function totalStudents()
    {
        return User::role('student')->count();
    }

    #[Computed]
    public function totalProgramHeads()
    {
        return User::role('program head')->count();
    }

    #[Computed]
    public function facilityRequests()
    {
        return $this->adminVisibleRequests()
            ->where('request_type_id', RequestType::FACILITY)
            ->count();
    }

    #[Computed]
    public function materialRequests()
    {
        return $this->adminVisibleRequests()
            ->where('request_type_id', RequestType::MATERIAL)
            ->count();
    }

    #[Computed]
    public function recentRequests()
    {
        $latestIds = $this->adminVisibleRequests()
            ->selectRaw('MAX(id) as id')
            ->groupBy('user_id')
            ->pluck('id');

        return ResourceRequest::whereIn('id', $latestIds)
            ->with(['user', 'requestType'])
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function programHeadIds()
    {
        return User::role('program head')->pluck('id')->all();
    }

    #[Computed]
    public function historyUser()
    {
        return $this->historyUserId ? User::find($this->historyUserId) : null;
    }

    #[Computed]
    public function historyRequests()
    {
        if (! $this->historyUserId) {
            return collect();
        }

        return ResourceRequest::where('user_id', $this->historyUserId)
            ->with(['requestType', 'department', 'items'])
            ->latest()
            ->get();
    }

    #[Computed]
    public function availableStockCount()
    {
        return Resource::where('quantity_available', '>', 10)->count();
    }

    #[Computed]
    public function lowStockCount()
    {
        return Resource::whereBetween('quantity_available', [1, 10])->count();
    }

    #[Computed]
    public function outOfStockCount()
    {
        return Resource::where('quantity_available', '<=', 0)->count();
    }

    #[Computed]
    public function totalStockItems()
    {
        return Resource::count();
    }

        /**
     * Years that have any requests in the database, plus the current year.
     * Uses PHP-side year extraction so it works on both MySQL and SQLite.
     */
    #[Computed]
    public function availableYears()
    {
        $years = $this->adminVisibleRequests()
            ->pluck('created_at')
            ->map(fn ($date) => (int) \Carbon\Carbon::parse($date)->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        $currentYear = (int) now()->year;

        if (! in_array($currentYear, $years, true)) {
            $years[] = $currentYear;
            rsort($years);
        }

        return $years;
    }

    #[Computed]
    public function monthlyData()
    {
        $rows = $this->adminVisibleRequests()
            ->whereYear('created_at', $this->chartYear)
            ->get(['created_at', 'department_id']);

        $labels = collect(range(1, 12))
            ->map(fn ($m) => now()->month($m)->format('M'))
            ->values();

        $departments = Department::all();

        $series = $departments->map(function ($dept) use ($rows) {
            $monthly = array_fill(1, 12, 0);

            $rows->where('department_id', $dept->id)->each(function ($row) use (&$monthly) {
                $month = (int) $row->created_at->format('n');
                $monthly[$month]++;
            });

            return [
                'name' => $dept->department_name,
                'data' => array_values($monthly),
            ];
        })->values();

        return [
            'labels'      => $labels,
            'departments' => $series,
        ];
    }
};
