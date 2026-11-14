@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Resumen')

@section('contenido')
{{-- [FASE 2 - Paso 8]: Encabezado del dashboard --}}
<h1 class="text-2xl font-bold md:text-3xl">Resumen General</h1>
<p class="mt-1 text-sm kx-muted">Datos reales de la tienda en tiempo real.</p>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">

        {{-- [Actualizado: KPIs clicables] Cada tarjeta lleva a su módulo (Clientes, Pedidos, Productos) --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
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
                <p class="mt-5 text-sm kx-muted">Pedidos y Ventas</p>
                <div class="mt-1 flex items-end justify-between">
                    <p class="text-2xl font-bold">{{ $totalPedidos }}</p>
                    <span class="kx-badge-ok">S/ {{ number_format($totalVentas, 2) }}</span>
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

        {{-- [FASE 2 - Paso 10]: Gráfico de barras verticales, productos por categoría --}}
        <div class="kx-card p-6">
            <h2 class="text-lg font-bold">Productos por categoría</h2>
            <div class="mt-6 flex h-56 items-end justify-around gap-6 border-b border-zinc-800 px-4">
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
    </div>

    {{-- [FASE 2 - Paso 11]: Arco semicircular SVG con el porcentaje real de tallas con stock --}}
    <div class="kx-card p-6">
        <h2 class="text-lg font-bold">Disponibilidad de Stock</h2>
        <p class="text-sm kx-muted">Tallas con stock disponible</p>
        <div class="relative mx-auto mt-6 w-64">
            <svg viewBox="0 0 200 110" class="w-full">
                <path d="M 20 100 A 80 80 0 0 1 180 100" pathLength="100" fill="none" class="stroke-zinc-800" stroke-width="14" stroke-linecap="round"/>
                <path d="M 20 100 A 80 80 0 0 1 180 100" pathLength="100" fill="none" stroke="#10b981" stroke-width="14" stroke-linecap="round" stroke-dasharray="{{ $disponibilidad }} 100"/>
            </svg>
            <p class="absolute inset-x-0 bottom-2 text-center text-4xl font-bold">{{ $disponibilidad }}%</p>
        </div>
        <p class="mt-6 text-center text-sm kx-muted">{{ $tallasConStock }} de {{ $totalTallas }} tallas tienen stock.</p>
        <div class="mt-6 grid grid-cols-3 gap-2 border-t border-zinc-800 pt-5 text-center">
            <div><p class="text-xs kx-muted">Disponibles</p><p class="font-bold text-ok">{{ $tallasConStock }}</p></div>
            <div><p class="text-xs kx-muted">Críticas</p><p class="font-bold text-bajo">{{ $stockCritico->where('stock', '>', 0)->count() }}</p></div>
            <div><p class="text-xs kx-muted">Agotadas</p><p class="font-bold text-agotado">{{ $totalTallas - $tallasConStock }}</p></div>
        </div>
    </div>
</div>

{{-- [FASE 3 - Paso 7]: Alertas de inventario con pestañas por marca (filtran al instante sin recargar) --}}
<div id="alertas" class="kx-card mt-6 p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <h2 class="text-lg font-bold">Alertas de Inventario Crítico</h2>
        <div class="flex flex-wrap gap-1 rounded-xl border border-zinc-800 bg-[#09090b] p-1">
            <button type="button" class="kx-tab kx-tab-activa" data-filtro-marca="todas">Todas</button>
            @foreach ($marcasFiltro as $marcaFiltro)
                <button type="button" class="kx-tab" data-filtro-marca="{{ $marcaFiltro }}">{{ $marcaFiltro }}</button>
            @endforeach
        </div>
    </div>

    {{-- [FASE 2 - Paso 12]: Tabla sin columna ID (Regla 4.4); marca en badge y modelo sin la marca repetida --}}
    <div class="mt-4 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="kx-th">Producto</th>
                    <th class="kx-th">Marca</th>
                    <th class="kx-th">Talla</th>
                    <th class="kx-th">Stock</th>
                    <th class="kx-th">Estado</th>
                    <th class="kx-th text-right">Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stockCritico as $talla)
                    <tr class="kx-row" data-marca="{{ $talla->producto->marca }}">
                        <td class="kx-td">
                            <div class="flex items-center gap-3">
                                <div class="kx-miniatura">
                                    <img src="{{ str_starts_with((string) $talla->producto->imagen, 'http') ? $talla->producto->imagen : asset($talla->producto->imagen) }}" alt="{{ $talla->producto->modelo }}" loading="lazy" decoding="async" class="h-full w-full rounded-lg object-cover">
                                </div>
                                <a href="{{ route('admin.productos.edit', $talla->producto) }}" class="font-medium hover:text-indigo-400">{{ $talla->producto->modelo }}</a>
                            </div>
                        </td>
                        <td class="kx-td"><span class="kx-badge-neutro">{{ $talla->producto->marca }}</span></td>
                        <td class="kx-td">{{ rtrim(rtrim(number_format($talla->talla, 1), '0'), '.') }}</td>
                        <td class="kx-td font-semibold">{{ $talla->stock }}</td>
                        <td class="kx-td">
                            @if ($talla->stock === 0)
                                <span class="kx-badge-agotado">Agotado</span>
                            @else
                                <span class="kx-badge-bajo">Stock bajo</span>
                            @endif
                        </td>
                        <td class="kx-td text-right">
                            <a href="{{ route('admin.productos.edit', $talla->producto) }}" class="kx-btn-ghost whitespace-nowrap px-3 py-1.5"><i class="fa-solid fa-boxes-stacked"></i>Reponer / Editar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p id="alertas-vacio" class="{{ $stockCritico->isEmpty() ? '' : 'hidden' }} py-6 text-center kx-muted">Sin alertas de stock crítico para esta marca.</p>
    </div>
</div>
@endsection
