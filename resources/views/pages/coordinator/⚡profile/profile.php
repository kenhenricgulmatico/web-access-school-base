<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.coordinator')] class extends Component
{
    public $name;
    public $email;
    public $department;
    public $role;

    // Password fields
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->department = $user->department->department_name ?? 'Not assigned';
        $this->role = $user->roles->first()->name ?? 'No role';
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'current_password' => 'required_with:new_password|current_password',
            'new_password' => 'nullable|min:6|confirmed',
        ];
    }

    protected $messages = [
        'name.required' => 'Name is required.',
        'email.required' => 'Email is required.',
        'email.email' => 'Please enter a valid email address.',
        'email.unique' => 'This email is already taken.',
        'current_password.current_password' => 'Current password is incorrect.',
        'new_password.min' => 'New password must be at least 6 characters.',
        'new_password.confirmed' => 'Password confirmation does not match.',
    ];

    public function updateProfile()
    {
        $this->validate();

        $user = Auth::user();
        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->new_password) {
            // plain value — the User model's 'hashed' cast hashes it once on save
            $data['password'] = $this->new_password;
        }

        $user->update($data);

        if ($this->new_password) {
            $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        }

        session()->flash('message', 'Profile updated successfully.');
    }
};
