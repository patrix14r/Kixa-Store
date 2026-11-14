{{-- [Actualizado: Tarjetas estilo Nike] Recibe el modelo Producto: foto cuadrada uniforme + texto limpio alineado a la izquierda --}}
{{-- [Sem 08 - Rutas y Vistas Blade]: Componente <x-tarjeta-producto :producto="$p" /> reutilizado en home, catálogo y detalle --}}
@props(['producto'])

<article class="kx-card" data-marca="{{ strtolower($producto->marca) }}">
    <a href="{{ route('producto.detalle', $producto->sku) }}" class="kx-card-enlace" aria-label="Ver {{ $producto->nombre }}">
        <div class="kx-card-img">
            <img src="{{ str_starts_with($producto->imagen, 'http') ? $producto->imagen : asset($producto->imagen) }}" alt="{{ $producto->nombre }}" loading="lazy" decoding="async">
        </div>

        <div class="kx-card-info">
            @if ($producto->en_oferta)
                <span class="kx-card-etiqueta kx-card-etiqueta--oferta">Oferta</span>
            @elseif ($producto->destacado)
                <span class="kx-card-etiqueta">Destacado</span>
            @endif

            <h3 class="kx-card-nombre">{{ $producto->nombre }}</h3>
            <p class="kx-card-tipo">{{ $producto->tipo_calzado }} • {{ $producto->categoria?->nombre }}</p>

            <div class="kx-card-precio">
                <span>S/ {{ number_format((float) $producto->precio, 2) }}</span>
                @if ($producto->en_oferta)
                    <span class="kx-card-badge">En oferta</span>
                @endif
            </div>
        </div>
    </a>

    {{-- Botón de favoritos: conserva la clase y los data-* que usa el script del layout --}}
    <button
        type="button"
        class="btn-fav-card"
        title="Guardar en favoritos"
        aria-label="Guardar {{ $producto->nombre }} en favoritos"
        data-id="{{ $producto->sku }}"
        data-sku="{{ $producto->sku }}"
        data-nombre="{{ $producto->nombre }}"
        data-marca="{{ $producto->marca }}"
        data-precio="{{ $producto->precio }}"
        data-imagen="{{ str_starts_with($producto->imagen, 'http') ? $producto->imagen : asset($producto->imagen) }}">
        <i class="fa-regular fa-heart"></i>
    </button>
</article>
