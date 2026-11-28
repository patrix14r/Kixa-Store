<?php

namespace App\Models;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
// Modelo de proveedores mayoristas
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

    // Convierte activo a booleano
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    // Usa el RUC en la URL
    public function getRouteKeyName(): string
    {
        return 'ruc';
    }

    // Filtra solo proveedores activos
    #[Scope]
    protected function activos(Builder $consulta): void
    {
        $consulta->where('activo', true);
    }

    // Un proveedor surte muchos productos
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'proveedor_id');
    }
}
