<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    {{-- [Sem 08 - Rutas y Vistas Blade]: vista que dompdf convierte en PDF; no hereda layouts porque no usa Vite ni Tailwind. --}}
    <title>Boleta {{ $pedido->codigo_orden }}</title>
    {{-- dompdf lee el CSS plano desde el disco (resources/css/boleta.css); así la vista no lleva bloques <style>. --}}
    <link rel="stylesheet" href="{{ resource_path('css/boleta.css') }}">
</head>
<body>
    {{-- Cabecera: datos del emisor a la izquierda y recuadro con el código de orden a la derecha --}}
    <table class="cabecera">
        <tr>
            <td class="col-logo"><img src="{{ $logo }}" alt="KixaStore" class="logo"></td>
            <td>
                <p class="empresa-nombre">{{ $empresa['nombre'] }}</p>
                <p class="empresa-dato">{{ $empresa['direccion'] }}</p>
                <p class="empresa-dato">{{ $empresa['web'] }}</p>
            </td>
            <td class="col-recuadro">
                <div class="recuadro">
                    <p class="recuadro-titulo">BOLETA DE VENTA</p>
                    <p class="recuadro-codigo">{{ $pedido->codigo_orden }}</p>
                </div>
            </td>
        </tr>
    </table>

    {{-- [Sem 12 - Eloquent ORM]: $pedido->usuario es la relación BelongsTo; ?-> evita error si el cliente ya no existe. --}}
    <div class="seccion">
        <p class="seccion-titulo">Datos del cliente</p>
        <table class="datos">
            <tr>
                <td class="etiqueta">Cliente</td>
                <td>{{ $pedido->usuario?->name ?? 'Cliente' }}</td>
                <td class="etiqueta">Fecha de emisión</td>
                <td>{{ $pedido->created_at?->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="etiqueta">DNI</td>
                <td>{{ $pedido->usuario?->dni ?: '—' }}</td>
                <td class="etiqueta">Correo</td>
                <td>{{ $pedido->usuario?->email ?? '—' }}</td>
            </tr>
            <tr>
                <td class="etiqueta">Dirección de envío</td>
                <td colspan="3">{{ $pedido->direccion_envio }}</td>
            </tr>
        </table>
    </div>

    {{-- Detalle: una fila por línea de order_items; talla_etiqueta muestra 40 o 40.5 --}}
    <div class="seccion">
        <p class="seccion-titulo">Detalle de la compra</p>
        <table class="productos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="centro">Talla</th>
                    <th class="centro">Cant.</th>
                    <th class="num">P. unitario</th>
                    <th class="num">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pedido->items as $linea)
                    <tr>
                        <td>{{ $linea->producto?->nombre ?? 'Producto no disponible' }}</td>
                        <td class="centro">{{ $linea->talla_etiqueta }}</td>
                        <td class="centro">{{ $linea->cantidad }}</td>
                        <td class="num">S/ {{ number_format($linea->precio_unitario, 2) }}</td>
                        <td class="num">S/ {{ number_format($linea->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Los precios ya incluyen IGV: el checkout guarda Op. Gravada (subtotal) e IGV 18% por separado; el envío (S/ 0 = gratis) forma parte del total --}}
        <table class="totales">
            <tr>
                <td>Envío</td>
                <td class="num">{{ (float) $pedido->costo_envio > 0 ? 'S/ ' . number_format($pedido->costo_envio, 2) : 'Gratis' }}</td>
            </tr>
            <tr>
                <td>Op. Gravada</td>
                <td class="num">S/ {{ number_format($pedido->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>IGV (18%)</td>
                <td class="num">S/ {{ number_format($pedido->igv, 2) }}</td>
            </tr>
            <tr class="total">
                <td>Total</td>
                <td class="num">S/ {{ number_format($pedido->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="seccion">
        <p class="seccion-titulo">Pago</p>
        <table class="datos">
            <tr>
                <td class="etiqueta">Método de pago</td>
                <td>{{ $pedido->metodo_pago_detalle }}</td>
                <td class="etiqueta">Estado</td>
                <td>{{ $pedido->estado }}</td>
            </tr>
        </table>
    </div>

    <p class="pie">Gracias por comprar en KixaStore · Comprobante generado por el sistema (proyecto académico, sin valor tributario).</p>
</body>
</html>
