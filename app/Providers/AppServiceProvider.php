<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Listeners\LogAuthActivity;
use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Models\User;
use App\Models\Department;
use App\Observers\AuditLogObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Existing observers
        User::observe(AuditLogObserver::class);
        ResourceRequest::observe(AuditLogObserver::class);
        RequestItem::observe(AuditLogObserver::class);
        Department::observe(AuditLogObserver::class);

        // ✅ Login and logout logging
        Event::listen(Login::class, [LogAuthActivity::class, 'handleLogin']);
        Event::listen(Logout::class, [LogAuthActivity::class, 'handleLogout']);
    }
}
