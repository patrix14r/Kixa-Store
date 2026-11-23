@extends('layouts.admin')

{{-- [Semana 8: Rutas y Herencia Blade] @extends hereda el layout del admin y @section rellena el título y el contenido de esta pantalla. --}}
@section('titulo', 'Kixa Admin - Categorías')

@section('contenido')
{{-- Listado de categorías con @foreach; sin columna ID --}}
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
            {{-- [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] @forelse recorre $categorias y @empty cubre la lista vacía; productos_count viene de withCount('productos') en el controlador (index de Route::resource). --}}
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
                            {{-- route() recibe el modelo y Laravel usa su slug (getRouteKeyName), por eso la URL no muestra el ID (edit de Route::resource). --}}
                            <a href="{{ route('admin.categorias.edit', $categoria) }}" class="kx-btn-ghost px-3 py-1.5"><i class="fa-solid fa-pen"></i>Editar</a>
                            <form action="{{ route('admin.categorias.estado', $categoria) }}" method="POST">
                                @csrf
                                {{-- El navegador solo envía GET y POST: @csrf agrega el token de seguridad y @method simula el verbo PATCH. --}}
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
