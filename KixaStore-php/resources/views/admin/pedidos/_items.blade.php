{{-- Detalle de zapatillas compradas en un pedido (parcial reutilizado en Pedidos e Historial del cliente) --}}
<ul class="space-y-2">
    @foreach ($pedido->items as $item)
        <li class="flex items-center gap-3">
            <div class="kx-miniatura">
                @if ($item->producto)
                    <img src="{{ asset($item->producto->imagen) }}" alt="{{ $item->producto->nombre }}" loading="lazy" class="h-full w-full rounded-lg object-cover">
                @endif
            </div>
            <span class="min-w-0 flex-1">
                <span class="block truncate font-medium">{{ $item->producto?->nombre ?? 'Producto retirado' }}</span>
                <span class="block text-xs kx-muted">Talla {{ rtrim(rtrim(number_format($item->talla, 1), '0'), '.') }} · {{ $item->cantidad }} {{ $item->cantidad === 1 ? 'par' : 'pares' }}</span>
            </span>
            <span class="whitespace-nowrap text-sm">S/ {{ number_format($item->precio_unitario * $item->cantidad, 2) }}</span>
        </li>
    @endforeach
</ul>
