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

        // Rejected → log out and block
        if ($user->status === 'rejected') {
            Auth::logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been rejected. Please contact the registrar.']);
        }

        // Pending → redirect to waiting page
        if ($user->status !== 'approved') {
            return redirect()->route('waiting');
        }

        return $next($request);
    }
}
