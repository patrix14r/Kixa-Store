<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    use RespuestaAutenticacion;

    public function toResponse($request)
    {
        return $this->redirigirCliente($request, 'Cuenta creada correctamente en KixaStore.');
    }
}
