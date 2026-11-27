<?php

use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new #[Layout('layouts::admin')] class extends Component
{
    public $name;
    public $selectedPermissions = [];

    /**
     * ✅ A new (custom) role can be given the Admin-side features — same list Edit Role uses.
     *    It can't get Roles & Permissions itself (only the Admin role has that).
     */
    public function mount()
    {
        abort_unless(auth()->user()->can('roles.manage'), 403);
    }

    #[Computed()]
    public function permissions()
    {
        return Permission::select('id', 'name')
            ->get()
            ->filter(fn ($permission) => $this->isManageable($permission->name))
            ->values();
    }

    #[Computed()]
    public function grouped(): array
    {
        return \App\Support\PermissionCatalog::grouped($this->permissions);
    }

    /** "All" / "None" for one feature group. */
    public function toggleGroup(string $group): void
    {
        $names = collect($this->grouped[$group] ?? [])->pluck('name');
        $allOn = $names->diff($this->selectedPermissions)->isEmpty();

        $this->selectedPermissions = $allOn
            ? collect($this->selectedPermissions)->diff($names)->values()->all()
            : collect($this->selectedPermissions)->merge($names)->unique()->values()->all();
    }

    protected function isManageable(string $permName): bool
    {
        return \App\Support\PermissionCatalog::manageable(null, $permName);
    }

    public function rules()
    {
        return [
            'name'                => 'required|string|min:3|unique:roles,name',
            'selectedPermissions' => 'required|array|min:1',
            'selectedPermissions.*' => 'exists:permissions,name',
        ];
    }

    public function messages()
    {
        return [
            'name.required'               => 'The role name is required.',
            'name.unique'                 => 'The role name must be unique/already taken.',
            'selectedPermissions.required' => 'Please select at least one permission.',
            'selectedPermissions.min'     => 'Please select at least one permission.',
        ];
    }

    public function save()
    {
        abort_unless(auth()->user()->can('roles.manage'), 403);
        $this->validate();

        // Sanitize the role name
        $roleName = Str::of($this->name)->trim()->title()->lower()->value();

        // 1. Create the role
        $role = Role::create([
            'name' => $roleName,
        ]);

        // 2. Only sync permissions this page is allowed to grant
        $manageable = collect($this->selectedPermissions)
            ->filter(fn ($permName) => $this->isManageable($permName))
            ->values()
            ->toArray();

        $role->syncPermissions($manageable);

        session()->flash('success', 'Role created successfully.');

        $this->reset([
            'name',
            'selectedPermissions',
        ]);

        // Refresh the computed cache after reset
        unset($this->permissions, $this->grouped);
    }
};
