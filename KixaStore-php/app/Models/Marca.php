<?php

namespace App\Models;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// [Sem 12 - Eloquent ORM]: modelo de la tabla marcas (CRUD 4) con #[Fillable], casts(), Scope #[Scope] y relación HasMany con productos.
#[Fillable(['nombre', 'slug', 'logo', 'activo'])]
class Marca extends Model
{
    protected $table = 'marcas';
    protected $fillable = ['nombre', 'slug', 'logo', 'activo'];

    // casts() devuelve activo como true/false en vez de 1/0.
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    // [Sem 08 - Rutas y Vistas Blade]: el binding usa el slug, así la URL queda /admin/marcas/new-balance/edit y no expone el ID.
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Scope local con #[Scope]: Marca::activos()->get() solo trae las marcas que se pueden asignar a productos.
    #[Scope]
    protected function activos(Builder $consulta): void
    {
        $consulta->where('activo', true);
    }

    // Accessor: $marca->url_logo devuelve la URL pública del logo (o null si no tiene).
    protected function urlLogo(): Attribute
    {
        return Attribute::get(fn () => $this->logo ? asset($this->logo) : null);
    }

    // Relación HasMany: una marca tiene muchos productos (FK marca_id en productos).
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'marca_id');
    }
}
