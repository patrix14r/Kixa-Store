<?php

namespace App\Models;

use App\Models\Categoria;
use App\Models\ProductoTalla;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'categoria_id',
    'proveedor_id',
    'marca',
    'nombre',
    'subcategoria',
    'sku',
    'precio',
    'descripcion',
    'imagen',
    'galeria_imagenes',
    'destacado',
    'en_oferta',
    'activo',
])]
class Producto extends Model
{

    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'proveedor_id',
        'marca',
        'nombre',
        'subcategoria',
        'sku',
        'precio',
        'descripcion',
        'imagen',
        'galeria_imagenes',
        'destacado',
        'en_oferta',
        'activo',
    ];


    protected function casts(): array
    {
        return [
            'precio'    => 'decimal:2',
            // [Actualizado: Galería] Imágenes secundarias guardadas como arreglo JSON
            'galeria_imagenes' => 'array',
            'destacado' => 'boolean',
            'en_oferta' => 'boolean',
            'activo'    => 'boolean',
        ];
    }

    // [Sem 08 - Rutas y Vistas Blade]: Las URLs del admin usan el SKU en lugar del ID (Regla 4.4)
    public function getRouteKeyName(): string
    {
        return 'sku';
    }

    // [Sem 12 - Eloquent ORM]: Accessor $producto->modelo: nombre sin la marca repetida ("Jordan Retro 4" -> "Retro 4")
    protected function modelo(): Attribute
    {
        return Attribute::get(function () {
            $prefijo = $this->marca . ' ';
            return str_starts_with(mb_strtolower($this->nombre), mb_strtolower($prefijo))
                ? mb_substr($this->nombre, mb_strlen($prefijo))
                : $this->nombre;
        });
    }

    // [Actualizado: Tarjetas estilo Nike] Nombre legible de cada subcategoría guardada en la BD
    public const SUBCATEGORIAS = [
        'urbano'    => ['Urbanas', 'Zapatillas urbanas'],
        'running'   => ['Running', 'Zapatillas de running'],
        'futbol'    => ['Chimpunes', 'Chimpunes de fútbol'],
        'basquet'   => ['Básquet', 'Zapatillas de básquet'],
        'escolar'   => ['Escolar', 'Zapatillas escolares'],
        'deportivo' => ['Deportivas', 'Zapatillas deportivas'],
        'gym'       => ['Gym', 'Zapatillas de entrenamiento'],
        'training'  => ['Training', 'Zapatillas de entrenamiento'],
    ];

    public static function nombreSubcategoria(?string $sub): string
    {
        return self::SUBCATEGORIAS[$sub][0] ?? ucfirst((string) $sub);
    }

    // [Sem 12 - Eloquent ORM]: Accessor $producto->tipo_calzado ("Zapatillas urbanas", "Chimpunes de fútbol"...)
    protected function tipoCalzado(): Attribute
    {
        return Attribute::get(fn () => self::SUBCATEGORIAS[$this->subcategoria][1] ?? 'Zapatillas');
    }

    #[Scope]
    protected function activos(Builder $consulta): void
    {
        $consulta->where('activo', true);
    }


    #[Scope]
    protected function destacados(Builder $consulta): void
    {
        $consulta->where('destacado', true);
    }


    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function tallas(): HasMany
    {
        return $this->hasMany(ProductoTalla::class, 'producto_id');
    }
}
