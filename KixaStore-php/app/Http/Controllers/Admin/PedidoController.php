<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\ProductoTalla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// [Semana 9-10: Controladores, Request y Componentes] Gestión de pedidos: listado con filtros y cambio de estado con PATCH.
class PedidoController extends Controller
{
    public const ESTADOS = ['Pendiente', 'Pagado', 'Enviado', 'Entregado', 'Cancelado'];

    // Máquina de estados: a qué estados puede pasar cada uno. Entregado y Cancelado son finales.
    public const TRANSICIONES = [
        'Pendiente' => ['Pagado', 'Enviado', 'Cancelado'],
        'Pagado'    => ['Enviado', 'Cancelado'],
        'Enviado'   => ['Entregado'],
        'Entregado' => [],
        'Cancelado' => [],
    ];

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
        $transiciones = self::TRANSICIONES;

        $ingresos = (float) Pedido::where('estado', '!=', 'Cancelado')->sum('total');
        $resumen = [
            ['etiqueta' => 'Total de pedidos', 'valor' => $conteos->sum(), 'icono' => 'fa-receipt', 'color' => 'text-indigo-400'],
            ['etiqueta' => 'Por despachar', 'valor' => ($conteos['Pendiente'] ?? 0) + ($conteos['Pagado'] ?? 0), 'icono' => 'fa-box', 'color' => 'text-amber-400'],
            ['etiqueta' => 'En camino', 'valor' => $conteos['Enviado'] ?? 0, 'icono' => 'fa-truck-fast', 'color' => 'text-indigo-400'],
            ['etiqueta' => 'Entregados', 'valor' => $conteos['Entregado'] ?? 0, 'icono' => 'fa-circle-check', 'color' => 'text-emerald-400'],
            ['etiqueta' => 'Ingresos totales', 'valor' => 'S/ ' . number_format($ingresos, 2), 'icono' => 'fa-sack-dollar', 'color' => 'text-emerald-400'],
        ];

        return view('admin.pedidos.index', compact('pedidos', 'conteos', 'estados', 'transiciones', 'buscar', 'estado', 'resumen'));
    }

    // [Sem 09 - Controladores y Middleware]: cambia el estado respetando TRANSICIONES; al cancelar devuelve el stock.
    public function estado(Request $request, Pedido $pedido)
    {
        $datos = $request->validate(['estado' => ['required', Rule::in(self::ESTADOS)]]);
        $nuevo = $datos['estado'];

        // DB::transaction + lockForUpdate: bloquea el pedido para que dos admins no lo cancelen a la vez (stock duplicado).
        $error = DB::transaction(function () use ($pedido, $nuevo) {
            $bloqueado = Pedido::whereKey($pedido->id)->lockForUpdate()->first();

            if ($bloqueado->estado === $nuevo) {
                return null;
            }

            if (! in_array($nuevo, self::TRANSICIONES[$bloqueado->estado] ?? [], true)) {
                return "Un pedido {$bloqueado->estado} no puede pasar a {$nuevo}.";
            }

            if ($nuevo === 'Cancelado') {
                foreach ($bloqueado->items as $item) {
                    // [Sem 13 - CRUDs, Query Builder y Paginación]: increment() devuelve los pares a la talla exacta del pedido.
                    ProductoTalla::where('producto_id', $item->producto_id)
                        ->where('talla', $item->talla)
                        ->lockForUpdate()
                        ->increment('stock', $item->cantidad);
                }
            }

            $bloqueado->update(['estado' => $nuevo]);

            return null;
        });

        if ($error) {
            return back()->with('error', $error);
        }

        return back()->with('exito', "Pedido {$pedido->codigo_orden} marcado como {$nuevo}.");
    }
}
