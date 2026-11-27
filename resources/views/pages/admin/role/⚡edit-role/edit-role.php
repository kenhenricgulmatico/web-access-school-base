<?php

use App\Support\PermissionCatalog;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.admin')] class extends Component
{
    public $role;
    public $name;
    public $selectedPermissions = [];

    public function mount(Role $role)
    {
        abort_unless(auth()->user()->can('roles.manage'), 403);

        $this->role = $role;
        $this->name = $role->name;

        $this->selectedPermissions = $role->permissions
            ->pluck('name')
            ->filter(fn ($permName) => $this->isManageable($permName) || $this->isLocked($permName))
            ->values()
            ->toArray();
    }

    /** ✅ Every feature this role's pages use (locked ones included, shown as fixed). */
    #[Computed()]
    public function permissions()
    {
        return Permission::select('id', 'name')
            ->get()
            ->filter(fn ($p) => $this->isManageable($p->name) || $this->isLocked($p->name))
            ->values();
    }

    #[Computed()]
    public function grouped(): array
    {
        return PermissionCatalog::orderFor(PermissionCatalog::grouped($this->permissions), $this->role->name);
    }

    /** "All" / "None" for one feature group. */
    public function toggleGroup(string $group): void
    {
        $names = collect($this->grouped[$group] ?? [])->pluck('name')->reject(fn ($n) => $this->isLocked($n));
        $allOn = $names->diff($this->selectedPermissions)->isEmpty();

        $this->selectedPermissions = $allOn
            ? collect($this->selectedPermissions)->diff($names)->values()->all()
            : collect($this->selectedPermissions)->merge($names)->unique()->values()->all();
    }

    public function isLocked(string $permName): bool
    {
        return in_array($permName, PermissionCatalog::lockedFor($this->role->name), true);
    }

    protected function isManageable(string $permName): bool
    {
        return ! $this->isLocked($permName) && PermissionCatalog::manageable($this->role->name, $permName);
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3|unique:roles,name,' . $this->role->id,
            'selectedPermissions' => 'array',
        ];
    }

    public function save()
    {
        abort_unless(auth()->user()->can('roles.manage'), 403);
        $this->validate();

        // ✅ the three built-in roles keep their names (routes and pages use them)
        if (in_array($this->role->name, ['admin', 'program head', 'faculty'], true) && $this->name !== $this->role->name) {
            $this->addError('name', 'The built-in roles (admin, program head, faculty) cannot be renamed.');
            return;
        }

        $manageable = collect($this->selectedPermissions)
            ->filter(fn ($permName) => $this->isManageable($permName))
            ->values();

        if ($manageable->isEmpty()) {
            $this->addError('selectedPermissions', 'Please select at least one permission.');
            return;
        }

        // Everything not on this checklist (Roles & Permissions, Reports…) stays as it was,
        // and locked ones are always kept.
        $preserved = $this->role->permissions
            ->pluck('name')
            ->filter(fn ($permName) => ! $this->isManageable($permName));

        $final = $preserved
            ->merge($manageable)
            ->merge(PermissionCatalog::lockedFor($this->role->name))
            ->unique()
            ->values()
            ->all();

        $this->role->update(['name' => $this->name]);
        $this->role->syncPermissions($final);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        session()->flash('success', 'Role updated successfully!');
    }
};
