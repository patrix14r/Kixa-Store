@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Abastecimiento')

@section('contenido')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Abastecimiento</h1>
        <p class="text-sm kx-muted">Órdenes de reposición de stock a proveedores.</p>
    </div>
    <a href="{{ route('admin.abastecimiento.create') }}" class="kx-btn-primary"><i class="fa-solid fa-plus"></i>Nueva orden</a>
</div>

{{-- 3 tarjetas KPI compactas --}}
<div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="kx-card flex items-center gap-4 p-4">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/10 text-indigo-400"><i class="fa-solid fa-truck-ramp-box"></i></span>
        <div><p class="text-xs kx-muted">Órdenes pendientes</p><p class="text-2xl font-bold">{{ $kpis['pendientes'] }}</p></div>
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
    <h2 class="px-5 pt-5 text-lg font-bold">Órdenes de abastecimiento</h2>
    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="kx-th">Lote</th>
                <th class="kx-th">Proveedor</th>
                <th class="kx-th">Producto / Talla</th>
                <th class="kx-th">Cantidad</th>
                <th class="kx-th">Costo total</th>
                <th class="kx-th">Estado</th>
                <th class="kx-th text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ordenes as $orden)
                <tr class="kx-row">
                    <td class="kx-td font-semibold">{{ $orden->codigo_lote }}</td>
                    <td class="kx-td">{{ $orden->proveedor->razon_social }}</td>
                    <td class="kx-td">{{ $orden->talla->producto->nombre }} · talla {{ $orden->talla->talla_etiqueta }}</td>
                    <td class="kx-td">{{ $orden->cantidad_solicitada }}</td>
                    <td class="kx-td">S/ {{ number_format($orden->costo_total, 2) }}</td>
                    <td class="kx-td">
                        <span class="{{ match ($orden->estado) { 'Recibido' => 'kx-badge-ok', 'Cancelado' => 'kx-badge-agotado', default => 'kx-badge-bajo' } }}">{{ $orden->estado }}</span>
                    </td>
                    <td class="kx-td">
                        @if ($orden->estado === 'Pendiente')
                            <div class="flex justify-end gap-2">
                                <form action="{{ route('admin.abastecimiento.recibir', $orden) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="kx-btn-primary px-3 py-1.5"><i class="fa-solid fa-box-open"></i>Recibir</button>
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
                <tr><td colspan="7" class="kx-td py-8 text-center kx-muted">Aún no hay órdenes de abastecimiento.</td></tr>
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
            <a href="{{ route('admin.tallas.index', ['estado' => 'critico']) }}" class="text-sm text-indigo-400 hover:underline">Ver todas en Tallas y Stock</a>
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
