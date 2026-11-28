<?php

namespace App\Models;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// Tallas de cada producto con su stock
#[Fillable(['producto_id', 'talla', 'stock'])]
class ProductoTalla extends Model
{
    protected $table = 'producto_tallas';

    // Stock crítico: 2 pares o menos
    public const STOCK_CRITICO = 2;

    // Aviso de últimas unidades (1 a 3)
    public const ULTIMAS_UNIDADES = 3;

    protected $fillable = ['producto_id', 'talla', 'stock'];

    // Talla con un decimal, stock entero
    protected function casts(): array
    {
        return [
            'talla' => 'decimal:1',
            'stock' => 'integer',
        ];
    }

    // Muestra la talla sin ceros sobrantes
    protected function tallaEtiqueta(): Attribute
    {
        return Attribute::get(fn () => rtrim(rtrim(number_format((float) $this->talla, 1), '0'), '.'));
    }

    // La talla pertenece a un producto
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
