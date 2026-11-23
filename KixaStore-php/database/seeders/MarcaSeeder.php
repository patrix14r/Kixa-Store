<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// [Sem 11 - Migraciones, Seeders y Roles]: marcas reales del catálogo; corre antes de ProductoSeeder porque productos.marca_id depende de ellas.
class MarcaSeeder extends Seeder
{
    public function run(): void
    {
        // El nombre debe coincidir con el texto de productos.marca para que el catálogo y sus filtros sigan funcionando.
        $marcas = ['Nike', 'Jordan', 'Adidas', 'Puma', 'Vans', 'New Balance', 'DC Shoes', 'Lacoste', 'Umbro'];

        foreach ($marcas as $nombre) {
            DB::table('marcas')->insert([
                'nombre'     => $nombre,
                'slug'       => Str::slug($nombre),
                'logo'       => null,
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
