{{-- Recibe el modelo Producto: foto cuadrada uniforme + texto limpio alineado a la izquierda --}}
{{-- Componente <x-tarjeta-producto :producto="$p" /> reutilizado en home, catálogo y detalle --}}
{{-- "group" permite que la foto haga zoom (group-hover) cuando el mouse pasa por cualquier parte de la tarjeta --}}
@props(['producto'])

<article class="group relative text-tinta transition-[translate] duration-300 hover:-translate-y-1" data-marca="{{ strtolower($producto->marca) }}">
    <a href="{{ route('producto.detalle', $producto->sku) }}" class="block" aria-label="Ver {{ $producto->nombre }}">
        <div class="aspect-square overflow-hidden rounded-xl bg-perla p-5">
            <img src="{{ str_starts_with($producto->imagen, 'http') ? $producto->imagen : asset($producto->imagen) }}" alt="{{ $producto->nombre }}" loading="lazy" decoding="async"
                 class="block size-full object-contain mix-blend-multiply transition-[scale] duration-500 group-hover:scale-104">
        </div>

        <div class="px-0.5 pt-3.5 text-left">
            @if ($producto->en_oferta)
                <span class="mb-1 block text-[13px] font-medium text-exito">Oferta</span>
            @elseif ($producto->destacado)
                <span class="mb-1 block text-[13px] font-medium text-[#d97706]">Destacado</span>
            @endif

            <h3 class="text-[15px] leading-[1.35] font-semibold tracking-[-0.01em] text-tinta max-escritorio:text-[14px]">{{ $producto->nombre }}</h3>
            <p class="mt-0.5 truncate text-[14px] text-suave max-escritorio:text-[13px]">{{ $producto->tipo_calzado }} • {{ $producto->categoria?->nombre }}</p>

            <div class="mt-2 flex flex-wrap items-center gap-2 text-[15px] font-medium text-tinta max-escritorio:text-[14px]">
                <span>S/ {{ number_format((float) $producto->precio, 2) }}</span>
                @if ($producto->en_oferta)
                    <span class="rounded-full bg-[rgba(16,185,129,0.12)] px-2 py-0.5 text-[12px] font-semibold text-exito">En oferta</span>
                @endif
            </div>
        </div>
    </a>

    {{-- Botón de favoritos: conserva la clase .btn-fav-card y los data-* que usa el script del layout --}}
    <button
        type="button"
        class="btn-fav-card absolute top-3 right-3 z-5 flex size-9 cursor-pointer items-center justify-center rounded-full border-none bg-white text-[15px] text-tinta shadow-[0_1px_4px_rgba(0,0,0,0.08)] transition-[scale] duration-200 hover:scale-108 max-[480px]:top-2 max-[480px]:right-2 max-[480px]:size-7 max-[480px]:text-[13px]"
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
