<?php

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

    private const ROLE_GROUPS = [
        // ✅ ADMIN — unchanged
        'admin' => [
            'program-head-requests',
            'stock-materials',
            'facility-schedule',
            'users',
        ],

        // ✅ PROGRAM HEAD — unchanged
        'program head' => [
            'members',
            'requests',
            'program-head-requests',
        ],

        // ✅ FACULTY — two clean groups now
        'faculty' => [
            'facility-requests',
            'material-requests',
        ],
    ];

    private const ROLE_HIDDEN_PERMISSIONS = [
        'admin' => [
            'program-head-requests.create',
            'program-head-requests.update',
            'program-head-requests.delete',
        ],

        'program head' => [
            'requests.view-own',
            'requests.create',
            'requests.update-own',
            'requests.delete-own',
            'requests.cancel-own',
            'program-head-requests.approve',
            'program-head-requests.reject',
        ],

        // ✅ Faculty — nothing hidden now; all 5 actions per group are controllable
        'faculty' => [],
    ];

    private const DEFAULT_GROUPS = [
        'program-head-requests',
        'stock-materials',
        'facility-schedule',
        'users',
    ];

    public function mount(Role $role)
    {
        $this->role = $role;
        $this->name = $role->name;

        $this->selectedPermissions = $role->permissions
            ->pluck('name')
            ->filter(fn ($permName) => $this->isManageable($permName))
            ->values()
            ->toArray();
    }

    #[Computed()]
    public function permissions()
    {
        return Permission::select('id', 'name')
            ->get()
            ->filter(fn ($permission) => $this->isManageable($permission->name))
            ->values();
    }

    protected function allowedGroups(): array
    {
        return self::ROLE_GROUPS[$this->role->name] ?? self::DEFAULT_GROUPS;
    }

    protected function hiddenPermissions(): array
    {
        return self::ROLE_HIDDEN_PERMISSIONS[$this->role->name] ?? [];
    }

    protected function isManageable(string $permName): bool
    {
        if (in_array($permName, $this->hiddenPermissions(), true)) {
            return false;
        }

        $prefix = explode('.', $permName, 2)[0] ?? '';

        return in_array($prefix, $this->allowedGroups(), true);
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3|unique:roles,name,' . $this->role->id,
            'selectedPermissions' => 'array|min:1',
        ];
    }

    public function save()
    {
        $this->validate();

        $manageable = collect($this->selectedPermissions)
            ->filter(fn ($permName) => $this->isManageable($permName))
            ->values()
            ->toArray();

        if (empty($manageable)) {
            $this->addError('selectedPermissions', 'Please select at least one permission from the available groups.');
            return;
        }

        $preserved = $this->role->permissions
            ->pluck('name')
            ->filter(fn ($permName) => ! $this->isManageable($permName))
            ->values()
            ->toArray();

        $finalPermissions = array_values(array_unique(array_merge($preserved, $manageable)));

        $this->role->update([
            'name' => $this->name,
        ]);

        $this->role->syncPermissions($finalPermissions);

        session()->flash('success', 'Role updated successfully!');
    }
};
