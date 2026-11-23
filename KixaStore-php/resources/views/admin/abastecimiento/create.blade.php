@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Nueva orden de abastecimiento')

@section('contenido')
<h1 class="text-2xl font-bold">Nueva orden de abastecimiento</h1>
<form action="{{ route('admin.abastecimiento.store') }}" method="POST" class="kx-card mt-6 p-6">
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
                <option value="">Selecciona un proveedor</option>
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}" @selected((int) old('proveedor_id') === $proveedor->id)>{{ $proveedor->razon_social }}</option>
                @endforeach
            </select>
            @error('proveedor_id') <p class="kx-error">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
            <label for="producto_talla_id" class="kx-label">Producto y talla *</label>
            <select id="producto_talla_id" name="producto_talla_id" class="kx-input" required>
                <option value="">Selecciona producto y talla</option>
                @foreach ($tallas as $talla)
                    <option value="{{ $talla->id }}" @selected((int) old('producto_talla_id', $seleccionada) === $talla->id)>{{ $talla->producto->nombre }} · talla {{ $talla->talla_etiqueta }} (stock {{ $talla->stock }})</option>
                @endforeach
            </select>
            @error('producto_talla_id') <p class="kx-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="cantidad_solicitada" class="kx-label">Cantidad *</label>
            <input type="number" id="cantidad_solicitada" name="cantidad_solicitada" value="{{ old('cantidad_solicitada') }}" class="kx-input" required min="1" max="100000">
            @error('cantidad_solicitada') <p class="kx-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="costo_unitario" class="kx-label">Costo unitario (S/) *</label>
            <input type="number" id="costo_unitario" name="costo_unitario" value="{{ old('costo_unitario') }}" class="kx-input" required min="0.01" step="0.01">
            <p class="mt-1 text-xs kx-muted">El costo total se calcula al guardar (cantidad × costo unitario).</p>
            @error('costo_unitario') <p class="kx-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('admin.abastecimiento.index') }}" class="kx-btn-ghost">Cancelar</a>
        <button type="submit" class="kx-btn-primary"><i class="fa-solid fa-floppy-disk"></i>Registrar orden</button>
    </div>
</form>
@endsection
