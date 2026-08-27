<?php

namespace App\Livewire\ProgramHead;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

new #[Layout('layouts.coordinator')] class extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $statusFilter = '';
    public string $roleFilter   = '';

    // Create modal
    public bool   $showCreateModal = false;
    public string $createName      = '';
    public string $createEmail     = '';
    public string $createPassword  = '';
    public string $createRole      = 'student';

    // Edit modal
    public bool   $showEditModal = false;
    public ?int   $editingId     = null;
    public string $editName      = '';
    public string $editEmail     = '';
    public string $editStatus    = '';
    public string $editRole      = '';

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingRoleFilter(): void   { $this->resetPage(); }

    #[Computed]
    public function departmentId(): ?int
    {
        return Auth::user()->department_id;
    }

    #[Computed]
    public function departmentName(): string
    {
        return Auth::user()->department?->department_name ?? 'Your Department';
    }

    #[Computed]
    public function students()
    {
        return User::with('roles', 'department')
            ->where('department_id', $this->departmentId)
            ->whereHas('roles', fn($q) =>
                $q->whereIn('name', ['student', 'faculty'])
            )
            ->when($this->search, fn($q) =>
                $q->where(fn($q) =>
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                )
            )
            ->when($this->statusFilter, fn($q) =>
                $q->where('status', $this->statusFilter)
            )
            ->when($this->roleFilter, fn($q) =>
                $q->whereHas('roles', fn($r) => $r->where('name', $this->roleFilter))
            )
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function totalCount(): int
    {
        return User::where('department_id', $this->departmentId)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['student', 'faculty']))
            ->count();
    }

    #[Computed]
    public function studentCount(): int
    {
        return User::where('department_id', $this->departmentId)
            ->whereHas('roles', fn($q) => $q->where('name', 'student'))
            ->count();
    }

    #[Computed]
    public function facultyCount(): int
    {
        return User::where('department_id', $this->departmentId)
            ->whereHas('roles', fn($q) => $q->where('name', 'faculty'))
            ->count();
    }

    #[Computed]
    public function approvedCount(): int
    {
        return User::where('department_id', $this->departmentId)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['student', 'faculty']))
            ->where('status', 'approved')
            ->count();
    }

    #[Computed]
    public function pendingCount(): int
    {
        return User::where('department_id', $this->departmentId)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['student', 'faculty']))
            ->where('status', 'pending')
            ->count();
    }

    // ── Create ──────────────────────────────────────────────
    public function openCreate(): void
    {
        $this->reset(['createName', 'createEmail', 'createPassword']);
        $this->createRole      = 'student';
        $this->showCreateModal = true;
    }

    public function create(): void
    {
        $this->validate([
            'createName'     => 'required|string|max:255',
            'createEmail'    => 'required|email|unique:users,email|ends_with:@csav.edu.ph',
            'createPassword' => 'required|string|min:6',
            'createRole'     => 'required|in:student,faculty',
        ]);

        $user = User::create([
            'name'          => $this->createName,
            'email'         => $this->createEmail,
            'password'      => Hash::make($this->createPassword),
            'department_id' => $this->departmentId,
            'status'        => 'pending', // ✅ admin must approve first
        ]);

        $user->assignRole($this->createRole);

        $this->showCreateModal = false;
        session()->flash('success', ucfirst($this->createRole) . ' created successfully. Waiting for admin approval.');
    }

    // ── Edit ────────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $user = User::with('roles')->findOrFail($id);

        if ($user->department_id !== $this->departmentId) return;

        $this->editingId     = $id;
        $this->editName      = $user->name;
        $this->editEmail     = $user->email;
        $this->editStatus    = $user->status;
        $this->editRole      = $user->roles->first()?->name ?? 'student';
        $this->showEditModal = true;
    }

    public function update(): void
    {
        $this->validate([
            'editName'   => 'required|string|max:255',
            'editEmail'  => 'required|email|ends_with:@csav.edu.ph|unique:users,email,' . $this->editingId,
            'editStatus' => 'required|in:pending,approved,rejected',
            'editRole'   => 'required|in:student,faculty',
        ]);

        $user = User::findOrFail($this->editingId);

        if ($user->department_id !== $this->departmentId) return;

        $user->update([
            'name'   => $this->editName,
            'email'  => $this->editEmail,
            'status' => $this->editStatus,
        ]);

        $user->syncRoles([$this->editRole]);

        $this->showEditModal = false;
        session()->flash('success', 'User updated successfully.');
    }

    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal   = false;
        $this->editingId       = null;
    }

    // ── Delete ──────────────────────────────────────────────
    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->department_id !== $this->departmentId) return;

        $user->delete();
        session()->flash('success', 'User deleted successfully.');
    }
};
