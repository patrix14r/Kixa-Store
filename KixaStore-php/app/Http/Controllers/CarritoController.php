<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoTalla;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// Carrito, checkout y registro de pedidos
class CarritoController extends Controller
{
    // Métodos de pago del simulador
    public const METODOS_PAGO = ['Tarjeta', 'Contraentrega'];

    private const MENSAJE_ADMIN = 'La cuenta Administrador es solo de gestión y no puede realizar compras de cliente.';

    // Máximo de pares por talla
    public const MAX_POR_TALLA = 10;

    // Muestra la página del carrito
    public function index()
    {
        $reglaEnvio = $this->reglaEnvio();

        return view('cliente.carrito', compact('reglaEnvio'));
    }

    // Devuelve stock y precio reales del carrito
    public function validar(Request $request)
    {
        $datos = $request->validate([
            'items'      => ['present', 'array', 'max:30'],
            'items.*.id' => ['required', 'alpha_dash:ascii', 'max:40'],
        ]);

        $skus = collect($datos['items'])->pluck('id')->map(fn ($sku) => (string) $sku)->unique()->values();

        // Una sola consulta para todo el carrito
        $productos = Producto::activos()
            ->whereIn('sku', $skus)
            ->with(['tallas' => fn ($q) => $q->orderBy('talla')])
            ->get()
            ->keyBy('sku');

        return response()->json([
            'max_por_talla'    => self::MAX_POR_TALLA,
            'ultimas_unidades' => ProductoTalla::ULTIMAS_UNIDADES,
            // Si no existe, disponible = false
            'productos' => $skus->mapWithKeys(fn ($sku) => [$sku => $productos->has($sku) ? [
                'disponible' => true,
                'nombre'     => $productos[$sku]->nombre,
                'precio'     => (float) $productos[$sku]->precio,
                'tallas'     => $productos[$sku]->tallas
                    ->map(fn ($t) => ['talla' => $t->talla_etiqueta, 'stock' => $t->stock])
                    ->values(),
            ] : ['disponible' => false]]),
        ]);
    }

