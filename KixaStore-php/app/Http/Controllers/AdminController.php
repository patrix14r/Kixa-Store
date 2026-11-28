<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoTalla;
use App\Models\User;
use Illuminate\Http\Request;

// Dashboard del panel admin
class AdminController extends Controller
{
    // Calcula métricas, gráficos y alertas
    public function home(Request $request)
    {
        // Totales de clientes y pedidos
        $totalClientes = User::role('cliente')->count();
        $totalPedidos = Pedido::count();
        $pedidosPendientes = Pedido::where('estado', 'Pendiente')->count();

        // Productos por marca para el gráfico
        $productosPorMarca = Marca::activos()
            ->withCount('productos')
            ->orderByDesc('productos_count')
            ->get()
            ->map(fn ($marca) => ['marca' => $marca->nombre, 'cantidad' => $marca->productos_count])
            ->values();

        // Disponibilidad de stock por talla
        $totalTallas = ProductoTalla::count();
        $tallasConStock = ProductoTalla::where('stock', '>', 0)->count();
        $tallasAgotadas = $totalTallas - $tallasConStock;
        $disponibilidad = $totalTallas > 0 ? round($tallasConStock / $totalTallas * 100, 2) : 0;
        $totalVentas = (float) Pedido::where('estado', '!=', 'Cancelado')->sum('total');
        $totalProductos = Producto::count();

        // Ingresos de los últimos 30 días
        $pedidosMes = Pedido::where('estado', '!=', 'Cancelado')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->get()
            ->groupBy(fn ($pedido) => $pedido->created_at->toDateString());

        $grafico = collect(range(29, 0))->map(function ($atras) use ($pedidosMes) {
            $dia = now()->subDays($atras);

            return [
                'dia' => $dia->format('d/m'),
                'ingresos' => round((float) $pedidosMes->get($dia->toDateString(), collect())->sum('total'), 2),
            ];
        })->values();

        // Las 5 alertas de stock más urgentes
        $marcasFiltro = ['Nike', 'Jordan', 'Adidas', 'Puma', 'Umbro'];
        $marcaActiva = in_array($request->query('marca'), $marcasFiltro, true) ? $request->query('marca') : null;
        $alertas = ProductoTalla::with('producto')
            ->where('stock', '<=', ProductoTalla::STOCK_CRITICO)
            ->when($marcaActiva, fn ($q) => $q->whereHas('producto', fn ($p) => $p->where('marca', $marcaActiva)))
            ->orderBy('stock')
            ->limit(5)
            ->get();

        // Últimos 5 pedidos
        $pedidosRecientes = Pedido::with(['usuario', 'items.producto'])->latest()->limit(5)->get();

        return view('admin.home', compact(
            'totalClientes', 'totalPedidos', 'pedidosPendientes', 'productosPorMarca',
            'disponibilidad', 'tallasConStock', 'totalTallas', 'tallasAgotadas', 'totalVentas', 'totalProductos',
            'grafico', 'marcasFiltro', 'marcaActiva', 'alertas', 'pedidosRecientes'
        ));
    }
}
