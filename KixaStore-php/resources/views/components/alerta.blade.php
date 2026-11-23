{{-- Componente <x-alerta>: tipo "alerta" (modal de confirmación) o "toast" (aviso oculto que se muestra con JS) --}}
@props([
    'id' => 'alerta',
    'tipo' => 'alerta', // 'alerta' (modal confirmación) o 'toast' (notificación flotante)
    'titulo' => '¡Pedido confirmado!',
    'mensaje' => '',
    'icono' => 'fa-solid fa-check',
    'botonTexto' => 'Volver a la tienda'
])

@if($tipo === 'toast')
    <div id="{{ $id }}" style="display: none;">
        <i class="{{ $icono }}"></i>
        <span>{{ $mensaje ?: $slot }}</span>
    </div>
@else
    {{-- El JS lo muestra cambiando style.display a "flex" --}}
    <div id="{{ $id }}" class="fixed inset-0 z-2000 items-center justify-center bg-[rgba(10,10,14,0.35)] backdrop-blur-[10px]" style="display: none;">
        <div class="w-[min(420px,90vw)] rounded-2xl bg-white p-7 text-center text-tinta">
            <div class="mb-3 text-[28px] text-exito">
                <i class="{{ $icono }}"></i>
            </div>

            <h2 class="text-[1.5em] font-bold">{{ $titulo }}</h2>

            <p id="mensaje-{{ $id }}" class="mt-2.5 mb-[18px] text-suave">
                {{ $mensaje ?: $slot }}
            </p>

            <button type="button" onclick="cerrarAlerta()" class="flex w-full cursor-pointer items-center justify-center rounded-full border-none bg-tinta p-[18px] text-[16px] font-medium text-white transition-colors duration-200 hover:bg-[#333]">
                {{ $botonTexto }}
            </button>
        </div>
    </div>
@endif
