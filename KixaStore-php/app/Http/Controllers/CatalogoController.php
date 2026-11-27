<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoTalla;
use Illuminate\Http\Request;

// [Semana 9-10: Controladores, Request y Componentes] Catálogo del cliente: filtros por Request (?marca, ?sub, ?talla, ?orden) y ficha por SKU.
class CatalogoController extends Controller
{
    private const BANNERS = [
        'mujer' => [
            'clase' => 'mujer-hero',
            'subtitulo' => 'KIXA WOMEN',
            'titulo_principal' => 'MUJER',
            'enfasis' => 'ESTILO',
            'slogan' => 'SIN LÍMITES.',
            'descripcion' => 'Las mejores marcas, los modelos más top y la comodidad que necesitas en cada paso.',
            'imagenes' => [
                'images/banners/mujer/dsa3.webp',
                'images/banners/mujer/dsa4.avif',
                'images/banners/mujer/dsa33.avif',
            ],
        ],
        'ninos' => [
            'clase' => 'ninos-hero',
            'subtitulo' => 'KIXA KIDS',
            'titulo_principal' => 'NIÑOS',
            'enfasis' => 'ENERGÍA',
            'slogan' => 'Y DIVERSIÓN.',
            'descripcion' => 'Calzado escolar y urbano ultrarresistente con amortiguación y máxima seguridad para los más chicos.',
            'imagenes' => [
                'images/banners/ninos/123.jpg',
                'images/banners/ninos/qwe.jpg',
            ],
        ],
        'deporte' => [
            'clase' => 'deporte-hero',
            'subtitulo' => 'KIXA SPORT',
            'titulo_principal' => 'DEPORTE',
            'enfasis' => 'MÁXIMO',
            'slogan' => 'RENDIMIENTO.',
            'descripcion' => 'Chimpunes, running y calzado de alto rendimiento para fútbol, futsal, gimnasio y running.',
            'imagenes' => [
                'images/banners/deporte/dsa1.jpg',
                'images/banners/deporte/dsa2.jpg',
                'images/banners/deporte/das3.jpg',
            ],
        ],
        'hombre' => [
            'clase' => 'hombre-hero',
            'subtitulo' => 'KIXA MEN',
            'titulo_principal' => 'HOMBRE',
            'enfasis' => 'ACTITUD',
            'slogan' => 'Y EXCLUSIVIDAD.',
            'descripcion' => 'Ediciones limitadas, zapatillas urbanas y de básquetbol con la mayor presencia del mercado.',
            'imagenes' => [
                'images/banners/hombre/hombre6.jpg',
                'images/banners/hombre/hombre5.jpg',
                'images/banners/hombre/87.png',
            ],
        ],
    ];

    private const ANCLA = '#zona-catalogo';

    private const TITULOS = [
        'hombre'  => 'Zapatillas Hombre',
        'mujer'   => 'Zapatillas Mujer',
        'ninos'   => 'Zapatillas Niños',
        'deporte' => 'Calzado Deportivo',
    ];

    private const ORDENES = [
        'destacados'  => 'Destacados',
        'precio_asc'  => 'Precio: menor a mayor',
        'precio_desc' => 'Precio: mayor a menor',
        'nuevo'       => 'Lo más nuevo',
    ];

    private const POR_PAGINA = 12;

