<?php

namespace App\Models;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


#[Fillable(['nombre', 'slug', 'descripcion'])]
class Categoria extends Model
{
    protected $table = 'categorias';
    protected $fillable = ['nombre', 'slug', 'descripcion'];
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}
