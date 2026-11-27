<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BoletaController;
use App\Http\Controllers\Controller;
use App\Models\OrdenAbastecimiento;
use App\Models\Producto;
use App\Models\ProductoTalla;
use App\Models\Proveedor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// [Sem 12-13 - Eloquent ORM y Route::resource]: proceso secundario de abastecimiento: un lote agrupa varias tallas de un proveedor
// y al confirmar su recepción se suma el stock de todas ellas.
// [Sem 09 - Controladores y Middleware]: flujo Entrada -> Proceso -> Salida:
//   Entrada = create/store (el admin registra el lote Pendiente con sus tallas, cantidades y costos),
//   Proceso = recibir (suma atómica del stock con DB::transaction + lockForUpdate),
//   Salida  = guia (Guía / Orden de Compra en PDF para el proveedor) e index (inversión y ganancia estimada).
class AbastecimientoController extends Controller
{
    // Máximo de filas por lote y tope de inversión (decimal(10,2) de costo_total admite hasta 99 999 999.99).
    private const MAX_FILAS = 30;
    private const INVERSION_MAXIMA = 99999999.99;

    // index (GET): lotes con inversión y ganancia estimada, 4 KPI y las 5 tallas más urgentes para reponer.
    public function index()
    {
        // [Sem 12 - Eloquent ORM]: with() trae proveedor, filas, talla y producto en pocas consultas (evita N+1 al calcular la ganancia).
        $ordenes = OrdenAbastecimiento::with(['proveedor', 'detalles.talla.producto'])
            ->latest()
            ->paginate(12);

        $kpis = [
            'pendientes' => OrdenAbastecimiento::where('estado', 'Pendiente')->count(),
            'inversion_pendiente' => (float) OrdenAbastecimiento::where('estado', 'Pendiente')->sum('costo_total'),
            'agotadas'   => ProductoTalla::where('stock', 0)->count(),
            'criticas'   => ProductoTalla::whereBetween('stock', [1, ProductoTalla::STOCK_CRITICO])->count(),
        ];

        $urgentes = ProductoTalla::with('producto')
            ->where('stock', '<=', ProductoTalla::STOCK_CRITICO)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return view('admin.abastecimiento.index', compact('ordenes', 'kpis', 'urgentes'));
    }

    // create (GET): tabla dinámica de filas Producto -> Talla + cantidad + costo. ?producto={sku}&talla={n} (botón "+ Reponer")
    // llega con la primera fila preseleccionada sin exponer IDs en la URL.
    public function create(Request $request)
    {
        // Solo productos activos; with('tallas') trae sus tallas en una sola consulta extra (evita N+1).
        // [Sem 13 - CRUDs, Query Builder y Paginación]: orderBy marca y luego nombre agrupa los modelos de cada marca en el <select>.
        $productos = Producto::activos()
            ->with(['tallas' => fn ($q) => $q->orderBy('talla')])
            ->orderBy('marca')
            ->orderBy('nombre')
            ->get();

        // Mapa SKU => precio de venta y tallas ("Talla 43 — Stock: 0 (Agotado)"); app.js lo usa para los selects encadenados
        // y para calcular en vivo la inversión y la ganancia estimada.
        $catalogo = $productos->mapWithKeys(fn ($p) => [$p->sku => [
            'precio' => (float) $p->precio,
            'tallas' => $p->tallas->map(fn ($t) => ['id' => $t->id, 'texto' => $this->textoTalla($t)])->values(),
        ]]);

        return view('admin.abastecimiento.create', [
            'proveedores' => Proveedor::activos()->orderBy('razon_social')->get(),
            'productos'   => $productos,
            'catalogo'    => $catalogo,
            'filas'       => $this->filasIniciales($request, $productos, $catalogo->all()),
            'filaVacia'   => $this->fila(),
            'codigoLote'  => $this->siguienteCodigo(),
        ]);
    }

