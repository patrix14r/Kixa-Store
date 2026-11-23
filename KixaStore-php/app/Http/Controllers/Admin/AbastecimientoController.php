<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrdenAbastecimiento;
use App\Models\ProductoTalla;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// [Sem 12-13 - Eloquent ORM y Route::resource]: proceso secundario de abastecimiento: solicitar un lote a un proveedor y recibirlo suma el stock.
class AbastecimientoController extends Controller
{
    // index (GET): órdenes (protagonista), 3 KPI con count() y las 5 tallas más urgentes para reponer.
    public function index()
    {
        $ordenes = OrdenAbastecimiento::with(['proveedor', 'talla.producto'])
            ->latest()
            ->paginate(12);

        $kpis = [
            'pendientes' => OrdenAbastecimiento::where('estado', 'Pendiente')->count(),
            'agotadas'   => ProductoTalla::where('stock', 0)->count(),
            'criticas'   => ProductoTalla::whereBetween('stock', [1, TallaController::STOCK_CRITICO])->count(),
        ];

        $urgentes = ProductoTalla::with('producto')
            ->where('stock', '<=', TallaController::STOCK_CRITICO)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return view('admin.abastecimiento.index', compact('ordenes', 'kpis', 'urgentes'));
    }

    // create (GET): <select> de proveedores activos y de producto + talla; ?producto={sku}&talla={n} preselecciona la talla (sin exponer IDs).
    public function create(Request $request)
    {
        $seleccionada = null;
        if ($request->filled('producto') && is_numeric($request->query('talla'))) {
            $seleccionada = ProductoTalla::where('talla', $request->query('talla'))
                ->whereHas('producto', fn ($q) => $q->where('sku', $request->query('producto')))
                ->value('id');
        }

        return view('admin.abastecimiento.create', [
            'proveedores' => Proveedor::activos()->orderBy('razon_social')->get(),
            'tallas' => ProductoTalla::with('producto')->get()->sortBy(fn ($t) => $t->producto->nombre . $t->talla),
            'codigoLote' => $this->siguienteCodigo(),
            'seleccionada' => $seleccionada,
        ]);
    }

    // store (POST): calcula costo_total, genera el código de lote y guarda con el admin logueado y estado Pendiente.
    public function store(Request $request)
    {
        $datos = $request->validate([
            'proveedor_id'        => ['required', 'exists:proveedores,id'],
            'producto_talla_id'   => ['required', 'exists:producto_tallas,id'],
            'cantidad_solicitada' => ['required', 'integer', 'min:1', 'max:100000'],
            'costo_unitario'      => ['required', 'numeric', 'min:0.01', 'max:99999999'],
        ]);

        abort_unless(Proveedor::activos()->whereKey($datos['proveedor_id'])->exists(), 422, 'El proveedor está inactivo.');

        $orden = OrdenAbastecimiento::create($datos + [
            'codigo_lote' => $this->siguienteCodigo(),
            'costo_total' => round($datos['cantidad_solicitada'] * $datos['costo_unitario'], 2),
            'user_id'     => $request->user()->id,
            'estado'      => 'Pendiente',
        ]);

        return redirect()->route('admin.abastecimiento.index')->with('exito', "Orden {$orden->codigo_lote} registrada.");
    }

    // recibir (PATCH): en una transacción marca la orden como Recibido y suma la cantidad al stock real de la talla.
    public function recibir(OrdenAbastecimiento $orden)
    {
        $recibida = DB::transaction(function () use ($orden) {
            // lockForUpdate evita recibir dos veces la misma orden si llegan dos clics a la vez.
            $orden = OrdenAbastecimiento::lockForUpdate()->findOrFail($orden->id);

            if ($orden->estado !== 'Pendiente') {
                return false;
            }

            $orden->update(['estado' => 'Recibido', 'fecha_recepcion' => now()]);
            $orden->talla()->increment('stock', $orden->cantidad_solicitada);

            return true;
        });

        return back()->with(
            $recibida ? 'exito' : 'error',
            $recibida ? "Orden {$orden->codigo_lote} recibida: stock actualizado." : 'Solo se pueden recibir órdenes pendientes.'
        );
    }

    // cancelar (PATCH): solo órdenes pendientes; no toca el stock.
    public function cancelar(OrdenAbastecimiento $orden)
    {
        if ($orden->estado !== 'Pendiente') {
            return back()->with('error', 'Solo se pueden cancelar órdenes pendientes.');
        }

        $orden->update(['estado' => 'Cancelado']);

        return back()->with('exito', "Orden {$orden->codigo_lote} cancelada.");
    }

    // Genera LOT-0001, LOT-0002... a partir del último código guardado.
    private function siguienteCodigo(): string
    {
        $ultimo = OrdenAbastecimiento::latest('id')->value('codigo_lote');
        $numero = $ultimo ? ((int) substr($ultimo, 4)) + 1 : 1;

        return sprintf('LOT-%04d', $numero);
    }
}
