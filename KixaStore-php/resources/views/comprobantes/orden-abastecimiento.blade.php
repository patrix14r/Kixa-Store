<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    {{-- [Sem 08 - Rutas y Vistas Blade]: Guía de Abastecimiento / Orden de Compra que dompdf convierte en PDF (AbastecimientoController@guia).
         No hereda layouts porque dompdf no usa Vite ni Tailwind: reutiliza el CSS plano de la boleta (resources/css/boleta.css). --}}
    <title>Orden de compra {{ $orden->codigo_lote }}</title>
    <link rel="stylesheet" href="{{ resource_path('css/boleta.css') }}">
</head>
<body>
    {{-- Cabecera: datos de KixaStore a la izquierda y recuadro con el código de lote a la derecha --}}
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
                    <p class="recuadro-titulo">ORDEN DE COMPRA</p>
                    <p class="recuadro-codigo">{{ $orden->codigo_lote }}</p>
                </div>
            </td>
        </tr>
    </table>

    {{-- [Sem 12 - Eloquent ORM]: $orden->proveedor es la relación BelongsTo; se muestran nombres y RUC, nunca el ID. --}}
    <div class="seccion">
        <p class="seccion-titulo">Datos del proveedor</p>
        <table class="datos">
            <tr>
                <td class="etiqueta">Razón social</td>
                <td>{{ $orden->proveedor?->razon_social ?? '—' }}</td>
                <td class="etiqueta">RUC</td>
                <td>{{ $orden->proveedor?->ruc ?? '—' }}</td>
            </tr>
            <tr>
                <td class="etiqueta">Contacto</td>
                <td>{{ $orden->proveedor?->contacto_nombre ?: '—' }}</td>
                <td class="etiqueta">Teléfono</td>
                <td>{{ $orden->proveedor?->telefono ?: '—' }}</td>
            </tr>
        </table>
    </div>

    <div class="seccion">
        <p class="seccion-titulo">Datos del lote</p>
        <table class="datos">
            <tr>
                <td class="etiqueta">Fecha de emisión</td>
                <td>{{ $orden->created_at?->format('d/m/Y H:i') }}</td>
                <td class="etiqueta">Estado</td>
                <td>{{ $orden->estado }}{{ $orden->fecha_recepcion ? ' (' . $orden->fecha_recepcion->format('d/m/Y') . ')' : '' }}</td>
            </tr>
            <tr>
                <td class="etiqueta">Registrado por</td>
                <td colspan="3">{{ $orden->usuario?->name ?? 'Administrador' }}</td>
            </tr>
        </table>
    </div>

    {{-- Detalle: una fila por talla del lote; talla_etiqueta muestra 40 o 40.5 (medias tallas) --}}
    <div class="seccion">
        <p class="seccion-titulo">Detalle del pedido al proveedor</p>
        <table class="productos">
            <thead>
                <tr>
                    <th>Marca</th>
                    <th>Producto</th>
                    <th>SKU</th>
                    <th class="centro">Talla</th>
                    <th class="centro">Cant.</th>
                    <th class="num">Costo unit.</th>
                    <th class="num">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orden->detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->talla?->producto?->marca ?: '—' }}</td>
                        <td>{{ $detalle->talla?->producto?->modelo ?? 'Producto retirado' }}</td>
                        <td>{{ $detalle->talla?->producto?->sku ?? '—' }}</td>
                        <td class="centro">{{ $detalle->talla?->talla_etiqueta ?? '—' }}</td>
                        <td class="centro">{{ $detalle->cantidad }}</td>
                        <td class="num">S/ {{ number_format($detalle->costo_unitario, 2) }}</td>
                        <td class="num">S/ {{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Inversión = suma de subtotales (costo_total); ganancia estimada = (precio de venta − costo) × cantidad (accessor del modelo) --}}
        <table class="totales">
            <tr>
                <td>Pares en el lote</td>
                <td class="num">{{ $orden->unidades }}</td>
            </tr>
            <tr>
                <td>Ganancia estimada</td>
                <td class="num">S/ {{ number_format($orden->ganancia_estimada, 2) }}</td>
            </tr>
            <tr class="total">
                <td>Inversión total</td>
                <td class="num">S/ {{ number_format($orden->costo_total, 2) }}</td>
            </tr>
        </table>
    </div>

    <p class="pie">Documento interno de KixaStore para el proveedor · Generado por el sistema (proyecto académico, sin valor tributario).</p>
</body>
</html>
