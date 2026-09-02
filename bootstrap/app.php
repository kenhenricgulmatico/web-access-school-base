<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\DepartmentMiddleware;
use App\Http\Middleware\FacultyMiddleware;
use App\Http\Middleware\ProgramHeadMiddleware;
use App\Http\Middleware\StudentMiddleware;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Added by EnvKit so shared (public) URLs keep the https scheme and
        // public host. Safe locally; remove to opt out.
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'auth' => Authenticate::class,
            'admin' => AdminMiddleware::class,
            'faculty' => FacultyMiddleware::class,
            'student' => StudentMiddleware::class,
            'program head' => ProgramHeadMiddleware::class,
            'department' => DepartmentMiddleware::class,
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
