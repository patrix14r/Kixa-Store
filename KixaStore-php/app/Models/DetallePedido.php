<?php

namespace App\Models;

// [CAMBIO: IMPORTACIONES ELOQUENT PARA MODELO DETALLEPEDIDO]
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    // [CAMBIO: NOMBRE DE LA TABLA EN MYSQL SEGUN MIGRACION]
    protected $table = 'order_items';

    // [CAMBIO: ASIGNACION MASIVA FILLABLE]
    protected $fillable = [
        'pedido_id',
        'producto_id',
        'talla',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    // [CAMBIO: CASTEO DE TIPOS DE DATOS]
    protected function casts(): array
    {
        return [
            'talla'           => 'decimal:1',
            'cantidad'        => 'integer',
            'precio_unitario' => 'decimal:2',
            'subtotal'        => 'decimal:2',
        ];
    }

    // [CAMBIO: RELACION BELONGS TO CON PEDIDO]
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // [CAMBIO: RELACION BELONGS TO CON PRODUCTO]
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
