<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApprovedUserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Rejected / deactivated → log out and block
        if (in_array($user->status, ['rejected', 'deactivated'], true)) {
            Auth::logout();

            return redirect()->route('login')->withErrors(['email' => $user->status === 'deactivated'
                ? 'This account has been deactivated. Please contact the Admin.'
                : 'Your account has been rejected. Please contact the registrar.']);
        }

        // Pending → redirect to waiting page
        if ($user->status !== 'approved') {
            return redirect()->route('waiting');
        }

        // ✅ Still using a password the Admin gave (or the shared default) → choose your own first
        if ($user->must_change_password) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
