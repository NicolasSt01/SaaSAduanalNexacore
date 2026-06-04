<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SingleSessionGuard::class,
            \App\Http\Middleware\CheckTenantActive::class,
        ],

        'api' => [
            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // Middlewares para roles
        //'admin' => \App\Http\Middleware\CheckAdmin::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'admin_n2' => \App\Http\Middleware\CheckAdminN2::class,
        'cliente' => \App\Http\Middleware\CheckCliente::class,
        'documentador' => \App\Http\Middleware\CheckDocumentador::class,
        'register.access' => \App\Http\Middleware\RegisterAccess::class,
        'role' => \App\Http\Middleware\CheckRole::class,
        'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
        'check.tenant.active' => \App\Http\Middleware\CheckTenantActive::class,
        'single.session' => \App\Http\Middleware\SingleSessionGuard::class,

        // Middlewares para trial y registro público
        'must.change.password' => \App\Http\Middleware\MustChangePassword::class,
        'check.trial.expired' => \App\Http\Middleware\CheckTrialExpired::class,

        // Middleware para control de acceso a reportes
        'report.access' => \App\Http\Middleware\CheckReportAccess::class,
    ];
}