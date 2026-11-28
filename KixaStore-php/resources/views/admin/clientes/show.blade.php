@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Historial de ' . $cliente->name)

@section('contenido')
<a href="{{ route('admin.clientes.index') }}" class="text-sm kx-muted hover:text-white"><i class="fa-solid fa-arrow-left"></i> Clientes</a>
<div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">{{ $cliente->name }}</h1>
        <p class="text-sm kx-muted">{{ $cliente->email }} · DNI {{ $cliente->dni ?? '—' }} · {{ $cliente->telefono ?? 'Sin celular' }}</p>
    </div>
    <a href="{{ route('admin.clientes.edit', $cliente) }}" class="kx-btn-ghost"><i class="fa-solid fa-pen"></i>Editar datos</a>
</div>

<div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-3">
    <div class="kx-card p-5"><p class="text-sm kx-muted">Pedidos</p><p class="mt-1 text-2xl font-bold">{{ $pedidos->count() }}</p></div>
    <div class="kx-card p-5"><p class="text-sm kx-muted">Total comprado</p><p class="mt-1 text-2xl font-bold">S/ {{ number_format($pedidos->where('estado', '!=', 'Cancelado')->sum('total'), 2) }}</p></div>
    <div class="kx-card col-span-2 p-5 md:col-span-1"><p class="text-sm kx-muted">Cliente desde</p><p class="mt-1 text-2xl font-bold">{{ $cliente->created_at?->format('d/m/Y') }}</p></div>
</div>

<div class="mt-6 space-y-4">
    @forelse ($pedidos as $pedido)
        <article class="kx-card p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-semibold">{{ $pedido->codigo_orden }}</p>
                    <p class="text-xs kx-muted">{{ $pedido->created_at?->format('d/m/Y H:i') }} · {{ ucfirst($pedido->metodo_pago_detalle) }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-admin.estado-pedido :estado="$pedido->estado" />
                    <span class="font-semibold">S/ {{ number_format($pedido->total, 2) }}</span>
                </div>
            </div>
            <div class="mt-4 border-t border-zinc-800/80 pt-4">@include('admin.pedidos._items')</div>
        </article>
    @empty
        <div class="kx-card px-6 py-12 text-center kx-muted">Este cliente aún no tiene pedidos.</div>
    @endforelse
</div>
@endsection
