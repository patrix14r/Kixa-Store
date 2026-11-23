<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeder de proveedores oficiales con DB::table()->insert()
class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('proveedores')->insert([
            [
                'razon_social'    => 'Nike Perú S.A.C.',
                'ruc'             => '20512345671',
                'contacto_nombre' => 'Carlos Mendoza',
                'telefono'        => '987654321',
                'correo'          => 'ventas@nikeperu.com',
                'direccion'       => 'Av. Javier Prado Este 4200, Lima',
                'activo'          => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'razon_social'    => 'Adidas Perú S.A.C.',
                'ruc'             => '20345678912',
                'contacto_nombre' => 'Lucía Fernández',
                'telefono'        => '912345678',
                'correo'          => 'distribucion@adidas.pe',
                'direccion'       => 'Av. Comandante Espinar 860, Miraflores',
                'activo'          => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'razon_social'    => 'Puma Sports Perú S.A.C.',
                'ruc'             => '20601122334',
                'contacto_nombre' => 'Roberto Vargas',
                'telefono'        => '998877665',
                'correo'          => 'logistica@puma.com.pe',
                'direccion'       => 'Av. El Derby 254, Surco',
                'activo'          => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'razon_social'    => 'Umbro Perú Deportes S.A.C.',
                'ruc'             => '20498765432',
                'contacto_nombre' => 'Gonzalo Alva',
                'telefono'        => '976543210',
                'correo'          => 'contacto@umbro.pe',
                'direccion'       => 'Av. Primavera 1230, San Borja',
                'activo'          => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);
    }
}
