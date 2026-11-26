<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

// [Sem 11 - Migraciones, Seeders y Roles]: Crea los roles admin y cliente en la tabla roles de Spatie Laravel Permission.
class RolSeeder extends Seeder
{
    public function run(): void
    {
        // Spatie guarda los roles en caché: se limpia para que los nuevos se lean al instante.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate('admin', 'web');   // Administrador del panel: catálogo, proveedores y abastecimiento
        Role::findOrCreate('cliente', 'web'); // Cliente de la tienda: compra productos y revisa sus pedidos
    }
}
