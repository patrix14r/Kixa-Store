<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- [CAMBIO: META TOKEN CSRF PARA PETICIONES AJAX DESDE JS] --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tienda Kixa</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">

    {{-- [CAMBIO: ESTILOS GLOBALES PARA BADGES DE NOTIFICACION EN ICONOS DEL HEADER] --}}
    <style>
      .header-icon-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: inherit;
        text-decoration: none;
      }
      .badge-notif {
        position: absolute;
        top: -6px;
        right: -8px;
        background-color: #ed001b;
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        min-width: 17px;
        height: 17px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        box-shadow: 0 0 6px rgba(237, 0, 27, 0.5);
      }
    </style>

    @stack('styles')
</head>
<body>

    <!-- BARRA SUPERIOR (TOP-BAR) -->
    <div class="top-bar">
      <div class="top-links">
        <a href="{{ route('ayuda') }}">Ayuda</a>
        <span>|</span>
        <a href="{{ route('contacto') }}">Contáctanos</a>
        <span>|</span>
        {{-- [CAMBIO: ESTADO DE AUTENTICACION CON @guest Y @auth EN TOP-BAR] --}}
        @guest
          <a href="{{ route('login') }}">INICIAR SESIÓN</a>
        @endguest
        @auth
          @if (Auth::user()->rol === 'admin')
            <a href="{{ route('admin.home') }}" style="color: #ed001b; font-weight: 700;">PANEL ADMIN</a>
            <span>|</span>
          @endif
          <a href="{{ route('perfil') }}"><i class="fa-solid fa-user"></i> {{ Auth::user()->name }}</a>
          <span>|</span>
          {{-- /* [FIX VISUAL] Botón cerrar sesión con estilo armónico en la top-bar */ --}}
          <form action="{{ route('logout') }}" method="POST" style="display: inline-flex; align-items: center; margin: 0; padding: 0;">
            @csrf
            <button type="submit" style="font-size: 11px; font-weight: 600; letter-spacing: 0.5px; color: #ff4d4d; background: transparent; border: none; padding: 0; cursor: pointer; text-transform: uppercase; display: inline;">
              CERRAR SESIÓN
            </button>
          </form>
        @endauth
      </div>
    </div>

    <header class="navbar">
      {{-- [CAMBIO: LOGO SIEMPRE VISIBLE Y CLICKEABLE HACIA HOME] --}}
      <div class="logo">
        <a href="{{ route('home') }}" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit;">
          <img src="{{ asset('images/ui/web.jpg') }}" alt="Logo de la tienda">
          <span>Tienda Kixa</span>
        </a>
      </div>

      <nav class="kixa-nav">
        <ul class="kixa-menu">
        <!-- MUJER -->
        <li class="kixa-dropdown">
            <a href="{{ route('catalogo', ['categoria' => 'mujer']) }}" class="kixa-link">MUJER</a>
            <div class="mega-menu">
                <div class="mega-columna">
                    <h4>Calzado</h4>
                    <a href="{{ route('catalogo', ['categoria' => 'mujer', 'sub' => 'urbano']) }}">Zapatillas Urbanas</a>
                    <a href="{{ route('catalogo', ['categoria' => 'mujer', 'sub' => 'running']) }}">Running</a>
                    <a href="{{ route('catalogo', ['categoria' => 'mujer', 'sub' => 'gym']) }}">Entrenamiento & Gym</a>
                    <a href="{{ route('catalogo', ['categoria' => 'mujer', 'sub' => 'basquet']) }}">Básquet</a>
                </div>
                <div class="mega-columna">
                    <h4>Colecciones</h4>
                    <a href="{{ route('catalogo', ['categoria' => 'mujer', 'coleccion' => 'retro']) }}">Clásicos Retro</a>
                    <a href="{{ route('catalogo', ['categoria' => 'mujer', 'coleccion' => 'tendencia']) }}">Tendencias 2026</a>
                    <a href="{{ route('catalogo', ['categoria' => 'mujer', 'oferta' => 1]) }}" class="link-oferta">Ver Ofertas</a>
                </div>
            </div>
        </li>

        <!-- HOMBRE -->
        <li class="kixa-dropdown">
            <a href="{{ route('catalogo', ['categoria' => 'hombre']) }}" class="kixa-link">HOMBRE</a>
            <div class="mega-menu">
                <div class="mega-columna">
                    <h4>Calzado</h4>
                    <a href="{{ route('catalogo', ['categoria' => 'hombre', 'sub' => 'urbano']) }}">Zapatillas Urbanas</a>
                    <a href="{{ route('catalogo', ['categoria' => 'hombre', 'sub' => 'futbol']) }}">Fútbol & Chimpunes</a>
                    <a href="{{ route('catalogo', ['categoria' => 'hombre', 'sub' => 'running']) }}">Running</a>
                    <a href="{{ route('catalogo', ['categoria' => 'hombre', 'sub' => 'basquet']) }}">Básquet & Jordan</a>
                </div>
                <div class="mega-columna">
                    <h4>Colecciones</h4>
                    <a href="{{ route('catalogo', ['categoria' => 'hombre', 'coleccion' => 'edicion-limitada']) }}">Edición Limitada</a>
                    <a href="{{ route('catalogo', ['categoria' => 'hombre', 'coleccion' => 'streetwear']) }}">Streetwear Kixa</a>
                    <a href="{{ route('catalogo', ['categoria' => 'hombre', 'oferta' => 1]) }}" class="link-oferta">Liquidación</a>
                </div>
            </div>
        </li>

        <!-- NIÑOS -->
        <li class="kixa-dropdown">
            <a href="{{ route('catalogo', ['categoria' => 'ninos']) }}" class="kixa-link">NIÑOS</a>
            <div class="mega-menu">
                <div class="mega-columna">
                    <h4>Por Estilo</h4>
                    <a href="{{ route('catalogo', ['categoria' => 'ninos', 'sub' => 'escolar']) }}">Escolares</a>
                    <a href="{{ route('catalogo', ['categoria' => 'ninos', 'sub' => 'urbano']) }}">Urbanas y Paseo</a>
                    <a href="{{ route('catalogo', ['categoria' => 'ninos', 'sub' => 'deportivo']) }}">Deportivas</a>
                </div>
                <div class="mega-columna">
                    <h4>Colecciones</h4>
                    <a href="{{ route('catalogo', ['categoria' => 'ninos', 'coleccion' => 'facil-ajuste']) }}">Fácil Ajuste (Velcro)</a>
                    <a href="{{ route('catalogo', ['categoria' => 'ninos', 'coleccion' => 'mini-jordan']) }}">Mini Clásicos</a>
                </div>
            </div>
        </li>

        <!-- DEPORTE -->
        <li class="kixa-dropdown">
            <a href="{{ route('catalogo', ['categoria' => 'deporte']) }}" class="kixa-link">DEPORTE</a>
            <div class="mega-menu">
                <div class="mega-columna">
                    <h4>Disciplinas</h4>
                    <a href="{{ route('catalogo', ['categoria' => 'deporte', 'sub' => 'running']) }}">Running Pro</a>
                    <a href="{{ route('catalogo', ['categoria' => 'deporte', 'sub' => 'futbol']) }}">Fútbol / Futsal</a>
                    <a href="{{ route('catalogo', ['categoria' => 'deporte', 'sub' => 'basquet']) }}">Básquetbol</a>
                    <a href="{{ route('catalogo', ['categoria' => 'deporte', 'sub' => 'training']) }}">Crossfit & Gym</a>
                </div>
                <div class="mega-columna">
                    <h4>Por Marca Deportiva</h4>
                    <a href="{{ route('catalogo', ['marca' => 'Nike']) }}">Nike Sport</a>
                    <a href="{{ route('catalogo', ['marca' => 'Adidas']) }}">Adidas Performance</a>
                    <a href="{{ route('catalogo', ['marca' => 'Puma']) }}">Puma Faster</a>
                    <a href="{{ route('catalogo', ['marca' => 'Umbro']) }}">Umbro Fútbol</a>
                </div>
            </div>
        </li>
    </ul>
