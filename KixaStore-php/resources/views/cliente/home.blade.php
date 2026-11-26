@extends('layouts.app')

@section('contenido')

    <!-- =====================================================
         HERO / CARRUSEL PRINCIPAL
         ===================================================== -->
    {{-- kx-carrusel: las posiciones 3D de cada .slide están en resources/css/app.css (las cambia public/js/movimiento.js) --}}
    {{-- En escritorio el hero sube bajo el header de vidrio: crece 76px (h-navbar) y baja su contenido con pt-navbar --}}
    {{-- La imagen de fondo llega por la variable CSS --fondo (asset() arma la URL pública) para que Vite no la reescriba --}}
    <section style="--fondo: url('{{ asset('images/ui/backgrounds/789.png') }}')" class="kx-carrusel relative h-[430px] w-full overflow-hidden bg-[linear-gradient(rgba(0,0,0,0.35),rgba(0,0,0,0.35)),var(--fondo)] bg-cover bg-center bg-no-repeat max-[750px]:h-[400px] max-[500px]:h-[360px] escritorio:h-[calc(430px_+_var(--spacing-navbar))] escritorio:pt-navbar">
        <div class="relative size-full">
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

            <!-- Controles del carrusel (clases prev / next que busca movimiento.js) -->
            <button class="prev absolute top-1/2 left-5 z-30 flex size-11 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full border-none bg-black/75 text-[22px] text-white transition-[background-color,scale] duration-250 hover:scale-108 hover:bg-black" aria-label="Anterior">&#10094;</button>
            <button class="next absolute top-1/2 right-5 z-30 flex size-11 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full border-none bg-black/75 text-[22px] text-white transition-[background-color,scale] duration-250 hover:scale-108 hover:bg-black" aria-label="Siguiente">&#10095;</button>

            <!-- Indicadores de posición: el punto activo lleva la clase "active" -->
            <div class="absolute bottom-3 left-1/2 z-40 flex -translate-x-1/2 items-center gap-[7px] [&>.dot]:size-[7px] [&>.dot]:cursor-pointer [&>.dot]:rounded-full [&>.dot]:bg-white/45 [&>.dot]:transition-all [&>.dot]:duration-250 [&>.dot.active]:size-[9px] [&>.dot.active]:bg-white">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>

        <div class="pointer-events-none absolute top-1/2 left-1/2 z-20 w-[530px] max-w-[60%] -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-white/10 bg-[rgba(10,10,14,0.5)] px-[30px] py-6 text-center text-white backdrop-blur-[12px] escritorio:top-[calc(50%_+_var(--spacing-navbar)_/_2)] max-[750px]:w-[60%] max-[750px]:max-w-none max-[750px]:p-[15px] max-[500px]:w-[70%] max-[500px]:p-3">
            <h1 class="text-[2.5rem] font-bold [text-shadow:0_3px_10px_rgba(0,0,0,0.5)] max-[750px]:text-[1.5rem] max-[500px]:text-[1.15rem]">Nueva Colección 2026</h1>
            <p class="mt-[5px] text-[0.95rem] [text-shadow:0_2px_6px_rgba(0,0,0,0.5)] max-[750px]:text-[0.75rem] max-[500px]:text-[0.65rem]">Descubre las últimas tendencias en zapatillas</p>
            <a href="{{ route('catalogo') }}" class="pointer-events-auto mt-[15px] inline-block rounded-[25px] bg-black px-6 py-[9px] text-[12px] font-semibold text-white transition-[background-color,translate] duration-250 hover:-translate-y-0.5 hover:bg-[#222] max-[500px]:px-[15px] max-[500px]:py-[7px] max-[500px]:text-[10px]"> Comprar ahora </a>
        </div>
    </section>


    {{-- Carrusel horizontal estilo Nike con productos destacado = true (HomeController) --}}
    <section class="bg-[linear-gradient(180deg,#f5f5f7_0%,#ffffff_55%,#f5f5f7_100%)] pt-16 pb-[72px] pl-[5%] max-escritorio:pt-11 max-escritorio:pb-[52px] max-escritorio:pl-4" id="lo-destacado">
        <div class="mb-6 flex items-center justify-between pr-[5%] max-escritorio:pr-4">
            <h2 class="text-[24px] font-semibold tracking-[-0.02em] text-tinta">Lo Destacado</h2>
            <div class="flex gap-2.5 [&>button]:inline-flex [&>button]:size-11 [&>button]:cursor-pointer [&>button]:items-center [&>button]:justify-center [&>button]:rounded-full [&>button]:border-none [&>button]:bg-[#e5e5e5] [&>button]:text-tinta [&>button]:transition-[background-color,opacity] [&>button]:duration-200 [&>button:not(:disabled):hover]:bg-[#cacacb] [&>button:disabled]:cursor-default [&>button:disabled]:opacity-40">
                <button type="button" id="destacado-prev" aria-label="Ver anteriores">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>
                </button>
                <button type="button" id="destacado-next" aria-label="Ver siguientes">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                </button>
            </div>
        </div>

        {{-- Pista con scroll horizontal: 4 tarjetas por "página" en escritorio, 3 en tablet y 72% de ancho en teléfono --}}
        <div id="destacado-pista" class="grid snap-x snap-mandatory auto-cols-[calc((100%_-_5vw_-_48px)_/_4)] grid-flow-col gap-4 overflow-x-auto pr-[5%] [scrollbar-width:none] *:min-w-0 *:snap-start [&::-webkit-scrollbar]:hidden max-[1100px]:auto-cols-[calc((100%_-_5vw_-_32px)_/_3)] max-escritorio:auto-cols-[72%] max-escritorio:pr-4">
            @forelse ($destacados as $producto)
                <x-tarjeta-producto :producto="$producto" />
            @empty
                <p class="col-span-full px-5 py-16 text-center text-suave">Pronto tendremos nuevos destacados.</p>
            @endforelse
        </div>
    </section>

    <!-- =====================================================
         SECCIÓN DE OFERTAS ESPECIALES
         ===================================================== -->
    <section class="relative overflow-hidden bg-[linear-gradient(rgba(0,0,0,0.68),rgba(0,0,0,0.68)),url('https://i.pinimg.com/736x/d6/24/1b/d6241b46598213e84e65eac9f4c8ef68.jpg')] bg-cover bg-center px-5 pt-[55px] pb-[65px] text-center">
        <div class="relative z-2 mb-[30px]">
            <span class="text-[10px] font-bold tracking-[3px] text-[#aaa]"> KIXA </span>
            <h2 class="mt-[3px] text-[25px] font-bold text-white">OFERTAS ESPECIALES</h2>
            <p class="mt-[5px] text-[12px] text-[#ddd]">Aprovecha nuestras promociones exclusivas</p>
        </div>

        <div class="relative z-2 flex flex-wrap items-center justify-center gap-[25px] [&>div]:w-[300px] [&>div]:overflow-hidden [&>div]:rounded-[13px] [&>div]:border-2 [&>div]:border-white/75 [&>div]:bg-tinta [&>div]:shadow-[0_12px_30px_rgba(0,0,0,0.55)] [&>div]:transition-[translate,scale,box-shadow] [&>div]:duration-300 [&>div:hover]:-translate-y-2 [&>div:hover]:scale-102 [&>div:hover]:shadow-[0_20px_40px_rgba(0,0,0,0.7)] max-[500px]:[&>div]:w-[90%] max-[500px]:[&>div]:max-w-[320px] [&_img]:block [&_img]:h-auto [&_img]:w-full">
            <div>
                <img src="{{ asset('images/promotions/oferta.png') }}" alt="Oferta especial" />
            </div>

            <div>
                <img src="{{ asset('images/promotions/oferta4.jpg') }}" alt="Envío gratis" />
            </div>

            <div>
                <img src="{{ asset('images/promotions/oferta2.png') }}" alt="Promoción 2x1" />
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{-- Flechas que desplazan la pista una "página" de tarjetas --}}
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
