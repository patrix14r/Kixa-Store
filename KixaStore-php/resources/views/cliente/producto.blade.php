@extends('layouts.app')

@push('styles')
    {{-- Ya no carga hombre.css (forzaba el fondo negro del body) --}}
    <link rel="stylesheet" href="{{ asset('css/producto.css') }}">
@endpush

@section('contenido')

    <main class="page">
        <div class="grid">

            <!-- GALERÍA DE IMÁGENES -->
            <div class="gallery">
                <div class="thumbs" id="thumbs">
                    <img src="{{ asset($producto->imagen) }}" class="active" alt="{{ $producto->nombre }}">
                    {{-- Imágenes secundarias subidas desde el Panel Admin --}}
                    @foreach ($producto->galeria_imagenes ?? [] as $ruta)
                        <img src="{{ asset($ruta) }}" alt="{{ $producto->nombre }}" loading="lazy">
                    @endforeach
                </div>

                <div class="main-img" id="mainImg">
                    <img id="mainImage" src="{{ asset($producto->imagen) }}" alt="{{ $producto->nombre }}">
                </div>
            </div>

            <!-- PANEL DE INFORMACIÓN Y COMPRA -->
            <aside class="panel">
                {{-- Marca, categoría y SKU en una línea discreta --}}
                <div class="brand-meta">
                    <span class="brand" id="productBrand">{{ strtoupper($producto->marca) }}</span>
                    <span class="brand-sep">•</span>
                    <span class="categoria-badge">{{ $producto->tipo_calzado }} · {{ $producto->categoria->nombre }}</span>
                    <span class="brand-sep">•</span>
                    <span class="brand-sku">SKU: <strong id="productSku">{{ $producto->sku }}</strong></span>
                </div>

                <h1 id="productName">
                    {{ $producto->nombre }}
                </h1>

                <div class="price" id="productPrice">
                    S/ {{ number_format((float)$producto->precio, 2) }}
                </div>

                <p id="productDescription">
                    {{ $producto->descripcion ?? 'Zapatilla original con la mejor tecnología de amortiguación, materiales de alta resistencia y diseño moderno.' }}
                </p>

                <!-- SELECTOR DE TALLAS -->
                <div>
                    <label class="sizes-label">
                        <span>Selecciona tu talla</span>
                        <span class="sizes-nota">Tallas Perú / US</span>
                    </label>

                    <div class="sizes" id="sizes">
                        @foreach ($producto->tallas as $tallaObj)
                            <button 
                                type="button" 
                                class="size-btn {{ $tallaObj->stock <= 0 ? 'agotado disabled' : '' }}" 
                                {{ $tallaObj->stock <= 0 ? 'disabled' : '' }} 
                                data-talla="{{ number_format($tallaObj->talla, 0) }}"
                                title="{{ $tallaObj->stock <= 0 ? 'Agotado' : 'Disponible (' . $tallaObj->stock . ' pares)' }}">
                                {{ number_format($tallaObj->talla, 0) }}
                            </button>
                        @endforeach
                    </div>
                    {{-- Abre el modal #modalGuiaTallas (public/js/guia-tallas.js) --}}
                    <button type="button" class="guia-tallas-enlace" id="abrirGuiaTallas" aria-haspopup="dialog" aria-controls="modalGuiaTallas">
                        <i class="fa-solid fa-ruler"></i> Guía de tallas
                    </button>
                </div>

                <!-- ACCIONES DE COMPRA Y FAVORITOS -->
                <div class="actions">
                    {{-- El admin no compra; ve un acceso directo para editar el producto --}}
                    @if (auth()->user()?->esAdmin())
                        <a href="{{ route('admin.productos.edit', $producto) }}" class="btn-primary btn-primary--enlace">
                            ⚙️ Gestionar este producto en Panel Admin
                        </a>
                    @else
                        <button 
                            class="btn-primary" 
                            id="addBtn" 
                            type="button"
                            data-id="{{ $producto->id }}"
                            data-sku="{{ $producto->sku }}"
                            data-nombre="{{ $producto->nombre }}"
                            data-precio="{{ $producto->precio }}"
                            data-imagen="{{ asset($producto->imagen) }}"
                            data-marca="{{ $producto->marca }}"
                            data-tipo="{{ $producto->tipo_calzado }} • {{ $producto->categoria->nombre }}">
                            <i class="fa-solid fa-cart-shopping"></i> Agregar al Carrito
                        </button>
                    @endif

                    <button 
                        class="btn-favorito-detalle" 
                        id="favBtn" 
                        type="button" 
                        title="Guardar en favoritos"
                        data-id="{{ $producto->id }}"
                        data-sku="{{ $producto->sku }}"
                        data-nombre="{{ $producto->nombre }}"
                        data-precio="{{ $producto->precio }}"
                        data-imagen="{{ asset($producto->imagen) }}"
                        data-marca="{{ $producto->marca }}">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                </div>

                <!-- INFORMACIÓN DE ENVÍO -->
                <div class="meta">
                    <i class="fa-solid fa-truck"></i>
                    Envío gratis a partir de S/ 200. Entrega en 24-48 horas a todo el Perú.
                </div>
            </aside>

        </div>
    </main>

    <!-- =====================================================
         SECCIÓN DE PRODUCTOS RECOMENDADOS (TAMBIÉN TE PUEDE INTERESAR)
         ===================================================== -->
    {{-- Recomendados con <x-tarjeta-producto> sobre fondo claro --}}
    <section class="recomendados-section">
        <div class="recomendados-cabecera">
            <h2>También te puede interesar</h2>
            <a href="{{ route('catalogo', ['categoria' => $producto->categoria->slug]) }}">Ver más de {{ $producto->categoria->nombre }}</a>
        </div>

        <div class="recomendados-grid">
            @foreach ($recomendados as $rec)
                <x-tarjeta-producto :producto="$rec" />
            @endforeach
        </div>
    </section>

    <!-- COMPONENTE DE ALERTA TIPO TOAST -->
    <x-alerta 
        id="toast" 
        tipo="toast" 
        mensaje="Añadido al carrito" 
        icono="fa-solid fa-circle-check" 
    />

    {{-- Modal "Guía de tallas": tabla de equivalencias US / EU / cm y cómo medir el pie --}}
    <div class="guia-tallas" id="modalGuiaTallas" hidden>
        <div class="guia-tallas-fondo" data-cerrar-guia></div>
        <div class="guia-tallas-panel" role="dialog" aria-modal="true" aria-labelledby="guiaTallasTitulo">
            <div class="guia-tallas-cabecera">
                <h2 id="guiaTallasTitulo">Guía de tallas</h2>
                <button type="button" class="guia-tallas-cerrar" data-cerrar-guia aria-label="Cerrar">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
            <table class="guia-tallas-tabla">
                <thead><tr><th>Talla US / Perú</th><th>Talla EU</th><th>Largo del pie (cm)</th></tr></thead>
                <tbody>
                    @foreach ($guiaTallas as $fila)
                        <tr><td>{{ $fila['us'] }}</td><td>{{ $fila['eu'] }}</td><td>{{ $fila['cm'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <p class="guia-tallas-nota"><strong>¿Cómo medir?</strong> Apoya el talón en una pared, marca la punta del dedo más largo y mide hasta la pared. Si estás entre dos tallas, elige la mayor.</p>
        </div>
    </div>

    <div class="kx-modal-carrito" id="modalCarrito" hidden>
        <div class="kx-modal-fondo" data-cerrar-modal></div>
        <div class="kx-modal-panel" role="dialog" aria-modal="true" aria-labelledby="modalCarritoTitulo">
            <div class="kx-modal-cabecera">
                <h2 id="modalCarritoTitulo">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                    1 artículo agregado al carrito
                </h2>
                <button type="button" class="kx-modal-cerrar" data-cerrar-modal aria-label="Cerrar">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <div class="kx-modal-item">
                <div class="kx-modal-img"><img id="modalImg" src="" alt=""></div>
                <div class="kx-modal-datos">
                    <strong id="modalNombre"></strong>
                    <span id="modalTipo"></span>
                    <span>Talla: <b id="modalTalla"></b></span>
                    <span>Cantidad: <b id="modalCantidad">1</b></span>
                </div>
                <strong class="kx-modal-precio" id="modalPrecio"></strong>
            </div>

            <div class="kx-modal-subtotal">
                <span>Subtotal del carrito (<span id="modalArticulos">1 artículo</span>)</span>
                <strong id="modalSubtotal"></strong>
            </div>

            <a href="{{ route('carrito') }}" class="kx-modal-btn">VER CARRITO Y PAGAR</a>
            <button type="button" class="kx-modal-btn kx-modal-btn--borde" data-cerrar-modal>Seguir comprando</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/producto-dinamico.js') }}"></script>
    <script src="{{ asset('js/guia-tallas.js') }}"></script>
@endpush
