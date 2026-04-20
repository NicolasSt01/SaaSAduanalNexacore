<?php

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
            \App\Http\Middleware\SingleSessionGuard::class,
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'admin_n2' => \App\Http\Middleware\CheckAdminN2::class,
            'cliente' => \App\Http\Middleware\CheckCliente::class,
            'documentador' => \App\Http\Middleware\CheckDocumentador::class,
            'register.access' => \App\Http\Middleware\RegisterAccess::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'single.session' => \App\Http\Middleware\SingleSessionGuard::class,
            'must.change.password' => \App\Http\Middleware\MustChangePassword::class,
            'check.trial.expired' => \App\Http\Middleware\CheckTrialExpired::class,
            'report.access' => \App\Http\Middleware\CheckReportAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
