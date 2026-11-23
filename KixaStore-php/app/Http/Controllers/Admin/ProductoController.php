<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\ProductoTalla;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] CRUD 2 (Productos): listado con filtros, create/store, edit/update, tallas con stock e imágenes.
class ProductoController extends Controller
{
    private const TALLAS_ADULTO = [38, 39, 40, 41, 42, 43];
    private const TALLAS_NINO = [30, 31, 32, 33, 34, 35, 36];

    // index (GET): los buscadores (buscar, categoria, estado, marca, stock) llegan por GET y se aplican con when(); with() trae categoría y proveedor para mostrar nombres, no IDs.
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar'));
        $categoriaSlug = $request->query('categoria');
        $marca = $request->query('marca');
        $estado = $request->query('estado');
        $stock = $request->query('stock');

        $productos = Producto::with(['categoria', 'proveedor'])
            ->withSum('tallas as stock_total', 'stock')
            ->when($buscar, fn ($q) => $q->where(function ($sub) use ($buscar) {
                $sub->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('marca', 'like', "%{$buscar}%")
                    ->orWhere('sku', 'like', "%{$buscar}%")
                    ->orWhereHas('categoria', fn ($c) => $c->where('nombre', 'like', "%{$buscar}%"));
            }))
            ->when($categoriaSlug, fn ($q) => $q->whereHas('categoria', fn ($c) => $c->where('slug', $categoriaSlug)))
            ->when($marca, fn ($q) => $q->where('marca', $marca))

            ->when($estado === 'activos', fn ($q) => $q->where('activo', true))
            ->when($estado === 'inactivos', fn ($q) => $q->where('activo', false))
            ->when($estado === 'destacados', fn ($q) => $q->where('destacado', true))
            ->when($estado === 'ofertas', fn ($q) => $q->where('en_oferta', true))

            ->when($stock === 'critico', fn ($q) => $q->whereHas('tallas', fn ($t) => $t->where('stock', '<=', ProductoTalla::STOCK_CRITICO)))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        $categorias = Categoria::orderBy('nombre')->get();
        $marcas = Producto::select('marca')->distinct()->orderBy('marca')->pluck('marca');

