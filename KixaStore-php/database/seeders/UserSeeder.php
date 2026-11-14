<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// [Sem 11 - Migraciones, Seeders y Roles]: Usuarios de prueba enlazados a su rol mediante rol_id
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdmin   = DB::table('roles')->where('nombre', 'admin')->value('id');
        $rolCliente = DB::table('roles')->where('nombre', 'cliente')->value('id');

        DB::table('users')->insert([
            [
                'name'       => 'Administrador Kixa',
                'email'      => 'admin@kixa.com',
                'password'   => Hash::make('admin123'),
                'rol_id'     => $rolAdmin,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Cliente Kixa',
                'email'      => 'cliente@kixa.com',
                'password'   => Hash::make('cliente123'),
                'rol_id'     => $rolCliente,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
