@props([
    'id' => 'alerta',
    'tipo' => 'alerta', // 'alerta' (modal confirmación) o 'toast' (notificación flotante)
    'titulo' => '¡Pedido confirmado!',
    'mensaje' => '',
    'icono' => 'fa-solid fa-check',
    'botonTexto' => 'Volver a la tienda'
])

@if($tipo === 'toast')
    <div id="{{ $id }}" class="toast" style="display: none;">
        <i class="{{ $icono }}"></i>
        <span>{{ $mensaje ?: $slot }}</span>
    </div>
@else
        <div id="{{ $id }}" class="alerta" style="display: none;">
            <div class="alerta-contenido animacion-alerta">
                <div class="check-confirmacion">
                    <i class="{{ $icono }}"></i>
                </div>

                <h2>{{ $titulo }}</h2>

                <p id="mensaje-{{ $id }}">
                    {{ $mensaje ?: $slot }}
                </p>

                <button type="button" onclick="cerrarAlerta()" class="btn-alerta">
                    {{ $botonTexto }}
                </button>
            </div>
        </div>
    @endif
