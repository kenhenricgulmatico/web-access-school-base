<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

new class extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public bool $terms = false;

    protected array $rules = [
        'email'    => 'required|email',
        // ✅ No "min:6" on LOGIN — a short wrong password should just say
        //    "Incorrect password", not a length error.
        'password' => 'required|string',
        'terms'    => 'accepted',
    ];

    protected array $messages = [
        'email.required'    => 'Please enter your email.',
        'email.email'       => 'Please enter a valid email address.',
        'password.required' => 'Please enter your password.',
        'terms.accepted'    => 'You must agree to the Privacy Policy before logging in.',
    ];

    public function login()
    {
        $this->validate();

        $email       = strtolower(trim($this->email));
        $throttleKey = Str::lower($email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Too many failed attempts. Please try again in {$seconds} seconds.");
            return;
        }

        // ✅ 1. Check the EMAIL first — if no account uses it, say so under the email box
        if (! User::whereRaw('LOWER(email) = ?', [$email])->exists()) {
            $this->countFailedAttempt($throttleKey);
            $this->addError('email', 'Invalid email. No account found with this email address.');
            return;
        }

        // ✅ 2. Email exists — now check the PASSWORD
        if (Auth::attempt([
            'email'    => $email,
            'password' => $this->password,
        ], $this->remember)) {
            RateLimiter::clear($throttleKey);
            session()->regenerate();

            $user = Auth::user();

            if ($user->status === 'rejected') {
                Auth::logout();
                $this->addError('email', 'Your account has been rejected. Please contact the registrar.');
                return;
            }

            if ($user->status !== 'approved') {
                return redirect()->route('waiting');
            }

            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->hasRole('program head')) {
                return redirect()->route('coordinator.dashboard');
            }

            return redirect()->route('portal.dashboard');
        }

        // ✅ 3. Wrong password — show the error under the PASSWORD box
        $this->countFailedAttempt($throttleKey);
        $this->reset('password');
        $this->addError('password', 'Incorrect password. Please try again.');
    }

    /** Count a failed login for the "too many attempts" lock. */
    protected function countFailedAttempt(string $throttleKey): void
    {
        $attempts = RateLimiter::attempts($throttleKey);
        $decaySeconds = match (true) {
            $attempts >= 6 => 300,
            $attempts >= 3 => 120,
            default        => 60,
        };

        RateLimiter::hit($throttleKey, $decaySeconds);
    }
};
