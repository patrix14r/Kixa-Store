@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos del catálogo de zapatillas de Tienda-Kixa_2/css/hombre.css --}}
    <link rel="stylesheet" href="{{ asset('css/hombre.css') }}">
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de la estructura de filtros y grilla de Tienda-Kixa_2/hombre.html (Líneas 130 a 450), mujer.html, niños.html y deporte.html --}}
    {{-- [SEPARADO]: Las tarjetas repetidas de zapatillas se migraron al componente Blade <x-tarjeta-producto> --}}
    {{-- [ADAPTADO]: Se integró a layouts.app, eliminando header/footer y configurando filtrado interactivo con hombre.js --}}

    <!-- =====================================================
         HERO / BANNER DEL CATÁLOGO
         ===================================================== -->
    <section class="hombre-hero">
        <div class="hero-slider">
            <img src="{{ asset('images/banners/hombre/hombre6.jpg') }}" class="hero-slide active" alt="Colección Kixa">
            <img src="{{ asset('images/banners/hombre/hombre5.jpg') }}" class="hero-slide" alt="Colección Kixa">
            <img src="{{ asset('images/banners/hombre/87.png') }}" class="hero-slide" alt="Colección Kixa">
        </div>

        <div class="hero-overlay"></div>

        <div class="hero-lines">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="hombre-hero-content">
            <span class="hero-small">KIXA SNEAKERS</span>
            <h1>
                {{ strtoupper($categoria ?? 'CATÁLOGO') }}
                <em>ESTILO</em>
                <strong>SIN LÍMITES.</strong>
            </h1>
            <p>
                Descubre nuestra colección exclusiva de zapatillas originales combinando comodidad, diseño y actitud.
            </p>
            <a href="#productos" class="hero-btn">
                Ver colección
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="hero-side-text">
            <span>KIXA</span>
            <span>STYLE</span>
            <span>SNEAKERS</span>
        </div>

        <button class="hero-arrow hero-prev" type="button" aria-label="Anterior">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <button class="hero-arrow hero-next" type="button" aria-label="Siguiente">
            <i class="fa-solid fa-chevron-right"></i>
        </button>

        <div class="hero-dots">
            <button class="dot active" type="button" aria-label="Slide 1"></button>
            <button class="dot" type="button" aria-label="Slide 2"></button>
            <button class="dot" type="button" aria-label="Slide 3"></button>
        </div>
    </section>

    <!-- =====================================================
         CONTENEDOR DE PRODUCTOS Y FILTROS
         ===================================================== -->
    <main class="productos-container" id="productos">
        <div class="coleccion-header">
            <div class="section-title">
                <span></span>
                COLECCIÓN OFICIAL
                <span></span>
            </div>
            <h2>
                Colección <strong>2026</strong>
            </h2>
            <p class="subtitulo">
                Zapatillas urbanas, deportivas y exclusivas de las mejores marcas.
            </p>
        </div>

        <!-- FILTROS POR MARCA -->
        <div class="filtros">
            <button type="button" class="active" data-filtro="todos">Todos</button>
            <button type="button" data-filtro="jordan">Jordan</button>
            <button type="button" data-filtro="nike">Nike</button>
            <button type="button" data-filtro="adidas">Adidas</button>
            <button type="button" data-filtro="puma">Puma</button>
            <button type="button" data-filtro="umbro">Umbro</button>
        </div>

        <!-- GRILLA DE PRODUCTOS DINÁMICOS -->
        <div class="grid-productos">
            {{-- PRODUCTOS REPRESENTATIVOS MEDIANTE EL COMPONENTE REUTILIZABLE --}}
            <x-tarjeta-producto 
                id="jordan-retro-black"
                nombre="Jordan Retro Black"
                marca="Jordan"
                dataMarca="jordan"
                precio="259.00"
                imagen="images/products/hombre/h1.jpg"
            />

            <x-tarjeta-producto 
                id="jordan-retro-yellow"
                nombre="Jordan Retro Yellow & Black"
                marca="Jordan"
                dataMarca="jordan"
                precio="189.00"
                imagen="images/products/hombre/h2.jpg"
            />

            <x-tarjeta-producto 
                id="nike-air-max-90"
                nombre="Nike Air Max 90"
                marca="Nike"
                dataMarca="nike"
                precio="199.00"
                imagen="images/products/mujer/m2.jpg"
            />

            <x-tarjeta-producto 
                id="nike-air-max-plus-tn"
                nombre="Nike Air Max Plus TN"
                marca="Nike"
                dataMarca="nike"
                precio="130.00"
                imagen="images/products/mujer/m3.jpg"
            />

            <x-tarjeta-producto 
                id="adidas-campus-00s"
                nombre="Adidas Campus 00s"
                marca="Adidas"
                dataMarca="adidas"
                precio="120.00"
                imagen="images/products/hombre/h10.jpg"
            />

            <x-tarjeta-producto 
                id="puma-180-prm"
                nombre="Puma 180 PRM"
                marca="Puma"
                dataMarca="puma"
                precio="159.00"
                imagen="images/products/mujer/m1.jpg"
            />

            <x-tarjeta-producto 
                id="umbro-speciali-pro"
                nombre="Umbro Speciali Pro Futsal"
                marca="Umbro"
                dataMarca="umbro"
                precio="149.00"
                imagen="images/products/hombre/h14.jpg"
            />

            <x-tarjeta-producto 
                id="jordan-1-low"
                nombre="Jordan 1 Low Chicago"
                marca="Jordan"
                dataMarca="jordan"
                precio="229.00"
                imagen="images/products/hombre/h6.jpg"
            />
        </div>
    </main>
@endsection

@push('scripts')
    {{-- [ADAPTADO]: Lógica del slider de cabecera y filtro interactivo en public/js/hombre.js --}}
    <script src="{{ asset('js/hombre.js') }}"></script>
@endpush
