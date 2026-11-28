@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Resumen')

@section('contenido')
<h1 class="text-2xl font-bold md:text-3xl">Resumen General</h1>
<p class="mt-1 text-sm kx-muted">Datos reales de la tienda en tiempo real.</p>

{{-- KPI, marcas y disponibilidad --}}
<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-12">
    <div class="flex flex-col gap-6 xl:col-span-7">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <a href="{{ route('admin.clientes.index') }}" class="kx-card block p-5 transition hover:border-indigo-500/50">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/10 text-indigo-400"><i class="fa-solid fa-users"></i></span>
                <p class="mt-5 text-sm kx-muted">Clientes</p>
                <div class="mt-1 flex items-end justify-between">
                    <p class="text-2xl font-bold">{{ $totalClientes }}</p>
                    <span class="kx-badge-ok"><i class="fa-solid fa-user-check"></i>Registrados</span>
                </div>
            </a>
            <a href="{{ route('admin.pedidos.index') }}" class="kx-card block p-5 transition hover:border-indigo-500/50">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/10 text-indigo-400"><i class="fa-solid fa-box"></i></span>
                <p class="mt-5 text-sm kx-muted">Pedidos</p>
                <div class="mt-1 flex items-end justify-between">
                    <p class="text-2xl font-bold">{{ $totalPedidos }}</p>
                    <span class="{{ $pedidosPendientes > 0 ? 'kx-badge-bajo' : 'kx-badge-ok' }}"><i class="fa-solid fa-clock"></i>{{ $pedidosPendientes }} pendientes</span>
                </div>
            </a>
        </div>

        {{-- Gráfico de productos por marca --}}
        <div class="kx-card flex flex-1 flex-col p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold">Productos por Marca</h2>
                <a href="{{ route('admin.marcas.index') }}" class="text-sm text-indigo-400 hover:underline">Ver marcas</a>
            </div>
            <div class="relative mt-4 min-h-56 flex-1">
                <canvas id="graficoMarcas" data-marcas='@json($productosPorMarca)'></canvas>
            </div>
        </div>
    </div>

    {{-- Disponibilidad de stock --}}
    <div class="kx-card flex flex-col overflow-hidden xl:col-span-5">
        <div class="flex flex-1 flex-col rounded-b-2xl border-b border-zinc-800/80 bg-[#121215] p-6">
            <h2 class="text-lg font-bold">Disponibilidad de Stock</h2>
            <p class="text-sm kx-muted">Porcentaje de tallas con al menos 1 par.</p>
            <div class="flex flex-1 flex-col items-center justify-center py-6">
                <div class="relative w-full max-w-72">
                    <svg viewBox="0 0 200 110" class="w-full">
                        <defs>
                            <linearGradient id="kxGauge" x1="0" x2="1" y1="0" y2="0">
                                <stop offset="0%" stop-color="#818cf8" stop-opacity="0.55"/>
                                <stop offset="100%" stop-color="#6366f1"/>
                            </linearGradient>
                        </defs>
                        <path d="M 20 100 A 80 80 0 0 1 180 100" pathLength="100" fill="none" class="stroke-zinc-800" stroke-width="12" stroke-linecap="round"/>
                        <path d="M 20 100 A 80 80 0 0 1 180 100" pathLength="100" fill="none" stroke="url(#kxGauge)" stroke-width="12" stroke-linecap="round" stroke-dasharray="{{ $disponibilidad }} 100"/>
                    </svg>
                    <p class="absolute inset-x-0 bottom-2 text-center text-4xl font-bold tracking-tight">{{ $disponibilidad }}%</p>
                </div>
                <span class="kx-badge-neutro mt-3">{{ $tallasConStock }} / {{ $totalTallas }} tallas</span>
                <p class="mt-4 max-w-xs text-center text-sm kx-muted">
                    @if ($tallasAgotadas > 0)
                        Hay {{ $tallasAgotadas }} tallas agotadas que conviene reponer.
                    @else
                        ¡Todas las tallas tienen stock!
                    @endif
                </p>
            </div>
        </div>
        <div class="grid grid-cols-3 divide-x divide-zinc-800/80 bg-[#09090b]/60 py-5 text-center">
            <div>
                <p class="text-xs kx-muted">Ventas (S/)</p>
                <p class="mt-1 font-bold">{{ number_format($totalVentas, 2) }}</p>
            </div>
            <div>
                <p class="text-xs kx-muted">Productos</p>
                <p class="mt-1 font-bold">{{ $totalProductos }}</p>
            </div>
            <div>
                <p class="text-xs kx-muted">Agotadas</p>
                <p class="mt-1 font-bold {{ $tallasAgotadas > 0 ? 'text-agotado' : '' }}">{{ $tallasAgotadas }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Estadísticas de ingresos --}}
