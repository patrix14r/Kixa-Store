@extends('layouts.admin')

{{-- [Sem 08 - Rutas y Vistas Blade]: @extends hereda el layout admin y @section rellena título y contenido. --}}
@section('titulo', 'Kixa Admin - Marcas')

@section('contenido')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Marcas</h1>
        <p class="text-sm kx-muted">Fabricantes de las zapatillas del catálogo (distintas de los proveedores que las distribuyen).</p>
    </div>
    <a href="{{ route('admin.marcas.create') }}" class="kx-btn-primary"><i class="fa-solid fa-plus"></i>Nueva marca</a>
</div>

{{-- Buscador GET: el controlador lee ?buscar= con $request->query() y filtra por nombre. --}}
<form method="GET" action="{{ route('admin.marcas.index') }}" class="mt-6 flex gap-3">
    <input type="search" name="buscar" value="{{ $buscar }}" class="kx-input max-w-sm" placeholder="Buscar marca por nombre">
    <button type="submit" class="kx-btn-ghost"><i class="fa-solid fa-magnifying-glass"></i>Buscar</button>
    @if ($buscar !== '')
        <a href="{{ route('admin.marcas.index') }}" class="kx-btn-ghost">Limpiar</a>
    @endif
</form>

<div class="kx-card mt-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="kx-th">Logo</th>
                <th class="kx-th">Marca</th>
                <th class="kx-th">Slug</th>
                <th class="kx-th">Productos</th>
                <th class="kx-th">Estado</th>
                <th class="kx-th text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            {{-- [Sem 13 - CRUDs, Query Builder y Paginación]: @forelse con @empty; productos_count viene de withCount('productos'); no se muestra el ID. --}}
            @forelse ($marcas as $marca)
                <tr class="kx-row">
                    <td class="kx-td">
                        @if ($marca->url_logo)
                            {{-- Contenedor claro uniforme para que logos negros o de colores se vean limpios en el tema oscuro --}}
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/95 p-1"><img src="{{ $marca->url_logo }}" alt="Logo {{ $marca->nombre }}" class="max-h-full max-w-full object-contain"></span>
                        @else
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-800 font-bold">{{ mb_substr($marca->nombre, 0, 1) }}</span>
                        @endif
                    </td>
                    <td class="kx-td font-semibold">{{ $marca->nombre }}</td>
                    <td class="kx-td kx-muted">{{ $marca->slug }}</td>
                    <td class="kx-td"><span class="kx-badge-neutro">{{ $marca->productos_count }}</span></td>
                    <td class="kx-td">
                        <span class="{{ $marca->activo ? 'kx-badge-ok' : 'kx-badge-agotado' }}">{{ $marca->activo ? 'Activa' : 'Inactiva' }}</span>
                    </td>
                    <td class="kx-td">
                        <div class="flex justify-end gap-2">
                            {{-- route() recibe el modelo y Laravel usa su slug (getRouteKeyName). --}}
                            <a href="{{ route('admin.marcas.edit', $marca) }}" class="kx-btn-ghost px-3 py-1.5"><i class="fa-solid fa-pen"></i>Editar</a>
                            <form action="{{ route('admin.marcas.estado', $marca) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="kx-btn-ghost px-3 py-1.5">
                                    <i class="fa-solid {{ $marca->activo ? 'fa-toggle-on text-ok' : 'fa-toggle-off' }}"></i>{{ $marca->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="kx-td py-8 text-center kx-muted">No hay marcas que coincidan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $marcas->links() }}</div>
@endsection