    // Filtra el catálogo con Eloquent: recibe la categoría por la ruta y el resto por query string con $request->query().
    // Si la petición llega por fetch (X-Requested-With: XMLHttpRequest) solo devuelve el fragmento de la grilla, sin layout ni banner.
    public function index(Request $request, $categoria = 'todos')
    {
        $subcategoria = $request->query('sub');
        $marca        = $request->query('marca');
        $talla        = $request->query('talla');
        $orden        = array_key_exists($request->query('orden'), self::ORDENES) ? $request->query('orden') : 'destacados';
        $buscar       = $this->limpiarBusqueda((string) $request->query('buscar'));

        // [Sem 12 - Eloquent ORM]: firstOrFail() responde 404 si la categoría no existe o está inactiva (antes mostraba todo el catálogo).
        $catObj = ($categoria && $categoria !== 'todos')
            ? Categoria::activas()->where('slug', $categoria)->firstOrFail()
            : null;

        $base = Producto::activos()->whereHas('categoria', fn ($c) => $c->where('activo', true));

        if ($buscar !== '') {
            foreach (preg_split('/\s+/', $buscar) as $palabra) {
                // addcslashes escapa % y _ para que el usuario no use comodines de LIKE.
                $palabra = addcslashes($palabra, '%_\\');
                $base->where(function ($q) use ($palabra) {
                    $q->where('nombre', 'like', "%{$palabra}%")
                      ->orWhere('marca', 'like', "%{$palabra}%")
                      ->orWhere('sku', 'like', "%{$palabra}%")
                      ->orWhereHas('categoria', fn ($c) => $c->where('nombre', 'like', "%{$palabra}%"));
                });
            }
        } elseif ($catObj) {
            $base->where('categoria_id', $catObj->id);
        }

        $marcasDisponibles = (clone $base)->distinct()->orderBy('marca')->pluck('marca')->all();
        $subsDisponibles   = (clone $base)->whereNotNull('subcategoria')->distinct()->orderBy('subcategoria')->pluck('subcategoria')->all();
        $tallasDisponibles = ProductoTalla::whereIn('producto_id', (clone $base)->select('id'))
            ->where('stock', '>', 0)->distinct()->orderBy('talla')->pluck('talla')
            ->map(fn ($t) => $this->formatoTalla($t))->unique()->values()->all();

        $query = (clone $base)->with(['categoria', 'tallas']);

        if ($subcategoria) {
            $query->where('subcategoria', $subcategoria);
        }

        if ($marca) {
            $query->where('marca', $marca);
        }

        if ($talla) {
            $query->whereHas('tallas', fn ($q) => $q->where('talla', $talla)->where('stock', '>', 0));
        }

        match ($orden) {
            'precio_asc'  => $query->orderBy('precio'),
            'precio_desc' => $query->orderByDesc('precio'),
            'nuevo'       => $query->latest('id'),
            default       => $query->orderByDesc('destacado')->orderBy('nombre'),
        };

        // [Sem 13 - CRUDs, Query Builder y Paginación]: 12 por página; withQueryString() conserva los filtros y fragment() el ancla #zona-catalogo.
        $productos = $query->paginate(self::POR_PAGINA)->withQueryString()->fragment(ltrim(self::ANCLA, '#'));

        $bannerActual = self::BANNERS[strtolower($categoria ?? 'todos')] ?? self::BANNERS['hombre'];

        $titulo = $buscar !== ''
            ? 'Resultados para "' . $buscar . '"'
            : ($catObj ? self::TITULOS[$catObj->slug] ?? 'Zapatillas ' . $catObj->nombre : 'Todas las zapatillas');

        $mostrarCategoria = ! $catObj || ! in_array($catObj->slug, ['hombre', 'mujer', 'ninos'], true) || $buscar !== '';

        $filtrosCategoria = Categoria::where('activo', true)->orderBy('id')->get()->map(fn ($c) => [
            'etiqueta' => $c->nombre,
            'activo'   => $catObj?->id === $c->id && $buscar === '',
            'url'      => route('catalogo', array_merge(['categoria' => $c->slug], $request->except(['buscar', 'marca', 'sub', 'talla', 'page']))) . self::ANCLA,
        ])->prepend([
            'etiqueta' => 'Todos',
            'activo'   => ! $catObj && $buscar === '',
            'url'      => route('catalogo', ['categoria' => 'todos'] + $request->only('orden')) . self::ANCLA,
        ])->all();

        $filtrosMarca = $this->filtros($request, $categoria, 'marca', $marcasDisponibles);
        $filtrosSub   = $this->filtros($request, $categoria, 'sub', $subsDisponibles, fn ($s) => Producto::nombreSubcategoria($s));
        $filtrosTalla = $this->filtros($request, $categoria, 'talla', $tallasDisponibles);

        $ordenes          = self::ORDENES;
        // Al cambiar el orden o un filtro se vuelve a la página 1 (sin ?page).
        $parametrosOrden  = $request->except(['orden', 'page']);

        $hayFiltros       = (bool) ($marca || $subcategoria || $talla || $buscar !== '');
        $urlLimpiar       = route('catalogo', ['categoria' => $categoria] + $request->only('orden')) . self::ANCLA;

        $vista = view('cliente.catalogo', compact(
            'productos', 'categoria', 'subcategoria', 'marca', 'talla', 'buscar', 'orden', 'titulo',
            'bannerActual', 'filtrosCategoria', 'filtrosMarca', 'filtrosSub', 'filtrosTalla',
            'ordenes', 'parametrosOrden', 'hayFiltros', 'urlLimpiar', 'mostrarCategoria'
        ));

        // [Sem 09 - Controladores y Middleware]: $request->ajax() detecta la cabecera X-Requested-With que envía catalogo-filtros.js;
        // fragment() renderiza solo el bloque @fragment('zona-catalogo') para reemplazarlo sin recargar header ni banner.
        // Vary avisa al navegador que la misma URL tiene dos respuestas (página completa o fragmento) y no debe mezclarlas en caché.
        return response($request->ajax() ? $vista->fragment('zona-catalogo') : $vista)
            ->header('Vary', 'X-Requested-With');
    }