<div class="kx-card mt-6 p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Estadísticas de Ingresos (S/)</h2>
            <p class="text-sm kx-muted">Ingresos diarios sin contar pedidos cancelados.</p>
        </div>
        <div class="flex gap-1 rounded-xl border border-zinc-800 bg-[#09090b] p-1">
            <button type="button" class="kx-tab kx-tab-activa" data-rango="7">7 días</button>
            <button type="button" class="kx-tab" data-rango="30">30 días</button>
        </div>
    </div>
    <div class="relative mt-4 h-72">
        <canvas id="graficoVentas" data-grafico='@json($grafico)'></canvas>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-12">
    {{-- Alertas de stock --}}
    <div id="alertas" class="kx-card flex flex-col p-6 xl:col-span-5">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold">Alertas de Stock Crítico</h2>
            <a href="{{ route('admin.productos.index', ['stock' => 'critico']) }}" class="text-sm text-indigo-400 hover:underline">Ver todo</a>
        </div>
        <div class="mt-3 flex flex-wrap gap-1">
            <a href="{{ route('admin.home') }}#alertas" class="kx-tab px-2.5 py-1 text-xs {{ $marcaActiva ? '' : 'kx-tab-activa' }}">Todas</a>
            @foreach ($marcasFiltro as $marcaFiltro)
                <a href="{{ route('admin.home', ['marca' => $marcaFiltro]) }}#alertas" class="kx-tab px-2.5 py-1 text-xs {{ $marcaActiva === $marcaFiltro ? 'kx-tab-activa' : '' }}">{{ $marcaFiltro }}</a>
            @endforeach
        </div>

        <ul class="mt-4 divide-y divide-zinc-800/60">
            @forelse ($alertas as $talla)
                <li class="flex items-center gap-3 py-3">
                    <img src="{{ $talla->producto->url_imagen }}" alt="{{ $talla->producto->modelo }}" loading="lazy" class="h-11 w-11 shrink-0 rounded-lg bg-zinc-900 object-contain p-1">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.productos.edit', $talla->producto) }}" class="block truncate text-sm font-medium hover:text-indigo-400">{{ $talla->producto->modelo }}</a>
                        <p class="text-xs kx-muted">{{ $talla->producto->marca }} · Talla {{ $talla->talla_etiqueta }}</p>
                    </div>
                    <span class="kx-badge-agotado shrink-0">{{ $talla->stock === 0 ? 'Agotado' : 'Stock ' . $talla->stock }}</span>
                    <a href="{{ route('admin.abastecimiento.create', ['producto' => $talla->producto->sku, 'talla' => $talla->talla]) }}" class="kx-btn-ghost shrink-0 whitespace-nowrap px-2.5 py-1.5 text-xs">+ Reponer</a>
                </li>
            @empty
                <li class="py-6 text-center text-sm kx-muted">Sin alertas de stock crítico{{ $marcaActiva ? ' para ' . $marcaActiva : '' }}.</li>
            @endforelse
        </ul>
    </div>

    {{-- Pedidos recientes --}}
    <div class="kx-card p-6 xl:col-span-7">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold">Pedidos recientes</h2>
            <a href="{{ route('admin.pedidos.index') }}" class="kx-btn-ghost px-3 py-1.5 text-xs">Ver todos</a>
        </div>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr><th class="kx-th">Pedido</th><th class="kx-th">Cliente</th><th class="kx-th">Total</th><th class="kx-th">Estado</th></tr></thead>
                <tbody>
                    @forelse ($pedidosRecientes as $pedido)
                        <tr class="kx-row">
                            <td class="kx-td">
                                <div class="flex items-center gap-3">
                                    @if ($primerProducto = $pedido->items->first()?->producto)
                                        <img src="{{ $primerProducto->url_imagen }}" alt="{{ $primerProducto->modelo }}" loading="lazy" class="h-11 w-11 shrink-0 rounded-lg bg-zinc-900 object-contain p-1">
                                    @else
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-zinc-900 text-zinc-500"><i class="fa-solid fa-box"></i></span>
                                    @endif
                                    <div>
                                        <p class="font-semibold">{{ $pedido->codigo_orden }}</p>
                                        <p class="text-xs kx-muted">{{ $pedido->items->count() }} {{ $pedido->items->count() === 1 ? 'producto' : 'productos' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="kx-td">{{ $pedido->usuario?->name ?? 'Cliente' }}</td>
                            <td class="kx-td whitespace-nowrap">S/ {{ number_format($pedido->total, 2) }}</td>
                            <td class="kx-td"><x-admin.estado-pedido :estado="$pedido->estado" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="kx-td py-6 text-center kx-muted">Aún no hay pedidos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/admin-grafico.js') }}"></script>
@endpush