    // Prepara el checkout con token único
    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login', ['redirect' => 'checkout']))->with('error', 'Debes iniciar sesión para proceder al pago.');
        }

        if (Auth::user()->esAdmin()) {
            return redirect()->route('carrito')->with('error', self::MENSAJE_ADMIN);
        }

        $usuario = Auth::user();
        $reglaEnvio = $this->reglaEnvio();

        $tokenCompra = (string) Str::uuid();
        session(['checkout_token' => $tokenCompra]);

        return response()
            ->view('cliente.checkout', compact('usuario', 'tokenCompra', 'reglaEnvio'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    // Registra el pedido y descuenta stock
    public function procesar(Request $request)
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'exito' => false,
                    'mensaje' => 'Debes iniciar sesión para completar la compra.',
                    'redirect_url' => route('login'),
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Inicia sesión para finalizar tu compra.');
        }

        if (Auth::user()->esAdmin()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['exito' => false, 'mensaje' => self::MENSAJE_ADMIN], 403);
            }
            return redirect()->route('carrito')->with('error', self::MENSAJE_ADMIN);
        }

        // Quita espacios del número de tarjeta
        if ($request->filled('tarjeta_numero')) {
            $request->merge(['tarjeta_numero' => str_replace([' ', '-'], '', (string) $request->input('tarjeta_numero'))]);
        }

        // Valida datos de pago y del carrito
        $request->validate([
            'metodo_pago'      => ['required', Rule::in(self::METODOS_PAGO)],
            'tarjeta_numero'   => ['exclude_unless:metodo_pago,Tarjeta', 'bail', 'required', 'regex:/^[0-9]{13,19}$/',
                fn ($campo, $valor, $falla) => self::pasaLuhn($valor) || $falla('El número de tarjeta no es válido.')],
            'tarjeta_titular'  => ['exclude_unless:metodo_pago,Tarjeta', 'bail', 'required', 'string', 'min:3', 'max:100', "regex:/^[\pL\s.'-]+$/u"],
            'tarjeta_vencimiento' => ['exclude_unless:metodo_pago,Tarjeta', 'bail', 'required', 'regex:/^(0[1-9]|1[0-2])\/[0-9]{2}$/',
                fn ($campo, $valor, $falla) => self::tarjetaVigente($valor) || $falla('La tarjeta está vencida.')],
            'tarjeta_cvv'      => ['exclude_unless:metodo_pago,Tarjeta', 'bail', 'required', 'regex:/^[0-9]{3,4}$/'],
            'direccion_envio'  => ['nullable', 'string', 'max:255'],
            'dni'              => ['nullable', 'regex:/^[0-9]{8}$/'],
            'telefono'         => ['nullable', 'regex:/^[0-9]{9}$/'],
            'token_compra'     => ['required', 'string'],
            'items'            => ['required', 'array', 'min:1', 'max:30'],
            'items.*.id'       => ['required'],
            'items.*.talla'    => ['required', 'numeric', 'min:1'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:10'],
        ], [
            'metodo_pago.in'               => 'Elige Tarjeta o Contraentrega como método de pago.',
            'tarjeta_numero.required'      => 'Ingresa el número de tu tarjeta.',
            'tarjeta_numero.regex'         => 'El número de tarjeta debe tener entre 13 y 19 dígitos.',
            'tarjeta_titular.required'     => 'Ingresa el nombre del titular de la tarjeta.',
            'tarjeta_titular.regex'        => 'El titular solo puede contener letras y espacios.',
            'tarjeta_vencimiento.required' => 'Ingresa la fecha de vencimiento.',
            'tarjeta_vencimiento.regex'    => 'El vencimiento debe tener el formato MM/AA.',
            'tarjeta_cvv.required'         => 'Ingresa el CVV de tu tarjeta.',
            'tarjeta_cvv.regex'            => 'El CVV debe tener 3 o 4 dígitos.',
            'items.required'         => 'El carrito está vacío.',
            'items.*.cantidad.min'   => 'La cantidad mínima por producto es 1.',
            'items.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'token_compra.required'  => 'La sesión de pago expiró. Vuelve a abrir el checkout.',
        ]);

        if (! hash_equals((string) session()->pull('checkout_token'), (string) $request->input('token_compra'))) {
            return $this->rechazar($request, 'Esta compra ya fue procesada o la sesión de pago expiró. Revisa tu historial de pedidos.', 409);
        }

        Auth::user()->fill(array_filter($request->only(['dni', 'telefono'])))->save();

        $items = $request->input('items');

        try {
            $pedido = DB::transaction(function () use ($request, $items) {
                $subtotal = 0;
                $lineasProcesadas = [];
                // Pares reservados por talla en este carrito
                $reservadoPorTalla = [];

                foreach ($items as $item) {
                    $cantidad = (int) $item['cantidad'];
                    $talla    = (float) $item['talla'];

                    $prod = Producto::activos()
                        ->where(fn ($q) => $q->where('sku', $item['id'])->orWhere('id', is_numeric($item['id']) ? (int) $item['id'] : 0))
                        ->first();
                    if (! $prod) {
                        throw new \DomainException('Uno de los productos ya no está disponible.');
                    }

                    $tallaRecord = ProductoTalla::where('producto_id', $prod->id)->where('talla', $talla)->lockForUpdate()->first();
                    $yaReservado = $tallaRecord ? ($reservadoPorTalla[$tallaRecord->id] ?? 0) : 0;
                    // Máximo 10 pares por talla
                    if ($yaReservado + $cantidad > self::MAX_POR_TALLA) {
                        throw new \DomainException("Máximo " . self::MAX_POR_TALLA . " pares de {$prod->nombre} por talla.");
                    }
                    if (! $tallaRecord || $tallaRecord->stock < $yaReservado + $cantidad) {
                        throw new \DomainException("Stock insuficiente de {$prod->nombre} en talla " . rtrim(rtrim(number_format($talla, 1), '0'), '.') . '.');
                    }
                    $reservadoPorTalla[$tallaRecord->id] = $yaReservado + $cantidad;

                    $precio = (float) $prod->precio;
                    $lineaSubtotal = round($precio * $cantidad, 2);
                    $subtotal += $lineaSubtotal;

                    $lineasProcesadas[] = [
                        'producto_id'     => $prod->id,
                        'talla'           => $talla,
                        'cantidad'        => $cantidad,
                        'precio_unitario' => $precio,
                        'subtotal'        => $lineaSubtotal,
                        'talla_record'    => $tallaRecord,
                    ];
                }

                if ($subtotal <= 0) {
                    throw new \DomainException('No se puede generar un pedido en cero.');
                }

                // Envío gratis: primera compra o S/ 200
                $montoProductos = round($subtotal, 2);
                $envio = Pedido::calcularEnvio($montoProductos, Auth::user()->esPrimeraCompra());

                // Separa el IGV del total
                $total = round($montoProductos + $envio, 2);
                $igv = round($total * 0.18 / 1.18, 2);
                $subtotal = round($total - $igv, 2);

                $codigoOrden = 'PED-' . strtoupper(Str::random(6));

                // Tarjeta queda Pagado, contraentrega Pendiente
                $esTarjeta = $request->input('metodo_pago') === 'Tarjeta';

                $pedido = Pedido::create([
                    'user_id'         => Auth::id(),
                    'codigo_orden'    => $codigoOrden,
                    'subtotal'        => $subtotal,
                    'igv'             => $igv,
                    'costo_envio'     => $envio,
                    'total'           => $total,
                    'estado'          => $esTarjeta ? 'Pagado' : 'Pendiente',
                    'direccion_envio' => $request->input('direccion_envio') ?: 'Entrega estándar / Tienda Kixa',
                    'metodo_pago'     => $request->input('metodo_pago'),
                    // Solo se guardan los últimos 4 dígitos
                    'tarjeta_ultimos4' => $esTarjeta ? substr($request->input('tarjeta_numero'), -4) : null,
                ]);

                foreach ($lineasProcesadas as $linea) {
                    DetallePedido::create([
                        'pedido_id'       => $pedido->id,
                        'producto_id'     => $linea['producto_id'],
                        'talla'           => $linea['talla'],
                        'cantidad'        => $linea['cantidad'],
                        'precio_unitario' => $linea['precio_unitario'],
                        'subtotal'        => $linea['subtotal'],
                    ]);

                    $linea['talla_record']->decrement('stock', $linea['cantidad']);
                }

                return $pedido;
            });

            session()->forget('checkout_token');
            session()->flash('carrito_vaciado', true);
            session()->flash('exito', '¡Tu pedido ' . $pedido->codigo_orden . ' ha sido confirmado y registrado con éxito!');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'exito'        => true,
                    'mensaje'      => '¡Pedido ' . $pedido->codigo_orden . ' registrado con éxito!',
                    'codigo_orden' => $pedido->codigo_orden,
                    'envio'        => number_format($pedido->costo_envio, 2),
                    'total'        => number_format($pedido->total, 2),
                    'redirect_url' => route('perfil', 'pedidos'),
                ]);
            }

            return redirect()->route('perfil', 'pedidos')->with('exito', '¡Tu pedido #' . $pedido->codigo_orden . ' se procesó exitosamente!');

        } catch (\DomainException $e) {
            session(['checkout_token' => $request->input('token_compra')]);

            return $this->rechazar($request, $e->getMessage(), 422);
        } catch (\Throwable $e) {
            // Error al log, mensaje genérico al cliente
            report($e);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'exito'   => false,
                    'mensaje' => 'Ocurrió un error al procesar la compra. Inténtalo nuevamente.',
                ], 500);
            }

            return back()->withErrors(['error' => 'Ocurrió un error al procesar la compra. Inténtalo nuevamente.']);
        }
    }

    // Valida la tarjeta con algoritmo Luhn
    public static function pasaLuhn(string $numero): bool
    {
        if (! ctype_digit($numero)) {
            return false;
        }

        $suma = 0;
        foreach (array_reverse(str_split($numero)) as $posicion => $digito) {
            $digito = (int) $digito;
            if ($posicion % 2 === 1) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }
            $suma += $digito;
        }

        return $suma % 10 === 0;
    }

    // Revisa que la tarjeta no esté vencida
    public static function tarjetaVigente(string $vencimiento): bool
    {
        if (! preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $vencimiento, $partes)) {
            return false;
        }

        return Carbon::create(2000 + (int) $partes[2], (int) $partes[1], 1)->endOfMonth()->isFuture();
    }

    // Datos de la regla de envío para JS
    private function reglaEnvio(): array
    {
        return [
            'costo'          => Pedido::COSTO_ENVIO,
            'gratis_desde'   => Pedido::ENVIO_GRATIS_DESDE,
            'primera_compra' => Auth::check() ? Auth::user()->esPrimeraCompra() : null,
        ];
    }

    // Responde el rechazo en JSON o redirección
    private function rechazar(Request $request, string $mensaje, int $codigo)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['exito' => false, 'mensaje' => $mensaje, 'redirect_url' => route('perfil', 'pedidos')], $codigo);
        }

        return redirect()->route('perfil', 'pedidos')->with('error', $mensaje);
    }
}
