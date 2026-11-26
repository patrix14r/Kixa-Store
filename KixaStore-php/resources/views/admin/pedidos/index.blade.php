@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Pedidos')

@section('contenido')
{{-- Proceso 2 - Gestión y despacho de pedidos; cliente por nombre y sin IDs (Reglas 4.4 y 4.5) --}}
<div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Pedidos</h1>
        <p class="text-sm kx-muted">{{ $pedidos->total() }} pedidos · cambia el estado y el cliente lo verá en su historial.</p>
    </div>
    <form action="{{ route('admin.pedidos.index') }}" method="GET" class="flex gap-2 lg:w-96">
        @if ($estado)<input type="hidden" name="estado" value="{{ $estado }}">@endif
        <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Código PED-... o cliente" class="kx-input">
        <button type="submit" class="kx-btn-primary px-3" aria-label="Buscar"><i class="fa-solid fa-magnifying-glass"></i></button>
        @if ($buscar || $estado)
            <a href="{{ route('admin.pedidos.index') }}" class="kx-btn-ghost shrink-0 px-3"><i class="fa-solid fa-xmark"></i>Limpiar</a>
        @endif
    </form>
</div>

{{-- Tarjetas con los totales reales de MySQL --}}
<div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
    @foreach ($resumen as $tarjeta)
        <div class="kx-card p-5 {{ $loop->last ? 'col-span-2 md:col-span-1' : '' }}">
            <div class="flex items-center justify-between">
                <p class="text-sm kx-muted">{{ $tarjeta['etiqueta'] }}</p>
                <i class="fa-solid {{ $tarjeta['icono'] }} {{ $tarjeta['color'] }}"></i>
            </div>
            <p class="mt-2 text-2xl font-bold">{{ $tarjeta['valor'] }}</p>
        </div>
    @endforeach
</div>

{{-- Pestañas por estado con su contador --}}
<div class="mt-6 flex flex-wrap gap-1 rounded-xl border border-zinc-800 bg-[#09090b] p-1 text-sm">
    <a href="{{ route('admin.pedidos.index', array_filter(['buscar' => $buscar])) }}" class="kx-tab {{ $estado ? '' : 'kx-tab-activa' }}">Todos <span class="kx-muted">{{ $conteos->sum() }}</span></a>
    @foreach ($estados as $opcion)
        <a href="{{ route('admin.pedidos.index', array_filter(['estado' => $opcion, 'buscar' => $buscar])) }}" class="kx-tab {{ $estado === $opcion ? 'kx-tab-activa' : '' }}">{{ $opcion }} <span class="kx-muted">{{ $conteos[$opcion] ?? 0 }}</span></a>
    @endforeach
</div>

<div class="mt-6 space-y-4">
    @forelse ($pedidos as $pedido)
        <article class="kx-card p-5">
            <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-[1.3fr_1.3fr_1fr_1fr_1fr_auto] md:items-center">
                <div>
                    <p class="text-xs kx-muted">Pedido</p>
                    <p class="font-semibold">{{ $pedido->codigo_orden }}</p>
                </div>
                <div>
                    <p class="text-xs kx-muted">Cliente</p>
                    <p class="truncate font-medium">{{ $pedido->usuario?->name ?? 'Cliente eliminado' }}</p>
                </div>
                <div>
                    <p class="text-xs kx-muted">Fecha</p>
                    <p>{{ $pedido->created_at?->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs kx-muted">Pago</p>
                    <p>{{ ucfirst($pedido->metodo_pago_detalle) }}</p>
                </div>
                <div>
                    <p class="text-xs kx-muted">Total</p>
                    <p class="font-semibold">S/ {{ number_format($pedido->total, 2) }}</p>
                </div>
                {{-- Cambio de estado con PATCH; el toast confirma el cambio --}}
                <form action="{{ route('admin.pedidos.estado', $pedido) }}" method="POST" class="col-span-2 flex items-center gap-2 md:col-span-1">
                    @csrf
                    @method('PATCH')
                    <x-admin.estado-pedido :estado="$pedido->estado" />
                    {{-- Solo se habilitan los estados permitidos por PedidoController::TRANSICIONES --}}
                    <select name="estado" class="kx-input py-2" aria-label="Cambiar estado" onchange="this.form.submit()" @disabled(empty($transiciones[$pedido->estado]))>
                        @foreach ($estados as $opcion)
                            <option value="{{ $opcion }}" @selected($pedido->estado === $opcion) @disabled($pedido->estado !== $opcion && ! in_array($opcion, $transiciones[$pedido->estado] ?? [], true))>{{ $opcion }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            {{-- Barra de 4 pasos sincronizada con el perfil del cliente --}}
            <div class="mt-5 border-t border-zinc-800/80 pt-5">
                <x-admin.seguimiento-pedido :pedido="$pedido" />
            </div>
            <details class="mt-4 border-t border-zinc-800/80 pt-3">
                <summary class="cursor-pointer rounded text-sm kx-muted outline-none hover:text-white focus:outline-none focus-visible:text-white">{{ $pedido->items->sum('cantidad') }} zapatillas · {{ $pedido->direccion_envio }}</summary>
                <div class="mt-3">@include('admin.pedidos._items')</div>
            </details>
        </article>
    @empty
        <div class="kx-card px-6 py-12 text-center kx-muted">No hay pedidos con esos filtros.</div>
    @endforelse
</div>

<div class="mt-4">{{ $pedidos->links() }}</div>
@endsection
