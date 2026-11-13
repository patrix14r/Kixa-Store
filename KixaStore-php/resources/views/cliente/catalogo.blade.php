@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos del catálogo de zapatillas de Tienda-Kixa_2/css/hombre.css --}}
    <link rel="stylesheet" href="{{ asset('css/hombre.css') }}">
    <style>
        /* [CAMBIO: ESTILOS PARA LOS FILTROS DE TALLAS ESTILO ADIDAS / NIKE] */
        .filtros a.filtro {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .filtros-tallas a.talla-pill {
            min-width: 42px;
            height: 42px;
            padding: 0 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
        }
        .filtros-tallas a.talla-pill:hover {
            border-color: #ed001b !important;
            transform: translateY(-2px);
        }

        /* [FIX VISUAL] DIMENSIONES EXACTAS DEL BANNER Y SEPARACION DE LA GRILLA */
        .catalogo-hero,
        .hombre-hero,
        .mujer-hero,
        .ninos-hero,
        .deporte-hero,
        .banner,
        .slider {
            position: relative !important;
            width: 100% !important;
            height: 420px !important;
            overflow: hidden !important;
            margin-bottom: 40px !important;
            display: block !important;
            clear: both !important;
        }

        .catalogo-hero .hero-slide,
        .hombre-hero .hero-slide,
        .mujer-hero .hero-slide,
        .ninos-hero .hero-slide,
        .deporte-hero .hero-slide {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }

        .productos-container,
        .contenedor-productos,
        .catalogo-grid {
            padding: 40px 5% !important;
            position: relative !important;
            z-index: 2 !important;
            clear: both !important;
        }
    </style>
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de la estructura de filtros y grilla de Tienda-Kixa_2/hombre.html (Líneas 130 a 450), mujer.html, niños.html y deporte.html --}}
    {{-- [SEPARADO]: Las tarjetas repetidas de zapatillas se migraron al componente Blade <x-tarjeta-producto> --}}
    {{-- [ADAPTADO]: Se integró a layouts.app con soporte completo de filtros por marca, talla estilo Adidas y buscador --}}

    @php
        // [CAMBIO: ASIGNACION DINAMICA DE BANNERS REALES SEGUN LA CATEGORIA SELECCIONADA]
        $catSlug = strtolower($categoria ?? 'todos');
        
        $configBanners = [
            'mujer' => [
                'clase' => 'mujer-hero',
                'subtitulo' => 'KIXA WOMEN',
                'titulo_principal' => 'MUJER',
                'enfasis' => 'ESTILO',
                'slogan' => 'SIN LÍMITES.',
                'descripcion' => 'Las mejores marcas, los modelos más top y la comodidad que necesitas en cada paso.',
                'imagenes' => [
                    'images/banners/mujer/dsa3.webp',
                    'images/banners/mujer/dsa4.avif',
                    'images/banners/mujer/dsa33.avif',
                ],
            ],
            'ninos' => [
                'clase' => 'ninos-hero',
                'subtitulo' => 'KIXA KIDS',
                'titulo_principal' => 'NIÑOS',
                'enfasis' => 'ENERGÍA',
                'slogan' => 'Y DIVERSIÓN.',
                'descripcion' => 'Calzado escolar y urbano ultrarresistente con amortiguación y máxima seguridad para los más chicos.',
                'imagenes' => [ 
                    'images/banners/ninos/123.jpg',
                    'images/banners/ninos/qwe.jpg',
                ],
            ],
            'deporte' => [
                'clase' => 'deporte-hero',
                'subtitulo' => 'KIXA SPORT',
                'titulo_principal' => 'DEPORTE',
                'enfasis' => 'MÁXIMO',
                'slogan' => 'RENDIMIENTO.',
                'descripcion' => 'Chimpunes, running y calzado de alto rendimiento para fútbol, futsal, gimnasio y running.',
                'imagenes' => [
                    'images/banners/deporte/dsa1.jpg',
                    'images/banners/deporte/dsa2.jpg',
                    'images/banners/deporte/das3.jpg',
                ],
            ],
            'hombre' => [
                'clase' => 'hombre-hero',
                'subtitulo' => 'KIXA MEN',
                'titulo_principal' => 'HOMBRE',
                'enfasis' => 'ACTITUD',
                'slogan' => 'Y EXCLUSIVIDAD.',
                'descripcion' => 'Ediciones limitadas, zapatillas urbanas y de básquetbol con la mayor presencia del mercado.',
                'imagenes' => [
                    'images/banners/hombre/hombre6.jpg',
                    'images/banners/hombre/hombre5.jpg',
                    'images/banners/hombre/87.png',
                ],
            ],
        ];

        $bannerActual = $configBanners[$catSlug] ?? $configBanners['hombre'];
    @endphp

    <!-- =====================================================
         HERO / BANNER DEL CATÁLOGO (DINÁMICO POR CATEGORÍA)
         ===================================================== -->
    <!-- [FIX VISUAL]: Contenedor del banner superior con position relative, height 420px y separación clara -->
    <section class="catalogo-hero {{ $bannerActual['clase'] }}">
        <div class="hero-slider">
            @foreach ($bannerActual['imagenes'] as $idx => $imgBanner)
                <img src="{{ asset($imgBanner) }}" class="hero-slide {{ $idx === 0 ? 'active' : '' }}" alt="Colección Kixa {{ $categoria ?? 'General' }}">
            @endforeach
        </div>

        <div class="hero-overlay"></div>

        <div class="hero-lines">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="hombre-hero-content">
            <span class="hero-small">{{ $bannerActual['subtitulo'] }}</span>
            <h1>
                {{ $bannerActual['titulo_principal'] }}
                <em>{{ $bannerActual['enfasis'] }}</em>
                <strong>{{ $bannerActual['slogan'] }}</strong>
            </h1>
            <p>
                {{ $bannerActual['descripcion'] }}
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
            @foreach ($bannerActual['imagenes'] as $idx => $imgBanner)
                <button class="dot {{ $idx === 0 ? 'active' : '' }}" type="button" aria-label="Slide {{ $idx + 1 }}"></button>
            @endforeach
        </div>
    </section>

    <!-- =====================================================
         CONTENEDOR DE PRODUCTOS Y FILTROS
         ===================================================== -->
    <!-- [FIX VISUAL]: Contenedor de productos y filtros ubicado debajo del banner con su propio padding -->
    <main class="productos-container contenedor-productos catalogo-grid" id="productos">
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
                @if(request('buscar'))
                    <span style="display: block; color: #ed001b; margin-top: 5px;">Resultados para: "{{ request('buscar') }}"</span>
                @endif
            </p>
        </div>

        <!-- [CAMBIO: FILTROS DINAMICOS POR MARCA MANTENIENDO PARAMETROS DE URL] -->
        <div class="filtros">
            <a href="{{ route('catalogo', array_merge(['categoria' => $categoria], request()->except('marca'))) }}" 
               class="filtro {{ !request('marca') ? 'activo active' : '' }}">
                Todos
            </a>
            @foreach(['Jordan', 'Nike', 'Adidas', 'Puma', 'Umbro'] as $m)
                @php
                    $isMarcaActive = (request('marca') === $m);
                    $params = request()->query();
                    if ($isMarcaActive) {
                        unset($params['marca']);
                    } else {
                        $params['marca'] = $m;
                    }
                @endphp
                <a href="{{ route('catalogo', array_merge(['categoria' => $categoria], $params)) }}" 
                   class="filtro {{ $isMarcaActive ? 'activo active' : '' }}">
                    {{ $m }}
                </a>
            @endforeach
        </div>

        <!-- [CAMBIO: FILTROS ESTILO ADIDAS POR TALLAS DISPONIBLES EN STOCK] -->
        <div class="filtros-tallas" style="display: flex; gap: 8px; justify-content: center; align-items: center; margin: 10px 0 35px; flex-wrap: wrap;">
            <span style="color: #888; font-size: 13px; font-weight: 700; text-transform: uppercase; margin-right: 8px;">
                <i class="fa-solid fa-ruler-horizontal"></i> Tallas:
            </span>
            @foreach ([30, 32, 34, 36, 38, 39, 40, 41, 42, 43] as $tallaNum)
                @php
                    $isTallaActive = ((string)request('talla') === (string)$tallaNum);
                    $urlParams = request()->query();
                    if ($isTallaActive) {
                        unset($urlParams['talla']);
                    } else {
                        $urlParams['talla'] = $tallaNum;
                    }
                @endphp
                <a href="{{ route('catalogo', array_merge(['categoria' => $categoria], $urlParams)) }}"
                   class="talla-pill"
                   style="border: 1px solid {{ $isTallaActive ? '#ed001b' : '#333' }}; background: {{ $isTallaActive ? '#ed001b' : '#141517' }}; color: #fff;">
                    {{ $tallaNum }}
                </a>
            @endforeach

            @if (request('talla') || request('marca') || request('buscar') || request('sub'))
                <a href="{{ route('catalogo', ['categoria' => $categoria]) }}"
                   style="font-size: 12px; color: #ed001b; text-decoration: underline; margin-left: 12px; font-weight: 600;">
                    Limpiar filtros
                </a>
            @endif
        </div>

        <!-- GRILLA DE PRODUCTOS DINÁMICOS -->
        <div class="grid-productos">
            {{-- [CAMBIO: RECORRIDO DE PRODUCTOS REALES CON FORELSE DESDE MYSQL] --}}
            @forelse ($productos as $prod)
                {{-- [CAMBIO: ENVIANDO EL SKU COMO $id PARA QUE EL ENLACE DEL COMPONENTE APUNTE A /producto/{sku}] --}}
                <x-tarjeta-producto
                    :id="$prod->sku"
                    :sku="$prod->sku"
                    :nombre="$prod->nombre"
                    :marca="$prod->marca"
                    :precio="$prod->precio"
                    :imagen="$prod->imagen"
                    :dataMarca="strtolower($prod->marca)"
                />
            @empty
                {{-- [CAMBIO: MENSAJE AMIGABLE SI NO HAY PRODUCTOS PARA EL FILTRO] --}}
                <div style="color: #aaa; text-align: center; grid-column: 1 / -1; padding: 50px 20px;">
                    <i class="fa-solid fa-shoe-prints" style="font-size: 2.5rem; color: #ed001b; margin-bottom: 12px; display: inline-block;"></i>
                    <h3 style="color: #fff; font-size: 1.2rem; margin-bottom: 8px;">No encontramos zapatillas con los filtros seleccionados</h3>
                    <p style="font-size: 0.95rem;">Prueba seleccionando otra talla o marca, o limpia los filtros.</p>
                    <a href="{{ route('catalogo', ['categoria' => $categoria]) }}" style="color: #ed001b; text-decoration: underline; font-weight: 600; display: inline-block; margin-top: 10px;">
                        Ver todo el catálogo
                    </a>
                </div>
            @endforelse
        </div>
    </main>
@endsection

@push('scripts')
    {{-- [ADAPTADO]: Lógica del slider de cabecera en public/js/hombre.js --}}
    <script src="{{ asset('js/hombre.js') }}"></script>
@endpush
