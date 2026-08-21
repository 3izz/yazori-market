<?php

namespace App\Http\Middleware;

use App\Services\BackupService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBackupSchedule
{
    public function handle(Request $request, Closure $next): Response
    {
        app(BackupService::class)->runIfDue();

        return $next($request);
    }
}
