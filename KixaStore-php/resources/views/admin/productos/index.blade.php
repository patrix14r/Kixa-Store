@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Productos')

@section('contenido')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Productos</h1>
        <p class="text-sm kx-muted">{{ $productos->total() }} zapatillas {{ $stock === 'critico' ? 'con tallas en stock crítico' : 'en el catálogo' }}.</p>
    </div>
    <a href="{{ route('admin.productos.create') }}" class="kx-btn-primary"><i class="fa-solid fa-plus"></i>Nuevo producto</a>
</div>

{{-- Filtros de búsqueda --}}
<form action="{{ route('admin.productos.index') }}" method="GET" class="kx-card mt-6 grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
    <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Nombre, marca, SKU o categoría" class="kx-input sm:col-span-2">
    <select name="categoria" class="kx-input">
        <option value="">Todas las categorías</option>
        @foreach ($categorias as $cat)
            <option value="{{ $cat->slug }}" @selected($categoriaSlug === $cat->slug)>{{ $cat->nombre }}</option>
        @endforeach
    </select>
    <select name="estado" class="kx-input">
        <option value="">Todos los estados</option>
        @foreach (['activos' => 'Activos', 'inactivos' => 'Inactivos', 'destacados' => 'Destacados', 'ofertas' => 'En oferta'] as $valor => $texto)
            <option value="{{ $valor }}" @selected($estado === $valor)>{{ $texto }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <select name="marca" class="kx-input">
            <option value="">Todas las marcas</option>
            @foreach ($marcas as $m)
                <option value="{{ $m }}" @selected($marca === $m)>{{ $m }}</option>
            @endforeach
        </select>
        <button type="submit" class="kx-btn-primary px-3" aria-label="Filtrar"><i class="fa-solid fa-filter"></i></button>
        @if ($stock)<input type="hidden" name="stock" value="{{ $stock }}">@endif
        @if ($buscar || $categoriaSlug || $marca || $estado || $stock)
            <a href="{{ route('admin.productos.index') }}" class="kx-btn-ghost shrink-0 px-3"><i class="fa-solid fa-xmark"></i>Limpiar</a>
        @endif
    </div>
</form>

<div class="kx-card mt-6 overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="kx-th">Producto</th>
                <th class="kx-th hidden xl:table-cell">Marca</th>
                <th class="kx-th hidden lg:table-cell">SKU</th>
                <th class="kx-th hidden md:table-cell">Categoría</th>
                <th class="kx-th">Precio</th>
                <th class="kx-th">Stock</th>
                <th class="kx-th">Estado</th>
                <th class="kx-th text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productos as $producto)
                <tr class="kx-row">
                    <td class="kx-td">
                        <div class="flex items-center gap-3">
                            <div class="kx-miniatura">
                                <img src="{{ str_starts_with((string) $producto->imagen, 'http') ? $producto->imagen : asset($producto->imagen) }}" alt="{{ $producto->modelo }}" loading="lazy" decoding="async" class="h-full w-full rounded-lg object-cover">
                            </div>
                            <span class="min-w-0"><span class="block font-medium leading-tight">{{ $producto->modelo }}</span><span class="block text-xs kx-muted xl:hidden">{{ $producto->marca }}</span></span>
                        </div>
                    </td>
                    <td class="kx-td hidden xl:table-cell"><span class="kx-badge-neutro">{{ $producto->marca }}</span></td>
                    <td class="kx-td hidden whitespace-nowrap kx-muted lg:table-cell">{{ $producto->sku }}</td>
                    <td class="kx-td hidden md:table-cell">{{ $producto->categoria->nombre }}</td>
                    <td class="kx-td whitespace-nowrap font-semibold">S/ {{ number_format($producto->precio, 2) }}</td>
                    <td class="kx-td">
                        <span class="whitespace-nowrap {{ $producto->stock_total == 0 ? 'kx-badge-agotado' : ($producto->stock_total <= 10 ? 'kx-badge-bajo' : 'kx-badge-ok') }}">{{ (int) $producto->stock_total }} pares</span>
                    </td>
                    <td class="kx-td">
                        <div class="flex max-w-[11rem] flex-wrap gap-1">
                            <span class="{{ $producto->activo ? 'kx-badge-ok' : 'kx-badge-agotado' }}">{{ $producto->activo ? 'Activo' : 'Inactivo' }}</span>
                            @if ($producto->destacado)<span class="kx-badge-neutro"><i class="fa-solid fa-star text-indigo-400"></i>Destacado</span>@endif
                            @if ($producto->en_oferta)<span class="kx-badge-bajo"><i class="fa-solid fa-tag"></i>Oferta</span>@endif
                        </div>
                    </td>
                    <td class="kx-td">
                        <div class="flex justify-end gap-1.5">
                            <button type="button" class="kx-btn-ghost px-3 py-1.5" title="Ver" data-vista-rapida="{{ json_encode(['imagen' => str_starts_with((string) $producto->imagen, 'http') ? $producto->imagen : asset($producto->imagen), 'nombre' => $producto->nombre, 'marca' => $producto->marca, 'sku' => $producto->sku, 'categoria' => $producto->categoria->nombre, 'proveedor' => $producto->proveedor?->razon_social ?? 'Sin proveedor', 'precio' => 'S/ ' . number_format($producto->precio, 2), 'stock' => (int) $producto->stock_total . ' pares', 'descripcion' => $producto->descripcion ?: 'Sin descripción.', 'tienda' => route('producto.detalle', $producto->sku)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"><i class="fa-regular fa-eye"></i></button>
                            <a href="{{ route('admin.productos.edit', $producto) }}" class="kx-btn-ghost px-3 py-1.5" title="Editar"><i class="fa-solid fa-pen"></i><span class="hidden 2xl:inline">Editar</span></a>
                            <form action="{{ route('admin.productos.estado', $producto) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="kx-btn-ghost px-3 py-1.5" title="{{ $producto->activo ? 'Desactivar' : 'Activar' }}">
                                    <i class="fa-solid {{ $producto->activo ? 'fa-toggle-on text-ok' : 'fa-toggle-off' }}"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="kx-td py-8 text-center kx-muted">No se encontraron productos con esos filtros.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $productos->links() }}</div>

