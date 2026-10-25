<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Login: 5 intentos por minuto por correo + IP.
        // (El frontend llama desde el servidor de Next.js, asi que contar solo por IP
        // bloquearia a todos los usuarios juntos.)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        // Registro: 10 por minuto por IP.
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}