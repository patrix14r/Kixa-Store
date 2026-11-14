<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// [Sem 11 - Migraciones, Seeders y Roles]: Seeder de la tabla roles (admin y cliente)
class RolSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'nombre'      => 'admin',
                'descripcion' => 'Administrador del panel: gestiona catálogo, proveedores y abastecimiento',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'cliente',
                'descripcion' => 'Cliente de la tienda: compra productos y revisa sus pedidos',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
