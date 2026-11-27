<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// [Sem 12 - Eloquent ORM]: fila de un lote de abastecimiento (tabla detalles_abastecimiento): qué talla, cuántos pares y a qué costo.
#[Fillable(['orden_abastecimiento_id', 'producto_talla_id', 'cantidad', 'costo_unitario', 'subtotal'])]
class DetalleAbastecimiento extends Model
{
    protected $table = 'detalles_abastecimiento';

    protected function casts(): array
    {
        return [
            'cantidad'       => 'integer',
            'costo_unitario' => 'decimal:2',
            'subtotal'       => 'decimal:2',
        ];
    }

    // Accessor: $detalle->ganancia_estimada = (precio de venta actual - costo unitario) × cantidad.
    protected function gananciaEstimada(): Attribute
    {
        return Attribute::get(fn () => round(((float) $this->talla?->producto?->precio - (float) $this->costo_unitario) * $this->cantidad, 2));
    }

    // Relación BelongsTo: la fila pertenece a un lote (FK orden_abastecimiento_id).
    public function orden(): BelongsTo
    {
        return $this->belongsTo(OrdenAbastecimiento::class, 'orden_abastecimiento_id');
    }

    // Relación BelongsTo: la talla (y por ella el producto) que se repone.
    public function talla(): BelongsTo
    {
        return $this->belongsTo(ProductoTalla::class, 'producto_talla_id');
    }
}
