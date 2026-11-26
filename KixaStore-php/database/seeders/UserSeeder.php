<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// [Sem 11 - Migraciones, Seeders y Roles]: Usuarios de prueba; el rol se asigna con assignRole() de Spatie (tabla model_has_roles), ya no con una columna rol_id.
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // El cast "hashed" del modelo User encripta la contraseña al guardarla.
        User::create([
            'name'     => 'Administrador Kixa',
            'email'    => 'admin@kixa.com',
            'password' => 'admin123',
        ])->assignRole('admin');

        User::create([
            'name'     => 'Cliente Kixa',
            'email'    => 'cliente@kixa.com',
            'password' => 'cliente123',
        ])->assignRole('cliente');
    }
}
