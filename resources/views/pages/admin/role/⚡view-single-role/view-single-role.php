<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.admin')] class extends Component
{
    public Role $role;

    public function mount(Role $role): void
    {
        abort_unless(auth()->user()->can('roles.view'), 403);
        $this->role = $role->load('permissions');
    }

    #[Computed]
    public function usersCount(): int
    {
        return $this->role->users()->count();
    }

    #[Computed]
    public function users()
    {
        return $this->role->users()
            ->with('department')
            ->orderBy('name')
            ->take(10)
            ->get();
    }

    /**
     * Group permissions by their prefix (users.*, requests.*, materials.*, etc.)
     */
    /** Permissions grouped by feature, in the same order and names as Edit Role. */
    #[Computed]
    public function groupedPermissions(): array
    {
        return \App\Support\PermissionCatalog::orderFor(\App\Support\PermissionCatalog::grouped($this->role->permissions->pluck('name')->all()), $this->role->name);
    }

    public function delete(): void
    {
        abort_unless(auth()->user()->can('roles.manage'), 403);
        // Safety: don't delete roles that are still assigned to users
        if ($this->role->users()->count() > 0) {
            session()->flash('error', 'Cannot delete this role — it is still assigned to ' . $this->role->users()->count() . ' user(s).');
            return;
        }

        // Safety: don't delete core system roles
        if (in_array($this->role->name, ['admin', 'program head', 'faculty'], true)) {
            session()->flash('error', 'Core system roles cannot be deleted.');
            return;
        }

        $this->role->delete();

        session()->flash('success', 'Role deleted successfully.');

        $this->redirect(route('admin.roles'), navigate: true);
    }
};
