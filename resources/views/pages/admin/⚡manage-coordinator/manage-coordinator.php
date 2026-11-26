<?php

use App\Traits\ReviewsProgramHeadRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;
    use ReviewsProgramHeadRequests; // approve / reject (shared with the View page)

    public $statusFilter = '';
    public $search       = '';

    #[Computed]
    public function requests()
    {
        $query = $this->incomingRequestsQuery()
            ->with(['user', 'department', 'requestType', 'items.resource']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('purpose', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', fn($u) =>
                        $u->where('name', 'like', '%' . $this->search . '%')
                    )
                    ->orWhereHas('department', fn($d) =>
                        $d->where('department_name', 'like', '%' . $this->search . '%')
                    );
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return $query->latest()->paginate(5);
    }

    #[Computed]
    public function pendingCount()
    {
        return $this->incomingRequestsQuery()->where('status', 'pending')->count();
    }

    #[Computed]
    public function approvedCount()
    {
        return $this->incomingRequestsQuery()->where('status', 'approved')->count();
    }

    #[Computed]
    public function rejectedCount()
    {
        return $this->incomingRequestsQuery()->where('status', 'rejected')->count();
    }

    #[Computed]
    public function departmentAllocations()
    {
        return DB::table('resource_all_locations')
            ->join('resources', 'resources.id', '=', 'resource_all_locations.resource_id')
            ->join('departments', 'departments.id', '=', 'resource_all_locations.department_id')
            ->where('resource_all_locations.allocated_quantity', '>', 0)
            ->select(
                'resource_all_locations.id',
                'resource_all_locations.allocated_quantity',
                'resource_all_locations.updated_at',
                'resources.resource_name',
                'departments.department_name'
            )
            ->orderByDesc('resource_all_locations.updated_at')
            ->limit(10)
            ->get();
    }

    public function clearFilters()
    {
        $this->reset('search');
    }
};
