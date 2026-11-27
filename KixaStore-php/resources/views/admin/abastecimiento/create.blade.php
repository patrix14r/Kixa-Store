@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Nuevo lote de abastecimiento')

{{-- [Sem 08 - Rutas y Vistas Blade]: formulario del proceso secundario; los <select> muestran nombres (razón social, producto, talla), nunca IDs.
     Un lote agrupa varias filas (producto + talla + cantidad + costo) del mismo proveedor.
     Flujo Entrada -> Proceso -> Salida: este formulario es la ENTRADA (lote Pendiente); "Confirmar recepción" es el PROCESO
     (suma atómica del stock con DB::transaction + lockForUpdate) y la Guía PDF del listado es la SALIDA para el proveedor. --}}
@section('contenido')
<h1 class="text-2xl font-bold">Nuevo lote de abastecimiento</h1>
<p class="mt-1 text-sm kx-muted">Agrega todas las tallas que llegan en el mismo pedido al proveedor; al confirmar la recepción se suma el stock de todas.</p>

{{-- data-catalogo: { SKU: { precio, tallas: [{ id, texto }] } } armado en AbastecimientoController@create; lo usa resources/js/app.js --}}
<form action="{{ route('admin.abastecimiento.store') }}" method="POST" class="kx-card mt-6 p-6" data-form-lote data-catalogo='@json($catalogo)'>
    @csrf
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="kx-label">Código de lote</label>
            <input type="text" value="{{ $codigoLote }}" class="kx-input" disabled>
            <p class="mt-1 text-xs kx-muted">Se genera automáticamente.</p>
        </div>
        <div>
            {{-- Proveedor obligatorio: sin opción vacía, solo proveedores activos --}}
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
        {{-- app.js copia el producto de la última fila y preselecciona la siguiente talla libre (sin repetir tallas del lote) --}}
        <button type="button" class="kx-btn-ghost" data-agregar-fila><i class="fa-solid fa-plus"></i>Agregar otra talla</button>
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

    {{-- Resumen del lote: app.js lo recalcula con cada cambio de producto, cantidad o costo --}}
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

{{-- Plantilla de una fila nueva (sin producto elegido) --}}
<template id="plantilla-fila-lote">
    @include('admin.abastecimiento._fila', ['indice' => '__I__', 'fila' => $filaVacia])
</template>
@endsection
