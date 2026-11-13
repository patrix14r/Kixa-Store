{{-- [AGREGADO: DEFINICION DE PROPIEDADES EN COMPONENTE TARJETA] --}}
@props([
    'id' => 1,
    'sku' => null,
    'nombre' => 'Zapatilla Kixa',
    'marca' => 'KIXA',
    'precio' => 199.00,
    'imagen' => 'images/products/hombre/h1.jpg',
    'dataMarca' => 'todos'
])

{{-- [ORIGEN]: Extraído de Tienda-Kixa_2/hombre.html (Líneas 364 a 417) --}}
{{-- [ADAPTADO]: Componente Blade reutilizable con atributos dinámicos (id, sku, nombre, marca, precio, imagen, dataMarca) --}}
<article class="producto" data-marca="{{ strtolower($dataMarca ?? $marca) }}">
    <div class="producto-img" style="position: relative;">
        {{-- [AGREGADO: RENDERIZADO DE IMAGEN CON ASSET O URL ABSOLUTA] --}}
        <img src="{{ str_starts_with($imagen, 'http') ? $imagen : asset($imagen) }}" alt="{{ $nombre }}">

        {{-- [CAMBIO: BOTON INTERACTIVO PARA GUARDAR EN FAVORITOS EN LA TARJETA] --}}
        <button 
            type="button" 
            class="btn-fav-card" 
            title="Guardar en favoritos"
            data-id="{{ $id }}"
            data-sku="{{ $sku ?? $id }}"
            data-nombre="{{ $nombre }}"
            data-marca="{{ $marca }}"
            data-precio="{{ $precio }}"
            data-imagen="{{ str_starts_with($imagen, 'http') ? $imagen : asset($imagen) }}"
            style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.6); border: none; width: 34px; height: 34px; border-radius: 50%; color: #ffffff; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 15px; z-index: 5; transition: all 0.2s;">
            <i class="fa-regular fa-heart"></i>
        </button>

        <div class="producto-action">
            {{-- [CAMBIO: ENLACE EXACTO A LA RUTA PRODUCTO.DETALLE CON $id QUE CONTIENE EL SKU] --}}
            <a href="{{ route('producto.detalle', $id) }}">
                Ver producto
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="producto-info">
        <div class="marca">
            {{ strtoupper($marca) }}
        </div>

        <h3>
            {{ $nombre }}
        </h3>

        <div class="producto-bottom">
            {{-- [AGREGADO: FORMATEO DE PRECIO A DOS DECIMALES] --}}
            <p class="precio">
                S/ {{ number_format((float)$precio, 2) }}
            </p>

            {{-- [CAMBIO: BOTON MINIATURA APUNTANDO EXACTAMENTE A PRODUCTO.DETALLE CON $id] --}}
            <a href="{{ route('producto.detalle', $id) }}" class="mini-btn" title="Ver detalle">
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
</article>
