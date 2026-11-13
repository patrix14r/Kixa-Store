<?php

namespace App\Http\Middleware;

// [CAMBIO: IMPORTACIONES PARA MIDDLEWARE DE ADMINISTRADOR]
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // [CAMBIO: VERIFICACION DE SESION ACTIVA Y ROL ADMIN]
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Debes iniciar sesión para acceder al panel de administración.'
            ]);
        }

        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('home')->with('error', 'No tienes permisos de administrador.');
        }

        return $next($request);
    }
}
