<!DOCTYPE html>
<html lang="es" class="overflow-x-clip scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- [Semana 8: Rutas y Herencia Blade] Layout maestro del Modo Cliente: las vistas de resources/views/cliente lo heredan con @extends('layouts.app') y rellenan @yield('titulo') y @yield('contenido'). --}}
    <title>@yield('titulo', 'KixaStore')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/ui/favicon-round.png') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Plus Jakarta Sans (400-700) en todo el Modo Cliente: clase font-jakarta del tema de Tailwind --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Cliente y Admin comparten el mismo Tailwind CSS v4 compilado por Vite (resources/css/app.css) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Carrito y favoritos se guardan por cuenta: al cambiar de usuario se intercambian --}}
    <script>
      (() => {
        try {
          const actual = @json(auth()->id() ? 'u' . auth()->id() : 'invitado');
          const previo = localStorage.getItem('kixa_usuario');
          const claves = ['carrito', 'favoritos'];
          const guardar = (quien) => claves.forEach(k => localStorage.setItem(`kixa:${quien}:${k}`, localStorage.getItem(k) || '[]'));

          if (previo && previo !== actual) {
            guardar(previo);
            claves.forEach(k => {
              const propio = JSON.parse(localStorage.getItem(`kixa:${actual}:${k}`) || '[]');
              const deInvitado = previo === 'invitado' ? JSON.parse(localStorage.getItem(k) || '[]') : [];
              // Al iniciar sesión, lo que agregó como invitado se suma a su cuenta
              localStorage.setItem(k, JSON.stringify(propio.length ? propio : deInvitado));
            });
            // Lo del invitado ya pasó a la cuenta: se vacía su copia para que no reaparezca al salir
            if (previo === 'invitado') localStorage.setItem('kixa:invitado:carrito', '[]');
          }
          // Tras una compra confirmada el servidor marca carrito_vaciado y se borra todo rastro
          if (@json((bool) session('carrito_vaciado'))) {
            Object.keys(localStorage).filter(c => c === 'carrito' || /^kixa:.+:carrito$/.test(c)).forEach(c => localStorage.setItem(c, '[]'));
          }
          localStorage.setItem('kixa_usuario', actual);
          window.addEventListener('pagehide', () => guardar(actual));
        } catch (e) { /* almacenamiento bloqueado: la tienda sigue funcionando sin memoria */ }
      })();
    </script>
