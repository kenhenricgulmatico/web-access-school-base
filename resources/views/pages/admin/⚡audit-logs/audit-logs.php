<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = 'all';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    /**
     * Base query: only logs belonging to student, faculty, or program head
     * (i.e. exclude the admin's own actions).
     */
    protected function baseQuery()
    {
        return AuditLog::with('user.roles')
            ->whereHas('user.roles', function ($q) {
                $q->whereIn('name', ['student', 'faculty', 'program head']);
            })
            ->when($this->roleFilter !== 'all', function ($query) {
                $query->whereHas('user.roles', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            });
    }

    #[Computed]
    public function logs()
    {
        return $this->baseQuery()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('action', 'like', '%' . $this->search . '%')
                        ->orWhere('table_name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($uq) {
                            $uq->where('name', 'like', '%' . $this->search . '%')
                               ->orWhere('email', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function totalLogs()
    {
        return $this->baseQuery()->count();
    }

    #[Computed]
    public function todayLogs()
    {
        return $this->baseQuery()->whereDate('created_at', today())->count();
    }
};
