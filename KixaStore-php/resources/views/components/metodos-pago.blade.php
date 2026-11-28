{{-- Métodos de pago aceptados --}}
@props(['oscuro' => false])

<div {{ $attributes->class(['@container flex flex-wrap items-center gap-x-2 gap-y-1.5 text-[12px]', $oscuro ? 'text-[#a1a1aa]' : 'text-suave']) }}>
    <span class="w-full">Métodos de pago aceptados:</span>
    <span class="inline-flex items-center gap-1.5"><x-logo-tarjeta marca="visa" class="h-5 w-[30px]" /> Visa</span>
    <span aria-hidden="true">·</span>
    <span class="inline-flex items-center gap-1.5"><x-logo-tarjeta marca="mastercard" class="h-5 w-[30px]" /> Mastercard</span>
    <span aria-hidden="true" class="@max-[22rem]:hidden">·</span>
    <span class="inline-flex items-center gap-1.5 @max-[22rem]:w-full"><i class="fa-solid fa-money-bill-wave"></i> Efectivo contraentrega</span>
</div>
