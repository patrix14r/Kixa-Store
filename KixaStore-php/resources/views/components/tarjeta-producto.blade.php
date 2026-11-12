@props([
    'id' => 1,
    'nombre' => 'Zapatilla Kixa',
    'marca' => 'KIXA',
    'precio' => 199.00,
    'imagen' => 'images/products/hombre/h1.jpg',
    'dataMarca' => 'todos'
])

{{-- [ORIGEN]: Extraído de Tienda-Kixa_2/hombre.html (Líneas 364 a 417) --}}
{{-- [ADAPTADO]: Componente Blade reutilizable con atributos dinámicos (id, nombre, marca, precio, imagen, dataMarca) --}}
<article class="producto" data-marca="{{ strtolower($dataMarca ?? $marca) }}">
    <div class="producto-img">
        <img src="{{ str_starts_with($imagen, 'http') ? $imagen : asset($imagen) }}" alt="{{ $nombre }}">

        <div class="producto-action">
            <a href="{{ route('producto.detalle', ['id' => $id]) }}">
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
            <p class="precio">
                S/ {{ number_format((float)$precio, 2) }}
            </p>

            <a href="{{ route('producto.detalle', ['id' => $id]) }}" class="mini-btn" title="Ver detalle">
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
</article>
