<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\NotificacionService;
use illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        //
        // Registrar el servicio como singleton
        $this->app->singleton(NotificacionService::class , function ($app) {
            return new NotificacionService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}