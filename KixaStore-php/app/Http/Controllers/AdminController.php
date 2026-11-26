<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoTalla;
use App\Models\User;
use Illuminate\Http\Request;

// [Sem 09 - Controladores y Middleware]: Dashboard del admin; el controlador calcula totales, gráficos y alertas y la vista solo los muestra.
class AdminController extends Controller
{
    // [Sem 12 - Eloquent ORM]: count()/sum(), withCount('productos') por marca y relaciones con with() para evitar consultas N+1.
    public function home(Request $request)
    {
        // Bloque superior: KPI de clientes y pedidos.
        // [Sem 11 - Migraciones, Seeders y Roles]: el scope role() de Spatie filtra por la tabla model_has_roles.
        $totalClientes = User::role('cliente')->count();
        $totalPedidos = Pedido::count();
        $pedidosPendientes = Pedido::where('estado', 'Pendiente')->count();

        // Gráfico de barras: productos por marca (CRUD 4), de la marca con más productos a la de menos.
        $productosPorMarca = Marca::activos()
            ->withCount('productos')
            ->orderByDesc('productos_count')
            ->get()
            ->map(fn ($marca) => ['marca' => $marca->nombre, 'cantidad' => $marca->productos_count])
            ->values();

        // Tarjeta "Disponibilidad de Stock": % de tallas con stock + 3 métricas compactas.
        $totalTallas = ProductoTalla::count();
        $tallasConStock = ProductoTalla::where('stock', '>', 0)->count();
        $tallasAgotadas = $totalTallas - $tallasConStock;
        $disponibilidad = $totalTallas > 0 ? round($tallasConStock / $totalTallas * 100, 2) : 0;
        $totalVentas = (float) Pedido::where('estado', '!=', 'Cancelado')->sum('total');
        $totalProductos = Producto::count();

        // Bloque medio: ingresos diarios de los últimos 30 días (sin cancelados); el JS muestra 7 o 30 días según la pestaña.
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

        // Bloque inferior izquierdo: las 5 alertas más urgentes; ?marca= filtra en el servidor (solo marcas de la lista cerrada).
        $marcasFiltro = ['Nike', 'Jordan', 'Adidas', 'Puma', 'Umbro'];
        $marcaActiva = in_array($request->query('marca'), $marcasFiltro, true) ? $request->query('marca') : null;
        $alertas = ProductoTalla::with('producto')
            ->where('stock', '<=', ProductoTalla::STOCK_CRITICO)
            ->when($marcaActiva, fn ($q) => $q->whereHas('producto', fn ($p) => $p->where('marca', $marcaActiva)))
            ->orderBy('stock')
            ->limit(5)
            ->get();

        // Bloque inferior derecho: 5 pedidos más recientes con su cliente y productos (para la miniatura del primero).
        $pedidosRecientes = Pedido::with(['usuario', 'items.producto'])->latest()->limit(5)->get();

        return view('admin.home', compact(
            'totalClientes', 'totalPedidos', 'pedidosPendientes', 'productosPorMarca',
            'disponibilidad', 'tallasConStock', 'totalTallas', 'tallasAgotadas', 'totalVentas', 'totalProductos',
            'grafico', 'marcasFiltro', 'marcaActiva', 'alertas', 'pedidosRecientes'
        ));
    }
}
