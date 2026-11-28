<?php

namespace App\Models;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo de categorías del catálogo
#[Fillable(['nombre', 'slug', 'descripcion', 'activo'])] 
class Categoria extends Model
{
    protected $table = 'categorias';
    protected $fillable = ['nombre', 'slug', 'descripcion', 'activo'];

    // Convierte activo a booleano
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    // Usa el slug en la URL
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    #[Scope]
    // Filtra solo categorías activas
    protected function activas(Builder $consulta): void
    {
        $consulta->where('activo', true);
    }

    // Una categoría tiene muchos productos
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}
