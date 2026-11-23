<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Responses\LoginResponse;
use App\Http\Responses\RegisterResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;

// [Sem 11 - Migraciones, Seeders y Roles]: Fortify aporta las rutas de login, registro y logout; aquí se conectan nuestras vistas y reglas
class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Reemplaza las respuestas por defecto de Fortify para conservar la redirección por rol y ?redirect=.
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        // Vistas existentes (su diseño no cambia).
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.registro'));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
