<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Debes iniciar sesión para acceder al panel de administración.'
            ]);
        }

        if (! Auth::user()->esAdmin()) {
            return redirect()->route('home')->with('error', 'No tienes permisos de administrador.');
        }

        return $next($request);
    }
}
