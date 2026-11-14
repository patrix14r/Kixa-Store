<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// [Sem 09 - Controladores y MVC]: Mantenimiento 2 (Productos): listado con filtros, create/store, edit/update, stock e imagen
class ProductoCrudController extends Controller
{
    private const TALLAS_ADULTO = [38, 39, 40, 41, 42, 43];
    private const TALLAS_NINO = [30, 31, 32, 33, 34, 35, 36];

    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar'));
        $categoriaSlug = $request->query('categoria');
        $marca = $request->query('marca');
        $estado = $request->query('estado');
        $stock = $request->query('stock');

        // [Sem 12 - Eloquent ORM]: Carga ansiosa with() para mostrar categoría y proveedor por nombre (Regla 4.5)
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
            // [Actualizado: Filtro por estado] activos, inactivos, destacados u ofertas
            ->when($estado === 'activos', fn ($q) => $q->where('activo', true))
            ->when($estado === 'inactivos', fn ($q) => $q->where('activo', false))
            ->when($estado === 'destacados', fn ($q) => $q->where('destacado', true))
            ->when($estado === 'ofertas', fn ($q) => $q->where('en_oferta', true))
            // [Actualizado: Notificaciones] ?stock=critico muestra productos con alguna talla en 0, 1 o 2 pares
            ->when($stock === 'critico', fn ($q) => $q->whereHas('tallas', fn ($t) => $t->where('stock', '<=', 2)))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        $categorias = Categoria::orderBy('nombre')->get();
        $marcas = Producto::select('marca')->distinct()->orderBy('marca')->pluck('marca');

        return view('admin.productos.index', compact('productos', 'categorias', 'marcas', 'buscar', 'categoriaSlug', 'marca', 'estado', 'stock'));
    }

    public function create()
    {
        $producto = new Producto(['activo' => true]);
        $tallas = collect(self::TALLAS_ADULTO)->map(fn ($t) => ['talla' => $t, 'stock' => 0])->all();

        return view('admin.productos.create', array_merge($this->opciones(), compact('producto', 'tallas')));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        DB::transaction(function () use ($request, $datos) {
            $producto = Producto::create($datos);
            $this->sincronizarTallas($producto, $request->input('tallas', []));
        });

        return redirect()->route('admin.productos.index')->with('exito', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        $producto->load('tallas');
        $tallas = $producto->tallas->map(fn ($t) => ['talla' => (float) $t->talla, 'stock' => $t->stock])->all();

        if (empty($tallas)) {
            $base = $producto->categoria?->slug === 'ninos' ? self::TALLAS_NINO : self::TALLAS_ADULTO;
            $tallas = collect($base)->map(fn ($t) => ['talla' => $t, 'stock' => 0])->all();
        }

        return view('admin.productos.edit', array_merge($this->opciones(), compact('producto', 'tallas')));
    }

    public function update(Request $request, Producto $producto)
    {
        $datos = $this->validar($request, $producto);

        DB::transaction(function () use ($request, $producto, $datos) {
            $producto->update($datos);
            $this->sincronizarTallas($producto, $request->input('tallas', []));
        });

        return redirect()->route('admin.productos.index')->with('exito', 'Producto actualizado correctamente.');
    }

    // Activa o desactiva el producto en la tienda (no se borra porque tiene pedidos asociados)
    public function estado(Producto $producto)
    {
        $producto->update(['activo' => ! $producto->activo]);

        return back()->with('exito', 'Estado de ' . $producto->nombre . ' actualizado.');
    }

    // [Sem 12 - Eloquent ORM]: Listas para los <select> por nombre (Regla 4.5)
    private function opciones(): array
    {
        return [
            'categorias'  => Categoria::orderBy('nombre')->get(),
            'proveedores' => Proveedor::where('activo', true)->orderBy('razon_social')->get(),
        ];
    }

    private function validar(Request $request, ?Producto $producto = null): array
    {
        $datos = $request->validate([
            'categoria_id'      => ['required', 'exists:categorias,id'],
            'proveedor_id'      => ['nullable', 'exists:proveedores,id'],
            'marca'             => ['required', 'string', 'max:50'],
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
            'imagen_archivo' => 'imagen',
            'galeria_archivos.*' => 'imagen secundaria',
        ]);

        // [Actualizado: SKU automático] Si el SKU llega vacío se genera desde la marca y el modelo (sin error)
        $datos['sku'] = strtoupper($datos['sku'] ?? '') ?: $this->generarSku($datos['marca'], $datos['nombre']);

        // Imagen: si se sube un archivo se guarda en public/images/products/uploads; si no, se usa la ruta escrita
        if ($request->hasFile('imagen_archivo')) {
            $archivo = $request->file('imagen_archivo');
            $nombre = Str::slug($datos['sku']) . '-' . time() . '.' . $archivo->extension();
            $archivo->move(public_path('images/products/uploads'), $nombre);
            $datos['imagen'] = 'images/products/uploads/' . $nombre;
        }

        // [Actualizado: Galería] Conserva las imágenes secundarias no marcadas y agrega las nuevas subidas
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

    // MARCA(3)-MODELO(3)-SUFIJO(3), p. ej. ADI-CAM-00S; se repite hasta que no exista en la BD
    private function generarSku(string $marca, string $nombre): string
    {
        $parte = fn (string $texto) => str_pad(substr(strtoupper(Str::slug($texto, '')), 0, 3), 3, 'X');
        $palabras = explode('-', Str::slug($nombre));

        do {
            $sku = $parte($marca) . '-' . $parte($palabras[0] ?? $nombre) . '-' . strtoupper(Str::random(3));
        } while (Producto::where('sku', $sku)->exists());

        return $sku;
    }

    // Guarda el stock por talla: crea/actualiza las filas enviadas y elimina las tallas quitadas del formulario
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
