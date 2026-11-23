<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoTalla;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;

// [Semana 9-10: Controladores, Request y Componentes] Dashboard del admin: calcula totales y alertas con Eloquent y la vista solo los muestra.
class AdminController extends Controller
{
    // Cuenta clientes, pedidos y ventas con count()/sum() y agrupa productos por categoría con withCount().
    public function home(Request $request)
    {
        $rolCliente = Rol::where('nombre', 'cliente')->value('id');
        $totalClientes = User::where('rol_id', $rolCliente)->count();

        $totalPedidos = Pedido::count();
        $totalVentas = (float) Pedido::sum('total');

        $totalProductos = Producto::count();
        $stockCritico = ProductoTalla::with('producto')->where('stock', '<=', 2)->orderBy('stock')->get();
        $marcasFiltro = ['Nike', 'Jordan', 'Adidas', 'Puma', 'Umbro'];

        // Alertas: las 8 más urgentes; ?marca= filtra en el servidor (solo marcas de la lista cerrada).
        $marcaActiva = in_array($request->query('marca'), $marcasFiltro, true) ? $request->query('marca') : null;
        $alertas = ProductoTalla::with('producto')
            ->where('stock', '<=', 2)
            ->when($marcaActiva, fn ($q) => $q->whereHas('producto', fn ($p) => $p->where('marca', $marcaActiva)))
            ->orderBy('stock')
            ->limit(8)
            ->get();

        $pedidosRecientes = Pedido::with('usuario')->latest()->limit(5)->get();

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

        // Ingresos de los últimos 7 días (sin contar cancelados) para el gráfico lineal de Chart.js.
        $pedidosSemana = Pedido::where('estado', '!=', 'Cancelado')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get()
            ->groupBy(fn ($pedido) => $pedido->created_at->toDateString());

        $grafico = collect(range(6, 0))->map(function ($atras) use ($pedidosSemana) {
            $dia = now()->subDays($atras);
            $delDia = $pedidosSemana->get($dia->toDateString(), collect());

            return [
                'dia' => $dia->format('d/m'),
                'ingresos' => round((float) $delDia->sum('total'), 2),
            ];
        })->values();

        return view('admin.home', compact(
            'totalClientes', 'totalPedidos', 'totalVentas', 'totalProductos',
            'stockCritico', 'marcasFiltro', 'marcaActiva', 'alertas', 'pedidosRecientes', 'disponibilidad', 'tallasConStock', 'totalTallas', 'barras', 'grafico'
        ));
    }
}
