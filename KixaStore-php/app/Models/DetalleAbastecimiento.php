<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Fila de un lote de abastecimiento
#[Fillable(['orden_abastecimiento_id', 'producto_talla_id', 'cantidad', 'costo_unitario', 'subtotal'])]
class DetalleAbastecimiento extends Model
{
    protected $table = 'detalles_abastecimiento';

    // Convierte cantidad y montos a número
    protected function casts(): array
    {
        return [
            'cantidad'       => 'integer',
            'costo_unitario' => 'decimal:2',
            'subtotal'       => 'decimal:2',
        ];
    }

    // Ganancia estimada de la fila
    protected function gananciaEstimada(): Attribute
    {
        return Attribute::get(fn () => round(((float) $this->talla?->producto?->precio - (float) $this->costo_unitario) * $this->cantidad, 2));
    }

    // La fila pertenece a un lote
    public function orden(): BelongsTo
    {
        return $this->belongsTo(OrdenAbastecimiento::class, 'orden_abastecimiento_id');
    }

    // Talla que se repone
    public function talla(): BelongsTo
    {
        return $this->belongsTo(ProductoTalla::class, 'producto_talla_id');
    }
}
