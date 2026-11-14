<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// [Sem 12 - Eloquent ORM]: Modelo Rol con #[Fillable] y relación HasMany hacia los usuarios (1:N)
#[Fillable(['nombre', 'descripcion'])]
class Rol extends Model
{
    protected $table = 'roles';

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}
