@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Abastecimiento')

@section('contenido')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Abastecimiento</h1>
        <p class="text-sm kx-muted">Lotes de reposición a proveedores: cada lote agrupa varias tallas y al confirmar su recepción suma el stock.</p>
    </div>
    <a href="{{ route('admin.abastecimiento.create') }}" class="kx-btn-primary"><i class="fa-solid fa-plus"></i>Nuevo lote</a>
</div>

{{-- 4 tarjetas KPI compactas --}}
<div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="kx-card flex items-center gap-4 p-4">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/10 text-indigo-400"><i class="fa-solid fa-truck-ramp-box"></i></span>
        <div><p class="text-xs kx-muted">Lotes pendientes</p><p class="text-2xl font-bold">{{ $kpis['pendientes'] }}</p></div>
    </div>
    <div class="kx-card flex items-center gap-4 p-4">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-400"><i class="fa-solid fa-sack-dollar"></i></span>
        <div><p class="text-xs kx-muted">Inversión por recibir</p><p class="text-2xl font-bold">S/ {{ number_format($kpis['inversion_pendiente'], 2) }}</p></div>
    </div>
    <div class="kx-card flex items-center gap-4 p-4">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-red-500/20 bg-red-500/10 text-red-400"><i class="fa-solid fa-ban"></i></span>
        <div><p class="text-xs kx-muted">Tallas agotadas</p><p class="text-2xl font-bold">{{ $kpis['agotadas'] }}</p></div>
    </div>
    <div class="kx-card flex items-center gap-4 p-4">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-400"><i class="fa-solid fa-triangle-exclamation"></i></span>
        <div><p class="text-xs kx-muted">Tallas con stock crítico</p><p class="text-2xl font-bold">{{ $kpis['criticas'] }}</p></div>
    </div>
</div>

<div class="kx-card mt-6 overflow-x-auto">
    <h2 class="px-5 pt-5 text-lg font-bold">Lotes de abastecimiento</h2>
    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="kx-th">Lote</th>
                <th class="kx-th">Proveedor</th>
                <th class="kx-th">Productos / Tallas</th>
                <th class="kx-th">Pares</th>
                <th class="kx-th">Inversión</th>
                <th class="kx-th">Ganancia est.</th>
                <th class="kx-th">Estado</th>
                <th class="kx-th text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ordenes as $orden)
                <tr class="kx-row align-top">
                    <td class="kx-td font-semibold">{{ $orden->codigo_lote }}</td>
                    <td class="kx-td">{{ $orden->proveedor->razon_social }}</td>
                    {{-- Una línea por fila del lote: producto · talla × cantidad (accessor talla_etiqueta para medias tallas) --}}
                    <td class="kx-td">
                        <ul class="space-y-0.5">
                            @foreach ($orden->detalles as $detalle)
                                <li>{{ $detalle->talla?->producto?->nombre ?? 'Producto retirado' }} <span class="kx-muted">· talla {{ $detalle->talla?->talla_etiqueta }} × {{ $detalle->cantidad }}</span></li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="kx-td">{{ $orden->unidades }}</td>
                    <td class="kx-td whitespace-nowrap">S/ {{ number_format($orden->costo_total, 2) }}</td>
                    <td class="kx-td whitespace-nowrap {{ $orden->ganancia_estimada >= 0 ? 'text-emerald-400' : 'text-amber-400' }}">S/ {{ number_format($orden->ganancia_estimada, 2) }}</td>
                    <td class="kx-td">
                        <span class="{{ match ($orden->estado) { 'Recibido' => 'kx-badge-ok', 'Cancelado' => 'kx-badge-agotado', default => 'kx-badge-bajo' } }}">{{ $orden->estado }}</span>
                    </td>
                    <td class="kx-td">
                        {{-- Guía de Abastecimiento / Orden de Compra en PDF: "Ver guía" abre la vista previa en otra pestaña y "Descargar" baja el archivo --}}
                        <div class="mb-2 flex justify-end gap-2">
                            <a href="{{ route('admin.abastecimiento.guia', $orden) }}" target="_blank" rel="noopener" class="kx-btn-ghost whitespace-nowrap px-3 py-1.5"><i class="fa-solid fa-file-pdf"></i>Ver guía</a>
                            <a href="{{ route('admin.abastecimiento.guia', ['orden' => $orden, 'descargar' => 1]) }}" class="kx-btn-ghost whitespace-nowrap px-3 py-1.5"><i class="fa-solid fa-download"></i>Descargar</a>
                        </div>
                        @if ($orden->estado === 'Pendiente')
                            <div class="flex justify-end gap-2">
                                {{-- data-confirmar: app.js pide confirmación antes de sumar el stock de todas las tallas del lote --}}
                                <form action="{{ route('admin.abastecimiento.recibir', $orden) }}" method="POST" data-confirmar="¿Llegó el lote {{ $orden->codigo_lote }} completo? Se sumarán {{ $orden->unidades }} pares al stock.">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="kx-btn-primary whitespace-nowrap px-3 py-1.5"><i class="fa-solid fa-box-open"></i>Confirmar recepción</button>
                                </form>
                                <form action="{{ route('admin.abastecimiento.cancelar', $orden) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="kx-btn-ghost px-3 py-1.5 text-red-400"><i class="fa-solid fa-ban"></i>Cancelar</button>
                                </form>
                            </div>
                        @else
                            <p class="text-right text-xs kx-muted">{{ $orden->fecha_recepcion ? 'Recibido el ' . $orden->fecha_recepcion->format('d/m/Y') : '—' }}</p>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="kx-td py-8 text-center kx-muted">Aún no hay lotes de abastecimiento.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $ordenes->links() }}</div>

{{-- Mini-tabla "Más urgentes": máximo 5 filas, con enlace directo a crear la orden de esa talla --}}
@if ($urgentes->isNotEmpty())
    <div class="kx-card mt-6 overflow-x-auto">
        <div class="flex items-center justify-between px-5 pt-5">
            <h2 class="text-lg font-bold">Más urgentes</h2>
            <a href="{{ route('admin.productos.index', ['stock' => 'critico']) }}" class="text-sm text-indigo-400 hover:underline">Ver productos con stock crítico</a>
        </div>
        <table class="mt-3 w-full text-sm">
            <thead><tr><th class="kx-th">Producto</th><th class="kx-th">Talla</th><th class="kx-th">Stock</th><th class="kx-th text-right">Acción</th></tr></thead>
            <tbody>
                @foreach ($urgentes as $talla)
                    <tr class="kx-row">
                        <td class="kx-td font-medium">{{ $talla->producto->nombre }}</td>
                        <td class="kx-td">{{ $talla->talla_etiqueta }}</td>
                        <td class="kx-td"><span class="{{ $talla->stock === 0 ? 'kx-badge-agotado' : 'kx-badge-bajo' }}">{{ $talla->stock === 0 ? 'Agotado' : $talla->stock }}</span></td>
                        <td class="kx-td text-right"><a href="{{ route('admin.abastecimiento.create', ['producto' => $talla->producto->sku, 'talla' => $talla->talla]) }}" class="kx-btn-primary px-3 py-1.5"><i class="fa-solid fa-plus"></i>Reponer</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
