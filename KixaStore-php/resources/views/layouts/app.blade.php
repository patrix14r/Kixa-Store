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
</head>
<body>

    <div class="top-bar">
      <div class="top-links">
        <a href="#">Ayuda</a>
        <span>|</span>
        <a href="#">Formulario</a>
        <span>|</span>
        <a href="#">Iniciar sesión</a>
      </div>
    </div>

    <header class="navbar">
      <div class="logo">
        <img src="{{ asset('images/ui/web.jpg') }}" alt="Logo de la tienda">
        <a href="{{ url('/') }}">
          <span>Tienda Kixa</span>
        </a>
      </div>

      <nav class="nav-links main-menu">
        <a href="#">Mujer</a>
        <a href="#">Hombre</a>
        <a href="#">Niños</a>
        <a href="#">Deporte</a>
      </nav>

      <div class="search-bar">
        <input type="text" placeholder="Buscar">
        <button aria-label="Buscar">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </div>

      <div class="icons">
        <a href="#" title="Carrito">
          <i class="fa-solid fa-bag-shopping"></i>
        </a>
        <a href="#" title="Mi cuenta">
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
              <a href="#">
                <i class="fa-solid fa-location-dot"></i>
                Tiendas cercanas
              </a>
            </li>
          </ul>
        </div>

        <div class="footer-column">
          <h3>Obtener ayuda</h3>
          <ul>
            <li><a href="#">Estado del pedido</a></li>
            <li><a href="#">Envío y entrega</a></li>
            <li><a href="#">Cambios y devoluciones</a></li>
            <li><a href="#">Opciones de pago</a></li>
            <li><a href="#">Preguntas frecuentes</a></li>
            <li><a href="#">Contáctanos</a></li>
          </ul>
        </div>

        <div class="footer-column">
          <h3>Acerca de la tienda</h3>
          <ul>
            <li><a href="#">Empleo</a></li>
            <li><a href="#">Nosotros</a></li>
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
</body>
</html>