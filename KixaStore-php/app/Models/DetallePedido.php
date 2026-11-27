<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] Modelo de order_items (una línea por zapatilla y talla comprada); se declara $table porque el nombre no sigue la convención.
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

    // casts() fija talla, cantidad y montos al tipo correcto.
    protected function casts(): array
    {
        return [
            'talla'           => 'decimal:1',
            'cantidad'        => 'integer',
            'precio_unitario' => 'decimal:2',
            'subtotal'        => 'decimal:2',
        ];
    }

    // Accessor: $linea->talla_etiqueta muestra 40 o 40.5 (igual que ProductoTalla) en el perfil y en la boleta PDF.
    protected function tallaEtiqueta(): Attribute
    {
        return Attribute::get(fn () => rtrim(rtrim(number_format((float) $this->talla, 1), '0'), '.'));
    }

    // Relación BelongsTo: la línea pertenece a un pedido (FK pedido_id con cascadeOnDelete).
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // Relación BelongsTo: producto comprado en esta línea (FK producto_id con restrictOnDelete).
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
