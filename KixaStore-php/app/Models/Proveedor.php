<?php

namespace App\Models;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
// [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] Modelo de la tabla proveedores (tabla en plural irregular, por eso se declara $table).
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

    // casts() devuelve activo como booleano.
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    // [Sem 08 - Rutas y Vistas Blade]: el binding usa el RUC, así la URL /admin/proveedores/20123456789/edit no expone el ID autoincremental.
    public function getRouteKeyName(): string
    {
        return 'ruc';
    }

    // Scope local con #[Scope]: Proveedor::activos()->get() reutiliza el where activo = true.
    #[Scope]
    protected function activos(Builder $consulta): void
    {
        $consulta->where('activo', true);
    }

    // Relación HasMany: un proveedor surte muchos productos (FK proveedor_id en productos).
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'proveedor_id');
    }
}
