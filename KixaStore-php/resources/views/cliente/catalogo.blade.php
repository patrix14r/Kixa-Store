@extends('layouts.app')

@section('titulo', $titulo . ' | KixaStore')

@section('fondo', 'bg-[#090a0b] text-white')

@section('contenido')

    {{-- Banner de la categoría --}}
    <section class="relative mb-10 block h-[420px] w-full overflow-hidden bg-[#080909]">
        <div class="absolute inset-0 size-full">
            @foreach ($bannerActual['imagenes'] as $idx => $imgBanner)
                <img src="{{ asset($imgBanner) }}" alt="Colección Kixa {{ $categoria ?? 'General' }}" @class([
                    'absolute inset-0 size-full object-cover transition-[opacity,scale] duration-800',
                    'scale-100 opacity-100' => $idx === 0,
                    'scale-105 opacity-0' => $idx !== 0,
                ])>
            @endforeach
        </div>

        <div class="absolute inset-0 z-1 bg-[linear-gradient(90deg,rgba(0,0,0,0.92)_0%,rgba(0,0,0,0.72)_35%,rgba(0,0,0,0.20)_75%,rgba(0,0,0,0.55)_100%)]"></div>

        <div class="absolute top-1/2 left-[12%] z-3 max-w-[560px] -translate-y-1/2 max-[700px]:right-[8%] max-[700px]:left-[8%]">
            <span class="mb-3 block text-[0.74rem] font-semibold tracking-[0.28em] text-[#c7c7cc]">{{ $bannerActual['subtitulo'] }}</span>
            <h1 class="m-0 text-[length:clamp(2.1rem,5vw,4.2rem)] leading-none font-bold tracking-[-0.03em] text-white [overflow-wrap:anywhere] max-[700px]:text-[length:clamp(1.9rem,9vw,2.6rem)]">
                {{ $bannerActual['titulo_principal'] }}
                <em class="text-white not-italic">{{ $bannerActual['enfasis'] }}</em>
                <strong class="block font-semibold text-[#c7c7cc]">{{ $bannerActual['slogan'] }}</strong>
            </h1>
            <p class="my-6 max-w-[440px] text-[0.9rem] leading-[1.7] text-[#d4d4d4]">{{ $bannerActual['descripcion'] }}</p>
            <a href="#zona-catalogo" class="group inline-flex items-center gap-[13px] rounded-full bg-white px-[26px] py-[13px] text-[0.9rem] font-semibold text-tinta transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#e5e5e5] hover:shadow-[0_10px_30px_rgba(0,0,0,0.25)]">
                Ver colección
                <i class="fa-solid fa-arrow-right transition-[translate] duration-300 group-hover:translate-x-[5px]"></i>
            </a>
        </div>
    </section>

    {{-- Zona del catálogo (se recarga por fetch) --}}
    <section class="scroll-mt-navbar bg-white px-[5%] pt-8 pb-[72px] text-tinta [&_.kx-grid]:transition-opacity [&_.kx-grid]:duration-200 aria-busy:[&_.kx-grid]:opacity-45" id="zona-catalogo" aria-live="polite">
        @fragment('zona-catalogo')
        <div class="sticky top-navbar z-20 -mx-[5%] mb-2 flex flex-wrap items-center justify-between gap-4 bg-white/94 px-[5%] py-4 backdrop-blur-[12px] max-escritorio:static" data-titulo-pagina="{{ $titulo }} | KixaStore">
            <h1 class="text-[24px] font-semibold tracking-[-0.02em] max-escritorio:text-[20px]">{{ $titulo }} <span class="font-normal text-suave">({{ $productos->total() }})</span></h1>

            <div class="flex items-center gap-7 max-escritorio:w-full max-escritorio:justify-between max-escritorio:gap-3">
                @if ($hayFiltros)
                    <a href="{{ $urlLimpiar }}" class="inline-flex items-center gap-1.5 rounded-full border border-[#e4e4e7] bg-white px-3.5 py-[7px] text-[14px] font-medium whitespace-nowrap text-tinta transition-[border-color,background-color] duration-200 hover:border-tinta hover:bg-perla"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>Limpiar filtros</a>
                @endif
                <button type="button" class="inline-flex cursor-pointer items-center gap-2 border-none bg-transparent text-[15px] font-medium whitespace-nowrap text-tinta" id="btn-filtros" aria-controls="kx-filtros" aria-expanded="true">
                    <span class="kx-btn-filtros-texto" data-ocultar="Ocultar filtros" data-mostrar="Mostrar filtros">Ocultar filtros</span>
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h4M12 17h8"/><circle cx="16" cy="7" r="2"/><circle cx="10" cy="17" r="2"/></svg>
                </button>

                {{-- Ordenar por --}}
                <form method="GET" action="{{ route('catalogo', ['categoria' => $categoria]) }}#zona-catalogo" class="flex items-center gap-1.5 text-[15px] font-medium" data-form-orden>
                    @foreach ($parametrosOrden as $clave => $valor)
                        @if (is_scalar($valor))
                            <input type="hidden" name="{{ $clave }}" value="{{ $valor }}">
                        @endif
                    @endforeach
                    <label for="orden" class="whitespace-nowrap">Ordenar por:</label>
                    <select id="orden" name="orden" class="cursor-pointer appearance-none border-none bg-transparent bg-(image:--kx-icono-flecha) bg-[position:right_center] bg-no-repeat py-1 pr-[22px] pl-0.5 text-[15px] font-normal text-suave outline-none">
                        @foreach ($ordenes as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected($orden === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit">Aplicar</button></noscript>
                </form>
            </div>
        </div>

        {{-- Filtros laterales y grilla --}}
        <div class="kx-catalogo-cuerpo" id="kx-catalogo-cuerpo">
            <div class="kx-filtros-fondo" id="kx-filtros-fondo" hidden></div>

            <aside class="kx-filtros" id="kx-filtros" aria-label="Filtros">
                <div class="hidden items-center justify-between pb-2 text-[18px] font-semibold max-[900px]:flex">
                    <span>Filtros</span>
                    <button type="button" class="inline-flex size-[38px] cursor-pointer items-center justify-center rounded-full border-none bg-perla text-tinta" id="kx-filtros-cerrar" aria-label="Cerrar filtros">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                @if ($mostrarCategoria)
                    <x-filtro-catalogo titulo="Categoría / Género" :filtros="$filtrosCategoria" abierto />
                @endif

                <x-filtro-catalogo titulo="Marca" :filtros="$filtrosMarca" abierto :contador="(bool) $marca" vacio="Sin marcas disponibles" />

                <x-filtro-catalogo titulo="Disciplina" :filtros="$filtrosSub" :abierto="(bool) $subcategoria" :contador="(bool) $subcategoria" vacio="Sin disciplinas disponibles" />

                <x-filtro-catalogo titulo="Talla" :filtros="$filtrosTalla" abierto :contador="(bool) $talla" tipo="talla" vacio="Sin tallas en stock" />

                @if ($hayFiltros)
                    <a href="{{ $urlLimpiar }}" class="mt-[18px] inline-flex items-center gap-1.5 rounded-full border border-[#e4e4e7] bg-white px-3.5 py-[7px] text-[14px] font-medium whitespace-nowrap text-tinta transition-[border-color,background-color] duration-200 hover:border-tinta hover:bg-perla"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>Limpiar filtros</a>
                @endif
            </aside>

            <div class="min-w-0">
                <div class="kx-grid">
                    @forelse ($productos as $prod)
                        <x-tarjeta-producto :producto="$prod" />
                    @empty
                        <div class="col-span-full px-5 py-16 text-center text-suave">
                            <h3 class="mb-1.5 text-[18px] font-semibold text-tinta">No encontramos zapatillas con esos filtros</h3>
                            <p>Prueba con otra talla o marca, o limpia los filtros.</p>
                            <a href="{{ route('catalogo', ['categoria' => $categoria]) }}" class="mt-3.5 inline-block rounded-full bg-tinta px-[22px] py-2.5 text-[14px] text-white">Ver todo</a>
                        </div>
                    @endforelse
                </div>

                {{-- Paginación --}}
                {{ $productos->onEachSide(1)->links('cliente.partials.paginacion') }}
            </div>
        </div>
        @endfragment
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/catalogo-filtros.js') }}"></script>
@endpush