    // Devuelve JSON con las tallas que tienen stock (lo consume favoritos.js).
    public function tallas(string $sku)
    {
        $producto = Producto::activos()->with(['tallas' => fn ($q) => $q->where('stock', '>', 0)->orderBy('talla'), 'categoria'])
            ->where('sku', $sku)->firstOrFail();

        return response()->json([
            'sku'    => $producto->sku,
            'nombre' => $producto->nombre,
            'precio' => (float) $producto->precio,
            'tipo'   => $producto->tipo_calzado . ' • ' . $producto->categoria?->nombre,
            'tallas' => $producto->tallas->map(fn ($t) => $this->formatoTalla($t->talla))->values(),
        ]);
    }

    // Busca el producto por su SKU con with() para evitar consultas repetidas y muestra la ficha.
    public function show($sku = null)
    {
        // Solo productos activos y solo por SKU (nunca por id) para no exponer IDs ni productos ocultos.
        // [Sem 12 - Eloquent ORM]: las tallas llegan ordenadas (38, 38.5, 39...) para mostrar bien las medias tallas.
        $consulta = Producto::activos()->with(['categoria', 'proveedor', 'tallas' => fn ($q) => $q->orderBy('talla')]);

        $producto = $sku
            ? $consulta->where('sku', $sku)->firstOrFail()
            : $consulta->firstOrFail();

        $recomendados = Producto::activos()
            ->where('id', '!=', $producto->id)
            ->where('categoria_id', $producto->categoria_id)
            ->take(4)
            ->get();

        if ($recomendados->count() < 4) {
            $faltantes = 4 - $recomendados->count();
            $adicionales = Producto::activos()
                ->where('id', '!=', $producto->id)
                ->whereNotIn('id', $recomendados->pluck('id'))
                ->take($faltantes)
                ->get();
            $recomendados = $recomendados->merge($adicionales);
        }

        $recomendados->load('categoria');

        // Equivalencias para el modal "Guía de tallas" (la lógica y los datos viven en el controlador, no en Blade).
        $guiaTallas = [
            ['us' => 6,  'eu' => 39, 'cm' => 24],
            ['us' => 7,  'eu' => 40, 'cm' => 25],
            ['us' => 8,  'eu' => 41, 'cm' => 26],
            ['us' => 9,  'eu' => 42, 'cm' => 27],
            ['us' => 10, 'eu' => 43, 'cm' => 28],
            ['us' => 11, 'eu' => 44, 'cm' => 29],
            ['us' => 12, 'eu' => 45, 'cm' => 30],
        ];

        // Marketing de escasez (1 a 3 pares) y regla de envío: salen de los modelos para no repetir números en la vista.
        $ultimasUnidades = ProductoTalla::ULTIMAS_UNIDADES;
        $reglaEnvio = Pedido::textoReglaEnvio();

        return view('cliente.producto', compact('producto', 'recomendados', 'guiaTallas', 'ultimasUnidades', 'reglaEnvio'));
    }

    private function filtros(Request $request, ?string $categoria, string $parametro, array $valores, ?callable $etiqueta = null): array
    {
        return array_map(function ($valor) use ($request, $categoria, $parametro, $etiqueta) {
            $activo = (string) $request->query($parametro) === (string) $valor;
            $params = $request->except('page');

            if ($activo) {
                unset($params[$parametro]);
            } else {
                $params[$parametro] = $valor;
            }

            return [
                'etiqueta' => $etiqueta ? $etiqueta($valor) : $valor,
                'activo'   => $activo,
                'url'      => route('catalogo', array_merge(['categoria' => $categoria], $params)) . self::ANCLA,
            ];
        }, $valores);
    }

    // [Sem 09 - Controladores y Middleware]: recorta el buscador a 80 caracteres y 6 palabras para evitar consultas abusivas.
    private function limpiarBusqueda(string $texto): string
    {
        $texto    = mb_substr(trim(preg_replace('/\s+/u', ' ', $texto)), 0, 80);
        $palabras = array_slice(array_filter(explode(' ', $texto), fn ($p) => $p !== ''), 0, 6);

        return implode(' ', $palabras);
    }

    private function formatoTalla($talla): string
    {
        return rtrim(rtrim(number_format((float) $talla, 1, '.', ''), '0'), '.');
    }
}
