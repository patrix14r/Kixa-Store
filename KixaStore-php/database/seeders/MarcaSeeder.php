<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Marcas con sus logos
class MarcaSeeder extends Seeder
{
    public function run(): void
    {
        // Nombres iguales al texto de productos.marca
        $marcas = ['Nike', 'Jordan', 'Adidas', 'Puma', 'Vans', 'New Balance', 'DC Shoes', 'Lacoste', 'Umbro'];

        // Logos de public/images/brands
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
