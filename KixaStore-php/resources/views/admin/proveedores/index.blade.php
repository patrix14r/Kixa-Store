@extends('layouts.admin')

{{-- [Sem 08 - Rutas y Vistas Blade]: @extends hereda el layout admin y @section rellena título y contenido. --}}
@section('titulo', 'Kixa Admin - Proveedores')

@section('contenido')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Proveedores</h1>
        <p class="text-sm kx-muted">Empresas que abastecen el catálogo.</p>
    </div>
    <a href="{{ route('admin.proveedores.create') }}" class="kx-btn-primary"><i class="fa-solid fa-plus"></i>Nuevo proveedor</a>
</div>

{{-- Buscador GET: el controlador lee ?buscar= con $request->query() y filtra razón social o RUC. --}}
<form method="GET" action="{{ route('admin.proveedores.index') }}" class="mt-6 flex gap-3">
    <input type="search" name="buscar" value="{{ $buscar }}" class="kx-input max-w-sm" placeholder="Buscar por razón social o RUC">
    <button type="submit" class="kx-btn-ghost"><i class="fa-solid fa-magnifying-glass"></i>Buscar</button>
    @if ($buscar !== '')
        <a href="{{ route('admin.proveedores.index') }}" class="kx-btn-ghost">Limpiar</a>
    @endif
</form>

<div class="kx-card mt-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="kx-th">Razón social</th>
                <th class="kx-th">RUC</th>
                <th class="kx-th">Contacto</th>
                <th class="kx-th">Teléfono</th>
                <th class="kx-th">Correo</th>
                <th class="kx-th">Productos</th>
                <th class="kx-th">Estado</th>
                <th class="kx-th text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            {{-- [Sem 12-13 - Eloquent ORM y Route::resource]: @forelse con @empty; productos_count viene de withCount('productos'); no se muestra el ID. --}}
            @forelse ($proveedores as $proveedor)
                <tr class="kx-row">
                    <td class="kx-td font-semibold">{{ $proveedor->razon_social }}</td>
                    <td class="kx-td kx-muted">{{ $proveedor->ruc }}</td>
                    <td class="kx-td kx-muted">{{ $proveedor->contacto_nombre ?: '—' }}</td>
                    <td class="kx-td kx-muted">{{ $proveedor->telefono ?: '—' }}</td>
                    <td class="kx-td max-w-xs truncate kx-muted">{{ $proveedor->correo ?: '—' }}</td>
                    <td class="kx-td"><span class="kx-badge-neutro">{{ $proveedor->productos_count }}</span></td>
                    <td class="kx-td">
                        <span class="{{ $proveedor->activo ? 'kx-badge-ok' : 'kx-badge-agotado' }}">{{ $proveedor->activo ? 'Activo' : 'Inactivo' }}</span>
                    </td>
                    <td class="kx-td">
                        <div class="flex justify-end gap-2">
                            {{-- route() recibe el modelo y Laravel usa su RUC (getRouteKeyName). --}}
                            <a href="{{ route('admin.proveedores.edit', $proveedor) }}" class="kx-btn-ghost px-3 py-1.5"><i class="fa-solid fa-pen"></i>Editar</a>
                            <form action="{{ route('admin.proveedores.estado', $proveedor) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="kx-btn-ghost px-3 py-1.5">
                                    <i class="fa-solid {{ $proveedor->activo ? 'fa-toggle-on text-ok' : 'fa-toggle-off' }}"></i>{{ $proveedor->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="kx-td py-8 text-center kx-muted">No hay proveedores que coincidan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $proveedores->links() }}</div>
@endsection
