<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'codigo_orden',
    'subtotal',
    'igv',
    'total',
    'estado',
    'direccion_envio',
    'metodo_pago',
])]
class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'user_id',
        'codigo_orden',
        'subtotal',
        'igv',
        'total',
        'estado',
        'direccion_envio',
        'metodo_pago',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'igv'      => 'decimal:2',
            'total'    => 'decimal:2',
        ];
    }

    // [Actualizado: Seguimiento] Pasos de la barra de progreso del historial de pedidos
    public const PASOS = ['Confirmado', 'En preparación', 'En camino', 'Entregado'];

    // [Sem 12 - Eloquent ORM]: Accessor $pedido->paso_seguimiento (1 a 4; 0 si fue cancelado)
    protected function pasoSeguimiento(): Attribute
    {
        return Attribute::get(fn () => match ($this->estado) {
            'Pendiente' => 1,
            'Pagado'    => 2,
            'Enviado'   => 3,
            'Entregado' => 4,
            default     => 0,
        });
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }
}
