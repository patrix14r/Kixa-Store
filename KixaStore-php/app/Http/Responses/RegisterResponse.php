<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    use RespuestaAutenticacion;

    // Redirige al cliente tras registrarse
    public function toResponse($request)
    {
        return $this->redirigirCliente($request, 'Cuenta creada correctamente en KixaStore.');
    }
}
