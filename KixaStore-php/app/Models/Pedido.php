<?php

namespace App\Models;

// [CAMBIO: IMPORTACIONES ELOQUENT PARA MODELO PEDIDO]
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
    // [CAMBIO: NOMBRE DE LA TABLA EN MYSQL]
    protected $table = 'pedidos';

    // [CAMBIO: ASIGNACION MASIVA FILLABLE]
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

    // [CAMBIO: CASTEO DE TIPOS DE DATOS]
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'igv'      => 'decimal:2',
            'total'    => 'decimal:2',
        ];
    }

    // [CAMBIO: RELACION BELONGS TO CON USUARIO]
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // [CAMBIO: RELACION HAS MANY CON DETALLES DE PEDIDO]
    public function items(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }
}
