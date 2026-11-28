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

// Proceso secundario: abastecimiento por lotes
class AbastecimientoController extends Controller
{
    // Límites de filas e inversión por lote
    private const MAX_FILAS = 30;
    private const INVERSION_MAXIMA = 99999999.99;

    // Lista lotes, KPI y tallas urgentes
    public function index()
    {
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

    // Formulario del lote con filas dinámicas
    public function create(Request $request)
    {
        // Productos activos con sus tallas
        $productos = Producto::activos()
            ->with(['tallas' => fn ($q) => $q->orderBy('talla')])
            ->orderBy('marca')
            ->orderBy('nombre')
            ->get();

        // Mapa SKU con precio y tallas
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

    // Guarda el lote como Pendiente
    public function store(Request $request)
    {
        $datos = $request->validate([
            'proveedor_id'              => ['required', Rule::exists('proveedores', 'id')->where('activo', true)],
            'items'                     => ['required', 'array', 'min:1', 'max:' . self::MAX_FILAS],
            // Talla sin repetir y de producto activo
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

        // Subtotal por fila e inversión total
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

        // Guarda encabezado y detalles juntos
        $orden = DB::transaction(function () use ($datos, $filas, $inversion, $request) {
            $orden = OrdenAbastecimiento::create([
                'codigo_lote'  => $this->siguienteCodigo(),
                'proveedor_id' => $datos['proveedor_id'],
                'user_id'      => $request->user()->id,
                'costo_total'  => $inversion,
                'estado'       => 'Pendiente',
            ]);

            $orden->detalles()->createMany($filas->all());

            return $orden;
        });

        return redirect()->route('admin.abastecimiento.index')
            ->with('exito', "Lote {$orden->codigo_lote} registrado con {$filas->count()} " . ($filas->count() === 1 ? 'talla' : 'tallas') . '.');
    }

    // Suma el stock al confirmar recepción
    public function recibir(OrdenAbastecimiento $orden)
    {
        $unidades = DB::transaction(function () use ($orden) {
            // Bloquea el lote para evitar doble recepción
            $orden = OrdenAbastecimiento::with('detalles')->lockForUpdate()->findOrFail($orden->id);

            if ($orden->estado !== 'Pendiente') {
                return null;
            }

            foreach ($orden->detalles as $detalle) {
                // Bloquea cada talla antes de sumar
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

    // Cancela solo lotes pendientes
    public function cancelar(OrdenAbastecimiento $orden)
    {
        // Bloquea el lote mientras se cancela
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

    // Genera la guía PDF del lote
    public function guia(Request $request, OrdenAbastecimiento $orden)
    {
        $orden->load(['proveedor', 'usuario', 'detalles.talla.producto']);

        $empresa = BoletaController::EMPRESA;
        $logo = public_path('images/ui/web.jpg');

        // Arma el PDF desde la vista Blade
        $pdf = Pdf::setOption(['isFontSubsettingEnabled' => true])
            ->loadView('comprobantes.orden-abastecimiento', compact('orden', 'empresa', 'logo'))
            ->setPaper('a4');

        $archivo = "Orden-{$orden->codigo_lote}.pdf";

        // Descarga o vista previa en el navegador
        return $request->boolean('descargar') ? $pdf->download($archivo) : $pdf->stream($archivo);
    }

    // Filas iniciales del formulario
    private function filasIniciales(Request $request, Collection $productos, array $catalogo): array
    {
        $anteriores = $request->old('items');

        if (is_array($anteriores) && $anteriores !== []) {
            // Descarta valores manipulados
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

    // Arma una fila del formulario
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

    // Texto de la talla con su stock
    private function textoTalla(ProductoTalla $talla): string
    {
        $aviso = match (true) {
            $talla->stock === 0 => ' (Agotado)',
            $talla->stock <= ProductoTalla::STOCK_CRITICO => ' (Crítico)',
            default => '',
        };

        return "Talla {$talla->talla_etiqueta} — Stock: {$talla->stock}{$aviso}";
    }

    // Genera LOT-0001, LOT-0002...
    private function siguienteCodigo(): string
    {
        $ultimo = OrdenAbastecimiento::latest('id')->value('codigo_lote');
        $numero = $ultimo ? ((int) substr($ultimo, 4)) + 1 : 1;

        return sprintf('LOT-%04d', $numero);
    }
}
