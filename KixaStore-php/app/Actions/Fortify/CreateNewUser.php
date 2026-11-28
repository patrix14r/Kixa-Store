<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

// Registra un cliente nuevo con Fortify
class CreateNewUser implements CreatesNewUsers
{
    /**
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'dni'      => ['nullable', 'string', 'size:8', 'regex:/^[0-9]{8}$/'],
            'phone'    => ['nullable', 'string', 'size:9', 'regex:/^[0-9]{9}$/'],
        ], [
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'dni.size'           => 'El DNI debe tener exactamente 8 dígitos numéricos.',
            'phone.size'         => 'El teléfono debe tener exactamente 9 dígitos numéricos.',
        ])->validate();

        $user = User::create([
            'name'     => $input['name'],
            'email'    => $input['email'],
            'dni'      => $input['dni'] ?? null,
            'telefono' => $input['phone'] ?? null,
            'password' => Hash::make($input['password']),
        ]);

        // Asigna el rol cliente
        $user->assignRole('cliente');

        return $user;
    }
}
