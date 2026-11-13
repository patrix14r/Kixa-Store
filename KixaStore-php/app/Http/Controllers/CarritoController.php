<?php

namespace App\Http\Controllers;

// [CAMBIO: IMPORTACIONES PARA CARRITOCONTROLLER Y PROCESAMIENTO DE COMPRAS]
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
    // [CAMBIO: VISTA DEL CARRITO DE COMPRAS]
    public function index()
    {
        return view('cliente.carrito');
    }

    // [CAMBIO: VISTA DE CHECKOUT PROTEGIDA PARA CLIENTES AUTENTICADOS]
    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para proceder al pago.');
        }
        return view('cliente.checkout');
    }

    // [CAMBIO: PROCESAR COMPRA Y REGISTRAR PEDIDO CON DETALLES Y DECREMENTO DE STOCK]
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

        $request->validate([
            'metodo_pago'      => ['required', 'string', 'max:50'],
            'direccion_envio'  => ['nullable', 'string', 'max:255'],
            'items'            => ['required'],
        ]);

        $items = $request->input('items');
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        if (empty($items) || !is_array($items)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'exito' => false,
                    'mensaje' => 'El carrito está vacío o el formato de productos es inválido.'
                ], 422);
            }
            return back()->withErrors(['items' => 'El carrito no contiene productos válidos.']);
        }

        try {
            $pedido = DB::transaction(function () use ($request, $items) {
                // Calcular totales
                $subtotal = 0;
                $lineasProcesadas = [];

                foreach ($items as $item) {
                    $productoId = $item['id'] ?? $item['producto_id'] ?? null;
                    $talla = $item['talla'] ?? 40;
                    $cantidad = max(1, intval($item['cantidad'] ?? 1));
                    $precio = floatval($item['precio'] ?? 0);

                    // [CAMBIO: BUSQUEDA ROBUSTA POR ID NUMERICO O SKU DEL PRODUCTO]
                    $prod = null;
                    if ($productoId) {
                        $prod = Producto::where('id', $productoId)
                            ->orWhere('sku', $productoId)
                            ->first();
                        if ($prod) {
                            $productoId = $prod->id;
                            $precio = floatval($prod->precio);
                        }
                    }

                    $lineaSubtotal = round($precio * $cantidad, 2);
                    $subtotal += $lineaSubtotal;

                    $lineasProcesadas[] = [
                        'producto_id'     => $productoId,
                        'talla'           => $talla,
                        'cantidad'        => $cantidad,
                        'precio_unitario' => $precio,
                        'subtotal'        => $lineaSubtotal,
                    ];
                }

                $igv = round($subtotal * 0.18, 2);
                $total = round($subtotal + $igv, 2);

                // [CAMBIO: GENERACION DE CODIGO DE ORDEN OFICIAL PED-XXXXXX]
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

                // [CAMBIO: INSERCION DE ITEMS EN ORDER_ITEMS Y DESCUENTO DE STOCK EN PRODUCTO_TALLAS]
                foreach ($lineasProcesadas as $linea) {
                    DetallePedido::create([
                        'pedido_id'       => $pedido->id,
                        'producto_id'     => $linea['producto_id'],
                        'talla'           => $linea['talla'],
                        'cantidad'        => $linea['cantidad'],
                        'precio_unitario' => $linea['precio_unitario'],
                        'subtotal'        => $linea['subtotal'],
                    ]);

                    // Descontar inventario de la talla si existe
                    if ($linea['producto_id']) {
                        $tallaRecord = ProductoTalla::where('producto_id', $linea['producto_id'])
                            ->where(function ($q) use ($linea) {
                                $q->where('talla', $linea['talla'])
                                  ->orWhere('talla', floatval($linea['talla']));
                            })
                            ->first();

                        if ($tallaRecord) {
                            $nuevoStock = max(0, $tallaRecord->stock - $linea['cantidad']);
                            $tallaRecord->update(['stock' => $nuevoStock]);
                        }
                    }
                }

                return $pedido;
            });

            // [CAMBIO: GUARDAR MENSAJE FLASH PARA MOSTRAR AL REDIRIGIR AL PERFIL]
            session()->flash('exito', '¡Tu pedido ' . $pedido->codigo_orden . ' ha sido confirmado y registrado con éxito!');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'exito'        => true,
                    'mensaje'      => '¡Pedido ' . $pedido->codigo_orden . ' registrado con éxito!',
                    'codigo_orden' => $pedido->codigo_orden,
                    'total'        => number_format($pedido->total, 2),
                    'redirect_url' => route('perfil'),
                ]);
            }

            return redirect()->route('perfil')->with('exito', '¡Tu pedido #' . $pedido->codigo_orden . ' se procesó exitosamente!');

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
}
