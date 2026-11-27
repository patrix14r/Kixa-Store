{{-- [Sem 08 - Rutas y Vistas Blade]: Componente <x-auth-cristal>: fondo fondo2.jpg a pantalla completa + tarjeta "liquid glass" que comparten Login y Registro.
     Los estilos de labels e inputs viven aquí (variantes [&_.auth-campo_input]) para que los dos formularios no repitan la misma cadena de clases.
     $ancho: max-w-[420px] (login, tarjeta vertical) o max-w-[480px] (registro, por la fila Teléfono/DNI). --}}
@props(['titulo', 'subtitulo', 'ancho' => 'max-w-[420px]'])

{{-- CAMBIAR FONDO: edita la ruta 'images/ui/backgrounds/fondo2.jpg' de --fondo (guarda tu foto en public/images/ui/backgrounds/) (asset() arma la URL); el degradado encima es el overlay oscuro.
     El header transparente se monta encima (-mb-navbar en layouts/app.blade.php), por eso el padding superior suma el alto del navbar. --}}
<section style="--fondo: url('{{ asset('images/ui/backgrounds/fondo2.jpg') }}')"
         class="relative flex min-h-[calc(100vh_-_34px)] items-center justify-center bg-[#09090b] bg-[linear-gradient(180deg,rgba(9,9,11,0.25)_0%,rgba(9,9,11,0.7)_100%),var(--fondo)] bg-cover bg-center bg-no-repeat px-4 pt-[calc(var(--spacing-navbar)_+_56px)] pb-14 max-escritorio:min-h-screen max-escritorio:pt-[92px] max-escritorio:pb-8">
    {{-- Cristal nítido: bg-zinc-950/45 + backdrop-blur-md deja ver la textura del fondo; border-white/20 marca el canto del vidrio.
         BORDES DE LOS INPUTS: cambia [&_.auth-campo_input]:rounded-lg (rounded-full = píldora, rounded-none = recto). --}}
    <div {{ $attributes->class([
        "w-full $ancho rounded-[24px] bg-zinc-950/45 backdrop-blur-md border border-white/20 px-8 py-10 text-white shadow-2xl max-[460px]:px-5 max-[460px]:py-8",
        // Campos: label gris claro arriba e input rectangular (rounded-lg) translúcido con foco blanco nítido
        '[&_.auth-campo]:mb-4 [&_.auth-campo_label]:mb-1.5 [&_.auth-campo_label]:block [&_.auth-campo_label]:pl-1 [&_.auth-campo_label]:text-[13px] [&_.auth-campo_label]:font-medium [&_.auth-campo_label]:text-zinc-300 [&_.auth-campo_label_small]:font-normal [&_.auth-campo_label_small]:text-zinc-500',
        '[&_.auth-campo_input]:w-full [&_.auth-campo_input]:rounded-lg [&_.auth-campo_input]:border [&_.auth-campo_input]:border-white/20 [&_.auth-campo_input]:bg-black/30 [&_.auth-campo_input]:px-5 [&_.auth-campo_input]:py-3 [&_.auth-campo_input]:text-[14px] [&_.auth-campo_input]:text-white [&_.auth-campo_input]:outline-none [&_.auth-campo_input]:transition-[border-color,background-color,box-shadow] [&_.auth-campo_input]:duration-200 [&_.auth-campo_input::placeholder]:text-zinc-500',
        '[&_.auth-campo_input:focus]:border-white/70 [&_.auth-campo_input:focus]:bg-white/10 [&_.auth-campo_input:focus]:shadow-[0_0_0_3px_rgba(255,255,255,0.12)]',
        // Errores de public/js/auth.js: el rojo queda solo para avisos de validación (regla de diseño)
        '[&_.auth-campo_input.input-invalido]:border-red-400/80 [&_.auth-campo_input.input-invalido]:shadow-[0_0_0_3px_rgba(248,113,113,0.15)] [&_.auth-error]:pl-1 [&_.auth-error]:text-red-300',
    ]) }}>
        {{-- Encabezado común: logo en blanco (filtro #kx-logo-blanco del layout), título y subtítulo --}}
        <div class="mb-8 text-center">
            <img src="{{ asset('images/ui/web.jpg') }}" alt="KixaStore" class="mx-auto mb-4 block size-12 object-contain [filter:url(#kx-logo-blanco)]">
            <h1 class="text-[26px] font-bold tracking-[-0.03em] text-white">{{ $titulo }}</h1>
            <p class="mt-1.5 text-[14px] text-zinc-400">{{ $subtitulo }}</p>
        </div>

        {{ $slot }}
    </div>
</section>
