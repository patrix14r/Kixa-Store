@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Resumen')

@section('contenido')
{{-- [FASE 2 - Paso 8]: Encabezado del dashboard --}}
<h1 class="text-2xl font-bold md:text-3xl">Resumen General</h1>
<p class="mt-1 text-sm kx-muted">Datos reales de la tienda en tiempo real.</p>

{{-- Fila 1: KPI principales (cada tarjeta lleva a su módulo) --}}
<div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
    <a href="{{ route('admin.clientes.index') }}" class="kx-card block p-5 transition hover:-translate-y-0.5 hover:border-indigo-500/50">
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"><i class="fa-solid fa-users"></i></span>
        <p class="mt-5 text-sm kx-muted">Clientes</p>
        <div class="mt-1 flex items-end justify-between">
            <p class="text-2xl font-bold">{{ $totalClientes }}</p>
            <span class="kx-badge-ok">Registrados</span>
        </div>
    </a>
    <a href="{{ route('admin.pedidos.index') }}" class="kx-card block p-5 transition hover:-translate-y-0.5 hover:border-indigo-500/50">
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"><i class="fa-solid fa-box"></i></span>
        <p class="mt-5 text-sm kx-muted">Pedidos</p>
        <div class="mt-1 flex items-end justify-between">
            <p class="text-2xl font-bold">{{ $totalPedidos }}</p>
            <span class="kx-badge-neutro">Totales</span>
        </div>
    </a>
    <a href="{{ route('admin.pedidos.index') }}" class="kx-card block p-5 transition hover:-translate-y-0.5 hover:border-indigo-500/50">
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"><i class="fa-solid fa-sack-dollar"></i></span>
        <p class="mt-5 text-sm kx-muted">Ventas</p>
        <div class="mt-1 flex items-end justify-between">
            <p class="text-2xl font-bold">S/ {{ number_format($totalVentas, 2) }}</p>
            <span class="kx-badge-ok">Acumulado</span>
        </div>
    </a>
    <a href="{{ route('admin.productos.index') }}" class="kx-card block p-5 transition hover:-translate-y-0.5 hover:border-indigo-500/50">
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"><i class="fa-solid fa-box-open"></i></span>
        <p class="mt-5 text-sm kx-muted">Total Productos</p>
        <div class="mt-1 flex items-end justify-between">
            <p class="text-2xl font-bold">{{ $totalProductos }}</p>
            <span class="{{ $stockCritico->count() > 0 ? 'kx-badge-bajo' : 'kx-badge-ok' }}">{{ $stockCritico->count() }} críticos</span>
        </div>
    </a>
</div>

{{-- Fila 2: dos columnas balanceadas --}}
<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    {{-- Izquierda: barras verticales, productos por categoría (ocupa toda la altura de la fila) --}}
    <div class="kx-card flex flex-col p-6">
        <h2 class="text-lg font-bold">Productos por categoría</h2>
        <div class="mt-6 flex min-h-56 flex-1 items-end justify-around gap-6 border-b border-zinc-800 px-4">
            @foreach ($barras as $barra)
                <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                    <span class="text-xs kx-muted">{{ $barra['cantidad'] }}</span>
                    <div class="w-10 rounded-t-lg bg-gradient-to-t from-indigo-500/50 to-indigo-500 shadow-[0_0_12px_rgba(99,102,241,0.35)]" style="height: {{ $barra['altura'] }}%"></div>
                </div>
            @endforeach
        </div>
        <div class="mt-2 flex justify-around gap-6 px-4 text-sm kx-muted">
            @foreach ($barras as $barra)
                <span class="flex-1 text-center">{{ $barra['nombre'] }}</span>
            @endforeach
        </div>
    </div>

    {{-- Derecha: disponibilidad de stock y pedidos recientes --}}
    <div class="space-y-6">
        <div class="kx-card p-6">
            <h2 class="text-lg font-bold">Disponibilidad de Stock</h2>
            <div class="mt-4 flex flex-col items-center gap-6 sm:flex-row">
                <div class="relative w-48 shrink-0">
                    <svg viewBox="0 0 200 110" class="w-full">
                        <path d="M 20 100 A 80 80 0 0 1 180 100" pathLength="100" fill="none" class="stroke-zinc-800" stroke-width="14" stroke-linecap="round"/>
                        <path d="M 20 100 A 80 80 0 0 1 180 100" pathLength="100" fill="none" stroke="#10b981" stroke-width="14" stroke-linecap="round" stroke-dasharray="{{ $disponibilidad }} 100"/>
                    </svg>
                    <p class="absolute inset-x-0 bottom-1 text-center text-3xl font-bold">{{ $disponibilidad }}%</p>
                </div>
                <div class="grid flex-1 grid-cols-3 gap-2 text-center">
                    <div><p class="text-xs kx-muted">Disponibles</p><p class="font-bold text-ok">{{ $tallasConStock }}</p></div>
                    <div><p class="text-xs kx-muted">Críticas</p><p class="font-bold text-bajo">{{ $stockCritico->where('stock', '>', 0)->count() }}</p></div>
                    <div><p class="text-xs kx-muted">Agotadas</p><p class="font-bold text-agotado">{{ $totalTallas - $tallasConStock }}</p></div>
                </div>
            </div>
            <p class="mt-3 text-center text-sm kx-muted sm:text-left">{{ $tallasConStock }} de {{ $totalTallas }} tallas tienen stock.</p>
        </div>

        <div class="kx-card p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold">Pedidos recientes</h2>
                <a href="{{ route('admin.pedidos.index') }}" class="text-sm text-indigo-400 hover:underline">Ver todos</a>
            </div>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr><th class="kx-th">Código</th><th class="kx-th">Cliente</th><th class="kx-th">Total</th><th class="kx-th">Estado</th></tr></thead>
                    <tbody>
                        @forelse ($pedidosRecientes as $pedido)
                            <tr class="kx-row">
                                <td class="kx-td font-semibold">{{ $pedido->codigo_orden }}</td>
                                <td class="kx-td">{{ $pedido->usuario->name }}</td>
                                <td class="kx-td">S/ {{ number_format($pedido->total, 2) }}</td>
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
</div>

