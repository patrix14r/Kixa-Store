<?php

namespace App\Models;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo de marcas (fabricantes)
#[Fillable(['nombre', 'slug', 'logo', 'activo'])]
class Marca extends Model
{
    protected $table = 'marcas';
    protected $fillable = ['nombre', 'slug', 'logo', 'activo'];

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

    // Filtra solo marcas activas
    #[Scope]
    protected function activos(Builder $consulta): void
    {
        $consulta->where('activo', true);
    }

    // URL pública del logo
    protected function urlLogo(): Attribute
    {
        return Attribute::get(fn () => $this->logo ? asset($this->logo) : null);
    }

    // Una marca tiene muchos productos
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'marca_id');
    }
}
