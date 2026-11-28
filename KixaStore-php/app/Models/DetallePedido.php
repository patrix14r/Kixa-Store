<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Línea de un pedido (order_items)
#[Fillable([
    'pedido_id',
    'producto_id',
    'talla',
    'cantidad',
    'precio_unitario',
    'subtotal',
])]
class DetallePedido extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'talla',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    // Convierte talla, cantidad y montos
    protected function casts(): array
    {
        return [
            'talla'           => 'decimal:1',
            'cantidad'        => 'integer',
            'precio_unitario' => 'decimal:2',
            'subtotal'        => 'decimal:2',
        ];
    }

    // Muestra la talla sin ceros sobrantes
    protected function tallaEtiqueta(): Attribute
    {
        return Attribute::get(fn () => rtrim(rtrim(number_format((float) $this->talla, 1), '0'), '.'));
    }

    // La línea pertenece a un pedido
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // Producto comprado en esta línea
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
