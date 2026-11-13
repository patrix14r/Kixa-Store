<?php

namespace App\Models;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
#[Fillable([
    'razon_social',
    'ruc',
    'contacto_nombre',
    'telefono',
    'correo',
    'direccion',
    'activo',
])]
class Proveedor extends Model
{
   
    protected $table = 'proveedores';

    protected $fillable = [
        'razon_social',
        'ruc',
        'contacto_nombre',
        'telefono',
        'correo',
        'direccion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'proveedor_id');
    }
}
