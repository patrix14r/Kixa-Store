{{-- [Sem 08 - Rutas y Vistas Blade]: parcial compartido por crear y editar con @include; old() conserva lo escrito si falla la validación. --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="nombre" class="kx-label">Nombre *</label>
        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $marca->nombre) }}" class="kx-input" required maxlength="50">
        <p class="mt-1 text-xs kx-muted">Máximo 50 caracteres. El slug de la URL se genera automáticamente a partir del nombre.</p>
        @error('nombre') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <p class="kx-label">Logo (opcional, máx. 2 MB)</p>
        <div class="flex items-center gap-3">
            {{-- La <img> existe siempre para que app.js pinte la vista previa también al crear; sin logo arranca oculta con hidden. --}}
            <img id="vista-imagen" src="{{ $marca->url_logo }}" alt="Vista previa del logo" class="h-12 w-12 rounded-lg bg-white object-contain p-1" @if (! $marca->url_logo) hidden @endif>
            {{-- Input de archivo oculto + botón propio en español (mismo patrón que Productos). --}}
            <input type="file" id="imagen-input" name="logo_archivo" accept="image/*" class="hidden">
            <div class="flex flex-1 items-center gap-3 rounded-xl border border-zinc-800 bg-[#09090b] p-1.5">
                <label for="imagen-input" class="inline-flex shrink-0 cursor-pointer items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                    <i class="fa-solid fa-upload"></i>Seleccionar logo
                </label>
                <span id="imagen-nombre" class="truncate text-sm kx-muted">Ningún archivo seleccionado</span>
            </div>
        </div>
        @error('logo_archivo') <p class="kx-error">{{ $message }}</p> @enderror
    </div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="activo" value="1" class="h-4 w-4 accent-indigo-500" @checked(old('activo', $marca->activo))>
        Marca activa (disponible al crear o editar productos)
    </label>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('admin.marcas.index') }}" class="kx-btn-ghost">Cancelar</a>
    <button type="submit" class="kx-btn-primary"><i class="fa-solid fa-floppy-disk"></i>Guardar</button>
</div>
