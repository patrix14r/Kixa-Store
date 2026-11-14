<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoTalla;
use App\Models\Rol;
use App\Models\User;

// [Sem 09 - Controladores y MVC]: Controlador del panel admin; reúne los datos reales y los envía a la vista
class AdminController extends Controller
{
    public function home()
    {
        // [Sem 12 - Eloquent ORM]: Conteos y sumas con Eloquent (count, sum, withCount, with)
        $rolCliente = Rol::where('nombre', 'cliente')->value('id');
        $totalClientes = User::where('rol_id', $rolCliente)->count();

        $totalPedidos = Pedido::count();
        $totalVentas = (float) Pedido::sum('total');

        $totalProductos = Producto::count();
        $stockCritico = ProductoTalla::with('producto')->where('stock', '<=', 2)->orderBy('stock')->get();
        $marcasFiltro = ['Nike', 'Jordan', 'Adidas', 'Puma', 'Umbro'];

        $totalTallas = ProductoTalla::count();
        $tallasConStock = ProductoTalla::where('stock', '>', 0)->count();
        $disponibilidad = $totalTallas > 0 ? round($tallasConStock / $totalTallas * 100, 2) : 0;

        $porCategoria = Categoria::withCount('productos')->get();
        $maxCategoria = max(1, (int) $porCategoria->max('productos_count'));
        $barras = $porCategoria->map(fn ($c) => [
            'nombre' => $c->nombre,
            'cantidad' => $c->productos_count,
            'altura' => round($c->productos_count / $maxCategoria * 100),
        ]);

        return view('admin.home', compact(
            'totalClientes', 'totalPedidos', 'totalVentas', 'totalProductos',
            'stockCritico', 'marcasFiltro', 'disponibilidad', 'tallasConStock', 'totalTallas', 'barras'
        ));
    }
}
