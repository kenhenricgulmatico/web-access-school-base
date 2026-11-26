<?php

use App\Models\Department;
use App\Models\User;
use App\Notifications\AccountCreatedByAdminNotification;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    private const DEFAULT_PASSWORD = 'csav.csav';

    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $department_id = '';
    public $role = '';

    #[Computed()]
    public function departments()
    {
        return Department::orderBy('department_name')->get(['id', 'department_name']);
    }

    #[Computed()]
    public function roles()
    {
        return \Spatie\Permission\Models\Role::orderBy('name')
            ->get()
            ->mapWithKeys(function ($role) {
                return [$role->name => ucwords($role->name)];
            })
            ->toArray();
    }

    #[Computed()]
    public function defaultPassword(): string
    {
        return self::DEFAULT_PASSWORD;
    }

    protected function rules()
    {
        return [
            'name'            => 'required|string|min:3',
            'email'           => 'required|email|unique:users,email|ends_with:@csav.edu.ph',
            'password'        => 'required|string|min:6|confirmed',
            'department_id'   => 'required|exists:departments,id',
            'role'            => 'required|in:program head,faculty',
        ];
    }

    protected $messages = [
        'name.required'          => 'Please enter the user\'s full name.',
        'name.min'               => 'Name must be at least 3 characters.',
        'email.required'         => 'Please enter an email address.',
        'email.email'            => 'Please enter a valid email address.',
        'email.unique'           => 'This email address is already registered.',
        'email.ends_with'        => 'Email must end with @csav.edu.ph.',
        'password.required'      => 'Please enter a password.',
        'password.min'           => 'Password must be at least 6 characters.',
        'password.confirmed'     => 'Password confirmation does not match.',
        'department_id.required' => 'Please select a department.',
        'department_id.exists'   => 'Selected department is invalid.',
        'role.required'          => 'Please select a role.',
        'role.in'                => 'Selected role is invalid.',
    ];

    // ✅ Auto-fill name from email (only if name is empty)
    public function updatedEmail($value): void
    {
        if (! empty($this->name)) {
            return;
        }

        if (! $value) {
            return;
        }

        $lower = strtolower(trim($value));

        if (! str_ends_with($lower, '@csav.edu.ph')) {
            return;
        }

        $localPart = strstr($lower, '@', true);

        if (! $localPart) {
            return;
        }

        $this->name = ucfirst($localPart);
    }

    public function useDefaultPassword(): void
    {
        $this->password              = self::DEFAULT_PASSWORD;
        $this->password_confirmation = self::DEFAULT_PASSWORD;

        $this->resetErrorBag(['password', 'password_confirmation']);
    }

    public function save()
    {
        $this->validate();

        $plainPassword = $this->password;

        $user = User::create([
            'name'          => $this->name,
            'email'         => strtolower($this->email),   // ✅ lowercased
            'password'      => Hash::make($plainPassword),
            'department_id' => $this->department_id,
            'status'        => 'approved',
        ]);

        $user->assignRole($this->role);

        $roleLabel = $this->roles[$this->role] ?? ucfirst($this->role);

        $departmentName = Department::find($this->department_id)?->department_name;

        try {
            $user->notify(new AccountCreatedByAdminNotification(
                userName:        $user->name,
                userEmail:       $user->email,
                defaultPassword: $plainPassword,
                role:            $roleLabel,
                department:      $departmentName,
            ));
        } catch (\Throwable $e) {
            \Log::warning('AccountCreatedByAdminNotification failed: ' . $e->getMessage());
        }

        $this->reset(['name', 'email', 'password', 'password_confirmation', 'department_id']);
        $this->role = 'program head';

        session()->flash('success', "{$roleLabel} created successfully! A welcome email has been sent to {$user->email}.");
    }
};
