<?php

namespace App\Livewire\Coordinator;

use App\Models\RequestType;
use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
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
        return ResourceRequest::whereIn('status', ['pending'])
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
        return ResourceRequest::where('request_type_id', RequestType::FACILITY)
            ->whereHas('user', fn($q) => $this->deptFilter($q))
            ->count();
    }

    #[Computed]
    public function materialRequests()
    {
        return ResourceRequest::where('request_type_id', RequestType::MATERIAL)
            ->whereHas('user', fn($q) => $this->deptFilter($q))
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
                $q->role('faculty');
            })
            ->withCount([
                'requests as facility_count' => fn($q) => $q->where('request_type_id', RequestType::FACILITY),
                'requests as material_count' => fn($q) => $q->where('request_type_id', RequestType::MATERIAL),
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

    /**
     * ✅ "Waiting for you": pending requests the Program Head must accept / forward / reject.
     *    Same rules as the Facility and Material pages, so the numbers match those lists.
     */
    #[Computed]
    public function waitingForMe(): array
    {
        $dept = Auth::user()->department_id;

        // Facility page: anyone in the department who isn't a Program Head
        $facility = ResourceRequest::where('request_type_id', RequestType::FACILITY)
            ->where('status', 'pending')
            ->whereHas('user', fn ($q) => $q
                ->where('department_id', $dept)
                ->whereDoesntHave('roles', fn ($r) => $r->where('name', 'program head')))
            ->count();

        // Material page: faculty of the department
        $material = ResourceRequest::where('request_type_id', RequestType::MATERIAL)
            ->where('status', 'pending')
            ->where('department_id', $dept)
            ->whereHas('user', fn ($q) => $q->role(['faculty']))
            ->count();

        return ['facility' => $facility, 'material' => $material, 'total' => $facility + $material];
    }

    /** ✅ "Waiting for Admin": the Program Head's own requests the Admin hasn't decided yet. */
    #[Computed]
    public function waitingForAdmin(): int
    {
        return ResourceRequest::where('user_id', Auth::id())
            ->whereNull('forwarded_from_request_id') // same list as "Request to Admin"
            ->where('status', 'pending')
            ->count();
    }

    /**
     * ✅ Borrowed items still out (students & faculty of the department).
     *    Same rules as the Borrowed Items page.
     */
    #[Computed]
    public function borrowedItems(): array
    {
        $base = RequestItem::whereNotNull('due_back_at')
            ->whereNull('returned_at')
            ->whereHas('request', fn ($q) => $q
                ->where('department_id', Auth::user()->department_id)
                ->where('status', 'approved')
                ->whereHas('user', fn ($u) => $u
                    ->whereDoesntHave('roles', fn ($r) => $r->where('name', 'program head'))));

        return [
            'out'     => (clone $base)->count(),
            'overdue' => (clone $base)->whereDate('due_back_at', '<', today())->count(),
        ];
    }

    /** ✅ Request Status donut: approved / pending / rejected for the department. */
    #[Computed]
    public function statusBreakdown(): array
    {
        $slices = [
            ['label' => 'Approved', 'count' => (int) $this->approvedRequests, 'color' => '#1C6B45'],
            ['label' => 'Pending',  'count' => (int) $this->pendingRequests,  'color' => '#F59E0B'],
            ['label' => 'Rejected', 'count' => (int) $this->rejectedRequests, 'color' => '#B8352A'],
        ];

        $total = array_sum(array_column($slices, 'count'));

        foreach ($slices as &$s) {
            $s['percent'] = $total ? round($s['count'] / $total * 100, 1) : 0;
        }
        unset($s);

        return ['total' => $total, 'slices' => $slices];
    }

    #[Computed]
    public function facultyFacilityRequests()
    {
        return ResourceRequest::where('request_type_id', RequestType::FACILITY)
            ->whereHas('user', function ($q) {
                $this->deptFilter($q);
                $q->role('faculty');
            })
            ->count();
    }

    #[Computed]
    public function facultyMaterialRequests()
    {
        return ResourceRequest::where('request_type_id', RequestType::MATERIAL)
            ->whereHas('user', function ($q) {
                $this->deptFilter($q);
                $q->role('faculty');
            })
            ->count();
    }
};
