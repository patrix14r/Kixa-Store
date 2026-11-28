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

// CRUD 2: productos
class ProductoController extends Controller
{
    private const TALLAS_ADULTO = [38, 39, 40, 41, 42, 43];
    private const TALLAS_NINO = [30, 31, 32, 33, 34, 35, 36];

    // Lista productos con filtros y stock
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

    // Formulario vacío con tallas base
    public function create()
    {
        $producto = new Producto(['activo' => true]);
        $tallas = collect(self::TALLAS_ADULTO)->map(fn ($t) => ['talla' => $t, 'stock' => 0])->all();

        return view('admin.productos.create', array_merge($this->opciones($producto), compact('producto', 'tallas')));
    }

    // Guarda el producto y sus tallas
    public function store(Request $request)
    {
        $datos = $this->validar($request);

        $producto = DB::transaction(function () use ($request, $datos) {
            $producto = Producto::create($datos);
            $this->sincronizarTallas($producto, $request->input('tallas', []));

            return $producto;
        });

        return redirect()->route('admin.productos.index')
            ->with('exito', 'Producto creado correctamente.')
            ->with('producto_guardado', $this->enlacesProducto($producto));
    }

    // Formulario de edición con sus tallas
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

    // Actualiza el producto y sus tallas
    public function update(Request $request, Producto $producto)
    {
        $datos = $this->validar($request, $producto);

        DB::transaction(function () use ($request, $producto, $datos) {
            $producto->update($datos);
            $this->sincronizarTallas($producto, $request->input('tallas', []));
        });

        return redirect()->route('admin.productos.index')
            ->with('exito', 'Producto actualizado correctamente.')
            ->with('producto_guardado', $this->enlacesProducto($producto));
    }

    // Activa o desactiva (baja lógica)
    public function estado(Producto $producto)
    {
        $producto->update(['activo' => ! $producto->activo]);

        return back()->with('exito', 'Estado de ' . $producto->nombre . ' actualizado.');
    }

    // Enlaces del aviso tras guardar
    private function enlacesProducto(Producto $producto): array
    {
        return [
            'nombre'     => $producto->nombre,
            'url_tienda' => $producto->activo ? route('producto.detalle', $producto->sku) : null,
            'url_editar' => route('admin.productos.edit', $producto),
        ];
    }

    // Listas para los select del formulario
    private function opciones(Producto $producto): array
    {
        return [
            'categorias'  => Categoria::orderBy('nombre')->get(),
            'proveedores' => Proveedor::where('activo', true)->orderBy('razon_social')->get(),
            'marcas'      => Marca::where('activo', true)->orWhere('id', $producto->marca_id)->orderBy('nombre')->get(),
        ];
    }

    // Valida datos, genera SKU y guarda imágenes
    private function validar(Request $request, ?Producto $producto = null): array
    {
        $datos = $request->validate([
            'categoria_id'      => ['required', 'exists:categorias,id'],
            'proveedor_id'      => ['nullable', 'exists:proveedores,id'],
            'marca_id'          => ['required', Rule::exists('marcas', 'id')->where(fn ($q) => $q->where('activo', true)->orWhere('id', $producto?->marca_id))],
            'nombre'            => ['required', 'string', 'max:120'],
            'subcategoria'      => ['nullable', 'string', 'max:50'],
            'sku'               => ['nullable', 'string', 'max:30', 'alpha_dash', Rule::unique('productos', 'sku')->ignore($producto)],
            // Precio dentro del rango de decimal(10,2)
            'precio'            => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'descripcion'       => ['nullable', 'string'],
            'imagen_archivo'    => ['nullable', 'image', 'max:2048'],
            'imagen'            => ['nullable', 'string', 'max:255'],
            'galeria_archivos'  => ['nullable', 'array', 'max:6'],
            'galeria_archivos.*' => ['image', 'max:2048'],
            'galeria_quitar'    => ['nullable', 'array'],
            // Al menos una talla, sin repetir
            'tallas'            => ['bail', 'required', 'array', 'min:1',
                fn ($campo, $valor, $falla) => collect($valor)->contains(fn ($f) => isset($f['talla']) && $f['talla'] !== '') || $falla('Registra al menos una talla.')],
            'tallas.*.talla'    => ['nullable', 'numeric', 'between:15,50', 'distinct'],
            'tallas.*.stock'    => ['nullable', 'integer', 'min:0', 'max:100000'],
        ], [
            'tallas.required'         => 'Registra al menos una talla.',
            'tallas.min'              => 'Registra al menos una talla.',
            'tallas.*.talla.distinct' => 'La talla :input está repetida.',
            'tallas.*.stock.max'      => 'El stock por talla no puede superar 100000 pares.',
        ], [
            'categoria_id' => 'categoría',
            'proveedor_id' => 'proveedor',
            'marca_id' => 'marca',
            'imagen_archivo' => 'imagen',
            'galeria_archivos.*' => 'imagen secundaria',
        ]);

        // Sincroniza el texto de la marca
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

    // Genera un SKU único automático
    private function generarSku(string $marca, string $nombre): string
    {
        $parte = fn (string $texto) => str_pad(substr(strtoupper(Str::slug($texto, '')), 0, 3), 3, 'X');
        $palabras = explode('-', Str::slug($nombre));

        do {
            $sku = $parte($marca) . '-' . $parte($palabras[0] ?? $nombre) . '-' . strtoupper(Str::random(3));
        } while (Producto::where('sku', $sku)->exists());

        return $sku;
    }

    // Guarda tallas y borra las quitadas
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
