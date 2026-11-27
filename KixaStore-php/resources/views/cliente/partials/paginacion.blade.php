{{-- [Sem 13 - CRUDs, Query Builder y Paginación]: vista propia de links() para el catálogo; Laravel le pasa $paginator y $elements (números y "...").
     Los enlaces conservan los filtros (withQueryString) y catalogo-filtros.js los carga por fetch sin recargar la página. --}}
@if ($paginator->hasPages())
    <nav class="mt-12 flex items-center justify-center gap-1.5 text-[15px] [&>a]:inline-flex [&>a]:size-10 [&>a]:items-center [&>a]:justify-center [&>a]:rounded-full [&>a]:text-tinta [&>a]:transition-colors [&>a]:duration-200 [&>a:hover]:bg-perla [&>span]:inline-flex [&>span]:size-10 [&>span]:items-center [&>span]:justify-center [&>span]:rounded-full" aria-label="Paginación del catálogo">
        @if ($paginator->onFirstPage())
            <span class="text-[#cacacb]" aria-hidden="true"><i class="fa-solid fa-chevron-left text-[13px]"></i></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Página anterior"><i class="fa-solid fa-chevron-left text-[13px]"></i></a>
        @endif

        @foreach ($elements as $elemento)
            @if (is_string($elemento))
                <span class="text-suave">{{ $elemento }}</span>
            @else
                @foreach ($elemento as $pagina => $url)
                    @if ($pagina === $paginator->currentPage())
                        <span class="bg-tinta font-semibold text-white" aria-current="page">{{ $pagina }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="Ir a la página {{ $pagina }}">{{ $pagina }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Página siguiente"><i class="fa-solid fa-chevron-right text-[13px]"></i></a>
        @else
            <span class="text-[#cacacb]" aria-hidden="true"><i class="fa-solid fa-chevron-right text-[13px]"></i></span>
        @endif
    </nav>
@endif
