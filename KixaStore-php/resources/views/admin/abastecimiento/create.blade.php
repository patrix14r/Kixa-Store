@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Nueva orden de abastecimiento')

{{-- [Sem 08 - Rutas y Vistas Blade]: formulario del proceso secundario; los <select> muestran nombres (razón social, producto, talla), nunca IDs. --}}
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
        {{-- [Sem 09 - Controladores y Middleware]: selects encadenados. El 1.º lista los productos (value = SKU, nunca el ID visible);
             el 2.º se llena con JS (resources/js/app.js) usando el mapa data-tallas que arma el controlador. --}}
        <div>
            <label for="producto" class="kx-label">Producto *</label>
            <select id="producto" name="producto" class="kx-input" required data-producto-abastecimiento>
                <option value="">Selecciona un producto</option>
                @foreach ($productos as $producto)
                    <option value="{{ $producto->sku }}" @selected($productoSku === $producto->sku)>{{ $producto->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="producto_talla_id" class="kx-label">Talla *</label>
            <select id="producto_talla_id" name="producto_talla_id" class="kx-input" required data-tallas='@json($tallasPorProducto)' @disabled($tallasIniciales->isEmpty())>
                <option value="">{{ $productoSku ? 'Selecciona una talla' : 'Primero elige un producto' }}</option>
                {{-- Las tallas del producto ya elegido se pintan desde el servidor para que "+ Reponer" llegue preseleccionado. --}}
                @foreach ($tallasIniciales as $talla)
                    <option value="{{ $talla['id'] }}" @selected($seleccionada === $talla['id'])>{{ $talla['texto'] }}</option>
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