{{-- Modal de vista rápida --}}
<div id="modal-vista" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
    <div class="kx-card w-full max-w-2xl overflow-hidden shadow-2xl shadow-black/60">
        <div class="flex items-center justify-between border-b border-zinc-800/80 px-5 py-3">
            <p class="font-semibold">Vista rápida</p>
            <button type="button" class="kx-icon-btn" data-cerrar-modal aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="grid gap-5 p-5 sm:grid-cols-2">
            <img data-campo="imagen" src="" alt="Producto" class="aspect-square w-full rounded-xl bg-zinc-900 object-cover">
            <div class="space-y-3 text-sm">
                <div>
                    <span class="kx-badge-neutro" data-campo="marca"></span>
                    <h3 class="mt-2 text-lg font-bold" data-campo="nombre"></h3>
                    <p class="text-xl font-semibold" data-campo="precio"></p>
                </div>
                <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1.5">
                    <dt class="kx-muted">SKU</dt><dd data-campo="sku"></dd>
                    <dt class="kx-muted">Categoría</dt><dd data-campo="categoria"></dd>
                    <dt class="kx-muted">Proveedor</dt><dd data-campo="proveedor"></dd>
                    <dt class="kx-muted">Stock</dt><dd data-campo="stock"></dd>
                </dl>
                <p class="kx-muted" data-campo="descripcion"></p>
                <a data-campo="tienda" href="#" target="_blank" class="kx-btn-ghost"><i class="fa-solid fa-arrow-up-right-from-square"></i>Ver en tienda</a>
            </div>
        </div>
    </div>
</div>
@endsection
