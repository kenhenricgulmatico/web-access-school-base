<?php

namespace App\Livewire\ProgramHead;

use App\Models\AuditLog;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.coordinator')] class extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $actionFilter = '';
    public string $roleFilter   = '';

    public function updatingSearch(): void      { $this->resetPage(); }
    public function updatingActionFilter(): void { $this->resetPage(); }
    public function updatingRoleFilter(): void   { $this->resetPage(); }

    private function baseDeptQuery()
    {
        $departmentId = Auth::user()->department_id;

        return AuditLog::with(['user.roles', 'user.department'])
            ->whereHas('user', fn($q) =>
                $q->where('department_id', $departmentId)
                  ->whereHas('roles', fn($r) =>
                      $r->whereIn('name', ['student', 'faculty'])
                  )
            );
    }

    #[Computed]
    public function logs()
    {
        return $this->baseDeptQuery()
            ->when($this->search, fn($q) =>
                $q->whereHas('user', fn($u) =>
                    $u->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                )
            )
            ->when($this->actionFilter, fn($q) =>
                $q->where('action', $this->actionFilter)
            )
            ->when($this->roleFilter, fn($q) =>
                $q->whereHas('user.roles', fn($r) =>
                    $r->where('name', $this->roleFilter)
                )
            )
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function totalLogs(): int
    {
        return $this->baseDeptQuery()->count();
    }

    #[Computed]
    public function todayLogs(): int
    {
        return $this->baseDeptQuery()->whereDate('created_at', today())->count();
    }

    #[Computed]
    public function loginToday(): int
    {
        return $this->baseDeptQuery()
            ->where('action', 'login')
            ->whereDate('created_at', today())
            ->count();
    }

    #[Computed]
    public function studentLogs(): int
    {
        $departmentId = Auth::user()->department_id;
        return AuditLog::whereHas('user', fn($q) =>
            $q->where('department_id', $departmentId)
              ->whereHas('roles', fn($r) => $r->where('name', 'student'))
        )->count();
    }

    #[Computed]
    public function facultyLogs(): int
    {
        $departmentId = Auth::user()->department_id;
        return AuditLog::whereHas('user', fn($q) =>
            $q->where('department_id', $departmentId)
              ->whereHas('roles', fn($r) => $r->where('name', 'faculty'))
        )->count();
    }
};
