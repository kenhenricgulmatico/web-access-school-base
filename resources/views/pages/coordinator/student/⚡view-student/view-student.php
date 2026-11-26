<?php

namespace App\Livewire\ProgramHead;

use App\Models\User;
use App\Notifications\NewUserPendingNotification;
use App\Notifications\AccountCreatedByProgramHeadNotification;
use App\Models\Notification as InAppNotification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
    public string $createRole      = 'faculty';

    // Edit modal
    public bool   $showEditModal = false;
    public ?int   $editingId     = null;
    public string $editName      = '';
    public string $editEmail     = '';
    public string $editStatus    = '';
    public string $editRole      = '';

    public function mount(): void
    {
        // ✅ Server-side guard — page requires `members.view`
        abort_unless(auth()->user()->can('members.view'), 403);
    }

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
                $q->whereIn('name', ['faculty'])
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
    public function counts(): array
    {
        $row = User::where('users.department_id', $this->departmentId)
            ->join('model_has_roles', function ($j) {
                $j->on('model_has_roles.model_id', '=', 'users.id')
                  ->where('model_has_roles.model_type', User::class);
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereIn('roles.name', ['faculty'])
            ->selectRaw("
                COUNT(DISTINCT users.id) as total,
                COUNT(DISTINCT CASE WHEN roles.name = 'faculty' THEN users.id END) as faculty,
                COUNT(DISTINCT CASE WHEN users.status = 'approved' THEN users.id END) as approved,
                COUNT(DISTINCT CASE WHEN users.status = 'pending' THEN users.id END) as pending
            ")
            ->first();

        return [
            'total'    => (int) $row->total,
            'faculty'  => (int) $row->faculty,
            'approved' => (int) $row->approved,
            'pending'  => (int) $row->pending,
        ];
    }

    // ── Create ──────────────────────────────────────────────
    public function openCreate(): void
    {
        // ✅ Server-side guard — creating requires `members.create`
        abort_unless(auth()->user()->can('members.create'), 403);

        $this->reset(['createName', 'createEmail', 'createPassword']);
        $this->createRole      = 'faculty';
        $this->showCreateModal = true;
    }

    public function create(): void
    {
        // ✅ Server-side guard — creating requires `members.create`
        abort_unless(auth()->user()->can('members.create'), 403);

        $this->validate([
            'createName'     => 'required|string|max:255',
            'createEmail'    => 'required|email|unique:users,email|ends_with:@csav.edu.ph',
            'createPassword' => 'required|string|min:6',
            'createRole'     => 'required|in:faculty',
        ]);

        $plainPassword = $this->createPassword;

        $user = User::create([
            'name'          => $this->createName,
            'email'         => $this->createEmail,
            'password'      => Hash::make($plainPassword),
            'department_id' => $this->departmentId,
            'status'        => 'pending',
        ]);

        $user->assignRole($this->createRole);

        $roleLabel = ucfirst($this->createRole);

        // ── 1. Welcome email to the NEW USER (from the Program Head) ──
        try {
            $user->notify(new AccountCreatedByProgramHeadNotification(
                userName:         $user->name,
                userEmail:        $user->email,
                defaultPassword:  $plainPassword,
                role:             $roleLabel,
                department:       $this->departmentName,
                programHeadName:  Auth::user()->name,
            ));
        } catch (\Throwable $e) {
            \Log::warning('AccountCreatedByProgramHeadNotification failed: ' . $e->getMessage());
        }

        // ── 2. Notify every admin (email + in-app bell) that a new account awaits approval ──
        User::role('admin')->get()->each(function ($admin) use ($user, $roleLabel) {
            try {
                $admin->notify(new NewUserPendingNotification(
                    userName:   $user->name,
                    userEmail:  $user->email,
                    role:       $roleLabel,
                    department: $this->departmentName,
                ));
            } catch (\Throwable $e) {
                \Log::warning('NewUserPendingNotification failed: ' . $e->getMessage());
            }

            InAppNotification::create([
                'user_id' => $admin->id,
                'message' => "New {$roleLabel} registration: {$user->name} is waiting for account approval.",
            ]);
        });

        $this->showCreateModal = false;
        session()->flash('success', $roleLabel . ' created successfully. Welcome email sent. Waiting for admin approval.');

        unset($this->counts, $this->students);
    }

    // ── Edit ────────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        // ✅ Server-side guard — editing requires `members.update`
        abort_unless(auth()->user()->can('members.update'), 403);

        $user = User::with('roles')->findOrFail($id);

        if ($user->department_id !== $this->departmentId) return;

        $this->editingId     = $id;
        $this->editName      = $user->name;
        $this->editEmail     = $user->email;
        $this->editStatus    = $user->status;
        $this->editRole      = $user->roles->first()?->name ?? 'faculty';
        $this->showEditModal = true;
    }

    public function update(): void
    {
        // ✅ Server-side guard — updating requires `members.update`
        abort_unless(auth()->user()->can('members.update'), 403);

        $this->validate([
            'editName'   => 'required|string|max:255',
            'editEmail'  => 'required|email|ends_with:@csav.edu.ph|unique:users,email,' . $this->editingId,
            'editStatus' => 'required|in:pending,approved,rejected',
            'editRole'   => 'required|in:faculty',
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

        unset($this->counts, $this->students);
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
        // ✅ Server-side guard — deleting requires `members.delete`
        abort_unless(auth()->user()->can('members.delete'), 403);

        $user = User::findOrFail($id);

        if ($user->department_id !== $this->departmentId) return;

        // Remove the member's avatar file so it doesn't get orphaned
        if ($user->avatar && file_exists(public_path($user->avatar))) {
            @unlink(public_path($user->avatar));
        }

        $user->delete();
        session()->flash('success', 'User deleted successfully.');

        unset($this->counts, $this->students);
    }
};
