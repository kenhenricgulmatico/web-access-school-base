<?php

namespace App\Livewire\Coordinator;

use App\Models\Request as ResourceRequest;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.coordinator')] class extends Component
{
    private function deptFilter($q)
    {
        $q->where('department_id', Auth::user()->department_id);
    }

    #[Computed]
    public function totalRequests()
    {
        return ResourceRequest::whereHas('user', fn($q) => $this->deptFilter($q))->count();
    }

    #[Computed]
    public function pendingRequests()
    {
        return ResourceRequest::whereIn('status', ['pending', 'admin_review', 'coordinator_review'])
            ->whereHas('user', fn($q) => $this->deptFilter($q))
            ->count();
    }

    #[Computed]
    public function approvedRequests()
    {
        return ResourceRequest::where('status', 'approved')
            ->whereHas('user', fn($q) => $this->deptFilter($q))
            ->count();
    }

    #[Computed]
    public function rejectedRequests()
    {
        return ResourceRequest::where('status', 'rejected')
            ->whereHas('user', fn($q) => $this->deptFilter($q))
            ->count();
    }

    #[Computed]
    public function facilityRequests()
    {
        return ResourceRequest::where('request_type_id', 1)
            ->whereHas('user', fn($q) => $this->deptFilter($q))
            ->count();
    }

    #[Computed]
    public function materialRequests()
    {
        return ResourceRequest::where('request_type_id', 2)
            ->whereHas('user', fn($q) => $this->deptFilter($q))
            ->count();
    }

    #[Computed]
    public function totalStudents()
    {
        return User::role('student')
            ->where('department_id', Auth::user()->department_id)
            ->count();
    }

     #[Computed]
    public function totalFaculty()
    {
        return User::role('faculty')
            ->where('department_id', Auth::user()->department_id)
            ->count();
    }

    #[Computed]
    public function monthlyData()
    {
        return ResourceRequest::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->whereHas('user', fn($q) => $this->deptFilter($q))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'month' => now()->month($r->month)->format('M'),
                'total' => $r->total,
            ]);
    }

    #[Computed]
public function userRequestHistory()
{
    return User::query()
        ->where('department_id', Auth::user()->department_id)
        ->where(function ($q) {
            $q->role('student')->orWhere(fn($q2) => $q2->role('faculty'));
        })
        ->withCount([
            'requests as facility_count' => fn($q) => $q->where('request_type_id', 1),
            'requests as material_count' => fn($q) => $q->where('request_type_id', 2),
        ])
        ->get()
        ->map(function ($user) {
            $user->total_count = $user->facility_count + $user->material_count;
            return $user;
        })
        ->filter(fn($user) => $user->total_count > 0)
        ->sortByDesc('total_count')
        ->values();
}

    #[Computed]
public function studentFacilityRequests()
{
    return ResourceRequest::where('request_type_id', 1)
        ->whereHas('user', function ($q) {
            $this->deptFilter($q);
            $q->role('student');
        })
        ->count();
}

#[Computed]
public function studentMaterialRequests()
{
    return ResourceRequest::where('request_type_id', 2)
        ->whereHas('user', function ($q) {
            $this->deptFilter($q);
            $q->role('student');
        })
        ->count();
}

#[Computed]
public function facultyFacilityRequests()
{
    return ResourceRequest::where('request_type_id', 1)
        ->whereHas('user', function ($q) {
            $this->deptFilter($q);
            $q->role('faculty');
        })
        ->count();
}

#[Computed]
public function facultyMaterialRequests()
{
    return ResourceRequest::where('request_type_id', 2)
        ->whereHas('user', function ($q) {
            $this->deptFilter($q);
            $q->role('faculty');
        })
        ->count();
}

};