</head>
{{-- @yield('fondo') permite que una vista cambie el fondo del body (el catálogo y el login usan un fondo oscuro detrás del banner) --}}
<body class="flex min-h-screen flex-col overflow-x-clip font-jakarta leading-[normal] tracking-[-0.005em] antialiased [&.menu-movil-activo]:overflow-hidden @yield('fondo', 'bg-white text-tinta')">

    {{-- Filtro SVG: convierte el logo JPG (negro sobre blanco) en blanco con fondo transparente --}}
    <svg width="0" height="0" class="absolute size-0 overflow-hidden" aria-hidden="true" focusable="false">
      <filter id="kx-logo-blanco" color-interpolation-filters="sRGB">
        <feColorMatrix type="matrix" values="0 0 0 0 1  0 0 0 0 1  0 0 0 0 1  -0.333 -0.333 -0.333 1 0"/>
      </filter>
    </svg>

    {{-- Barra superior con rutas nombradas (Acerca de y cuenta); se oculta en teléfonos --}}
    <div class="relative z-101 shrink-0 border-b border-white/5 bg-noche px-[5%] py-[7px] max-escritorio:hidden">
      <div class="flex items-center justify-end gap-3.5 [&_a]:text-[10.5px] [&_a]:font-semibold [&_a]:tracking-[0.08em] [&_a]:text-[#a1a1aa] [&_a]:uppercase [&_a]:transition-colors [&_a]:duration-200 [&_a:hover]:text-white">
        <a href="{{ route('nosotros') }}">Acerca de</a>
        <span class="text-[10px] text-[#3f3f46]" aria-hidden="true">|</span>
        {{-- [Semana 8: Rutas y Herencia Blade] @guest y @auth muestran el menú según haya sesión iniciada o no. --}}
        @guest
          <a href="{{ route('login') }}">Iniciar sesión</a>
        @endguest
        @auth
          <a href="{{ route('perfil') }}" title="Mi perfil"><i class="fa-solid fa-user"></i> {{ Auth::user()->name }}</a>
        @endauth
      </div>
    </div>

    {{-- En el home el header es "vidrio" translúcido (navbar--glass) y sube sobre el carrusel; al bajar 40px el script le agrega navbar--scroll --}}
    <header id="navbar-principal" @class([
        'sticky top-0 z-100 grid h-navbar shrink-0 grid-cols-[1fr_auto_1fr] items-center gap-8 border-b border-white/5 px-[5%] text-white transition-[background,box-shadow] duration-350',
        'max-[1000px]:gap-5 max-[1000px]:px-[3%] max-escritorio:flex max-escritorio:h-[60px] max-escritorio:flex-nowrap max-escritorio:justify-start max-escritorio:gap-2 max-escritorio:px-3',
        'navbar--glass bg-[rgba(10,10,14,0.55)] backdrop-blur-[20px] escritorio:-mb-navbar max-escritorio:bg-carbon [&.navbar--scroll]:bg-[rgba(12,12,15,0.96)] [&.navbar--scroll]:backdrop-blur-none' => request()->routeIs('home'),
        'bg-[rgba(12,12,15,0.96)]' => ! request()->routeIs('home'),
    ])>
      <button type="button" id="btn-menu-movil" class="hidden size-10 cursor-pointer items-center justify-center rounded-xl border border-white/14 bg-white/6 text-white max-escritorio:inline-flex" aria-label="Abrir menú" aria-controls="menu-movil" aria-expanded="false">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>

      {{-- Solo el isotipo a la izquierda (como el Swoosh de Nike) --}}
      <div class="flex items-center justify-self-start max-escritorio:shrink-0">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-white max-escritorio:gap-2" aria-label="KixaStore - Inicio">
          <img src="{{ asset('images/ui/web.jpg') }}" alt="KixaStore" class="block size-[46px] object-contain [filter:url(#kx-logo-blanco)] max-escritorio:size-[34px]">
        </a>
      </div>

      {{-- Menú centrado: el subrayado animado es un pseudo-elemento after: que crece al pasar el mouse o en la categoría activa --}}
      <nav class="flex h-full items-center max-escritorio:hidden" aria-label="Categorías">
        <ul class="flex h-full items-center gap-[30px] max-[1000px]:gap-5">
          <li class="flex h-full items-center"><a href="{{ route('home') }}#lo-destacado" class="relative inline-block py-1.5 text-[14px] font-medium tracking-[-0.01em] text-white/86 transition-colors duration-250 after:absolute after:inset-x-0 after:bottom-0 after:h-0.5 after:origin-right after:scale-x-0 after:rounded-[2px] after:bg-white after:transition-[scale] after:duration-350 after:ease-[cubic-bezier(0.65,0,0.35,1)] after:content-[''] hover:text-white hover:after:origin-left hover:after:scale-x-100">Destacados</a></li>
          @foreach (['hombre' => 'Hombre', 'mujer' => 'Mujer', 'ninos' => 'Niños', 'deporte' => 'Deporte'] as $slug => $nombre)
            <li class="flex h-full items-center">
              {{-- La categoría actual lleva aria-current="page" y la variante aria-[current=page]: la deja blanca y subrayada --}}
              <a href="{{ route('catalogo', ['categoria' => $slug]) }}" @if (request()->route('categoria') === $slug || request('categoria') === $slug) aria-current="page" @endif
                 class="relative inline-block py-1.5 text-[14px] font-medium tracking-[-0.01em] text-white/86 transition-colors duration-250 after:absolute after:inset-x-0 after:bottom-0 after:h-0.5 after:origin-right after:scale-x-0 after:rounded-[2px] after:bg-white after:transition-[scale] after:duration-350 after:ease-[cubic-bezier(0.65,0,0.35,1)] after:content-[''] hover:text-white hover:after:origin-left hover:after:scale-x-100 aria-[current=page]:text-white aria-[current=page]:after:origin-left aria-[current=page]:after:scale-x-100">{{ $nombre }}</a>
            </li>
          @endforeach
        </ul>
      </nav>

      <div class="flex min-w-0 items-center justify-end gap-[22px] max-escritorio:flex-1 max-escritorio:gap-2.5">
        {{-- Buscador GET hacia CatalogoController@index (?buscar=modelo o marca) --}}
        <form action="{{ route('catalogo') }}" method="GET" role="search" class="relative flex w-[200px] items-center rounded-full border border-white/14 bg-white/7 transition-[border-color,background,box-shadow] duration-250 hover:border-white/24 focus-within:border-white/40 focus-within:bg-white/11 focus-within:shadow-[0_0_0_4px_rgba(255,255,255,0.06)] max-[1000px]:w-[170px] max-escritorio:w-auto max-escritorio:min-w-0 max-escritorio:flex-[1_1_0]">
          <input type="search" name="buscar" placeholder="Buscar" value="{{ request('buscar') }}" aria-label="Buscar zapatillas" class="w-full min-w-0 flex-1 border-none bg-transparent py-2.5 pr-[46px] pl-[18px] text-[14px] text-white outline-none placeholder:text-white/50 [&::-webkit-search-cancel-button]:hidden max-escritorio:py-2 max-escritorio:pr-[38px] max-escritorio:pl-3.5 max-escritorio:text-[16px]">
          <button type="submit" aria-label="Buscar" class="absolute top-1/2 right-1 flex size-8 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full border-none bg-transparent text-white transition-colors duration-200 hover:bg-white/12 max-escritorio:size-7">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
          </button>
        </form>

        {{-- Íconos con contador: el script del final cambia style.display de #fav-count y #cart-count --}}
        <div class="flex shrink-0 items-center gap-[22px] max-escritorio:gap-3 [&>a]:relative [&>a]:inline-flex [&>a]:items-center [&>a]:justify-center [&>a]:text-white [&>a]:opacity-90 [&>a]:transition-[opacity,translate] [&>a]:duration-200 [&>a:hover]:-translate-y-px [&>a:hover]:opacity-100 max-escritorio:[&_svg]:size-5">
          <a href="{{ route('favoritos') }}" title="Mis Favoritos" aria-label="Mis Favoritos">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>
            <span id="fav-count" class="absolute -top-1.5 -right-2 h-[17px] min-w-[17px] items-center justify-center rounded-full bg-agotado px-1 text-[10px] leading-none font-bold text-white shadow-[0_0_0_2px_var(--color-carbon)]" style="display: none;">0</span>
          </a>

          <a href="{{ route('carrito') }}" title="Carrito de compras" aria-label="Carrito de compras">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l1 14H5L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
            <span id="cart-count" class="absolute -top-1.5 -right-2 h-[17px] min-w-[17px] items-center justify-center rounded-full bg-agotado px-1 text-[10px] leading-none font-bold text-white shadow-[0_0_0_2px_var(--color-carbon)]" style="display: none;">0</span>
          </a>

          <a href="{{ Auth::check() ? route('perfil') : route('login') }}" title="{{ Auth::check() ? Auth::user()->name : 'Mi cuenta' }}" aria-label="Mi cuenta">
            @if (Auth::user()?->urlAvatar())
              <img src="{{ Auth::user()->urlAvatar() }}" alt="Foto de {{ Auth::user()->name }}" class="block size-[30px] rounded-full border-[1.5px] border-white/85 object-cover max-escritorio:size-[26px]">
            @else
              <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
            @endif
          </a>
        </div>
      </div>
    </header>

    {{-- [FASE 4]: Menú móvil deslizable (public/js/menu-movil.js le agrega la clase "abierto") --}}
    <div id="menu-movil-fondo" class="fixed inset-0 z-900 bg-black/55" hidden></div>
    <nav id="menu-movil" aria-label="Menú principal" aria-hidden="true" class="invisible fixed inset-y-0 left-0 z-1000 flex w-[min(300px,85vw)] -translate-x-full flex-col overflow-y-auto border-r border-white/8 bg-carbon px-5 pt-[18px] pb-7 text-white shadow-[8px_0_30px_rgba(0,0,0,0.45)] transition-[translate,visibility] duration-280 [&.abierto]:visible [&.abierto]:translate-x-0 [&>a]:flex [&>a]:items-center [&>a]:justify-between [&>a]:border-b [&>a]:border-white/6 [&>a]:px-1 [&>a]:py-3 [&>a]:text-[15px] [&>a]:font-semibold [&>a]:text-white [&>a_i]:w-[18px] [&>a_i]:text-[13px] [&>a_i]:text-[#71717a] [&>a>span]:inline-flex [&>a>span]:items-center [&>a>span]:gap-2.5">
      <div class="flex items-center justify-between border-b border-white/8 pb-3.5 text-[16px] font-bold tracking-[1px] uppercase">
        <span>Menú</span>
        <button type="button" id="btn-menu-cerrar" class="size-9 cursor-pointer rounded-full border-none bg-white/8 text-[16px] text-white" aria-label="Cerrar menú"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <p class="mt-[22px] mb-1.5 text-[11px] font-bold tracking-[1.5px] text-[#888] uppercase">Categorías</p>
      <a href="{{ route('catalogo', ['categoria' => 'mujer']) }}">Mujer <i class="fa-solid fa-chevron-right"></i></a>
      <a href="{{ route('catalogo', ['categoria' => 'hombre']) }}">Hombre <i class="fa-solid fa-chevron-right"></i></a>
      <a href="{{ route('catalogo', ['categoria' => 'ninos']) }}">Niños <i class="fa-solid fa-chevron-right"></i></a>
      <a href="{{ route('catalogo', ['categoria' => 'deporte']) }}">Deporte <i class="fa-solid fa-chevron-right"></i></a>

      <p class="mt-[22px] mb-1.5 text-[11px] font-bold tracking-[1.5px] text-[#888] uppercase">Kixa</p>
      <a href="{{ route('nosotros') }}"><span><i class="fa-solid fa-store"></i> Acerca de</span></a>
      <a href="{{ route('nosotros') }}#centro-ayuda"><span><i class="fa-regular fa-circle-question"></i> Centro de ayuda</span></a>
      <a href="{{ Auth::check() ? route('perfil') : route('login') }}"><span><i class="fa-regular fa-user"></i> {{ Auth::check() ? 'Mi Cuenta' : 'Iniciar sesión' }}</span></a>
    </nav>

    {{-- Toast único: public/js/toast.js pinta aquí los flash y errores de validación (esquina superior derecha) --}}
    <div id="kx-toasts" class="pointer-events-none fixed top-[calc(var(--spacing-navbar)_+_16px)] right-5 z-3000 flex w-[min(380px,calc(100vw_-_32px))] flex-col gap-2.5 max-escritorio:top-[120px] max-escritorio:right-4" aria-live="polite"></div>
    <script>
      window.KX_FLASH = @json(['exito' => session('exito'), 'error' => session('error'), 'errores' => $errors->all()]);
    </script>

    <main class="flex-1">
        {{-- Aquí se inserta el @section('contenido') de la página hija. --}}
        @yield('contenido')
    </main>

    {{-- Footer con rutas nombradas y anclas (#ubicanos, #preguntas-frecuentes) --}}
    <footer class="shrink-0 border-t border-white/6 bg-carbon px-[5%] pt-16 pb-6 text-white">
      <div class="m-auto grid max-w-[1200px] grid-cols-[1.4fr_repeat(4,1fr)] gap-10 max-[1000px]:grid-cols-[repeat(2,1fr)] max-[520px]:grid-cols-[1fr] max-[520px]:gap-7">
        <div class="max-[1000px]:col-span-full">
          <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 text-[13.5px] text-[#a1a1aa] transition-colors duration-200 hover:text-white">
            <img src="{{ asset('images/ui/web.jpg') }}" alt="Logo de KixaStore" class="block size-[42px] object-contain [filter:url(#kx-logo-blanco)]">
            <span class="text-[22px] font-bold tracking-[-0.03em] text-white">KixaStore</span>
          </a>
          <p class="mt-4 max-w-[250px] text-[13px] leading-[1.7] text-[#a1a1aa]">Zapatillas originales para hombre, mujer, niños y deporte. Envíos a todo el Perú.</p>
        </div>

        <div>
          <h3 class="mb-[18px] text-[12px] font-bold tracking-[0.14em] text-white uppercase">Buscar tienda</h3>
          <ul>
            <li class="my-2.5">
              <a href="{{ route('nosotros') }}#ubicanos" class="inline-flex items-center gap-2 text-[13.5px] text-[#a1a1aa] transition-colors duration-200 hover:text-white">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                Tiendas cercanas
              </a>
            </li>
          </ul>
        </div>

        <div>
          <h3 class="mb-[18px] text-[12px] font-bold tracking-[0.14em] text-white uppercase">Obtener ayuda</h3>
          {{-- Todos llevan a las Preguntas Frecuentes de "Acerca de" --}}
          <ul class="[&_li]:my-2.5 [&_a]:text-[13.5px] [&_a]:text-[#a1a1aa] [&_a]:transition-colors [&_a]:duration-200 [&_a:hover]:text-white">
            <li><a href="{{ auth()->check() ? route('perfil', 'pedidos') : route('nosotros') . '#preguntas-frecuentes' }}">Estado del pedido</a></li>
            <li><a href="{{ route('nosotros') }}#preguntas-frecuentes">Envío y entrega</a></li>
            <li><a href="{{ route('nosotros') }}#preguntas-frecuentes">Cambios y devoluciones</a></li>
            <li><a href="{{ route('nosotros') }}#preguntas-frecuentes">Opciones de pago</a></li>
            <li><a href="{{ route('nosotros') }}#preguntas-frecuentes">Preguntas frecuentes</a></li>
          </ul>
        </div>

        <div>
          <h3 class="mb-[18px] text-[12px] font-bold tracking-[0.14em] text-white uppercase">Acerca de la tienda</h3>
          <ul>
            <li class="my-2.5"><a href="{{ route('nosotros') }}" class="text-[13.5px] text-[#a1a1aa] transition-colors duration-200 hover:text-white">Acerca de</a></li>
          </ul>
        </div>

        <div>
          <h3 class="mb-[18px] text-[12px] font-bold tracking-[0.14em] text-white uppercase">Síguenos</h3>
          {{-- Redes sociales con el color oficial de cada marca al pasar el cursor --}}
          <div class="flex gap-3 [&>a]:inline-flex [&>a]:size-[42px] [&>a]:items-center [&>a]:justify-center [&>a]:rounded-full [&>a]:border [&>a]:border-white/10 [&>a]:bg-white/4 [&>a]:text-[18px] [&>a]:text-white [&>a]:transition-all [&>a]:duration-300 [&>a:hover]:-translate-y-[3px]">
            <a href="https://www.facebook.com/people/Zapatillas-Kixa/pfbid0246fZ4YBwM2GBdeEQf8FfUFJUdLqgaXgWAMwz5UrPMX37SW7AabDdAATinm152jZCl/" target="_blank" rel="noopener" aria-label="Facebook de KixaStore"
               class="hover:border-[rgba(24,119,242,0.55)] hover:bg-[rgba(24,119,242,0.12)] hover:text-[#1877F2] hover:shadow-[0_8px_24px_-6px_rgba(24,119,242,0.55)]">
              <i class="fab fa-facebook"></i>
            </a>
            <a href="https://www.instagram.com/zapatillas.kixa/" target="_blank" rel="noopener" aria-label="Instagram de KixaStore"
               class="hover:border-[rgba(225,48,108,0.55)] hover:bg-[rgba(225,48,108,0.12)] hover:text-[#E1306C] hover:shadow-[0_8px_24px_-6px_rgba(225,48,108,0.55)]">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="https://www.tiktok.com/@zapatillaskixa" target="_blank" rel="noopener" aria-label="TikTok de KixaStore"
               class="hover:border-[rgba(0,242,254,0.45)] hover:bg-[rgba(255,0,80,0.08)] hover:text-white hover:[text-shadow:-2px_-1px_0_#00f2fe,2px_1px_0_#ff0050] hover:shadow-[-4px_6px_20px_-8px_rgba(0,242,254,0.7),4px_6px_20px_-8px_rgba(255,0,80,0.7)]">
              <i class="fab fa-tiktok"></i>
            </a>
          </div>
        </div>
      </div>

      <div class="mx-auto mt-12 flex max-w-[1200px] flex-wrap justify-between gap-2 border-t border-white/8 pt-5 text-[12px] text-[#71717a] max-[520px]:justify-center max-[520px]:text-center">
        <p>© 2026 - KixaStore. Todos los derechos reservados.</p>
        <p>Sede Central: Av. Cascanueces 2221, Santa Anita, Lima</p>
      </div>
    </footer>

    <script src="{{ asset('js/toast.js') }}"></script>
    <script src="{{ asset('js/movimiento.js') }}"></script>
    <script src="{{ asset('js/menu-movil.js') }}"></script>
    <script>
      function actualizarBadgesHeader() {
        try {
          const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
          const favs = JSON.parse(localStorage.getItem('favoritos')) || [];

          const cartBadge = document.getElementById('cart-count');
          if (cartBadge) {
            if (carrito.length > 0) {
              cartBadge.textContent = carrito.length;
              cartBadge.style.display = 'flex';
            } else {
              cartBadge.style.display = 'none';
            }
          }

          const favBadge = document.getElementById('fav-count');
          if (favBadge) {
            if (favs.length > 0) {
              favBadge.textContent = favs.length;
              favBadge.style.display = 'flex';
            } else {
              favBadge.style.display = 'none';
            }
          }
        } catch (e) {
          console.error("Error sincronizando badges:", e);
        }
      }

      // Header de vidrio del home: al bajar más de 40px adopta el carbón casi opaco
      (() => {
        const navbar = document.getElementById('navbar-principal');
        if (!navbar || !navbar.classList.contains('navbar--glass')) return;
        const alScroll = () => navbar.classList.toggle('navbar--scroll', window.scrollY > 40);
        alScroll();
        window.addEventListener('scroll', alScroll, { passive: true });
      })();

      document.addEventListener('DOMContentLoaded', () => {
        actualizarBadgesHeader();

        const corazonLleno = '<i class="fa-solid fa-heart text-[#ed001b]"></i>';
        const favs = JSON.parse(localStorage.getItem('favoritos')) || [];
        document.querySelectorAll('.btn-fav-card').forEach(btn => {
          const id = btn.dataset.id;
          const sku = btn.dataset.sku;
          if (favs.some(f => f.id == id || f.sku == sku)) {
            btn.innerHTML = corazonLleno;
          }

          btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            let currentFavs = JSON.parse(localStorage.getItem('favoritos')) || [];
            const pId = btn.dataset.id;
            const pSku = btn.dataset.sku || pId;
            const pNombre = btn.dataset.nombre || 'Zapatilla Kixa';
            const pMarca = btn.dataset.marca || 'KIXA';
            const pPrecio = parseFloat(btn.dataset.precio) || 0;
            const pImagen = btn.dataset.imagen || '';

            const idx = currentFavs.findIndex(f => f.id == pId || f.sku == pSku);
            if (idx >= 0) {
              currentFavs.splice(idx, 1);
              btn.innerHTML = '<i class="fa-regular fa-heart"></i>';
            } else {
              currentFavs.push({
                id: pId,
                sku: pSku,
                nombre: pNombre,
                marca: pMarca,
                precio: pPrecio,
                imagen: pImagen
              });
              btn.innerHTML = corazonLleno;
            }

            localStorage.setItem('favoritos', JSON.stringify(currentFavs));
            actualizarBadgesHeader();
          });
        });
      });
      window.addEventListener('storage', actualizarBadgesHeader);
    </script>
    {{-- @stack('scripts') recibe el JavaScript de cada página con @push('scripts'). --}}
    @stack('scripts')
</body>
</html>
