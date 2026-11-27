<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    protected function rules()
    {
        return [
            'current_password' => 'required|current_password',
            'new_password'     => 'required|min:6|confirmed',
        ];
    }

    protected $messages = [
        'current_password.required'         => 'Please enter your current password.',
        'current_password.current_password' => 'Current password is incorrect.',
        'new_password.required'             => 'Please enter a new password.',
        'new_password.min'                  => 'New password must be at least 6 characters.',
        'new_password.confirmed'            => 'Password confirmation does not match.',
    ];

    /**
     * Live check — every time the admin types in the new password field,
     * compare it with the current password input to catch duplicates early.
     */
    public function updatedNewPassword(): void
    {
        $this->resetErrorBag('new_password');

        if (! $this->new_password || ! $this->current_password) {
            return;
        }

        if ($this->new_password === $this->current_password) {
            $this->addError('new_password', 'Your new password must be different from your current password.');
        }
    }

    public function updatePassword(): void
    {
        $this->validate();

        // ✅ Extra server-side guard — even if live check was bypassed
        if ($this->new_password === $this->current_password) {
            $this->addError('new_password', 'Your new password must be different from your current password.');
            return;
        }

        // ✅ Extra safety: compare against the HASH too
        // (in case someone bypasses the plaintext comparison with case tricks)
        if (Hash::check($this->new_password, Auth::user()->password)) {
            $this->addError('new_password', 'Your new password must be different from your current password.');
            return;
        }

        Auth::user()->update([
            'password' => $this->new_password,
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        session()->flash('password_message', 'Password updated successfully.');
    }
};
