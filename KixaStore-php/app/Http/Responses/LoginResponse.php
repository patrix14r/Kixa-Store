<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

// [Sem 11 - Migraciones, Seeders y Roles]: Tras el login Fortify llama a esta clase; el rol decide el destino
class LoginResponse implements LoginResponseContract
{
    use RespuestaAutenticacion;

    public function toResponse($request)
    {
        if ($request->user()->esAdmin()) {
            return redirect()->route('admin.home')->with('exito', 'Bienvenido al Panel Administrador');
        }

        return $this->redirigirCliente($request, '¡Bienvenido de nuevo a KixaStore!');
    }
}
