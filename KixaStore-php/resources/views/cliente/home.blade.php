@extends('layouts.app')

@section('contenido')

    <!-- =====================================================
         HERO / CARRUSEL PRINCIPAL
         ===================================================== -->
    <section class="hero">
        <div class="slider">
            <div class="slide active">
                <img src="{{ asset('images/banners/home/dsa6.jpg') }}" alt="Zapatillas Kixa" />
            </div>

            <div class="slide">
                <img src="{{ asset('images/banners/home/dsa9.webp') }}" alt="Zapatillas Kixa" />
            </div>

            <div class="slide">
                <img src="{{ asset('images/banners/home/dsa7.jpg') }}" alt="Zapatillas Kixa" />
            </div>

            <div class="slide">
                <img src="{{ asset('images/banners/home/zapatillas retro homnbre.webp') }}" alt="Zapatillas retro" />
            </div>

            <div class="slide">
                <img src="{{ asset('images/banners/home/das11.jpeg') }}" alt="Zapatillas Kixa" />
            </div>

            <div class="slide">
                <img src="{{ asset('images/banners/home/dsa5.webp') }}" alt="Zapatillas Kixa" />
            </div>

            <!-- Controles del carrusel -->
            <button class="prev" aria-label="Anterior">&#10094;</button>
            <button class="next" aria-label="Siguiente">&#10095;</button>

            <!-- Indicadores de posición -->
            <div class="slider-dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>

        <div class="hero-content">
            <h1>Nueva Colección 2026</h1>
            <p>Descubre las últimas tendencias en zapatillas</p>
            <a href="{{ route('catalogo') }}" class="btn-hero"> Comprar ahora </a>
        </div>
    </section>


    {{-- [Actualizado: Lo Destacado] Carrusel horizontal estilo Nike con productos destacado = true (HomeController) --}}
    <section class="kx-destacado" id="lo-destacado">
        <div class="kx-destacado-cabecera">
            <h2>Lo Destacado</h2>
            <div class="kx-destacado-flechas">
                <button type="button" class="kx-flecha" id="destacado-prev" aria-label="Ver anteriores">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>
                </button>
                <button type="button" class="kx-flecha" id="destacado-next" aria-label="Ver siguientes">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                </button>
            </div>
        </div>

        <div class="kx-destacado-pista" id="destacado-pista">
            @forelse ($destacados as $producto)
                <x-tarjeta-producto :producto="$producto" />
            @empty
                <p class="kx-vacio">Pronto tendremos nuevos destacados.</p>
            @endforelse
        </div>
    </section>

    <!-- =====================================================
         SECCIÓN DE OFERTAS ESPECIALES
         ===================================================== -->
    <section class="ofertas">
        <div class="ofertas-header">
            <span> KIXA </span>
            <h2>OFERTAS ESPECIALES</h2>
            <p>Aprovecha nuestras promociones exclusivas</p>
        </div>

        <div class="contenedor-ofertas">
            <div class="oferta-card">
                <img src="{{ asset('images/promotions/oferta.png') }}" alt="Oferta especial" />
            </div>

            <div class="oferta-card">
                <img src="{{ asset('images/promotions/oferta4.jpg') }}" alt="Envío gratis" />
            </div>

            <div class="oferta-card">
                <img src="{{ asset('images/promotions/oferta2.png') }}" alt="Promoción 2x1" />
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{-- [Actualizado: Lo Destacado] Flechas que desplazan la pista una "página" de tarjetas --}}
    <script>
        (() => {
            const pista = document.getElementById('destacado-pista');
            const prev = document.getElementById('destacado-prev');
            const next = document.getElementById('destacado-next');
            if (!pista || !prev || !next) return;

            const actualizar = () => {
                prev.disabled = pista.scrollLeft <= 4;
                next.disabled = pista.scrollLeft + pista.clientWidth >= pista.scrollWidth - 4;
            };
            const mover = (dir) => pista.scrollBy({ left: dir * pista.clientWidth * 0.9, behavior: 'smooth' });

            prev.addEventListener('click', () => mover(-1));
            next.addEventListener('click', () => mover(1));
            pista.addEventListener('scroll', actualizar, { passive: true });
            window.addEventListener('resize', actualizar);
            actualizar();
        })();
    </script>
@endpush
