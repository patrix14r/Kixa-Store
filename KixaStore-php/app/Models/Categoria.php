<?php

namespace App\Models;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] Modelo de la tabla categorias con #[Fillable], casts(), un Scope y una relación HasMany.
#[Fillable(['nombre', 'slug', 'descripcion', 'activo'])] 
class Categoria extends Model
{
    protected $table = 'categorias';
    protected $fillable = ['nombre', 'slug', 'descripcion', 'activo'];

    // casts() devuelve activo como true/false en vez de 1/0.
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    // [Semana 8: Rutas y Herencia Blade] El route model binding busca por slug: la URL queda /admin/categorias/hombre/edit y no muestra el ID autoincremental.
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    #[Scope]
    // Scope local con #[Scope]: se usa como Categoria::activas()->get() y reutiliza el where activo = true.
    protected function activas(Builder $consulta): void
    {
        $consulta->where('activo', true);
    }

    // Relación HasMany: una categoría tiene muchos productos; la FK categoria_id está en la tabla productos.
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}
