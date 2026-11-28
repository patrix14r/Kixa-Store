<?php

namespace App\Models;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\ProductoTalla;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo de productos de la tienda
#[Fillable([
    'categoria_id',
    'proveedor_id',
    'marca_id',
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
        'marca_id',
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

    // Convierte precio, galería y flags
    protected function casts(): array
    {
        return [
            'precio'    => 'decimal:2',

            'galeria_imagenes' => 'array',
            'destacado' => 'boolean',
            'en_oferta' => 'boolean',
            'activo'    => 'boolean',
        ];
    }

    // Usa el SKU en la URL
    public function getRouteKeyName(): string
    {
        return 'sku';
    }

    // Nombre del modelo sin la marca
    protected function modelo(): Attribute
    {
        return Attribute::get(function () {
            $prefijo = $this->marca . ' ';
            return str_starts_with(mb_strtolower($this->nombre), mb_strtolower($prefijo))
                ? mb_substr($this->nombre, mb_strlen($prefijo))
                : $this->nombre;
        });
    }

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

    // Nombre legible de la subcategoría
    public static function nombreSubcategoria(?string $sub): string
    {
        return self::SUBCATEGORIAS[$sub][0] ?? ucfirst((string) $sub);
    }

    // URL de la imagen principal
    protected function urlImagen(): Attribute
    {
        return Attribute::get(fn () => str_starts_with((string) $this->imagen, 'http') ? $this->imagen : asset((string) $this->imagen));
    }

    // Tipo de calzado según la subcategoría
    protected function tipoCalzado(): Attribute
    {
        return Attribute::get(fn () => self::SUBCATEGORIAS[$this->subcategoria][1] ?? 'Zapatillas');
    }

    #[Scope]
    // Filtra productos activos
    protected function activos(Builder $consulta): void
    {
        $consulta->where('activo', true);
    }

    #[Scope]
    // Filtra productos destacados
    protected function destacados(Builder $consulta): void
    {
        $consulta->where('destacado', true);
    }

    // El producto pertenece a una categoría
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    // El producto pertenece a una marca
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    // Proveedor opcional del producto
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    // Tallas con su stock
    public function tallas(): HasMany
    {
        return $this->hasMany(ProductoTalla::class, 'producto_id');
    }
}
