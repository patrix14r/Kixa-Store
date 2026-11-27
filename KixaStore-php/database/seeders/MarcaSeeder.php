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

        // Logos que ya existen en public/images/brands/; las marcas sin logo muestran su inicial en el panel.
        $logos = [
            'Nike'   => 'images/brands/nike-logo.png',
            'Jordan' => 'images/brands/jordan-logo.webp',
            'Adidas' => 'images/brands/adidas-logo.png',
            'Puma'   => 'images/brands/puma-logo.png',
            'Umbro'  => 'images/brands/umbro-logo.png',
            'Lacoste' => 'images/brands/lacoste-logo.png',
            'DC Shoes' => 'images/brands/dcshoes-logo.webp',
            'Vans' => 'images/brands/vans-logo.png',
            'New Balance' => 'images/brands/new-balance-logo.png',
        ];

        foreach ($marcas as $nombre) {
            DB::table('marcas')->insert([
                'nombre'     => $nombre,
                'slug'       => Str::slug($nombre),
                'logo'       => $logos[$nombre] ?? null,
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
