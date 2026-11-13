<?php

namespace App\Models;

use App\Models\Categoria;
use App\Models\ProductoTalla;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'categoria_id',
    'proveedor_id',
    'marca',
    'nombre',
    'subcategoria',
    'sku',
    'precio',
    'descripcion',
    'imagen',
    'destacado',
    'en_oferta',
    'activo',
])]
class Producto extends Model
{

    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'proveedor_id',
        'marca',
        'nombre',
        'subcategoria',
        'sku',
        'precio',
        'descripcion',
        'imagen',
        'destacado',
        'en_oferta',
        'activo',
    ];


    protected function casts(): array
    {
        return [
            'precio'    => 'decimal:2',
            'destacado' => 'boolean',
            'en_oferta' => 'boolean',
            'activo'    => 'boolean',
        ];
    }

    #[Scope]
    protected function activos(Builder $consulta): void
    {
        $consulta->where('activo', true);
    }


    #[Scope]
    protected function destacados(Builder $consulta): void
    {
        $consulta->where('destacado', true);
    }


    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function tallas(): HasMany
    {
        return $this->hasMany(ProductoTalla::class, 'producto_id');
    }
}
