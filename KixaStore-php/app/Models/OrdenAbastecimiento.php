<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Encabezado de un lote de abastecimiento
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

    // Usa el código de lote en la URL
    public function getRouteKeyName(): string
    {
        return 'codigo_lote';
    }

    // Convierte inversión y fecha de recepción
    protected function casts(): array
    {
        return [
            'costo_total'     => 'decimal:2',
            'fecha_recepcion' => 'datetime',
        ];
    }

    // Total de pares del lote
    protected function unidades(): Attribute
    {
        return Attribute::get(fn () => (int) $this->detalles->sum('cantidad'));
    }

    // Ganancia estimada de todo el lote
    protected function gananciaEstimada(): Attribute
    {
        return Attribute::get(fn () => round($this->detalles->sum(fn (DetalleAbastecimiento $d) => $d->ganancia_estimada), 2));
    }

    // Un lote tiene muchas filas
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleAbastecimiento::class, 'orden_abastecimiento_id');
    }

    // Proveedor al que se pide el lote
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    // Admin que registró la orden
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
