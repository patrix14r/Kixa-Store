<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// Llama a los seeders en orden
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            UserSeeder::class,
            CategoriaSeeder::class,
            ProveedorSeeder::class,
            MarcaSeeder::class,
            ProductoSeeder::class,
        ]);
    }
}
