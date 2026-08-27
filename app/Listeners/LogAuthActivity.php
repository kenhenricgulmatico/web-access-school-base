<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthActivity
{
    public function handleLogin(Login $event): void
    {
        // ✅ Skip duplicate — if same user logged in within last 5 seconds, ignore
        $recentExists = AuditLog::where('user_id', $event->user->id)
            ->where('action', 'login')
            ->where('created_at', '>=', now()->subSeconds(5))
            ->exists();

        if ($recentExists) return;

        AuditLog::create([
            'user_id'    => $event->user->id,
            'action'     => 'login',
            'table_name' => 'sessions',
            'record'     => json_encode([
                'name'   => $event->user->name,
                'email'  => $event->user->email,
                'status' => $event->user->status,
                'ip'     => request()->ip(),
                'at'     => now()->toDateTimeString(),
            ]),
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if (!$event->user) return;

        // ✅ Skip duplicate — if same user logged out within last 5 seconds, ignore
        $recentExists = AuditLog::where('user_id', $event->user->id)
            ->where('action', 'logout')
            ->where('created_at', '>=', now()->subSeconds(5))
            ->exists();

        if ($recentExists) return;

        AuditLog::create([
            'user_id'    => $event->user->id,
            'action'     => 'logout',
            'table_name' => 'sessions',
            'record'     => json_encode([
                'name'  => $event->user->name,
                'email' => $event->user->email,
                'at'    => now()->toDateTimeString(),
            ]),
        ]);
    }
}
