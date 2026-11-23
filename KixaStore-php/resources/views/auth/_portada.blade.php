{{-- Columna de marca compartida por Login y Registro (@include) --}}
{{-- La foto de fondo llega por la variable CSS --fondo (asset() arma la URL) para que Vite no la reescriba --}}
<aside style="--fondo: url('{{ asset('images/banners/fondo-contacto.jpg') }}')" class="relative flex flex-col justify-between gap-10 bg-[#0c0c0f] bg-[linear-gradient(160deg,rgba(10,10,14,0.55)_0%,rgba(10,10,14,0.92)_70%),var(--fondo)] bg-cover bg-center bg-no-repeat px-9 py-10 text-white max-[760px]:gap-[18px] max-[760px]:px-6 max-[760px]:py-7" aria-hidden="true">
    <img src="{{ asset('images/ui/web.jpg') }}" alt="" class="h-10 w-[120px] object-contain object-left [filter:url(#kx-logo-blanco)]">
    <div>
        <span class="text-[11px] font-semibold tracking-[0.18em] text-white/60">KIXASTORE · COLECCIÓN 2026</span>
        <h2 class="mt-3 mb-6 text-[32px] leading-[1.1] font-extrabold tracking-[-0.03em] max-[760px]:mb-0 max-[760px]:text-[24px]">Actitud y exclusividad en cada paso</h2>
        <ul class="grid gap-3 max-[760px]:hidden [&>li]:flex [&>li]:items-center [&>li]:gap-2.5 [&>li]:text-[14px] [&>li]:text-white/85">
            <li><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.8"/><circle cx="17.5" cy="18" r="1.8"/></svg>Envíos a todo el Perú</li>
            <li><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 4.5 3.4 8.3 8 9 4.6-.7 8-4.5 8-9V6l-8-3z"/><path d="m8.5 12 2.5 2.5 4.5-5"/></svg>Calzado 100% original</li>
            <li><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>Cambios fáciles en 30 días</li>
        </ul>
    </div>
</aside>
