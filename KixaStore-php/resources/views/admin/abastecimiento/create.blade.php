@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Nuevo lote de abastecimiento')

@section('contenido')
<h1 class="text-2xl font-bold">Nuevo lote de abastecimiento</h1>
<p class="mt-1 text-sm kx-muted">Agrega todas las tallas que llegan en el mismo pedido al proveedor; al confirmar la recepción se suma el stock de todas.</p>

{{-- Formulario del lote de abastecimiento --}}
<form action="{{ route('admin.abastecimiento.store') }}" method="POST" class="kx-card mt-6 p-6" data-form-lote data-catalogo='@json($catalogo)'>
    @csrf
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="kx-label">Código de lote</label>
            <input type="text" value="{{ $codigoLote }}" class="kx-input" disabled>
            <p class="mt-1 text-xs kx-muted">Se genera automáticamente.</p>
        </div>
        <div>
            <label for="proveedor_id" class="kx-label">Proveedor *</label>
            <select id="proveedor_id" name="proveedor_id" class="kx-input" required>
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}" @selected((int) old('proveedor_id') === $proveedor->id)>{{ $proveedor->razon_social }}</option>
                @endforeach
            </select>
            @error('proveedor_id') <p class="kx-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold">Tallas del lote</h2>
            <p class="text-xs kx-muted">Ganancia estimada = (precio de venta actual − costo unitario) × cantidad.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            {{-- Filtro rápido de productos --}}
            <label class="relative">
                <span class="sr-only">Filtrar productos</span>
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-xs text-zinc-500"></i>
                <input type="search" class="kx-input w-72 pl-9 max-sm:w-full" placeholder="Filtrar por marca, modelo o SKU..." autocomplete="off" data-filtro-productos>
            </label>
            <button type="button" class="kx-btn-ghost" data-agregar-fila><i class="fa-solid fa-plus"></i>Agregar fila</button>
        </div>
    </div>
    @error('items') <p class="kx-error mt-2">{{ $message }}</p> @enderror

    <div class="mt-4 overflow-x-auto">
        <table class="w-full min-w-[960px] text-sm">
            <thead>
                <tr>
                    <th class="kx-th">Producto *</th>
                    <th class="kx-th">Talla *</th>
                    <th class="kx-th">Cantidad *</th>
                    <th class="kx-th">Costo unit. (S/) *</th>
                    <th class="kx-th">Precio venta</th>
                    <th class="kx-th">Subtotal</th>
                    <th class="kx-th">Ganancia est.</th>
                    <th class="kx-th"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody id="lote-filas">
                @foreach ($filas as $indice => $fila)
                    @include('admin.abastecimiento._fila', ['indice' => $indice, 'fila' => $fila])
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Resumen del lote --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-zinc-800/80 bg-[#09090b] p-4">
            <p class="text-xs kx-muted">Pares en el lote</p>
            <p class="mt-1 text-xl font-bold" data-total-unidades>0</p>
        </div>
        <div class="rounded-xl border border-zinc-800/80 bg-[#09090b] p-4">
            <p class="text-xs kx-muted">Inversión total del lote</p>
            <p class="mt-1 text-xl font-bold" data-total-inversion>S/ 0.00</p>
        </div>
        <div class="rounded-xl border border-zinc-800/80 bg-[#09090b] p-4">
            <p class="text-xs kx-muted">Ganancia estimada</p>
            <p class="mt-1 text-xl font-bold" data-total-ganancia>S/ 0.00</p>
        </div>
    </div>

    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('admin.abastecimiento.index') }}" class="kx-btn-ghost">Cancelar</a>
        <button type="submit" class="kx-btn-primary"><i class="fa-solid fa-floppy-disk"></i>Registrar lote</button>
    </div>
</form>

{{-- Plantilla de fila nueva --}}
<template id="plantilla-fila-lote">
    @include('admin.abastecimiento._fila', ['indice' => '__I__', 'fila' => $filaVacia])
</template>
@endsection
