<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

// Redirige según el rol tras el login
class LoginResponse implements LoginResponseContract
{
    use RespuestaAutenticacion;

    public function toResponse($request) //$request es el objeto que representa la petición HTTP que esta ubicado en 
    {
        if ($request->user()->esAdmin()) {
            return redirect()->route('admin.home')->with('exito', 'Bienvenido al Panel Administrador');
        }

        return $this->redirigirCliente($request, '¡Bienvenido de nuevo a KixaStore!');
    }
}
