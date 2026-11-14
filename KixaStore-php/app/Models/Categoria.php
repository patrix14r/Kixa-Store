<?php

namespace App\Models;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// [Sem 12 - Eloquent ORM]: Categoría con #[Fillable], casts(), Scope activas y relación HasMany hacia productos
#[Fillable(['nombre', 'slug', 'descripcion', 'activo'])]
class Categoria extends Model
{
    protected $table = 'categorias';
    protected $fillable = ['nombre', 'slug', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    // [Sem 08 - Rutas y Vistas Blade]: Las URLs del admin usan el slug en lugar del ID (Regla 4.4)
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    #[Scope]
    protected function activas(Builder $consulta): void
    {
        $consulta->where('activo', true);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}
