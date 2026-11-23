{{-- [Semana 8: Rutas y Herencia Blade] El guion bajo en _formulario indica que es un parcial (no tiene ruta propia): create y edit lo incluyen con @include y así el formulario se escribe una sola vez. --}}
{{-- Formulario compartido por crear y editar; old() conserva lo escrito si falla la validación --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="nombre" class="kx-label">Nombre *</label>
        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $categoria->nombre) }}" class="kx-input" required maxlength="50">
        {{-- @error('campo') imprime el mensaje de validación del Request solo cuando ese campo falló. --}}
        @error('nombre') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
    <div>
        {{-- Se completa solo al escribir el nombre (resources/js/app.js) --}}
        <label for="slug" class="kx-label">Slug (URL) <span class="font-normal kx-muted">· automático</span></label>
        <input type="text" id="slug" name="slug" value="{{ old('slug', $categoria->slug) }}" class="kx-input" maxlength="60" placeholder="se-genera-desde-el-nombre" data-slug-auto="nombre">
        @error('slug') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label for="descripcion" class="kx-label">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="3" class="kx-input" maxlength="255">{{ old('descripcion', $categoria->descripcion) }}</textarea>
        @error('descripcion') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="activo" value="1" class="h-4 w-4 accent-indigo-500" @checked(old('activo', $categoria->activo))>
        Categoría activa (visible en la tienda)
    </label>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('admin.categorias.index') }}" class="kx-btn-ghost">Cancelar</a>
    <button type="submit" class="kx-btn-primary"><i class="fa-solid fa-floppy-disk"></i>Guardar</button>
</div>
