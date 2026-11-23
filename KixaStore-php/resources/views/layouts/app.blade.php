<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- [Semana 8: Rutas y Herencia Blade] Layout maestro del Modo Cliente: las vistas de resources/views/cliente lo heredan con @extends('layouts.app') y rellenan @yield('titulo') y @yield('contenido'). --}}
    <title>@yield('titulo', 'KixaStore')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/ui/favicon-round.png') }}"> 
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Plus Jakarta Sans (400-700) estilo Google Stitch en todo el Modo Cliente --}}
    <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">


    {{-- @stack('styles') recibe el CSS de cada página con @push('styles') (hombre.css, producto.css...) sin cargar todo en todas partes. --}}
    @stack('styles')

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
<body class="{{ request()->routeIs('home') ? 'pagina-home' : '' }}">

    {{-- Filtro SVG: convierte el logo JPG (negro sobre blanco) en blanco con fondo transparente --}}
    <svg width="0" height="0" class="svg-oculto" aria-hidden="true" focusable="false">
      <filter id="kx-logo-blanco" color-interpolation-filters="sRGB">
        <feColorMatrix type="matrix" values="0 0 0 0 1  0 0 0 0 1  0 0 0 0 1  -0.333 -0.333 -0.333 1 0"/>
      </filter>
    </svg>

    {{-- Barra superior con rutas nombradas (Acerca de y cuenta) --}}
    <div class="top-bar">
      <div class="top-links">
        <a href="{{ route('nosotros') }}">Acerca de</a>
        <span aria-hidden="true">|</span>
        {{-- [Semana 8: Rutas y Herencia Blade] @guest y @auth muestran el menú según haya sesión iniciada o no. --}}
        @guest
          <a href="{{ route('login') }}">Iniciar sesión</a>
        @endguest
        @auth
          <a href="{{ route('perfil') }}" title="Mi perfil"><i class="fa-solid fa-user"></i> {{ Auth::user()->name }}</a>
        @endauth
      </div>
    </div>

    {{-- routeIs('home') activa el header Liquid Glass; en el resto es carbón sólido --}}
    <header class="navbar {{ request()->routeIs('home') ? 'navbar--glass' : 'navbar--solido' }}" id="navbar-principal">
      <button type="button" class="btn-menu-movil" id="btn-menu-movil" aria-label="Abrir menú" aria-controls="menu-movil" aria-expanded="false">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>

      {{-- Solo el isotipo a la izquierda (como el Swoosh de Nike) --}}
      <div class="logo">
        <a href="{{ route('home') }}" aria-label="KixaStore - Inicio">
          <img src="{{ asset('images/ui/web.jpg') }}" alt="KixaStore">
        </a>
      </div>

      {{-- Menú centrado en la mitad exacta del header --}}
      <nav class="kixa-nav" aria-label="Categorías">
        <ul class="kixa-menu">
          <li><a href="{{ route('home') }}#lo-destacado" class="kixa-link">Destacados</a></li>
          <li><a href="{{ route('catalogo', ['categoria' => 'hombre']) }}" class="kixa-link {{ request()->route('categoria') === 'hombre' || request('categoria') === 'hombre' ? 'activo' : '' }}">Hombre</a></li>
          <li><a href="{{ route('catalogo', ['categoria' => 'mujer']) }}" class="kixa-link {{ request()->route('categoria') === 'mujer' || request('categoria') === 'mujer' ? 'activo' : '' }}">Mujer</a></li>
          <li><a href="{{ route('catalogo', ['categoria' => 'ninos']) }}" class="kixa-link {{ request()->route('categoria') === 'ninos' || request('categoria') === 'ninos' ? 'activo' : '' }}">Niños</a></li>
          <li><a href="{{ route('catalogo', ['categoria' => 'deporte']) }}" class="kixa-link {{ request()->route('categoria') === 'deporte' || request('categoria') === 'deporte' ? 'activo' : '' }}">Deporte</a></li>
        </ul>
      </nav>

      <div class="navbar-acciones">
      {{-- Buscador GET hacia CatalogoController@index (?buscar=modelo o marca) --}}
      <form action="{{ route('catalogo') }}" method="GET" class="search-bar" role="search">
        <input type="search" name="buscar" placeholder="Buscar" value="{{ request('buscar') }}" aria-label="Buscar zapatillas">
        <button type="submit" aria-label="Buscar">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        </button>
      </form>

      <div class="icons">
        <a href="{{ route('favoritos') }}" title="Mis Favoritos" aria-label="Mis Favoritos" class="header-icon-btn">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>
          <span id="fav-count" class="badge-notif" style="display: none;">0</span>
        </a>

        <a href="{{ route('carrito') }}" title="Carrito de compras" aria-label="Carrito de compras" class="header-icon-btn">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l1 14H5L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
          <span id="cart-count" class="badge-notif" style="display: none;">0</span>
        </a>

        <a href="{{ Auth::check() ? route('perfil') : route('login') }}" title="{{ Auth::check() ? Auth::user()->name : 'Mi cuenta' }}" aria-label="Mi cuenta" class="header-icon-btn">
          @if (Auth::user()?->urlAvatar())
            <img src="{{ Auth::user()->urlAvatar() }}" alt="Foto de {{ Auth::user()->name }}" class="avatar-header">
          @else
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
          @endif
        </a>
      </div>
      </div>
    </header>

    {{-- [FASE 4]: Menú móvil deslizable con categorías y páginas informativas --}}
    <div class="menu-movil-fondo" id="menu-movil-fondo" hidden></div>
    <nav class="menu-movil" id="menu-movil" aria-label="Menú principal" aria-hidden="true">
      <div class="menu-movil-cabecera">
        <span>Menú</span>
        <button type="button" class="btn-menu-cerrar" id="btn-menu-cerrar" aria-label="Cerrar menú"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <p class="menu-movil-titulo">Categorías</p>
      <a href="{{ route('catalogo', ['categoria' => 'mujer']) }}">Mujer <i class="fa-solid fa-chevron-right"></i></a>
      <a href="{{ route('catalogo', ['categoria' => 'hombre']) }}">Hombre <i class="fa-solid fa-chevron-right"></i></a>
      <a href="{{ route('catalogo', ['categoria' => 'ninos']) }}">Niños <i class="fa-solid fa-chevron-right"></i></a>
      <a href="{{ route('catalogo', ['categoria' => 'deporte']) }}">Deporte <i class="fa-solid fa-chevron-right"></i></a>

      <p class="menu-movil-titulo">Kixa</p>
      <a href="{{ route('nosotros') }}"><span><i class="fa-solid fa-store"></i> Acerca de</span></a>
      <a href="{{ route('nosotros') }}#centro-ayuda"><span><i class="fa-regular fa-circle-question"></i> Centro de ayuda</span></a>
      <a href="{{ Auth::check() ? route('perfil') : route('login') }}"><span><i class="fa-regular fa-user"></i> {{ Auth::check() ? 'Mi Cuenta' : 'Iniciar sesión' }}</span></a>
    </nav>

    {{-- Toast único: Flash y errores de validación en el toast de la esquina superior derecha --}}
    <div id="kx-toasts" class="kx-toasts" aria-live="polite"></div>
    <script>
      window.KX_FLASH = @json(['exito' => session('exito'), 'error' => session('error'), 'errores' => $errors->all()]);
    </script>

    <main>
        {{-- Aquí se inserta el @section('contenido') de la página hija. --}}
        @yield('contenido')
    </main>

    {{-- Footer con rutas nombradas y anclas (#ubicanos, #centro-ayuda) --}}
    <footer class="footer">
      <div class="footer-container">
        <div class="footer-column footer-marca">
          <a href="{{ route('home') }}" class="footer-logo">
            <img src="{{ asset('images/ui/web.jpg') }}" alt="Logo de KixaStore">
            <span>KixaStore</span>
          </a>
          <p>Zapatillas originales para hombre, mujer, niños y deporte. Envíos a todo el Perú.</p>
        </div>

        <div class="footer-column">
          <h3>Buscar tienda</h3>
          <ul>
            <li>
              <a href="{{ route('nosotros') }}#ubicanos" class="footer-link-icono">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                Tiendas cercanas
              </a>
            </li>
          </ul>
        </div>

        <div class="footer-column">
          <h3>Obtener ayuda</h3>
          {{-- Todos llevan a las Preguntas Frecuentes de "Acerca de" --}}
          <ul>
            <li><a href="{{ auth()->check() ? route('perfil', 'pedidos') : route('nosotros') . '#preguntas-frecuentes' }}">Estado del pedido</a></li>
            <li><a href="{{ route('nosotros') }}#preguntas-frecuentes">Envío y entrega</a></li>
            <li><a href="{{ route('nosotros') }}#preguntas-frecuentes">Cambios y devoluciones</a></li>
            <li><a href="{{ route('nosotros') }}#preguntas-frecuentes">Opciones de pago</a></li>
            <li><a href="{{ route('nosotros') }}#preguntas-frecuentes">Preguntas frecuentes</a></li>
          </ul>
        </div>

        <div class="footer-column">
          <h3>Acerca de la tienda</h3>
          <ul>
            <li><a href="{{ route('nosotros') }}">Acerca de</a></li>
          </ul>
        </div>

        <div class="footer-column social">
          <h3>Síguenos</h3>
          <div class="social-icons">
            <a href="https://www.facebook.com/people/Zapatillas-Kixa/pfbid0246fZ4YBwM2GBdeEQf8FfUFJUdLqgaXgWAMwz5UrPMX37SW7AabDdAATinm152jZCl/" target="_blank" rel="noopener" class="social-facebook" aria-label="Facebook de KixaStore">
              <i class="fab fa-facebook"></i>
            </a>
            <a href="https://www.instagram.com/zapatillas.kixa/" target="_blank" rel="noopener" class="social-instagram" aria-label="Instagram de KixaStore">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="https://www.tiktok.com/@zapatillaskixa" target="_blank" rel="noopener" class="social-tiktok" aria-label="TikTok de KixaStore">
              <i class="fab fa-tiktok"></i>
            </a>
          </div>

          
        </div>
      </div>

      <div class="footer-bottom">
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

      // Header Liquid Glass del home: al bajar más de 40px adopta el carbón sólido #121215
      (() => {
        const navbar = document.getElementById('navbar-principal');
        if (!navbar || !navbar.classList.contains('navbar--glass')) return;
        const alScroll = () => navbar.classList.toggle('navbar--scroll', window.scrollY > 40);
        alScroll();
        window.addEventListener('scroll', alScroll, { passive: true });
      })();

      document.addEventListener('DOMContentLoaded', () => {
        actualizarBadgesHeader();

        const favs = JSON.parse(localStorage.getItem('favoritos')) || [];
        document.querySelectorAll('.btn-fav-card').forEach(btn => {
          const id = btn.dataset.id;
          const sku = btn.dataset.sku;
          if (favs.some(f => f.id == id || f.sku == sku)) {
            btn.innerHTML = '<i class="fa-solid fa-heart" style="color: #ed001b;"></i>';
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
              btn.innerHTML = '<i class="fa-solid fa-heart" style="color: #ed001b;"></i>';
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