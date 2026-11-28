<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// Usuarios de prueba con su rol
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Correos en minúsculas por Fortify

        // 1) Administradores
        $administradores = [
            ['name' => 'Administrador Kixa', 'email' => 'admin@kixa.com',     'password' => 'admin123'],
            ['name' => 'Chambergo',          'email' => 'chambergo@kixa.com', 'password' => 'admin123'],
        ];

        foreach ($administradores as $datos) {
            // Crea solo si no existe
            User::firstOrCreate(
                ['email' => $datos['email']],
                ['name' => $datos['name'], 'password' => $datos['password']],
            )->assignRole('admin');
        }

        // 2) Clientes
        $clientes = [
            ['name' => 'Cliente Kixa', 'email' => 'cliente@kixa.com', 'password' => 'cliente123'],
            ['name' => 'Junior',       'email' => 'junior@kixa.com',  'password' => 'cliente123'],
            ['name' => 'Víctor',       'email' => 'victor@kixa.com',  'password' => 'cliente123'],
        ];

        foreach ($clientes as $datos) {
            // Crea clientes y asigna su rol
            User::firstOrCreate(
                ['email' => $datos['email']],
                ['name' => $datos['name'], 'password' => $datos['password']],
            )->assignRole('cliente');
        }
    }
}
