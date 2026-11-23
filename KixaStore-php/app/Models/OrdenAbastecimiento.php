<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] Modelo de ordenes_abastecimiento (reposición de stock a un proveedor).
#[Fillable([
    'codigo_lote',
    'proveedor_id',
    'producto_talla_id',
    'user_id',
    'cantidad_solicitada',
    'costo_unitario',
    'costo_total',
    'estado',
    'fecha_recepcion',
])]
class OrdenAbastecimiento extends Model
{
    protected $table = 'ordenes_abastecimiento';

    public const ESTADOS = ['Pendiente', 'Recibido', 'Cancelado'];

    // [Sem 08 - Rutas y Vistas Blade]: el binding usa el código de lote (LOT-0001), no el ID autoincremental.
    public function getRouteKeyName(): string
    {
        return 'codigo_lote';
    }

    // casts() deja las cantidades y costos tipados y fecha_recepcion como fecha (Carbon).
    protected function casts(): array
    {
        return [
            'cantidad_solicitada' => 'integer',
            'costo_unitario'      => 'decimal:2',
            'costo_total'         => 'decimal:2',
            'fecha_recepcion'     => 'datetime',
        ];
    }

    // Relación BelongsTo: a qué proveedor se le pide el lote.
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    // Relación BelongsTo: qué talla de qué producto se repone.
    public function talla(): BelongsTo
    {
        return $this->belongsTo(ProductoTalla::class, 'producto_talla_id');
    }

    // Relación BelongsTo: el administrador que registró la orden.
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
