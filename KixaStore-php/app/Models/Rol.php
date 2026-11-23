<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] Modelo de la tabla roles (admin y cliente); separa los roles de los usuarios.
#[Fillable(['nombre', 'descripcion'])]
class Rol extends Model
{
    protected $table = 'roles';

    // Relación HasMany: un rol tiene muchos usuarios (inversa de User::rol()).
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}
