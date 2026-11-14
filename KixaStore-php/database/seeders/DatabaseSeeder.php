<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// [Sem 11 - Migraciones, Seeders y Roles]: Seeder principal que llama a los seeders en orden de dependencias (FK)
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            UserSeeder::class,
            CategoriaSeeder::class,
            ProveedorSeeder::class,
            ProductoSeeder::class,
        ]);
    }
}
