<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoTalla;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// [Sem 12-13 - Eloquent ORM y Route::resource]: CRUD 4 (Tallas y Stock) sobre producto_tallas, con los 6 métodos index, create, store, edit, update y destroy.
class TallaController extends Controller
{
    // Umbral de stock crítico (el mismo que usa el dashboard).
    public const STOCK_CRITICO = 2;

    // index (GET): filtros por texto (nombre o SKU), marca, categoría (slug), estado de stock y producto (SKU); paginate() conserva los filtros.
    public function index(Request $request)
    {
        $sku = $request->query('producto');
        $buscar = trim((string) $request->query('q'));
        $marca = $request->query('marca');
        $categoria = $request->query('categoria');
        // 'bajo' se mantiene como alias de "agotado o crítico" (2 o menos).
        $estado = $request->query('stock') === 'bajo' ? 'bajo' : $request->query('estado');

        $tallas = ProductoTalla::with('producto')
            ->when($sku, fn ($q) => $q->whereHas('producto', fn ($p) => $p->where('sku', $sku)))
            ->when($buscar !== '', fn ($q) => $q->whereHas('producto', fn ($p) => $p->where(fn ($w) => $w->where('nombre', 'like', "%{$buscar}%")->orWhere('sku', 'like', "%{$buscar}%"))))
            ->when($marca, fn ($q) => $q->whereHas('producto', fn ($p) => $p->where('marca', $marca)))
            ->when($categoria, fn ($q) => $q->whereHas('producto.categoria', fn ($c) => $c->where('slug', $categoria)))
            ->when($estado === 'agotado', fn ($q) => $q->where('stock', 0))
            ->when($estado === 'critico', fn ($q) => $q->whereBetween('stock', [1, self::STOCK_CRITICO]))
            ->when($estado === 'disponible', fn ($q) => $q->where('stock', '>', self::STOCK_CRITICO))
            ->when($estado === 'bajo', fn ($q) => $q->where('stock', '<=', self::STOCK_CRITICO))
            ->orderBy('stock')
            ->orderBy('producto_id')
            ->orderBy('talla')
            ->paginate(15)
            ->withQueryString();

        $productos = Producto::orderBy('nombre')->get(['sku', 'marca', 'nombre']);
        $marcas = Producto::select('marca')->distinct()->orderBy('marca')->pluck('marca');
        $categorias = Categoria::orderBy('nombre')->get(['nombre', 'slug']);
        $hayFiltros = $sku || $buscar !== '' || $marca || $categoria || $estado;

        return view('admin.tallas.index', compact('tallas', 'productos', 'marcas', 'categorias', 'sku', 'buscar', 'marca', 'categoria', 'estado', 'hayFiltros'));
    }

    // create (GET): formulario vacío con la lista de productos para el <select>.
    public function create()
    {
        return view('admin.tallas.create', [
            'talla' => new ProductoTalla(['stock' => 0]),
            'productos' => Producto::orderBy('nombre')->get(),
        ]);
    }

    // store (POST): valida y crea; la combinación producto + talla no puede repetirse.
    public function store(Request $request)
    {
        ProductoTalla::create($this->validar($request));

        return redirect()->route('admin.tallas.index')->with('exito', 'Talla registrada correctamente.');
    }

    // edit (GET): {producto} se resuelve por SKU y la talla se busca dentro de sus tallas (404 si no existe).
    public function edit(Producto $producto, string $talla)
    {
        $talla = $this->buscar($producto, $talla);

        return view('admin.tallas.edit', [
            'talla' => $talla,
            'productos' => Producto::orderBy('nombre')->get(),
        ]);
    }

    // update (PUT): valida ignorando la propia fila en la regla unique.
    public function update(Request $request, Producto $producto, string $talla)
    {
        $talla = $this->buscar($producto, $talla);
        $talla->update($this->validar($request, $talla));

        return redirect()->route('admin.tallas.index')->with('exito', 'Talla actualizada correctamente.');
    }

    // destroy (DELETE): borra la talla; las órdenes de abastecimiento de esa talla se eliminan por cascadeOnDelete.
    public function destroy(Producto $producto, string $talla)
    {
        $this->buscar($producto, $talla)->delete();

        return redirect()->route('admin.tallas.index')->with('exito', 'Talla eliminada correctamente.');
    }

    // Busca la talla de un producto por su número (9, 9.0 o 9.5).
    private function buscar(Producto $producto, string $talla): ProductoTalla
    {
        abort_unless(is_numeric($talla), 404);

        return $producto->tallas()->where('talla', $talla)->firstOrFail();
    }

    // Reglas compartidas; el <select> envía el id del producto (el usuario solo ve el nombre).
    private function validar(Request $request, ?ProductoTalla $talla = null): array
    {
        return $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'talla'       => [
                'required', 'numeric', 'between:20,50', 'multiple_of:0.5',
                Rule::unique('producto_tallas', 'talla')
                    ->where('producto_id', $request->input('producto_id'))
                    ->ignore($talla),
            ],
            'stock'       => ['required', 'integer', 'min:0', 'max:100000'],
        ], [
            'talla.unique' => 'Ese producto ya tiene registrada esa talla.',
        ]);
    }
}
