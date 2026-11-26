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
     * Same "safe default" groups the Edit Role page falls back to
     * for roles that aren't admin / program head / faculty.
     */
    private const DEFAULT_GROUPS = [
        'program-head-requests',
        'stock-materials',
        'facility-schedule',
        'users',
    ];

    /**
     * Permissions that should never be grantable from the Create page,
     * matching the Edit Role page's admin hidden list.
     */
    private const HIDDEN_PERMISSIONS = [
        'program-head-requests.create',
        'program-head-requests.update',
        'program-head-requests.delete',
    ];

    public function mount()
    {
        $this->permissions(); // warm the computed property
    }

    #[Computed()]
    public function permissions()
    {
        return Permission::select('id', 'name')
            ->get()
            ->filter(fn ($permission) => $this->isManageable($permission->name))
            ->values();
    }

    protected function isManageable(string $permName): bool
    {
        if (in_array($permName, self::HIDDEN_PERMISSIONS, true)) {
            return false;
        }

        $prefix = explode('.', $permName, 2)[0] ?? '';

        return in_array($prefix, self::DEFAULT_GROUPS, true);
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
        unset($this->permissions);
    }
};
