<?php

namespace App\Livewire\Admin;

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

    public function showHistory($userId)
    {
        // Only program heads can have their history opened
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

    /**
     * Only count requests that have reached the admin's visibility scope.
     * Requests still at 'pending' (with student/faculty) are excluded.
     */
    private function adminVisibleRequests()
    {
        return ResourceRequest::whereIn('status', [
            'pending',
            'approved',
            'rejected',
            'cancelled',
        ]);
    }

    #[Computed]
    public function totalRequests()
    {
        return $this->adminVisibleRequests()->count();
    }

    #[Computed]
    public function pendingRequests()
    {
        // "Pending" for admin = waiting at coordinator or admin level
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
            ->where('request_type_id', 1)
            ->count();
    }

    #[Computed]
    public function materialRequests()
    {
        return $this->adminVisibleRequests()
            ->where('request_type_id', 2)
            ->count();
    }

    #[Computed]
    public function recentRequests()
    {
        // One row per requester: their latest request only
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

    /**
     * ✅ New: Stock analytics — Available / Low Stock / Out of Stock,
     * based on quantity_available on the resources table.
     */
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
     * ✅ Updated: monthly totals broken down PER DEPARTMENT,
     * so "Monthly Requests" shows which department leads each month.
     * Shape: [
     *   'labels' => ['Jan', 'Feb', ...],
     *   'departments' => [
     *       ['name' => 'Computer Studies', 'data' => [3, 5, 0, ...]],
     *       ['name' => 'Engineering', 'data' => [1, 2, 4, ...]],
     *       ...
     *   ]
     * ]
     */
    #[Computed]
    public function monthlyData()
    {
        $rows = $this->adminVisibleRequests()
            ->whereYear('created_at', now()->year)
            ->get(['created_at', 'department_id']);

        $labels = collect(range(1, 12))->map(fn ($m) => now()->month($m)->format('M'))->values();

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
            'labels' => $labels,
            'departments' => $series,
        ];
    }
};
