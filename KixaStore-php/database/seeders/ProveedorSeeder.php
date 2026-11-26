<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// [Sem 11 - Migraciones, Seeders y Roles]: proveedores = distribuidoras mayoristas peruanas (a quién se le compra), distintas de las Marcas (quién fabrica); los RUC tienen dígito verificador SUNAT válido.
class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('proveedores')->insert([
            [
                'razon_social'    => 'Distribuidora Deportiva Perú S.A.C.',
                'ruc'             => '20512345671',
                'contacto_nombre' => 'Carlos Mendoza',
                'telefono'        => '987654321',
                'correo'          => 'ventas@distribuidoradeportiva.pe',
                'direccion'       => 'Av. Javier Prado Este 4200, Lima',
                'activo'          => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'razon_social'    => 'Distribuidora Andina de Calzado S.A.C.',
                'ruc'             => '20345678914',
                'contacto_nombre' => 'Lucía Fernández',
                'telefono'        => '912345678',
                'correo'          => 'pedidos@andinacalzado.pe',
                'direccion'       => 'Av. Nicolás Arriola 2240, La Victoria',
                'activo'          => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'razon_social'    => 'Comercializadora Sport Lima S.A.C.',
                'ruc'             => '20601122333',
                'contacto_nombre' => 'Roberto Vargas',
                'telefono'        => '998877665',
                'correo'          => 'logistica@sportlima.pe',
                'direccion'       => 'Av. El Derby 254, Surco',
                'activo'          => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'razon_social'    => 'Calzado Urbano Mayorista S.A.C.',
                'ruc'             => '20498765433',
                'contacto_nombre' => 'Gonzalo Alva',
                'telefono'        => '976543210',
                'correo'          => 'contacto@calzadourbano.pe',
                'direccion'       => 'Jr. Gamarra 1230, La Victoria',
                'activo'          => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);
    }
}