        return view('admin.productos.index', compact('productos', 'categorias', 'marcas', 'buscar', 'categoriaSlug', 'marca', 'estado', 'stock'));
    }

    // create (GET): mezcla opciones() (categorías y proveedores para los <select>) con un Producto vacío y las tallas base.
    public function create()
    {
        $producto = new Producto(['activo' => true]);
        $tallas = collect(self::TALLAS_ADULTO)->map(fn ($t) => ['talla' => $t, 'stock' => 0])->all();

        return view('admin.productos.create', array_merge($this->opciones($producto), compact('producto', 'tallas')));
    }

    // store (POST): DB::transaction guarda el producto y sus tallas juntos; si algo falla no queda nada a medias.
    public function store(Request $request)
    {
        $datos = $this->validar($request);

        DB::transaction(function () use ($request, $datos) {
            $producto = Producto::create($datos);
            $this->sincronizarTallas($producto, $request->input('tallas', []));
        });

        return redirect()->route('admin.productos.index')->with('exito', 'Producto creado correctamente.');
    }

    // edit (GET): el producto llega por su SKU; carga sus tallas o usa las tallas base según la categoría.
    public function edit(Producto $producto)
    {
        $producto->load('tallas');
        $tallas = $producto->tallas->map(fn ($t) => ['talla' => (float) $t->talla, 'stock' => $t->stock])->all();

        if (empty($tallas)) {
            $base = $producto->categoria?->slug === 'ninos' ? self::TALLAS_NINO : self::TALLAS_ADULTO;
            $tallas = collect($base)->map(fn ($t) => ['talla' => $t, 'stock' => 0])->all();
        }

        return view('admin.productos.edit', array_merge($this->opciones($producto), compact('producto', 'tallas')));
    }

    // update (PUT): misma transacción que store, pero con $producto->update().
    public function update(Request $request, Producto $producto)
    {
        $datos = $this->validar($request, $producto);

        DB::transaction(function () use ($request, $producto, $datos) {
            $producto->update($datos);
            $this->sincronizarTallas($producto, $request->input('tallas', []));
        });

        return redirect()->route('admin.productos.index')->with('exito', 'Producto actualizado correctamente.');
    }

    // Activa o desactiva: no se borra porque tiene pedidos asociados (order_items tiene FK restrict).
    public function estado(Producto $producto)
    {
        $producto->update(['activo' => ! $producto->activo]);

        return back()->with('exito', 'Estado de ' . $producto->nombre . ' actualizado.');
    }

    // opciones() es privado (no es ruta) y lo reutilizan create y edit: devuelve las listas ordenadas por nombre para llenar los <select> de categoría, proveedor y marca.
    // Así la vista muestra $cat->nombre, $prov->razon_social y $m->nombre, y el value interno es el id solo para la FK. Solo se listan proveedores y marcas activos
    // (más la marca actual del producto, por si fue desactivada después, para no perderla al editar).
    private function opciones(Producto $producto): array
    {
        return [
            'categorias'  => Categoria::orderBy('nombre')->get(),
            'proveedores' => Proveedor::where('activo', true)->orderBy('razon_social')->get(),
            'marcas'      => Marca::where('activo', true)->orWhere('id', $producto->marca_id)->orderBy('nombre')->get(),
        ];
    }

    // Valida el Request, genera el SKU si viene vacío y guarda las imágenes subidas en public/images/products/uploads.
    private function validar(Request $request, ?Producto $producto = null): array
    {
        $datos = $request->validate([
            'categoria_id'      => ['required', 'exists:categorias,id'],
            'proveedor_id'      => ['nullable', 'exists:proveedores,id'],
            // La marca llega como FK y debe estar activa (o ser la que el producto ya tenía).
            'marca_id'          => ['required', Rule::exists('marcas', 'id')->where(fn ($q) => $q->where('activo', true)->orWhere('id', $producto?->marca_id))],
            'nombre'            => ['required', 'string', 'max:120'],
            'subcategoria'      => ['nullable', 'string', 'max:50'],
            'sku'               => ['nullable', 'string', 'max:30', 'alpha_dash', Rule::unique('productos', 'sku')->ignore($producto)],
            'precio'            => ['required', 'numeric', 'min:0', 'max:99999999'],
            'descripcion'       => ['nullable', 'string'],
            'imagen_archivo'    => ['nullable', 'image', 'max:2048'],
            'imagen'            => ['nullable', 'string', 'max:255'],
            'galeria_archivos'  => ['nullable', 'array', 'max:6'],
            'galeria_archivos.*' => ['image', 'max:2048'],
            'galeria_quitar'    => ['nullable', 'array'],
            'tallas'            => ['array'],
            'tallas.*.talla'    => ['nullable', 'numeric', 'between:15,50'],
            'tallas.*.stock'    => ['nullable', 'integer', 'min:0'],
        ], [], [
            'categoria_id' => 'categoría',
            'proveedor_id' => 'proveedor',
            'marca_id' => 'marca',
            'imagen_archivo' => 'imagen',
            'galeria_archivos.*' => 'imagen secundaria',
        ]);

        // Sincroniza la columna de texto 'marca' con el nombre de la marca elegida: el catálogo y sus filtros la siguen usando.
        $datos['marca'] = Marca::whereKey($datos['marca_id'])->value('nombre');

        $datos['sku'] = strtoupper($datos['sku'] ?? '') ?: $this->generarSku($datos['marca'], $datos['nombre']);

        if ($request->hasFile('imagen_archivo')) {
            $archivo = $request->file('imagen_archivo');
            $nombre = Str::slug($datos['sku']) . '-' . time() . '.' . $archivo->extension();
            $archivo->move(public_path('images/products/uploads'), $nombre);
            $datos['imagen'] = 'images/products/uploads/' . $nombre;
        }

        $galeria = array_values(array_diff($producto?->galeria_imagenes ?? [], $request->input('galeria_quitar', [])));
        foreach ($request->file('galeria_archivos', []) as $i => $archivo) {
            $nombre = Str::slug($datos['sku']) . '-g' . $i . '-' . time() . '.' . $archivo->extension();
            $archivo->move(public_path('images/products/uploads'), $nombre);
            $galeria[] = 'images/products/uploads/' . $nombre;
        }
        $datos['galeria_imagenes'] = array_slice($galeria, 0, 6) ?: null;

        unset($datos['imagen_archivo'], $datos['tallas'], $datos['galeria_archivos'], $datos['galeria_quitar']);
        $datos['destacado'] = $request->boolean('destacado');
        $datos['en_oferta'] = $request->boolean('en_oferta');
        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }

    private function generarSku(string $marca, string $nombre): string
    {
        $parte = fn (string $texto) => str_pad(substr(strtoupper(Str::slug($texto, '')), 0, 3), 3, 'X');
        $palabras = explode('-', Str::slug($nombre));

        do {
            $sku = $parte($marca) . '-' . $parte($palabras[0] ?? $nombre) . '-' . strtoupper(Str::random(3));
        } while (Producto::where('sku', $sku)->exists());

        return $sku;
    }

    // Crea o actualiza las filas de producto_tallas enviadas y borra las tallas quitadas del formulario.
    private function sincronizarTallas(Producto $producto, array $filas): void
    {
        $tallasGuardadas = [];

        foreach ($filas as $fila) {
            if (! isset($fila['talla']) || $fila['talla'] === '') {
                continue;
            }
            $talla = round((float) $fila['talla'], 1);
            $producto->tallas()->updateOrCreate(['talla' => $talla], ['stock' => (int) ($fila['stock'] ?? 0)]);
            $tallasGuardadas[] = $talla;
        }

        $producto->tallas()->whereNotIn('talla', $tallasGuardadas)->delete();
    }
}
