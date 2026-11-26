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

// [Semana 9-10: Controladores, Request y Componentes] Carrito y compra: el carrito vive en el navegador y el servidor valida todo al procesar.
class CarritoController extends Controller
{
    // Métodos de pago del simulador: Tarjeta (se valida en el servidor) y Contraentrega (se paga al recibir).
    public const METODOS_PAGO = ['Tarjeta', 'Contraentrega'];

    private const MENSAJE_ADMIN = 'La cuenta Administrador es solo de gestión y no puede realizar compras de cliente.';

    public function index()
    {
        return view('cliente.carrito');
    }

    // Exige sesión (guest() recuerda la URL), bloquea al admin y genera un token de un solo uso contra envíos dobles.
    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login', ['redirect' => 'checkout']))->with('error', 'Debes iniciar sesión para proceder al pago.');
        }

        if (Auth::user()->esAdmin()) {
            return redirect()->route('carrito')->with('error', self::MENSAJE_ADMIN);
        }

        $usuario = Auth::user();

        $tokenCompra = (string) Str::uuid();
        session(['checkout_token' => $tokenCompra]);

        return response()
            ->view('cliente.checkout', compact('usuario', 'tokenCompra'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    // Registra el pedido y descuenta stock con DB::transaction; el precio se toma de la BD y lockForUpdate() evita vender el mismo stock dos veces.
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

        // El número de tarjeta llega con espacios ("4242 4242 ..."); se limpian antes de validar.
        if ($request->filled('tarjeta_numero')) {
            $request->merge(['tarjeta_numero' => str_replace([' ', '-'], '', (string) $request->input('tarjeta_numero'))]);
        }

        // [Sem 13 - CRUDs, Query Builder y Paginación]: validate() con exclude_unless (los campos de tarjeta solo se validan si el método es Tarjeta) y closures para Luhn y vencimiento.
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
                // Unidades ya pedidas por cada talla en este mismo carrito: si la misma talla llega en dos líneas, se suman antes de comparar con el stock.
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

                $total = round($subtotal, 2);
                $igv = round($total * 0.18 / 1.18, 2);
                $subtotal = round($total - $igv, 2);

                $codigoOrden = 'PED-' . strtoupper(Str::random(6));

                // Tarjeta aprobada = pedido Pagado; Contraentrega queda Pendiente hasta que el cliente pague al recibir.
                $esTarjeta = $request->input('metodo_pago') === 'Tarjeta';

                $pedido = Pedido::create([
                    'user_id'         => Auth::id(),
                    'codigo_orden'    => $codigoOrden,
                    'subtotal'        => $subtotal,
                    'igv'             => $igv,
                    'total'           => $total,
                    'estado'          => $esTarjeta ? 'Pagado' : 'Pendiente',
                    'direccion_envio' => $request->input('direccion_envio') ?: 'Entrega estándar / Tienda Kixa',
                    'metodo_pago'     => $request->input('metodo_pago'),
                    // Nunca se guarda el número completo ni el CVV: solo los últimos 4 dígitos para el historial.
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
                    'total'        => number_format($pedido->total, 2),
                    'redirect_url' => route('perfil', 'pedidos'),
                ]);
            }

            return redirect()->route('perfil', 'pedidos')->with('exito', '¡Tu pedido #' . $pedido->codigo_orden . ' se procesó exitosamente!');

        } catch (\DomainException $e) {
            session(['checkout_token' => $request->input('token_compra')]);

            return $this->rechazar($request, $e->getMessage(), 422);
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'exito'   => false,
                    'mensaje' => 'Error al procesar la compra: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['error' => 'Ocurrió un error al procesar la compra. Inténtalo nuevamente.']);
        }
    }

    // Algoritmo de Luhn: desde la derecha se duplica un dígito sí y uno no (restando 9 si pasa de 9); la suma debe ser múltiplo de 10.
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

    // La tarjeta "MM/AA" vale hasta el último día de ese mes.
    public static function tarjetaVigente(string $vencimiento): bool
    {
        if (! preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $vencimiento, $partes)) {
            return false;
        }

        return Carbon::create(2000 + (int) $partes[2], (int) $partes[1], 1)->endOfMonth()->isFuture();
    }

    private function rechazar(Request $request, string $mensaje, int $codigo)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['exito' => false, 'mensaje' => $mensaje, 'redirect_url' => route('perfil', 'pedidos')], $codigo);
        }

        return redirect()->route('perfil', 'pedidos')->with('error', $mensaje);
    }
}
