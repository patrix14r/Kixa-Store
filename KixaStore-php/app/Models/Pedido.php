<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] Modelo de pedidos: guarda totales con IGV y su estado de envío.
#[Fillable([
    'user_id',
    'codigo_orden',
    'subtotal',
    'igv',
    'total',
    'estado',
    'direccion_envio',
    'metodo_pago',
    'tarjeta_ultimos4',
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
        'tarjeta_ultimos4',
    ];

    // casts() mantiene subtotal, igv y total con 2 decimales.
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'igv'      => 'decimal:2',
            'total'    => 'decimal:2',
        ];
    }

    // [Sem 08 - Rutas y Vistas Blade]: La URL del admin usa el código de orden (/admin/pedidos/PED-AB12CD/estado) en vez del ID autoincremental.
    public function getRouteKeyName(): string
    {
        return 'codigo_orden';
    }

    public const PASOS =['Confirmado', 'En preparación', 'En camino', 'Entregado'];

    // Accessor: $pedido->paso_seguimiento traduce el estado (Pendiente, Pagado...) a un número de 1 a 4 para la barra de progreso.
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

    // Accessor: $pedido->metodo_pago_detalle muestra "Tarjeta •••• 4242" o "Contraentrega" en el perfil y en el panel.
    protected function metodoPagoDetalle(): Attribute
    {
        return Attribute::get(fn () => $this->tarjeta_ultimos4
            ? "{$this->metodo_pago} •••• {$this->tarjeta_ultimos4}"
            : $this->metodo_pago);
    }

    // Relación BelongsTo: el pedido pertenece al cliente que compró (FK user_id).
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación HasMany: un pedido tiene muchas líneas en la tabla order_items.
    public function items(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }
}
