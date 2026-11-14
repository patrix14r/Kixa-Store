@extends('layouts.app')

@section('titulo', $titulo . ' | KixaStore')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/hombre.css') }}">
@endpush

@section('contenido')

    {{-- [Sem 08 - Rutas y Vistas Blade]: Banner dinámico por categoría (datos preparados en CatalogoController) --}}
    <section class="catalogo-hero {{ $bannerActual['clase'] }}">
        <div class="hero-slider">
            @foreach ($bannerActual['imagenes'] as $idx => $imgBanner)
                <img src="{{ asset($imgBanner) }}" class="hero-slide {{ $idx === 0 ? 'active' : '' }}" alt="Colección Kixa {{ $categoria ?? 'General' }}">
            @endforeach
        </div>

        <div class="hero-overlay"></div>

        <div class="hombre-hero-content">
            <span class="hero-small">{{ $bannerActual['subtitulo'] }}</span>
            <h1>
                {{ $bannerActual['titulo_principal'] }}
                <em>{{ $bannerActual['enfasis'] }}</em>
                <strong>{{ $bannerActual['slogan'] }}</strong>
            </h1>
            <p>{{ $bannerActual['descripcion'] }}</p>
            <a href="#zona-catalogo" class="hero-btn">
                Ver colección
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </section>

    {{-- [Actualizado: Filtros estilo Nike] Barra superior (título + contador, ocultar filtros, ordenar) y sidebar de acordeones --}}
    <section class="kx-catalogo" id="zona-catalogo">
        <div class="kx-catalogo-barra">
            <h1 class="kx-catalogo-titulo">{{ $titulo }}</h1>

            <div class="kx-catalogo-acciones">
                {{-- [Actualizado: Limpiar filtros] Visible solo con marca, disciplina, talla o búsqueda activa --}}
                @if ($hayFiltros)
                    <a href="{{ $urlLimpiar }}" class="kx-btn-limpiar"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>Limpiar filtros</a>
                @endif
                <button type="button" class="kx-btn-filtros" id="btn-filtros" aria-controls="kx-filtros" aria-expanded="true">
                    <span class="kx-btn-filtros-texto" data-ocultar="Ocultar filtros" data-mostrar="Mostrar filtros">Ocultar filtros</span>
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h4M12 17h8"/><circle cx="16" cy="7" r="2"/><circle cx="10" cy="17" r="2"/></svg>
                </button>

                {{-- [Sem 09 - Controladores y MVC]: El orden viaja por GET (?orden=) conservando los demás filtros --}}
                <form method="GET" action="{{ route('catalogo', ['categoria' => $categoria]) }}#zona-catalogo" class="kx-orden">
                    @foreach ($parametrosOrden as $clave => $valor)
                        @if (is_scalar($valor))
                            <input type="hidden" name="{{ $clave }}" value="{{ $valor }}">
                        @endif
                    @endforeach
                    <label for="orden">Ordenar por:</label>
                    <select id="orden" name="orden" onchange="this.form.submit()">
                        @foreach ($ordenes as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected($orden === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit">Aplicar</button></noscript>
                </form>
            </div>
        </div>

        <div class="kx-catalogo-cuerpo" id="kx-catalogo-cuerpo">
            <div class="kx-filtros-fondo" id="kx-filtros-fondo" hidden></div>

            <aside class="kx-filtros" id="kx-filtros" aria-label="Filtros">
                <div class="kx-filtros-cabecera">
                    <span>Filtros</span>
                    <button type="button" class="kx-filtros-cerrar" id="kx-filtros-cerrar" aria-label="Cerrar filtros">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                {{-- [Actualizado: Filtro inteligente] Solo en Deporte, Todos o búsquedas --}}
                @if ($mostrarCategoria)
                <details class="kx-filtro" open>
                    <summary>Categoría / Género</summary>
                    <div class="kx-filtro-lista">
                        @foreach ($filtrosCategoria as $filtro)
                            <a href="{{ $filtro['url'] }}" class="kx-check {{ $filtro['activo'] ? 'activo' : '' }}">{{ $filtro['etiqueta'] }}</a>
                        @endforeach
                    </div>
                </details>
                @endif

                <details class="kx-filtro" open>
                    <summary>Marca @if ($marca)<em>(1)</em>@endif</summary>
                    <div class="kx-filtro-lista">
                        @forelse ($filtrosMarca as $filtro)
                            <a href="{{ $filtro['url'] }}" class="kx-check {{ $filtro['activo'] ? 'activo' : '' }}">{{ $filtro['etiqueta'] }}</a>
                        @empty
                            <p class="kx-filtro-vacio">Sin marcas disponibles</p>
                        @endforelse
                    </div>
                </details>

                <details class="kx-filtro" @if ($subcategoria) open @endif>
                    <summary>Disciplina @if ($subcategoria)<em>(1)</em>@endif</summary>
                    <div class="kx-filtro-lista">
                        @forelse ($filtrosSub as $filtro)
                            <a href="{{ $filtro['url'] }}" class="kx-check {{ $filtro['activo'] ? 'activo' : '' }}">{{ $filtro['etiqueta'] }}</a>
                        @empty
                            <p class="kx-filtro-vacio">Sin disciplinas disponibles</p>
                        @endforelse
                    </div>
                </details>

                <details class="kx-filtro" open>
                    <summary>Talla @if ($talla)<em>(1)</em>@endif</summary>
                    <div class="kx-tallas">
                        @forelse ($filtrosTalla as $filtro)
                            <a href="{{ $filtro['url'] }}" class="kx-talla {{ $filtro['activo'] ? 'activo' : '' }}">{{ $filtro['etiqueta'] }}</a>
                        @empty
                            <p class="kx-filtro-vacio">Sin tallas en stock</p>
                        @endforelse
                    </div>
                </details>

                @if ($hayFiltros)
                    <a href="{{ $urlLimpiar }}" class="kx-btn-limpiar kx-btn-limpiar--lateral"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>Limpiar filtros</a>
                @endif
            </aside>

            <div class="kx-grid">
                @forelse ($productos as $prod)
                    <x-tarjeta-producto :producto="$prod" />
                @empty
                    <div class="kx-vacio">
                        <h3>No encontramos zapatillas con esos filtros</h3>
                        <p>Prueba con otra talla o marca, o limpia los filtros.</p>
                        <a href="{{ route('catalogo', ['categoria' => $categoria]) }}">Ver todo</a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/catalogo-filtros.js') }}"></script>
@endpush
