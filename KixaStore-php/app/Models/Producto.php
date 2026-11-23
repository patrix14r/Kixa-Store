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

// [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] Modelo de la tabla productos: scopes, accessors, casts y relaciones con categoría, proveedor y tallas.
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

    // casts() convierte precio a decimal, galeria_imagenes (JSON) a array y los flags a booleano.
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

    // [Semana 8: Rutas y Herencia Blade] La URL usa el SKU (/producto/ADI-CAM-00S) para no mostrar el ID autoincremental.
    public function getRouteKeyName(): string
    {
        return 'sku';
    }

    // Accessor con Attribute::get: $producto->modelo devuelve el nombre sin repetir la marca ("Jordan Retro 4" -> "Retro 4").
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

    public static function nombreSubcategoria(?string $sub): string
    {
        return self::SUBCATEGORIAS[$sub][0] ?? ucfirst((string) $sub);
    }

    // Accessor: $producto->tipo_calzado traduce la subcategoría guardada ("futbol") a un texto legible ("Chimpunes de fútbol").
    // Accessor: $producto->url_imagen sirve tanto para URLs externas como para rutas locales de public/.
    protected function urlImagen(): Attribute
    {
        return Attribute::get(fn () => str_starts_with((string) $this->imagen, 'http') ? $this->imagen : asset((string) $this->imagen));
    }

    protected function tipoCalzado(): Attribute
    {
        return Attribute::get(fn () => self::SUBCATEGORIAS[$this->subcategoria][1] ?? 'Zapatillas');
    }

    #[Scope]
    // Scope local: Producto::activos() deja solo los productos visibles en la tienda.
    protected function activos(Builder $consulta): void
    {
        $consulta->where('activo', true);
    }

    #[Scope]
    // Scope local: Producto::destacados() se usa en "Lo Destacado" del home.
    protected function destacados(Builder $consulta): void
    {
        $consulta->where('destacado', true);
    }

    // Relación BelongsTo: cada producto pertenece a una categoría (FK categoria_id); se muestra con $producto->categoria->nombre.
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    

    // Relación BelongsTo: cada producto tiene un proveedor opcional (FK proveedor_id con nullOnDelete); se muestra con ->proveedor->razon_social.
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    // Relación HasMany: un producto tiene muchas filas de producto_tallas, cada una con su stock.
    public function tallas(): HasMany
    {
        return $this->hasMany(ProductoTalla::class, 'producto_id');
    }
}
