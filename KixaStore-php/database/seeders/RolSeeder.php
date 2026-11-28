<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

// Roles admin y cliente (Spatie)
class RolSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia la caché de roles
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate('admin', 'web'); // Administrador del panel
        Role::findOrCreate('cliente', 'web'); // Cliente de la tienda
    }
}