    // store (POST): valida todas las filas, calcula subtotales e inversión y guarda el lote Pendiente con sus detalles.
    public function store(Request $request)
    {
        $datos = $request->validate([
            // Proveedor obligatorio y activo: Rule::exists con where() rechaza proveedores dados de baja.
            'proveedor_id'              => ['required', Rule::exists('proveedores', 'id')->where('activo', true)],
            'items'                     => ['required', 'array', 'min:1', 'max:' . self::MAX_FILAS],
            // distinct: la misma talla (producto_talla_id) no puede repetirse en el lote, aunque se salte la validación de app.js;
            // el closure solo acepta tallas de productos activos.
            'items.*.producto_talla_id' => ['required', 'distinct', Rule::exists('producto_tallas', 'id')
                ->where(fn ($q) => $q->whereIn('producto_id', Producto::activos()->select('id')))],
            'items.*.cantidad'          => ['required', 'integer', 'min:1', 'max:10000'],
            'items.*.costo_unitario'    => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
        ], [
            'proveedor_id.required'              => 'Selecciona el proveedor del lote.',
            'proveedor_id.exists'                => 'El proveedor no existe o está inactivo.',
            'items.required'                     => 'Agrega al menos una talla al lote.',
            'items.max'                          => 'Un lote admite como máximo ' . self::MAX_FILAS . ' filas.',
            'items.*.producto_talla_id.required' => 'Elige el producto y la talla.',
            'items.*.producto_talla_id.distinct' => 'Esta talla ya está en el lote: suma las cantidades en una sola fila.',
            'items.*.producto_talla_id.exists'   => 'La talla no pertenece a un producto activo.',
            'items.*.cantidad.min'               => 'La cantidad mínima es 1.',
            'items.*.cantidad.max'               => 'La cantidad máxima por fila es 10 000.',
            'items.*.costo_unitario.min'         => 'El costo debe ser mayor a S/ 0.00.',
        ]);

        // Subtotal de cada fila = cantidad × costo unitario; la inversión del lote es la suma de las filas.
        $filas = collect($datos['items'])->map(fn ($item) => [
            'producto_talla_id' => (int) $item['producto_talla_id'],
            'cantidad'          => (int) $item['cantidad'],
            'costo_unitario'    => round((float) $item['costo_unitario'], 2),
            'subtotal'          => round((int) $item['cantidad'] * (float) $item['costo_unitario'], 2),
        ])->values();

        $inversion = round($filas->sum('subtotal'), 2);
        if ($inversion > self::INVERSION_MAXIMA) {
            throw ValidationException::withMessages(['items' => 'La inversión del lote supera el máximo permitido (S/ 99 999 999.99).']);
        }

        // [Sem 09 - Controladores y Middleware]: encabezado y detalles se guardan juntos o no se guarda nada.
        $orden = DB::transaction(function () use ($datos, $filas, $inversion, $request) {
            $orden = OrdenAbastecimiento::create([
                'codigo_lote'  => $this->siguienteCodigo(),
                'proveedor_id' => $datos['proveedor_id'],
                'user_id'      => $request->user()->id,
                'costo_total'  => $inversion,
                'estado'       => 'Pendiente',
            ]);

            // [Sem 12 - Eloquent ORM]: createMany() inserta todas las filas por la relación HasMany (llena orden_abastecimiento_id solo).
            $orden->detalles()->createMany($filas->all());

            return $orden;
        });

        return redirect()->route('admin.abastecimiento.index')
            ->with('exito', "Lote {$orden->codigo_lote} registrado con {$filas->count()} " . ($filas->count() === 1 ? 'talla' : 'tallas') . '.');
    }

    // recibir (PATCH) "Confirmar recepción": en una transacción marca el lote como Recibido y suma el stock de TODAS sus tallas.
    public function recibir(OrdenAbastecimiento $orden)
    {
        // [Sem 09 - Controladores y Middleware]: suma atómica: DB::transaction() confirma todo o revierte todo si algo falla,
        // y lockForUpdate() bloquea las filas hasta el commit para que dos peticiones no sumen el mismo lote a la vez.
        $unidades = DB::transaction(function () use ($orden) {
            // lockForUpdate evita recibir dos veces el mismo lote si llegan dos clics a la vez.
            $orden = OrdenAbastecimiento::with('detalles')->lockForUpdate()->findOrFail($orden->id);

            if ($orden->estado !== 'Pendiente') {
                return null;
            }

            foreach ($orden->detalles as $detalle) {
                // Cada talla también se bloquea antes de sumarle, por si en ese instante se está vendiendo en el checkout.
                ProductoTalla::whereKey($detalle->producto_talla_id)->lockForUpdate()->firstOrFail()
                    ->increment('stock', $detalle->cantidad);
            }

            $orden->update(['estado' => 'Recibido', 'fecha_recepcion' => now()]);

            return $orden->unidades;
        });

        return back()->with(
            $unidades !== null ? 'exito' : 'error',
            $unidades !== null
                ? "Lote {$orden->codigo_lote} recibido: se sumaron {$unidades} pares al stock."
                : 'Solo se pueden recibir órdenes pendientes.'
        );
    }

