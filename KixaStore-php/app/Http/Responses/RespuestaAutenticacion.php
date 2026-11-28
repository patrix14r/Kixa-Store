<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

// Redirección compartida de login y registro
trait RespuestaAutenticacion
{
    // Solo acepta redirect a checkout o carrito
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
