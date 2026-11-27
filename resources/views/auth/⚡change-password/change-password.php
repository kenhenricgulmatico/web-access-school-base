<?php

use App\Support\Accounts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

/**
 * ✅ "Choose your own password" — shown after signing in with a password the Admin gave
 *    (new account, reset) or the shared default. Every other page sends people here until done.
 */
new class extends Component
{
    public string $new_password              = '';
    public string $new_password_confirmation = '';

    public function mount()
    {
        $user = Auth::user();

        if (! $user || $user->status !== 'approved') {
            return redirect()->route('login');
        }

        if (! $user->must_change_password) {
            return redirect(Accounts::homeRoute($user));
        }
    }

    public function save()
    {
        $user = Auth::user();
        abort_unless($user && $user->status === 'approved', 403);

        $this->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.required'  => 'Please enter a new password.',
            'new_password.min'       => 'Use at least 8 characters.',
            'new_password.confirmed' => 'The two passwords do not match.',
        ]);

        if ($this->new_password === Accounts::DEFAULT_PASSWORD || Hash::check($this->new_password, $user->password)) {
            $this->addError('new_password', 'Choose a new password — not the one the Admin gave you.');
            return;
        }

        $user->forceFill([
            'password'             => $this->new_password,   // hashed by the model
            'must_change_password' => false,
        ])->save();

        session()->flash('success', 'Your password has been changed.');

        return redirect(Accounts::homeRoute($user));
    }
};
