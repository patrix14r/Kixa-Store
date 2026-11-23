{{-- [Semana 8: Rutas y Herencia Blade] El guion bajo en _formulario indica que es un parcial: create y edit lo incluyen con @include. old('campo', $producto->campo) conserva lo escrito si falla la validación. --}}
{{-- Formulario compartido; FK con <select> por nombre, nunca IDs visibles --}}
<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="kx-card space-y-5 p-6 xl:col-span-2">
        <h2 class="font-bold">Datos del producto</h2>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label for="categoria_id" class="kx-label">Categoría *</label>
                <select id="categoria_id" name="categoria_id" class="kx-input" required>
                    <option value="">Selecciona una categoría</option>
                    {{-- [Semana 9-10: Controladores, Request y Componentes] $categorias y $proveedores llegan del método privado opciones() del controlador, que consulta ambos modelos ordenados por nombre; así el <select> muestra nombres y el value guarda el id de la FK. --}}
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}" @selected(old('categoria_id', $producto->categoria_id) == $cat->id)>{{ $cat->nombre }}{{ $cat->activo ? '' : ' (inactiva)' }}</option>
                    @endforeach
                </select>
                @error('categoria_id') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="proveedor_id" class="kx-label">Proveedor</label>
                <select id="proveedor_id" name="proveedor_id" class="kx-input">
                    <option value="">Sin proveedor</option>
                    {{-- Mismo caso: se lista $prov->razon_social (solo proveedores activos), nunca el ID. --}}
                    @foreach ($proveedores as $prov)
                        <option value="{{ $prov->id }}" @selected(old('proveedor_id', $producto->proveedor_id) == $prov->id)>{{ $prov->razon_social }}</option>
                    @endforeach
                </select>
                @error('proveedor_id') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="marca" class="kx-label">Marca *</label>
                <input type="text" id="marca" name="marca" list="lista-marcas" value="{{ old('marca', $producto->marca) }}" class="kx-input" required maxlength="50">
                <datalist id="lista-marcas">
                    @foreach (['Nike', 'Jordan', 'Adidas', 'Puma', 'Umbro', 'New Balance', 'Vans'] as $m)
                        <option value="{{ $m }}">
                    @endforeach
                </datalist>
                @error('marca') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="nombre" class="kx-label">Nombre del modelo *</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $producto->nombre) }}" class="kx-input" required maxlength="120">
                @error('nombre') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
            <div>
                {{-- Se arma con la Marca y el Modelo; si llega vacío lo genera el controlador --}}
                <label for="sku" class="kx-label">SKU <span class="font-normal kx-muted">· automático</span></label>
                <div class="flex gap-2">
                    <input type="text" id="sku" name="sku" value="{{ old('sku', $producto->sku) }}" class="kx-input uppercase" maxlength="30" placeholder="ADI-CAM-00S" data-sku-auto>
                    <button type="button" id="btn-autogenerar-sku" class="kx-btn-ghost shrink-0 px-3"><i class="fa-solid fa-wand-magic-sparkles"></i>Autogenerar</button>
                </div>
                @error('sku') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="precio" class="kx-label">Precio (S/) *</label>
                <input type="number" id="precio" name="precio" step="0.01" min="0" value="{{ old('precio', $producto->precio) }}" class="kx-input" required>
                @error('precio') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="subcategoria" class="kx-label">Subcategoría</label>
                <input type="text" id="subcategoria" name="subcategoria" value="{{ old('subcategoria', $producto->subcategoria) }}" class="kx-input" maxlength="50" placeholder="urbanas, running, chimpunes...">
                @error('subcategoria') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-wrap items-end gap-5 pb-2 text-sm">
                <label class="flex items-center gap-2"><input type="checkbox" name="activo" value="1" class="h-4 w-4 accent-indigo-500" @checked(old('activo', $producto->activo))>Activo</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="destacado" value="1" class="h-4 w-4 accent-indigo-500" @checked(old('destacado', $producto->destacado))>Destacado</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="en_oferta" value="1" class="h-4 w-4 accent-indigo-500" @checked(old('en_oferta', $producto->en_oferta))>En oferta</label>
            </div>
            <div class="md:col-span-2">
                <label for="descripcion" class="kx-label">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3" class="kx-input">{{ old('descripcion', $producto->descripcion) }}</textarea>
                @error('descripcion') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Imagen: subir un archivo nuevo o escribir la ruta de una imagen existente en public/ --}}
        <div class="kx-card space-y-4 p-6">
            <h2 class="font-bold">Imagen</h2>
            <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 p-2 {{ $producto->imagen ? '' : 'hidden' }}" id="vista-imagen-caja">
                <img id="vista-imagen" src="{{ $producto->imagen ? (str_starts_with($producto->imagen, 'http') ? $producto->imagen : asset($producto->imagen)) : '' }}" alt="Vista previa" class="aspect-square w-full rounded-xl object-cover">
            </div>
            {{-- [FASE 3.5]: Input de archivo oculto + botón y texto propios en español (evita "Choose File" del navegador) --}}
            <div>
                <p class="kx-label">Subir imagen (máx. 2 MB)</p>
                <input type="file" id="imagen-input" name="imagen_archivo" accept="image/*" class="hidden">
                <div class="flex items-center gap-3 rounded-xl border border-zinc-800 bg-[#09090b] p-1.5">
                    <label for="imagen-input" class="inline-flex shrink-0 cursor-pointer items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                        <i class="fa-solid fa-upload"></i>Seleccionar imagen
                    </label>
                    <span id="imagen-nombre" class="truncate text-sm kx-muted">Ningún archivo seleccionado</span>
                </div>
                @error('imagen_archivo') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
            {{-- Imágenes secundarias opcionales: salen como miniaturas en la ficha del producto --}}
            <div>
                <p class="kx-label">Imágenes secundarias (opcional, máx. 6)</p>
                <input type="file" id="galeria-input" name="galeria_archivos[]" accept="image/*" multiple class="hidden">
                <div class="flex items-center gap-3 rounded-xl border border-zinc-800 bg-[#09090b] p-1.5">
                    <label for="galeria-input" class="inline-flex shrink-0 cursor-pointer items-center gap-2 rounded-lg border border-zinc-700 px-3 py-1.5 text-sm font-semibold transition hover:bg-white/5">
                        <i class="fa-regular fa-images"></i>Agregar imágenes
                    </label>
                    <span id="galeria-nombre" class="truncate text-sm kx-muted">Ninguna imagen secundaria</span>
                </div>
                @error('galeria_archivos.*') <p class="kx-error">{{ $message }}</p> @enderror
                @if (! empty($producto->galeria_imagenes))
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        @foreach ($producto->galeria_imagenes as $ruta)
                            <label class="group relative block cursor-pointer">
                                <img src="{{ asset($ruta) }}" alt="Imagen secundaria" class="aspect-square w-full rounded-lg border border-zinc-800 object-cover">
                                <span class="mt-1 flex items-center gap-1 text-[11px] kx-muted"><input type="checkbox" name="galeria_quitar[]" value="{{ $ruta }}" class="accent-red-500">Quitar</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
            <div>
                <label for="imagen" class="kx-label">o ruta de imagen existente</label>
                <input type="text" id="imagen" name="imagen" value="{{ old('imagen', $producto->imagen) }}" class="kx-input" placeholder="images/products/hombre/h1.jpg">
                @error('imagen') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Gestión de stock por talla --}}
        <div class="kx-card p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-bold">Tallas y stock</h2>
                <button type="button" id="btn-agregar-talla" class="kx-btn-ghost px-3 py-1.5"><i class="fa-solid fa-plus"></i>Talla</button>
            </div>
            <div class="mt-3 grid grid-cols-[1fr_1fr_auto] gap-2 text-xs kx-muted"><span>Talla</span><span>Stock</span><span class="w-8"></span></div>
            <div id="tallas-filas" class="mt-1 space-y-2">
                {{-- Una fila por talla con su stock; los nombres tallas[i][talla] y tallas[i][stock] llegan como arreglo y sincronizarTallas() los guarda en producto_tallas. --}}
                @foreach (old('tallas', $tallas) as $i => $fila)
                    <div class="grid grid-cols-[1fr_1fr_auto] gap-2" data-fila-talla>
                        <input type="number" step="0.5" name="tallas[{{ $i }}][talla]" value="{{ $fila['talla'] ?? '' }}" class="kx-input py-2">
                        <input type="number" min="0" name="tallas[{{ $i }}][stock]" value="{{ $fila['stock'] ?? 0 }}" class="kx-input py-2">
                        <button type="button" class="w-8 text-agotado" data-quitar-talla aria-label="Quitar talla"><i class="fa-solid fa-trash"></i></button>
                    </div>
                @endforeach
            </div>
            @error('tallas.*') <p class="kx-error">{{ $message }}</p> @enderror
            <template id="plantilla-talla">
                <div class="grid grid-cols-[1fr_1fr_auto] gap-2" data-fila-talla>
                    <input type="number" step="0.5" name="tallas[__I__][talla]" class="kx-input py-2">
                    <input type="number" min="0" name="tallas[__I__][stock]" value="0" class="kx-input py-2">
                    <button type="button" class="w-8 text-agotado" data-quitar-talla aria-label="Quitar talla"><i class="fa-solid fa-trash"></i></button>
                </div>
            </template>
        </div>
    </div>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('admin.productos.index') }}" class="kx-btn-ghost">Cancelar</a>
    <button type="submit" class="kx-btn-primary"><i class="fa-solid fa-floppy-disk"></i>Guardar producto</button>
</div>
