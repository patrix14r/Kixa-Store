<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// [Semana 9-10: Controladores, Request y Componentes] Gestión de pedidos: listado con filtros y cambio de estado con PATCH.
class PedidoController extends Controller
{
    public const ESTADOS = ['Pendiente', 'Pagado', 'Enviado', 'Entregado', 'Cancelado'];

    // Lista pedidos con with() para traer cliente e items en pocas consultas; filtra por estado y búsqueda.
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar'));
        $estado = $request->query('estado');

        $pedidos = Pedido::with(['usuario', 'items.producto'])
            ->when(in_array($estado, self::ESTADOS, true), fn ($q) => $q->where('estado', $estado))
            ->when($buscar, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('codigo_orden', 'like', "%{$buscar}%")
                ->orWhereHas('usuario', fn ($u) => $u->where('name', 'like', "%{$buscar}%"))))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $conteos = Pedido::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $estados = self::ESTADOS;

        $ingresos = (float) Pedido::where('estado', '!=', 'Cancelado')->sum('total');
        $resumen = [
            ['etiqueta' => 'Total de pedidos', 'valor' => $conteos->sum(), 'icono' => 'fa-receipt', 'color' => 'text-indigo-400'],
            ['etiqueta' => 'Por despachar', 'valor' => ($conteos['Pendiente'] ?? 0) + ($conteos['Pagado'] ?? 0), 'icono' => 'fa-box', 'color' => 'text-amber-400'],
            ['etiqueta' => 'En camino', 'valor' => $conteos['Enviado'] ?? 0, 'icono' => 'fa-truck-fast', 'color' => 'text-indigo-400'],
            ['etiqueta' => 'Entregados', 'valor' => $conteos['Entregado'] ?? 0, 'icono' => 'fa-circle-check', 'color' => 'text-emerald-400'],
            ['etiqueta' => 'Ingresos totales', 'valor' => 'S/ ' . number_format($ingresos, 2), 'icono' => 'fa-sack-dollar', 'color' => 'text-emerald-400'],
        ];

        return view('admin.pedidos.index', compact('pedidos', 'conteos', 'estados', 'buscar', 'estado', 'resumen'));
    }

    // Valida que el estado esté en ESTADOS y lo guarda; el cliente lo ve en su historial.
    public function estado(Request $request, Pedido $pedido)
    {
        $datos = $request->validate(['estado' => ['required', Rule::in(self::ESTADOS)]]);

        $pedido->update($datos);

        return back()->with('exito', "Pedido {$pedido->codigo_orden} marcado como {$pedido->estado}.");
    }
}
