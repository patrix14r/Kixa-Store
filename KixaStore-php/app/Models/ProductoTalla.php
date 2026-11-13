<?php

namespace App\Models;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['producto_id', 'talla', 'stock'])]
class ProductoTalla extends Model
{

    protected $table = 'producto_tallas';


    protected $fillable = ['producto_id', 'talla', 'stock'];

    protected function casts(): array
    {
        return [
            'talla' => 'decimal:1',
            'stock' => 'integer',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
