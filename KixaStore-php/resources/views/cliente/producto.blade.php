@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos de la ficha de producto de Tienda-Kixa_2/css/producto.css --}}
    <link rel="stylesheet" href="{{ asset('css/producto.css') }}">
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de Tienda-Kixa_2/producto.html (Líneas 612 a 723) --}}
    {{-- [SEPARADO]: El toast de confirmación flotante se extrajo hacia el componente Blade <x-alerta> --}}
    {{-- [ADAPTADO]: Se integró a layouts.app, eliminando header y footer repetidos --}}
    <main class="page">
        <div class="grid">

            <!-- GALERÍA DE IMÁGENES -->
            <div class="gallery">
                <div class="thumbs" id="thumbs">
                    <!-- Miniaturas inyectadas dinámicamente por producto-dinamico.js -->
                </div>

                <div class="main-img" id="mainImg">
                    <img id="mainImage" src="{{ asset('images/products/mujer/m1.jpg') }}" alt="Producto Tienda Kixa">
                </div>
            </div>

            <!-- PANEL DE INFORMACIÓN Y COMPRA -->
            <aside class="panel">
                <div class="brand" id="productBrand">
                    PUMA
                </div>

                <h1 id="productName">Puma 180 PRM</h1>

                <div class="price" id="productPrice">
                    S/ 159.00
                </div>

                <p id="productDescription">
                    Puma 180 PRM — cómoda, ligera y con el mejor estilo urbano.
                </p>

                <!-- SELECTOR DE TALLAS -->
                <div>
                    <label>
                        Selecciona tu talla
                    </label>
                    <div class="sizes" id="sizes">
                        <!-- Tallas generadas dinámicamente con interacción de selección -->
                    </div>
                </div>

                <!-- ACCIONES DE COMPRA -->
                <div class="actions">
                    <button class="btn-primary" id="addBtn" type="button">
                        Agregar al Carrito
                    </button>
                </div>

                <!-- INFORMACIÓN DE ENVÍO -->
                <div class="meta">
                    <i class="fa-solid fa-truck"></i>
                    Envío gratis a partir de S/ 200. Entrega en 3-5 días hábiles a todo el Perú.
                </div>
            </aside>

        </div>
    </main>

    <!-- COMPONENTE DE ALERTA TIPO TOAST -->
    <x-alerta 
        id="toast" 
        tipo="toast" 
        mensaje="Añadido al carrito" 
        icono="fa-solid fa-circle-check" 
    />
@endsection

@push('scripts')
    {{-- [ADAPTADO]: Script de carga dinámica de detalles y gestión del carrito en public/js/producto-dinamico.js --}}
    <script src="{{ asset('js/producto-dinamico.js') }}"></script>
@endpush
