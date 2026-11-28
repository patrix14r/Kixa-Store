@props(['pedido'])
{{-- Barra de 4 pasos del pedido --}}
@if ($pedido->paso_seguimiento > 0)
    <ol class="relative grid grid-cols-4 text-center text-[11px] sm:text-xs">
        <span class="absolute top-[7px] right-[12.5%] left-[12.5%] h-[3px] rounded bg-zinc-800" aria-hidden="true"></span>
        <span class="absolute top-[7px] left-[12.5%] h-[3px] rounded bg-gradient-to-r from-indigo-500 to-emerald-500 transition-all" style="width: {{ ($pedido->paso_seguimiento - 1) * 25 }}%" aria-hidden="true"></span>
        @foreach (\App\Models\Pedido::PASOS as $i => $paso)
            <li @class(['relative z-10 flex flex-col items-center gap-2', 'font-semibold text-zinc-100' => $i < $pedido->paso_seguimiento, 'kx-muted' => $i >= $pedido->paso_seguimiento])>
                <span @class([
                    'h-[17px] w-[17px] rounded-full border-[3px]',
                    'border-emerald-500 bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' => $i < $pedido->paso_seguimiento,
                    'border-zinc-700 bg-[#121215]' => $i >= $pedido->paso_seguimiento,
                ])></span>
                {{ $paso }}
            </li>
        @endforeach
    </ol>
@else
    <p class="flex items-center gap-2 text-sm text-red-400"><i class="fa-solid fa-ban"></i>Pedido cancelado: no avanza en el seguimiento.</p>
@endif
