{{-- [Sem 08 - Rutas y Vistas Blade]: componente anónimo <x-...> con @props y $slot --}}
{{-- Pregunta del Centro de Ayuda: <x-pregunta-frecuente pregunta="..." abierta>respuesta</x-pregunta-frecuente> --}}
{{-- Acordeón nativo <details> sin JavaScript; el "+" es un pseudo-elemento after: que gira 45° al abrirse (group-open) --}}
@props(['pregunta', 'abierta' => false])

<details class="group border-b border-[#e5e5e5] last:border-b-0" @if ($abierta) open @endif>
    <summary class="relative cursor-pointer list-none py-3.5 pr-7 text-[14.5px] font-semibold text-tinta transition-colors duration-200 hover:text-[#52525b] [&::-webkit-details-marker]:hidden after:absolute after:top-1/2 after:right-0.5 after:-translate-y-1/2 after:text-[18px] after:font-normal after:text-suave after:transition-[rotate,color] after:duration-250 after:content-['+'] group-open:after:rotate-45 group-open:after:text-tinta">{{ $pregunta }}</summary>
    <p class="pr-1 pb-4 text-[13px] leading-[1.7] text-[#52525b] [&_a]:text-tinta [&_a]:underline [&_a]:underline-offset-[3px]">{{ $slot }}</p>
</details>
