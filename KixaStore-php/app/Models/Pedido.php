<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo de pedidos con totales e IGV
#[Fillable([
    'user_id',
    'codigo_orden',
    'subtotal',
    'igv',
    'costo_envio',
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
        'costo_envio',
        'total',
        'estado',
        'direccion_envio',
        'metodo_pago',
        'tarjeta_ultimos4',
    ];

    // Regla de envío: costo y mínimo gratis
    public const COSTO_ENVIO = 15.00;
    public const ENVIO_GRATIS_DESDE = 200.00;

    // Montos con 2 decimales
    protected function casts(): array
    {
        return [
            'subtotal'    => 'decimal:2',
            'igv'         => 'decimal:2',
            'costo_envio' => 'decimal:2',
            'total'       => 'decimal:2',
        ];
    }

    // Calcula el costo de envío
    public static function calcularEnvio(float $montoProductos, bool $primeraCompra): float
    {
        return ($primeraCompra || $montoProductos >= self::ENVIO_GRATIS_DESDE) ? 0.0 : self::COSTO_ENVIO;
    }

    // Texto de la regla de envío
    public static function textoReglaEnvio(): string
    {
        return 'El envío es gratis en tu primera compra o cuando tus productos suman S/ ' . number_format(self::ENVIO_GRATIS_DESDE, 2)
            . ' o más; en los demás casos cuesta S/ ' . number_format(self::COSTO_ENVIO, 2) . '.';
    }

    // Usa el código de orden en la URL
    public function getRouteKeyName(): string
    {
        return 'codigo_orden';
    }

    public const PASOS =['Confirmado', 'En preparación', 'En camino', 'Entregado'];

    // Paso de la barra de seguimiento
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

    // Método de pago con últimos 4 dígitos
    protected function metodoPagoDetalle(): Attribute
    {
        return Attribute::get(fn () => $this->tarjeta_ultimos4
            ? "{$this->metodo_pago} •••• {$this->tarjeta_ultimos4}"
            : $this->metodo_pago);
    }

    // Cliente dueño del pedido
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Líneas del pedido
    public function items(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }
}
