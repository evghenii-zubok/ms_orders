<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // forza a usare HTTPS sempre
        URL::forceScheme('https');

        // rate limiter
        RateLimiter::for('api', function (Request $request) {

            // 50 richieste al minuto per ip credo siano più che sufficienti
            return Limit::perMinute(50)->by($request->ip());
        });
    }
}
