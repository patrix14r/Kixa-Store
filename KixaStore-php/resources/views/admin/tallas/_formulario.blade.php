{{-- Parcial compartido por crear y editar; el <select> muestra el nombre del producto y envía su id internamente. --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="producto_id" class="kx-label">Producto *</label>
        <select id="producto_id" name="producto_id" class="kx-input" required>
            <option value="">Selecciona un producto</option>
            @foreach ($productos as $producto)
                <option value="{{ $producto->id }}" @selected((int) old('producto_id', $talla->producto_id) === $producto->id)>{{ $producto->marca }} · {{ $producto->nombre }}</option>
            @endforeach
        </select>
        @error('producto_id') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="talla" class="kx-label">Talla * <span class="font-normal kx-muted">· de 0.5 en 0.5</span></label>
        <input type="number" id="talla" name="talla" value="{{ old('talla', $talla->talla_etiqueta) }}" class="kx-input" required min="20" max="50" step="0.5">
        @error('talla') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="stock" class="kx-label">Stock *</label>
        <input type="number" id="stock" name="stock" value="{{ old('stock', $talla->stock) }}" class="kx-input" required min="0" max="100000">
        @error('stock') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('admin.tallas.index') }}" class="kx-btn-ghost">Cancelar</a>
    <button type="submit" class="kx-btn-primary"><i class="fa-solid fa-floppy-disk"></i>Guardar</button>
</div>
