<?php

namespace App\Models;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] Modelo de producto_tallas: una fila por talla de cada zapatilla con su stock.
#[Fillable(['producto_id', 'talla', 'stock'])]
class ProductoTalla extends Model
{
    protected $table = 'producto_tallas';

    protected $fillable = ['producto_id', 'talla', 'stock'];

    // casts() deja la talla con un decimal (39.5) y el stock como entero.
    protected function casts(): array
    {
        return [
            'talla' => 'decimal:1',
            'stock' => 'integer',
        ];
    }

    // Accessor: $talla->talla_etiqueta devuelve "9" o "9.5" sin ceros sobrantes.
    protected function tallaEtiqueta(): Attribute
    {
        return Attribute::get(fn () => rtrim(rtrim(number_format((float) $this->talla, 1), '0'), '.'));
    }

    // Relación BelongsTo: cada talla pertenece a un producto (FK producto_id con cascadeOnDelete).
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
