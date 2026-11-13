@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos de la ficha de producto de Tienda-Kixa_2/css/producto.css y grid de hombre.css --}}
    <link rel="stylesheet" href="{{ asset('css/producto.css') }}">
    <link rel="stylesheet" href="{{ asset('css/hombre.css') }}">
    <style>
        /* [CAMBIO: ESTILOS VISUALES PARA TALLAS AGOTADAS O DESHABILITADAS] */
        .size-btn.agotado, .size-btn:disabled {
            opacity: 0.35 !important;
            cursor: not-allowed !important;
            background: #151618 !important;
            border-color: #2b2c2d !important;
            color: #777 !important;
            text-decoration: line-through;
        }
        .size-btn.selected {
            background: #ed001b !important;
            border-color: #ed001b !important;
            color: #ffffff !important;
            box-shadow: 0 0 12px rgba(237, 0, 27, 0.4);
        }
    </style>
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de Tienda-Kixa_2/producto.html (Líneas 612 a 723) --}}
    {{-- [SEPARADO]: El toast de confirmación flotante se extrajo hacia el componente Blade <x-alerta> --}}
    {{-- [ADAPTADO]: Integrado con Eloquent en Laravel 13 sin depender de JSONs estáticos --}}

    <main class="page">
        <div class="grid">

            <!-- GALERÍA DE IMÁGENES -->
            <div class="gallery">
                <div class="thumbs" id="thumbs">
                    <img src="{{ asset($producto->imagen) }}" class="active" alt="{{ $producto->nombre }}">
                </div>

                <div class="main-img" id="mainImg">
                    {{-- [CAMBIO: RENDERIZADO DE IMAGEN PRINCIPAL REAL DESDE MYSQL] --}}
                    <img id="mainImage" src="{{ asset($producto->imagen) }}" alt="{{ $producto->nombre }}">
                </div>
            </div>

            <!-- PANEL DE INFORMACIÓN Y COMPRA -->
            <aside class="panel">
                {{-- [CAMBIO: MARCA, CATEGORIA REAL (NOMBRE TEXTUAL, NUNCA ID) Y SKU] --}}
                <div class="brand-meta" style="display: flex; gap: 10px; align-items: center; margin-bottom: 12px; flex-wrap: wrap;">
                    <span class="brand" id="productBrand" style="margin-bottom: 0;">{{ strtoupper($producto->marca) }}</span>
                    <span style="color: #555;">•</span>
                    <span class="categoria-badge" style="color: #ed001b; font-size: 13px; text-transform: uppercase; font-weight: 700;">
                        {{ $producto->categoria->nombre }}
                    </span>
                    <span style="color: #555;">•</span>
                    <span style="color: #888; font-size: 13px;">
                        SKU: <strong id="productSku" style="color: #bbb;">{{ $producto->sku }}</strong>
                    </span>
                </div>

                {{-- [CAMBIO: NOMBRE REAL DEL MODELO ELOQUENT] --}}
                <h1 id="productName">
                    {{ $producto->nombre }}
                </h1>

                {{-- [CAMBIO: PRECIO REAL FORMATEADO A DOS DECIMALES] --}}
                <div class="price" id="productPrice">
                    S/ {{ number_format((float)$producto->precio, 2) }}
                </div>

                {{-- [CAMBIO: DESCRIPCION REAL DEL PRODUCTO DESDE MYSQL] --}}
                <p id="productDescription">
                    {{ $producto->descripcion ?? 'Zapatilla original con la mejor tecnología de amortiguación, materiales de alta resistencia y diseño moderno.' }}
                </p>

                <!-- SELECTOR DE TALLAS -->
                <div>
                    <label style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Selecciona tu talla</span>
                        <span style="font-size: 12px; color: #888;">Tallas Perú / US</span>
                    </label>

                    <div class="sizes" id="sizes">
                        {{-- [CAMBIO: RECORRIDO DE TALLAS ASOCIADAS DESDE MYSQL CON VALIDACION DE STOCK] --}}
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
                </div>

                <!-- ACCIONES DE COMPRA Y FAVORITOS -->
                <div class="actions" style="margin-top: 25px; display: flex; gap: 12px; align-items: center;">
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
                        style="flex: 1;">
                        <i class="fa-solid fa-cart-shopping"></i> Agregar al Carrito
                    </button>

                    {{-- [CAMBIO: BOTON DE AGREGAR / QUITAR DE FAVORITOS EN FICHA DETALLE] --}}
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
                        data-marca="{{ $producto->marca }}"
                        style="background: #191b1d; border: 1px solid #333; color: #fff; width: 50px; height: 50px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 20px; transition: all 0.2s;">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                </div>

                <!-- INFORMACIÓN DE ENVÍO -->
                <div class="meta" style="margin-top: 20px;">
                    <i class="fa-solid fa-truck"></i>
                    Envío gratis a partir de S/ 200. Entrega en 24-48 horas a todo el Perú.
                </div>
            </aside>

        </div>
    </main>

    <!-- =====================================================
         SECCIÓN DE PRODUCTOS RECOMENDADOS (TAMBIÉN TE PUEDE INTERESAR)
         ===================================================== -->
    <section class="recomendados-section page" style="padding-top: 30px; border-top: 1px solid #222;">
        <div class="coleccion-header" style="text-align: center; margin-bottom: 35px;">
            <span style="color: #ed001b; font-weight: 700; letter-spacing: 2px; font-size: 13px;">KIXA RECOMIENDA</span>
            {{-- [CAMBIO: TITULO OBLIGATORIO DE SECCION RECOMENDADOS] --}}
            <h2 style="font-size: 26px; font-weight: 800; color: #fff; margin-top: 5px;">
                TAMBIÉN TE PUEDE INTERESAR (RECOMENDADOS)
            </h2>
            <p style="color: #888; font-size: 14px; margin-top: 5px;">
                Descubre modelos similares de la colección oficial {{ $producto->categoria->nombre }}.
            </p>
        </div>

        <div class="grid-productos">
            {{-- [CAMBIO: RECORRIDO DE PRODUCTOS RECOMENDADOS USANDO TARJETA-PRODUCTO] --}}
            @foreach ($recomendados as $rec)
                <x-tarjeta-producto 
                    :id="$rec->sku"
                    :sku="$rec->sku"
                    :nombre="$rec->nombre"
                    :marca="$rec->marca"
                    :precio="$rec->precio"
                    :imagen="$rec->imagen"
                    :dataMarca="strtolower($rec->marca)"
                />
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
@endsection

@push('scripts')
    {{-- [CAMBIO: SCRIPT OPTIMIZADO PARA LA INTERACCION DE TALLAS Y CARRITO] --}}
    <script src="{{ asset('js/producto-dinamico.js') }}"></script>
@endpush
