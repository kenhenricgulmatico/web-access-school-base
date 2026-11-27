<?php

use App\Models\Department;
use App\Models\User;
use App\Notifications\PasswordResetByAdminNotification;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.admin')] class extends Component
{
    private const DEFAULT_PASSWORD = 'csav.csav';

    public $user;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $department_id = '';
    public $role = '';

    public function mount($id)
    {
        abort_unless(auth()->user()->can('users.update'), 403);
        $this->user          = User::with('roles')->findOrFail($id);
        $this->name          = $this->user->name;
        $this->email         = $this->user->email;
        $this->department_id = $this->user->department_id ?? '';
        $this->role          = $this->user->roles->first()?->name ?? '';
    }

    #[Computed()]
    public function departments()
    {
        return Department::orderBy('department_name')->get(['id', 'department_name']);
    }

    #[Computed()]
    public function isAdmin()
    {
        return $this->user->hasRole('admin');
    }

    /**
     * ✅ Same choices as Add User: Program Head or Faculty — never Admin
     *    (one wrong click must not turn a teacher into a full admin).
     */
    #[Computed()]
    public function roles()
    {
        return Role::whereIn('name', \App\Support\Accounts::ASSIGNABLE_ROLES)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->name => ucwords($r->name)])
            ->toArray();
    }

    /** ✅ Their pending requests / borrowed items (for the role / department warning). */
    #[Computed()]
    public function openItems(): array
    {
        return \App\Support\Accounts::openItems([$this->user->id])[$this->user->id];
    }

    /** Warning shown when the role or department is being changed while things are still open. */
    public function changeWarning(): string
    {
        if ($this->isAdmin) {
            return '';
        }

        $roleChanged = $this->role !== ($this->user->roles->first()?->name ?? '');
        $deptChanged = (string) $this->department_id !== (string) ($this->user->department_id ?? '');
        $open        = \App\Support\Accounts::openItemsText($this->openItems);

        if (! ($roleChanged || $deptChanged) || $open === '') {
            return '';
        }

        $what = $roleChanged && $deptChanged ? 'role and department' : ($roleChanged ? 'role' : 'department');

        return "{$this->user->name} still has {$open}. Changing the {$what} now may leave them with the wrong Program Head or page — "
            . 'consider finishing those first (Program Head Requests / Borrowed Items).';
    }

    #[Computed()]
    public function defaultPassword()
    {
        return self::DEFAULT_PASSWORD;
    }

    protected function rules()
    {
        $rules = [
            'name'     => 'required|string|min:3',
            'email'    => 'required|email|unique:users,email,' . $this->user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ];

        if (! $this->isAdmin) {
            $rules['department_id'] = 'required|exists:departments,id';
            // ✅ same rules as Add User
            $rules['role']          = 'required|in:' . implode(',', \App\Support\Accounts::ASSIGNABLE_ROLES);
            $rules['email']        .= '|ends_with:@csav.edu.ph';
        }

        return $rules;
    }

    protected $messages = [
        'name.required'          => 'Please enter the user\'s full name.',
        'name.min'               => 'Name must be at least 3 characters.',
        'email.required'         => 'Please enter an email address.',
        'email.email'            => 'Please enter a valid email address.',
        'email.unique'           => 'This email address is already registered.',
        'password.min'           => 'Password must be at least 6 characters.',
        'password.confirmed'     => 'Password confirmation does not match.',
        'department_id.required' => 'Please select a department.',
        'department_id.exists'   => 'Selected department is invalid.',
        'role.required'          => 'Please select a role.',
        'role.exists'            => 'Selected role is invalid.',
        'role.in'                => 'Choose Program Head or Faculty.',
        'email.ends_with'        => 'Email must end with @csav.edu.ph.',
    ];

    public function save()
    {
        abort_unless(auth()->user()->can('users.update'), 403);
        $this->validate();

        $passwordChanged = filled($this->password);
        $plainPassword   = $this->password;

        $data = [
            'name'  => $this->name,
            'email' => strtolower($this->email),
        ];

        if (! $this->isAdmin) {
            $data['department_id'] = $this->department_id;
        }

        if ($passwordChanged) {
            $data['password'] = Hash::make($plainPassword);
            // ✅ The Admin knows this password → they must choose their own at next sign-in
            //    (not when the Admin edits their own account)
            if ($this->user->id !== auth()->id()) {
                $data['must_change_password'] = true;
            }
        }

        $this->user->update($data);

        if (! $this->isAdmin) {
            $this->user->syncRoles([$this->role]);
        }

        if ($passwordChanged) {
            $this->sendPasswordResetEmail($plainPassword);
        }

        $this->reset(['password', 'password_confirmation']);

        $message = $passwordChanged
            ? 'User updated successfully! A password reset email has been sent.'
            : 'User updated successfully!';

        session()->flash('success', $message);
    }

    public function resetToDefaultPassword(): void
    {
        abort_unless(auth()->user()->can('users.reset-password'), 403);
        if ($this->isAdmin) {
            session()->flash('success', 'Cannot reset the admin password from this page.');
            return;
        }

        $default = self::DEFAULT_PASSWORD;

        $this->user->update([
            'password'             => Hash::make($default),
            'must_change_password' => true,   // ✅ shared default → must choose their own at next sign-in
        ]);

        $this->sendPasswordResetEmail($default);

        session()->flash('success', "Password reset to the default value. A reset email has been sent to {$this->user->email}.");
    }

    protected function sendPasswordResetEmail(string $plainPassword): void
    {
        try {
            $departmentName = Department::find($this->department_id)?->department_name;
            $roleName       = $this->user->roles->first()?->name;

            $roleLabel = $roleName
                ? ucwords(str_replace('_', ' ', $roleName))
                : null;

            $this->user->notify(new PasswordResetByAdminNotification(
                userName:    $this->user->name,
                userEmail:   $this->user->email,
                newPassword: $plainPassword,
                role:        $roleLabel,
                department:  $departmentName,
            ));
        } catch (\Throwable $e) {
            \Log::warning('PasswordResetByAdminNotification failed: ' . $e->getMessage());
        }
    }
};