    // cancelar (PATCH): solo órdenes pendientes; no toca el stock.
    public function cancelar(OrdenAbastecimiento $orden)
    {
        // [Sem 09 - Controladores y Middleware]: DB::transaction + lockForUpdate evita cancelar una orden que otro admin está recibiendo.
        $cancelada = DB::transaction(function () use ($orden) {
            $orden = OrdenAbastecimiento::lockForUpdate()->findOrFail($orden->id);

            if ($orden->estado !== 'Pendiente') {
                return false;
            }

            $orden->update(['estado' => 'Cancelado']);

            return true;
        });

        return back()->with(
            $cancelada ? 'exito' : 'error',
            $cancelada ? "Orden {$orden->codigo_lote} cancelada." : 'Solo se pueden cancelar órdenes pendientes.'
        );
    }

    // guia (GET): Guía de Abastecimiento / Orden de Compra en PDF. {orden} llega por código de lote (LOT-0001), nunca por ID.
    // Sin parámetros abre la vista previa en el navegador; con ?descargar=1 baja el archivo.
    public function guia(Request $request, OrdenAbastecimiento $orden)
    {
        // [Sem 12 - Eloquent ORM]: load() trae proveedor, admin y cada talla con su producto en pocas consultas (evita N+1 en la tabla).
        $orden->load(['proveedor', 'usuario', 'detalles.talla.producto']);

        $empresa = BoletaController::EMPRESA;
        $logo = public_path('images/ui/web.jpg');

        // [Sem 09 - Controladores y Middleware]: generación de la Guía PDF: loadView() renderiza la vista Blade y dompdf la
        // convierte a PDF; isFontSubsettingEnabled embebe solo las letras usadas para que el archivo pese poco.
        $pdf = Pdf::setOption(['isFontSubsettingEnabled' => true])
            ->loadView('comprobantes.orden-abastecimiento', compact('orden', 'empresa', 'logo'))
            ->setPaper('a4');

        $archivo = "Orden-{$orden->codigo_lote}.pdf";

        // download() envía "attachment" (descarga) y stream() envía "inline" (vista previa en una pestaña nueva).
        return $request->boolean('descargar') ? $pdf->download($archivo) : $pdf->stream($archivo);
    }

    // Filas con las que abre el formulario: las de old() si falló la validación, la del enlace "+ Reponer" o una vacía.
    // Las claves se conservan para que @error('items.{clave}.cantidad') muestre el mensaje en su fila.
    private function filasIniciales(Request $request, Collection $productos, array $catalogo): array
    {
        $anteriores = $request->old('items');

        if (is_array($anteriores) && $anteriores !== []) {
            // is_scalar() descarta valores manipulados (arreglos) antes de volver a pintarlos en el formulario.
            $valor = fn ($item, $clave) => is_array($item) && is_scalar($item[$clave] ?? null) ? (string) $item[$clave] : null;

            return collect($anteriores)->map(fn ($item) => $this->fila(
                $valor($item, 'producto'),
                is_numeric($valor($item, 'producto_talla_id')) ? (int) $valor($item, 'producto_talla_id') : null,
                $valor($item, 'cantidad'),
                $valor($item, 'costo_unitario'),
                $catalogo,
            ))->all();
        }

        $sku = $productos->firstWhere('sku', $request->query('producto'))?->sku;
        $tallaId = $sku && is_numeric($request->query('talla'))
            ? $productos->firstWhere('sku', $sku)->tallas->first(fn ($t) => (float) $t->talla === (float) $request->query('talla'))?->id
            : null;

        return [0 => $this->fila($sku, $tallaId, null, null, $catalogo)];
    }

    // Arma una fila del formulario con las tallas del producto elegido (vacía si aún no hay producto).
    private function fila(?string $sku = null, ?int $tallaId = null, $cantidad = null, $costo = null, array $catalogo = []): array
    {
        return [
            'producto'          => $sku,
            'producto_talla_id' => $tallaId,
            'cantidad'          => $cantidad,
            'costo_unitario'    => $costo,
            'tallas'            => $sku ? ($catalogo[$sku]['tallas'] ?? collect()) : collect(),
        ];
    }

    // Texto de cada opción del <select> de talla, con aviso de agotado o crítico.
    private function textoTalla(ProductoTalla $talla): string
    {
        $aviso = match (true) {
            $talla->stock === 0 => ' (Agotado)',
            $talla->stock <= ProductoTalla::STOCK_CRITICO => ' (Crítico)',
            default => '',
        };

        return "Talla {$talla->talla_etiqueta} — Stock: {$talla->stock}{$aviso}";
    }

    // Genera LOT-0001, LOT-0002... a partir del último código guardado.
    private function siguienteCodigo(): string
    {
        $ultimo = OrdenAbastecimiento::latest('id')->value('codigo_lote');
        $numero = $ultimo ? ((int) substr($ultimo, 4)) + 1 : 1;

        return sprintf('LOT-%04d', $numero);
    }
}