{{-- Fila 3: ancho completo, ingresos con una sola serie (Chart.js; datos calculados en AdminController) --}}
<div class="kx-card col-span-full mt-6 p-6">
    <h2 class="text-lg font-bold">Ingresos de los últimos 7 días (S/)</h2>
    <p class="text-sm kx-muted">No incluye pedidos cancelados.</p>
    <div class="mt-4 h-72">
        <canvas id="graficoVentas" data-grafico='@json($grafico)'></canvas>
    </div>
</div>

{{-- Fila 4: alertas de inventario (8 más urgentes); el filtro por marca se aplica en el servidor --}}
<div id="alertas" class="kx-card mt-6 p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <h2 class="text-lg font-bold">Alertas de Inventario Crítico</h2>
        <div class="flex flex-wrap gap-1 rounded-xl border border-zinc-800 bg-[#09090b] p-1">
            <a href="{{ route('admin.home') }}#alertas" class="kx-tab {{ $marcaActiva ? '' : 'kx-tab-activa' }}">Todas</a>
            @foreach ($marcasFiltro as $marcaFiltro)
                <a href="{{ route('admin.home', ['marca' => $marcaFiltro]) }}#alertas" class="kx-tab {{ $marcaActiva === $marcaFiltro ? 'kx-tab-activa' : '' }}">{{ $marcaFiltro }}</a>
            @endforeach
        </div>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="kx-th">Producto</th><th class="kx-th">Marca</th><th class="kx-th">Talla</th>
                    <th class="kx-th">Stock</th><th class="kx-th">Estado</th><th class="kx-th text-right">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($alertas as $talla)
                    <tr class="kx-row">
                        <td class="kx-td">
                            <div class="flex items-center gap-3">
                                <img src="{{ $talla->producto->url_imagen }}" alt="{{ $talla->producto->modelo }}" loading="lazy" class="w-10 h-10 rounded-lg object-contain bg-zinc-900 p-1">
                                <a href="{{ route('admin.productos.edit', $talla->producto) }}" class="font-medium hover:text-indigo-400">{{ $talla->producto->modelo }}</a>
                            </div>
                        </td>
                        <td class="kx-td"><span class="kx-badge-neutro">{{ $talla->producto->marca }}</span></td>
                        <td class="kx-td">{{ $talla->talla_etiqueta }}</td>
                        <td class="kx-td font-semibold">{{ $talla->stock }}</td>
                        <td class="kx-td"><span class="{{ $talla->stock === 0 ? 'kx-badge-agotado' : 'kx-badge-bajo' }}">{{ $talla->stock === 0 ? 'Agotado' : 'Crítico' }}</span></td>
                        <td class="kx-td text-right">
                            <a href="{{ route('admin.abastecimiento.create', ['producto' => $talla->producto->sku, 'talla' => $talla->talla]) }}" class="kx-btn-ghost whitespace-nowrap px-3 py-1.5"><i class="fa-solid fa-boxes-stacked"></i>Reponer</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="kx-td py-6 text-center kx-muted">Sin alertas de stock crítico{{ $marcaActiva ? ' para ' . $marcaActiva : '' }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4 text-right">
        <a href="{{ route('admin.tallas.index', ['estado' => 'bajo']) }}" class="text-sm text-indigo-400 hover:underline">Ver todas en Tallas y Stock →</a>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/admin-grafico.js') }}"></script>
@endpush
