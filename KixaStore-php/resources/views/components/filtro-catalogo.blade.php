{{-- Acordeón de filtros del catálogo --}}
@props(['titulo', 'filtros', 'abierto' => false, 'contador' => false, 'tipo' => 'check', 'vacio' => 'Sin opciones disponibles'])

<details class="group border-b border-[#e5e5e5]" data-filtro="{{ $titulo }}" @if ($abierto) open @endif>
    <summary class="relative cursor-pointer list-none py-4 pr-6 text-[15px] font-medium [&::-webkit-details-marker]:hidden after:absolute after:top-1/2 after:right-1 after:size-[9.75px] after:-translate-y-[70%] after:rotate-45 after:border-r-[1.75px] after:border-b-[1.75px] after:border-tinta after:transition-[translate,rotate] after:duration-250 after:content-[''] group-open:after:-translate-y-[30%] group-open:after:-rotate-135">
        {{ $titulo }} @if ($contador)<em class="text-suave not-italic">(1)</em>@endif
    </summary>

    @if ($tipo === 'talla')
        <div class="grid grid-cols-4 gap-2 pb-[18px] max-[900px]:grid-cols-5">
            @forelse ($filtros as $filtro)
                <a href="{{ $filtro['url'] }}" @class([
                    'flex aspect-square items-center justify-center rounded-lg border text-[14px] transition-colors duration-200 hover:border-tinta',
                    'border-tinta bg-tinta text-white' => $filtro['activo'],
                    'border-[#e5e5e5] text-tinta' => ! $filtro['activo'],
                ])>{{ $filtro['etiqueta'] }}</a>
            @empty
                <p class="text-[13px] text-suave">{{ $vacio }}</p>
            @endforelse
        </div>
    @else
        <div class="flex flex-col gap-3 pb-[18px]">
            @forelse ($filtros as $filtro)
                <a href="{{ $filtro['url'] }}" @class([
                    "relative flex items-center gap-2.5 text-[15px] text-tinta before:size-[21px] before:shrink-0 before:rounded-[5px] before:border-[1.5px] before:transition-[border-color,background-color] before:duration-200 before:content-[''] hover:before:border-tinta",
                    'before:border-tinta before:bg-tinta before:bg-(image:--kx-icono-check) before:bg-center before:bg-no-repeat' => $filtro['activo'],
                    'before:border-[#cacacb]' => ! $filtro['activo'],
                ])>{{ $filtro['etiqueta'] }}</a>
            @empty
                <p class="text-[13px] text-suave">{{ $vacio }}</p>
            @endforelse
        </div>
    @endif
</details>
