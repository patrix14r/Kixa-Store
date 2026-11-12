<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Kixa</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">

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
        <a href="{{ route('login') }}">Iniciar sesión</a>
      </div>
    </div>

    <header class="navbar">
      <div class="logo">
        <img src="{{ asset('images/ui/web.jpg') }}" alt="Logo de la tienda">
        <a href="{{ url('/') }}">
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

      <div class="search-bar">
        <input type="text" placeholder="Buscar">
        <button aria-label="Buscar">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </div>

      <div class="icons">
        <a href="{{ route('carrito') }}" title="Carrito">
          <i class="fa-solid fa-bag-shopping"></i>
        </a>
        <a href="{{ route('login') }}" title="Mi cuenta">
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
    @stack('scripts')
</body>
</html>