<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

new #[Layout('layouts.admin')] class extends Component
{
    public array $selectedRoles = [];
    public bool   $selectAll    = false;

    /**
     * Core role names that require typed confirmation to delete.
     */
    public const CORE_ROLES = ['admin', 'program head', 'faculty'];

    /**
     * Default permission groups used when restoring missing core roles.
     * Keep these in sync with PermissionSeeder.
     */
    public const CORE_ROLE_PERMISSIONS = [
        'admin' => [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'users.approve', 'users.reset-password',
            'roles.view', 'roles.manage',
            'departments.view', 'departments.manage',
            'requests.view-all', 'requests.approve', 'requests.reject',
            'program-head-requests.view', 'program-head-requests.view-all',
            'program-head-requests.approve', 'program-head-requests.reject',
            'stock-materials.view', 'stock-materials.view-all',
            'stock-materials.create', 'stock-materials.update', 'stock-materials.delete',
            'facility-schedule.view', 'facility-schedule.view-all',
            'facility-schedule.create', 'facility-schedule.update', 'facility-schedule.delete',
            'allocations.view', 'allocations.manage',
            'audit.view-all',
            'reports.view-all',
        ],
        'program head' => [
            'members.view', 'members.create', 'members.update', 'members.delete',
            'requests.view-department', 'requests.approve', 'requests.reject',
            'program-head-requests.view', 'program-head-requests.create',
            'program-head-requests.update', 'program-head-requests.delete',
            'stock-materials.view',
            'facility-schedule.view',
            'allocations.view',
            'audit.view-own-department', 'reports.view-department',
        ],
        'faculty' => [
            'facility-requests.view', 'facility-requests.create',
            'facility-requests.update', 'facility-requests.cancel',
            'material-requests.view', 'material-requests.create',
            'material-requests.update', 'material-requests.cancel',
            'facility-schedule.view', 'stock-materials.view', 'reports.view-own',
        ],
    ];

    // ── Delete-modal state ──
    public bool    $showDeleteModal    = false;
    public ?array  $pendingDeleteIds   = null;
    public string  $deleteConfirmation = '';

    public function mount(): void
    {
        abort_unless(Auth::user()->can('roles.view'), 403);
    }

    #[Computed]
    public function roles()
    {
        return Role::with('permissions')
            ->withCount('users')
            ->select('id', 'name', 'created_at')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function missingCoreRoles(): array
    {
        $existing = Role::whereIn('name', self::CORE_ROLES)->pluck('name')->all();
        return array_values(array_diff(self::CORE_ROLES, $existing));
    }

    public function updatedSelectAll($value): void
    {
        $this->selectedRoles = $value
            ? $this->roles->pluck('id')->toArray()
            : [];
    }

    public function updatedSelectedRoles(): void
    {
        $this->selectAll = count($this->selectedRoles) === $this->roles->count();
    }

    // ============================================================
    // DELETE FLOW
    // ============================================================

    public function deleteSelected(): void
    {
        abort_unless(Auth::user()->can('roles.manage'), 403);

        if (empty($this->selectedRoles)) {
            session()->flash('error', 'No roles selected.');
            return;
        }

        $roles = Role::whereIn('id', $this->selectedRoles)->withCount('users')->get();

        // Block deleting your own role
        $ownRole = Auth::user()->getRoleNames()->first();
        if ($ownRole && $roles->contains('name', $ownRole)) {
            session()->flash('error', "You cannot delete your own role ({$ownRole}).");
            return;
        }

        // Block deleting the last admin-capable role
        if ($roles->contains('name', 'admin')) {
            $remainingAdmins = Role::where('name', 'admin')
                ->whereNotIn('id', $this->selectedRoles)
                ->exists();

            if (!$remainingAdmins) {
                session()->flash('error', 'Cannot delete the last remaining "admin" role — you would be locked out.');
                return;
            }
        }

        // Core roles → typed confirmation modal
        if ($roles->whereIn('name', self::CORE_ROLES)->isNotEmpty()) {
            $this->pendingDeleteIds   = $this->selectedRoles;
            $this->deleteConfirmation = '';
            $this->showDeleteModal    = true;
            return;
        }

        // Custom roles → delete directly
        $this->performDelete($this->selectedRoles);
    }

    public function confirmCoreDelete(): void
    {
        abort_unless(Auth::user()->can('roles.manage'), 403);

        if (strtoupper(trim($this->deleteConfirmation)) !== 'DELETE') {
            $this->addError('deleteConfirmation', 'Type DELETE (uppercase) to confirm.');
            return;
        }

        if (!$this->pendingDeleteIds) {
            return;
        }

        $this->performDelete($this->pendingDeleteIds);
    }

    public function cancelCoreDelete(): void
    {
        $this->showDeleteModal    = false;
        $this->pendingDeleteIds   = null;
        $this->deleteConfirmation = '';
        $this->resetErrorBag('deleteConfirmation');
    }

    protected function performDelete(array $ids): void
    {
        $roles = Role::whereIn('id', $ids)->withCount('users')->get();

        $summary = $roles->map(fn ($r) => [
            'name'  => $r->name,
            'users' => $r->users_count,
        ])->values()->all();

        $totalUsers = (int) $roles->sum('users_count');

        // Cascade: FK on model_has_roles strips the role from users
        Role::whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'deleted',
            'table_name' => 'roles',
            'record'     => [
                'deleted_roles'  => $summary,
                'affected_users' => $totalUsers,
                'at'             => now()->toDateTimeString(),
            ],
        ]);

        $this->selectedRoles      = [];
        $this->selectAll          = false;
        $this->showDeleteModal    = false;
        $this->pendingDeleteIds   = null;
        $this->deleteConfirmation = '';

        session()->flash(
            'success',
            "Deleted {$roles->count()} role(s). {$totalUsers} user(s) lost their role assignment."
        );
    }

    // ============================================================
    // RESTORE MISSING CORE ROLES
    // ============================================================

    public function restoreCoreRoles(): void
    {
        abort_unless(Auth::user()->can('roles.manage'), 403);

        $missing = $this->missingCoreRoles();

        if (empty($missing)) {
            session()->flash('error', 'All core roles already exist.');
            return;
        }

        foreach ($missing as $name) {
            $role = Role::firstOrCreate([
                'name'       => $name,
                'guard_name' => 'web',
            ]);

            $perms = array_values(array_filter(
                self::CORE_ROLE_PERMISSIONS[$name] ?? [],
                fn ($p) => Permission::where('name', $p)->where('guard_name', 'web')->exists()
            ));

            $role->syncPermissions($perms);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'restored',
            'table_name' => 'roles',
            'record'     => ['restored' => $missing, 'at' => now()->toDateTimeString()],
        ]);

        session()->flash('success', 'Restored: ' . implode(', ', $missing));
    }
};
