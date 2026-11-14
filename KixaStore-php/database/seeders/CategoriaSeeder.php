<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// [Sem 11 - Migraciones, Seeders y Roles]: Seeder de las 4 categorías de la tienda
class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categorias')->insert([
            [
                'nombre'      => 'Hombre',
                'slug'        => 'hombre',
                'descripcion' => 'Calzado urbano, deportivo y exclusivo para hombre',
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Mujer',
                'slug'        => 'mujer',
                'descripcion' => 'Zapatillas de tendencia, running y entrenamiento para mujer',
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Niños',
                'slug'        => 'ninos',
                'descripcion' => 'Calzado escolar, urbano y deportivo para niños',
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Deporte',
                'slug'        => 'deporte',
                'descripcion' => 'Especializados en running, fútbol, básquet y gimnasio',
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
