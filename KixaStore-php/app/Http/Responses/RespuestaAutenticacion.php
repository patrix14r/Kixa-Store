<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

// [Sem 09 - Controladores y MVC]: Lógica de redirección compartida por LoginResponse y RegisterResponse de Fortify
trait RespuestaAutenticacion
{
    // Solo acepta valores conocidos de ?redirect (evita redirecciones abiertas); si no, vuelve a la URL prevista o al home.
    protected function redirigirCliente(Request $request, string $mensaje): RedirectResponse
    {
        $destino = match ($request->input('redirect')) {
            'checkout' => route('checkout'),
            'carrito'  => route('carrito'),
            default    => null,
        };

        if ($destino) {
            $request->session()->forget('url.intended');
            return redirect()->to($destino)->with('exito', $mensaje);
        }

        return redirect()->intended(route('home'))->with('exito', $mensaje);
    }
}
