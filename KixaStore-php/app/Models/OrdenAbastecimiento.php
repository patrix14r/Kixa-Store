<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] Modelo de ordenes_abastecimiento: encabezado del lote
// (proveedor, inversión total y estado); las tallas, cantidades y costos van en sus detalles (detalles_abastecimiento).
#[Fillable([
    'codigo_lote',
    'proveedor_id',
    'user_id',
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

    // casts() deja la inversión con 2 decimales y fecha_recepcion como fecha (Carbon).
    protected function casts(): array
    {
        return [
            'costo_total'     => 'decimal:2',
            'fecha_recepcion' => 'datetime',
        ];
    }

    // Accessor: $orden->unidades suma los pares de todas las filas del lote.
    protected function unidades(): Attribute
    {
        return Attribute::get(fn () => (int) $this->detalles->sum('cantidad'));
    }

    // Accessor: $orden->ganancia_estimada compara el costo de cada fila con el precio de venta actual del producto.
    protected function gananciaEstimada(): Attribute
    {
        return Attribute::get(fn () => round($this->detalles->sum(fn (DetalleAbastecimiento $d) => $d->ganancia_estimada), 2));
    }

    // Relación HasMany: un lote tiene muchas filas (una por talla).
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleAbastecimiento::class, 'orden_abastecimiento_id');
    }

    // Relación BelongsTo: a qué proveedor se le pide el lote.
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    // Relación BelongsTo: el administrador que registró la orden.
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
