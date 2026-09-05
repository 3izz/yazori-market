<?php

use App\Http\Middleware\CheckBackupSchedule;
use App\Http\Middleware\EnsurePosAccess;
use App\Http\Middleware\RequireAdminNavPin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            CheckBackupSchedule::class,
        ]);
        $middleware->alias([
            'pos.access' => EnsurePosAccess::class,
            'admin.nav.pin' => RequireAdminNavPin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
