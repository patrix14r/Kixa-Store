<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoTalla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CarritoController extends Controller
{
    private const MENSAJE_ADMIN = 'La cuenta Administrador es solo de gestión y no puede realizar compras de cliente.';

    public function index()
    {
        return view('cliente.carrito');
    }

    public function checkout()
    {
        if (!Auth::check()) {
            // [Actualizado: Login → Checkout] guest() guarda url.intended para volver aquí tras iniciar sesión
            return redirect()->guest(route('login', ['redirect' => 'checkout']))->with('error', 'Debes iniciar sesión para proceder al pago.');
        }

        // [Sem 11 - Migraciones, Seeders y Roles]: El rol admin no puede comprar en la tienda pública
        if (Auth::user()->esAdmin()) {
            return redirect()->route('carrito')->with('error', self::MENSAJE_ADMIN);
        }

        // [Actualizado: Checkout] Datos del cliente para pre-llenar Nombre, DNI, Teléfono y Dirección
        $usuario = Auth::user();

        // [Actualizado: Blindaje] Token de un solo uso: si el usuario vuelve con "Atrás" tras comprar, no puede reenviar el pedido
        $tokenCompra = (string) Str::uuid();
        session(['checkout_token' => $tokenCompra]);

        return response()
            ->view('cliente.checkout', compact('usuario', 'tokenCompra'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

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

        // [Sem 11 - Migraciones, Seeders y Roles]: Bloqueo de compra para el Administrador (también vía AJAX)
        if (Auth::user()->esAdmin()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['exito' => false, 'mensaje' => self::MENSAJE_ADMIN], 403);
            }
            return redirect()->route('carrito')->with('error', self::MENSAJE_ADMIN);
        }

        // [Actualizado: Blindaje] Cantidades enteras de 1 a 10, talla obligatoria y al menos 1 producto
        $request->validate([
            'metodo_pago'      => ['required', 'string', 'max:50'],
            'direccion_envio'  => ['nullable', 'string', 'max:255'],
            'dni'              => ['nullable', 'regex:/^[0-9]{8}$/'],
            'telefono'         => ['nullable', 'regex:/^[0-9]{9}$/'],
            'token_compra'     => ['required', 'string'],
            'items'            => ['required', 'array', 'min:1', 'max:30'],
            'items.*.id'       => ['required'],
            'items.*.talla'    => ['required', 'numeric', 'min:1'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:10'],
        ], [
            'items.required'         => 'El carrito está vacío.',
            'items.*.cantidad.min'   => 'La cantidad mínima por producto es 1.',
            'items.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'token_compra.required'  => 'La sesión de pago expiró. Vuelve a abrir el checkout.',
        ]);

        // [Actualizado: Blindaje] El token se consume ANTES de crear el pedido: un segundo envío (doble clic o "Atrás") se rechaza
        if (! hash_equals((string) session()->pull('checkout_token'), (string) $request->input('token_compra'))) {
            return $this->rechazar($request, 'Esta compra ya fue procesada o la sesión de pago expiró. Revisa tu historial de pedidos.', 409);
        }

        // [Actualizado: Checkout] Se recuerdan DNI y teléfono para pre-llenar la próxima compra
        Auth::user()->fill(array_filter($request->only(['dni', 'telefono'])))->save();

        $items = $request->input('items');

        try {
            $pedido = DB::transaction(function () use ($request, $items) {
                // Calcular totales
                $subtotal = 0;
                $lineasProcesadas = [];

                foreach ($items as $item) {
                    $cantidad = (int) $item['cantidad'];
                    $talla    = (float) $item['talla'];

                    // [Sem 12 - Eloquent ORM]: El precio sale SIEMPRE de la BD (nunca del navegador)
                    $prod = Producto::activos()
                        ->where(fn ($q) => $q->where('sku', $item['id'])->orWhere('id', is_numeric($item['id']) ? (int) $item['id'] : 0))
                        ->first();
                    if (! $prod) {
                        throw new \DomainException('Uno de los productos ya no está disponible.');
                    }

                    // Bloqueo de fila para que dos compras simultáneas no vendan el mismo stock
                    $tallaRecord = ProductoTalla::where('producto_id', $prod->id)->where('talla', $talla)->lockForUpdate()->first();
                    if (! $tallaRecord || $tallaRecord->stock < $cantidad) {
                        throw new \DomainException("Stock insuficiente de {$prod->nombre} en talla " . rtrim(rtrim(number_format($talla, 1), '0'), '.') . '.');
                    }

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

                // [Actualizado: IGV] Los precios ya incluyen IGV: IGV = total * 0.18 / 1.18
                $total = round($subtotal, 2);
                $igv = round($total * 0.18 / 1.18, 2);
                $subtotal = round($total - $igv, 2);

                $codigoOrden = 'PED-' . strtoupper(Str::random(6));

                $pedido = Pedido::create([
                    'user_id'         => Auth::id(),
                    'codigo_orden'    => $codigoOrden,
                    'subtotal'        => $subtotal,
                    'igv'             => $igv,
                    'total'           => $total,
                    'estado'          => 'Pagado',
                    'direccion_envio' => $request->input('direccion_envio') ?: 'Entrega estándar / Tienda Kixa',
                    'metodo_pago'     => $request->input('metodo_pago'),
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

                    // Descontar inventario (ya validado y bloqueado arriba)
                    $linea['talla_record']->decrement('stock', $linea['cantidad']);
                }

                return $pedido;
            });

            // [Actualizado: Carrito limpio] El token ya se consumió; se marca en sesión para que la vista vacíe el carrito local
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
            // Stock o producto inválido: se devuelve el token para que pueda corregir el carrito y reintentar
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

    private function rechazar(Request $request, string $mensaje, int $codigo)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['exito' => false, 'mensaje' => $mensaje, 'redirect_url' => route('perfil', 'pedidos')], $codigo);
        }

        return redirect()->route('perfil', 'pedidos')->with('error', $mensaje);
    }
}
