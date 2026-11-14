@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Categorías')

@section('contenido')
{{-- [Sem 08 - Rutas y Vistas Blade]: Listado de categorías con @foreach; sin columna ID (Regla 4.4) --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Categorías</h1>
        <p class="text-sm kx-muted">Organiza las colecciones de la tienda.</p>
    </div>
    <a href="{{ route('admin.categorias.create') }}" class="kx-btn-primary"><i class="fa-solid fa-plus"></i>Nueva categoría</a>
</div>

<div class="kx-card mt-6 overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="kx-th">Nombre</th>
                <th class="kx-th">Slug</th>
                <th class="kx-th">Descripción</th>
                <th class="kx-th">Productos</th>
                <th class="kx-th">Estado</th>
                <th class="kx-th text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categorias as $categoria)
                <tr class="kx-row">
                    <td class="kx-td font-semibold">{{ $categoria->nombre }}</td>
                    <td class="kx-td kx-muted">{{ $categoria->slug }}</td>
                    <td class="kx-td max-w-xs truncate kx-muted">{{ $categoria->descripcion ?: '—' }}</td>
                    <td class="kx-td"><span class="kx-badge-neutro">{{ $categoria->productos_count }}</span></td>
                    <td class="kx-td">
                        <span class="{{ $categoria->activo ? 'kx-badge-ok' : 'kx-badge-agotado' }}">{{ $categoria->activo ? 'Activa' : 'Inactiva' }}</span>
                    </td>
                    <td class="kx-td">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.categorias.edit', $categoria) }}" class="kx-btn-ghost px-3 py-1.5"><i class="fa-solid fa-pen"></i>Editar</a>
                            <form action="{{ route('admin.categorias.estado', $categoria) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="kx-btn-ghost px-3 py-1.5">
                                    <i class="fa-solid {{ $categoria->activo ? 'fa-toggle-on text-ok' : 'fa-toggle-off' }}"></i>{{ $categoria->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="kx-td py-8 text-center kx-muted">Aún no hay categorías registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
