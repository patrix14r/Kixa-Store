@extends('layouts.admin')

{{-- [Sem 08 - Rutas y Vistas Blade]: @extends hereda el layout admin y @section rellena título y contenido. --}}
@section('titulo', 'Kixa Admin - Tallas y Stock')

@section('contenido')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Tallas y Stock</h1>
        <p class="text-sm kx-muted">Inventario por talla de cada zapatilla.</p>
    </div>
    <a href="{{ route('admin.tallas.create') }}" class="kx-btn-primary"><i class="fa-solid fa-plus"></i>Nueva talla</a>
</div>

{{-- Filtros GET: texto (nombre o SKU), marca, categoría y estado de stock --}}
<form action="{{ route('admin.tallas.index') }}" method="GET" class="kx-card mt-6 grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
    @if ($sku) <input type="hidden" name="producto" value="{{ $sku }}"> @endif
    <input type="search" name="q" value="{{ $buscar }}" placeholder="Buscar por nombre o SKU" class="kx-input lg:col-span-2">
    <select name="marca" class="kx-input">
        <option value="">Todas las marcas</option>
        @foreach ($marcas as $m)
            <option value="{{ $m }}" @selected($marca === $m)>{{ $m }}</option>
        @endforeach
    </select>
    <select name="categoria" class="kx-input">
        <option value="">Todas las categorías</option>
        @foreach ($categorias as $c)
            <option value="{{ $c->slug }}" @selected($categoria === $c->slug)>{{ $c->nombre }}</option>
        @endforeach
    </select>
    <select name="estado" class="kx-input">
        <option value="">Todos los estados</option>
        <option value="agotado" @selected($estado === 'agotado')>Agotado</option>
        <option value="critico" @selected($estado === 'critico')>Crítico (1 a 2)</option>
        <option value="disponible" @selected($estado === 'disponible')>Disponible</option>
    </select>
    <div class="flex gap-2 sm:col-span-2 lg:col-span-5">
        <button type="submit" class="kx-btn-primary px-3"><i class="fa-solid fa-filter"></i>Filtrar</button>
        @if ($hayFiltros)
            <a href="{{ route('admin.tallas.index') }}" class="kx-btn-ghost px-3"><i class="fa-solid fa-xmark"></i>Limpiar</a>
        @endif
    </div>
</form>

<div class="kx-card mt-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="kx-th">Producto</th>
                <th class="kx-th">Marca</th>
                <th class="kx-th">Talla</th>
                <th class="kx-th">Stock</th>
                <th class="kx-th">Estado</th>
                <th class="kx-th text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            {{-- Se muestra el nombre del producto (relación BelongsTo con with('producto')), nunca su ID. --}}
            @forelse ($tallas as $talla)
                <tr class="kx-row">
                    <td class="kx-td">
                        <div class="flex items-center gap-3">
                            <img src="{{ $talla->producto->url_imagen }}" alt="{{ $talla->producto->nombre }}" loading="lazy" class="w-10 h-10 rounded-lg object-contain bg-zinc-900 p-1">
                            <span class="font-semibold">{{ $talla->producto->nombre }}</span>
                        </div>
                    </td>
                    <td class="kx-td"><span class="kx-badge-neutro">{{ $talla->producto->marca }}</span></td>
                    <td class="kx-td">{{ $talla->talla_etiqueta }}</td>
                    <td class="kx-td font-semibold">{{ $talla->stock }}</td>
                    <td class="kx-td">
                        @if ($talla->stock === 0)
                            <span class="kx-badge-agotado">Agotado</span>
                        @elseif ($talla->stock <= 2)
                            <span class="kx-badge-bajo">Crítico</span>
                        @else
                            <span class="kx-badge-ok">Disponible</span>
                        @endif
                    </td>
                    <td class="kx-td">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.tallas.edit', [$talla->producto, $talla->talla]) }}" class="kx-btn-ghost px-3 py-1.5"><i class="fa-solid fa-pen"></i>Editar</a>
                            <form action="{{ route('admin.tallas.destroy', [$talla->producto, $talla->talla]) }}" method="POST" onsubmit="return confirm('¿Eliminar la talla {{ $talla->talla_etiqueta }} de {{ $talla->producto->nombre }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="kx-btn-ghost px-3 py-1.5 text-red-400"><i class="fa-solid fa-trash"></i>Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="kx-td py-8 text-center kx-muted">No hay tallas que coincidan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $tallas->links() }}</div>
@endsection