</nav>

      {{-- [CAMBIO: FORMULARIO DE BUSQUEDA EN TIEMPO REAL HACIA LA RUTA CATALOGO] --}}
      <form action="{{ route('catalogo') }}" method="GET" class="search-bar">
        <input type="text" name="buscar" placeholder="Buscar zapatillas..." value="{{ request('buscar') }}">
        <button type="submit" aria-label="Buscar">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </form>

      {{-- [CAMBIO: ICONOS DE HEADER CON NAVEGACION A FAVORITOS, CARRITO Y PERFIL CON CONTADORES] --}}
      <div class="icons">
        <a href="{{ route('favoritos') }}" title="Mis Favoritos" class="header-icon-btn">
          <i class="fa-solid fa-heart"></i>
          <span id="fav-count" class="badge-notif" style="display: none;">0</span>
        </a>

        <a href="{{ route('carrito') }}" title="Carrito de compras" class="header-icon-btn">
          <i class="fa-solid fa-bag-shopping"></i>
          <span id="cart-count" class="badge-notif" style="display: none;">0</span>
        </a>

        {{-- [CAMBIO: ICONO DE USUARIO REDIRIGE A PERFIL SI ESTA LOGUEADO O A LOGIN SI ES VISITANTE] --}}
        <a href="{{ Auth::check() ? route('perfil') : route('login') }}" title="{{ Auth::check() ? Auth::user()->name : 'Mi cuenta' }}" class="header-icon-btn">
          <i class="fa-solid fa-user"></i>
        </a>
      </div>
    </header>

    <main>
        @yield('contenido')
    </main>

    <footer class="footer">
      <div class="footer-container">
        <div class="footer-column">
          <h3>Buscar tienda</h3>
          <ul>
            <li>
              <a href="{{ route('contacto') }}">
                <i class="fa-solid fa-location-dot"></i>
                Tiendas cercanas
              </a>
            </li>
          </ul>
        </div>
        <div class="footer-column">
          <h3>Obtener ayuda</h3>
          <ul>
            <li><a href="{{ route('ayuda') }}">Estado del pedido</a></li>
            <li><a href="{{ route('ayuda') }}">Envío y entrega</a></li>
            <li><a href="{{ route('ayuda') }}">Cambios y devoluciones</a></li>
            <li><a href="{{ route('ayuda') }}">Opciones de pago</a></li>
            <li><a href="{{ route('ayuda') }}">Preguntas frecuentes</a></li>
            <li><a href="{{ route('contacto') }}">Contáctanos</a></li>
          </ul>
        </div>

        <div class="footer-column">
          <h3>Acerca de la tienda</h3>
          <ul>
            <li><a href="{{ route('ayuda') }}">Empleo</a></li>
            <li><a href="{{ route('ayuda') }}">Nosotros</a></li>
          </ul>
        </div>

        <div class="footer-column social">
          <h3>Síguenos</h3>
          <div class="social-icons">
            <a href="https://www.facebook.com/people/Zapatillas-Kixa/pfbid0246fZ4YBwM2GBdeEQf8FfUFJUdLqgaXgWAMwz5UrPMX37SW7AabDdAATinm152jZCl/" target="_blank">
              <i class="fab fa-facebook"></i>
            </a>
            <a href="https://www.instagram.com/zapatillas.kixa/" target="_blank">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="https://www.tiktok.com/@zapatillaskixa" target="_blank">
              <i class="fab fa-tiktok"></i>
            </a>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>© 2026 - Tienda Kixa. Todos los derechos reservados.</p>
      </div>
    </footer>

    <script src="{{ asset('js/movimiento.js') }}"></script>
    {{-- [CAMBIO: SCRIPT GLOBAL DE SINCRONIZACION DE BADGES DE CARRITO Y FAVORITOS] --}}
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

      document.addEventListener('DOMContentLoaded', () => {
        actualizarBadgesHeader();

        // [CAMBIO: GESTION GLOBAL DE BOTONES DE FAVORITO EN TARJETAS DE PRODUCTO]
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
    @stack('scripts')
</body>
</html>