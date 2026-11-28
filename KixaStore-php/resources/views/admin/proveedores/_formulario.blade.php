<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="razon_social" class="kx-label">Razón social *</label>
        <input type="text" id="razon_social" name="razon_social" value="{{ old('razon_social', $proveedor->razon_social) }}" class="kx-input" required maxlength="100">
        @error('razon_social') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="ruc" class="kx-label">RUC * <span class="font-normal kx-muted">· 11 dígitos</span></label>
        <input type="text" id="ruc" name="ruc" value="{{ old('ruc', $proveedor->ruc) }}" class="kx-input" required inputmode="numeric" pattern="[0-9]{11}" maxlength="11">
        @error('ruc') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="contacto_nombre" class="kx-label">Contacto</label>
        <input type="text" id="contacto_nombre" name="contacto_nombre" value="{{ old('contacto_nombre', $proveedor->contacto_nombre) }}" class="kx-input" maxlength="80">
        @error('contacto_nombre') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="telefono" class="kx-label">Teléfono <span class="font-normal kx-muted">· 9 dígitos</span></label>
        <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $proveedor->telefono) }}" class="kx-input" inputmode="numeric" pattern="[0-9]{9}" maxlength="9">
        @error('telefono') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="correo" class="kx-label">Correo</label>
        <input type="email" id="correo" name="correo" value="{{ old('correo', $proveedor->correo) }}" class="kx-input" maxlength="100">
        @error('correo') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label for="direccion" class="kx-label">Dirección</label>
        <input type="text" id="direccion" name="direccion" value="{{ old('direccion', $proveedor->direccion) }}" class="kx-input" maxlength="200">
        @error('direccion') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="activo" value="1" class="h-4 w-4 accent-indigo-500" @checked(old('activo', $proveedor->activo))>
        Proveedor activo
    </label>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('admin.proveedores.index') }}" class="kx-btn-ghost">Cancelar</a>
    <button type="submit" class="kx-btn-primary"><i class="fa-solid fa-floppy-disk"></i>Guardar</button>
</div>
