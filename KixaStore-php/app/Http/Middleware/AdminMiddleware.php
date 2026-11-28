<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Solo deja pasar al rol admin
class AdminMiddleware
{
    // Con admin:403 responde 403 en vez de redirigir
    public function handle(Request $request, Closure $next, ?string $modo = null): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Debes iniciar sesión para acceder al panel de administración.'
            ]);
        }

        if (! Auth::user()->esAdmin()) {
            abort_if($modo === '403', 403, 'No tienes permisos de administrador.');

            return redirect()->route('home')->with('error', 'No tienes permisos de administrador.');
        }

        return $next($request);
    }
}
