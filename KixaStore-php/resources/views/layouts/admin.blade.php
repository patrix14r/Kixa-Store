<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KixaStore - Panel Administrador</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #f4f6f9;
            color: #333333;
        }

        /* SIDEBAR */
        .admin-sidebar {
            width: 260px;
            background-color: #111111;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }

        .admin-logo {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #222222;
        }

        .admin-logo img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }

        .admin-logo span {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #ffffff;
        }

        .admin-logo span em {
            color: #ed001b;
            font-style: normal;
        }

        .admin-menu {
            list-style: none;
            padding: 20px 0;
            flex: 1;
            overflow-y: auto;
        }

        .admin-menu-header {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #777777;
            padding: 10px 24px;
            font-weight: 700;
        }

        .admin-menu li a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 24px;
            color: #cccccc;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .admin-menu li a:hover,
        .admin-menu li a.active {
            color: #ffffff;
            background-color: #1f1f1f;
            border-left: 4px solid #ed001b;
        }

        .admin-menu li a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .admin-footer-actions {
            padding: 20px;
            border-top: 1px solid #222222;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-sidebar-secondary {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
            background-color: #222222;
            padding: 10px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .btn-sidebar-secondary:hover {
            background-color: #333333;
        }

        .btn-logout {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: rgba(237, 0, 27, 0.15);
            color: #ff4d5e;
            border: 1px solid rgba(237, 0, 27, 0.3);
            padding: 10px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background-color: #ed001b;
            color: #ffffff;
        }

        /* CONTENIDO PRINCIPAL */
        .admin-main {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .admin-topbar {
            background-color: #ffffff;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-bottom: 1px solid #e5e7eb;
        }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 600;
        }

        .admin-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: #ed001b;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .admin-content {
            padding: 30px;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- [BARRA LATERAL EXCLUSIVA ADMIN] -->
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <img src="{{ asset('images/ui/web.jpg') }}" alt="Logo Kixa">
            <span>KIXA <em>ADMIN</em></span>
        </div>

        <ul class="admin-menu">
            <li class="admin-menu-header">General</li>
            <li>
                <a href="{{ route('admin.home') }}" class="{{ request()->routeIs('admin.home') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="admin-menu-header">Gestión de Catálogo</li>
            <li>
                <a href="{{ route('catalogo') }}">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Productos</span>
                </a>
            </li>
            <li>
                <a href="{{ route('catalogo') }}">
                    <i class="fa-solid fa-tags"></i>
                    <span>Categorías</span>
                </a>
            </li>
            <li>
                <a href="{{ route('catalogo') }}">
                    <i class="fa-solid fa-truck-field"></i>
                    <span>Proveedores</span>
                </a>
            </li>

            <li class="admin-menu-header">Operaciones</li>
            <li>
                <a href="{{ route('admin.home') }}">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Stock & Tallas</span>
                </a>
            </li>
        </ul>

        <div class="admin-footer-actions">
            <a href="{{ route('home') }}" class="btn-sidebar-secondary" target="_blank">
                <i class="fa-solid fa-store"></i> Ver Tienda
            </a>
            
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    <!-- [CONTENEDOR DE CONTENIDO PRINCIPAL] -->
    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <strong>Modo Administrador</strong>
            </div>
            <div class="admin-user-info">
                <span>{{ Auth::user()->name ?? 'Administrador' }}</span>
                <div class="admin-user-avatar">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
            </div>
        </header>

        <main class="admin-content">
            @yield('contenido')
        </main>
    </div>

    @stack('scripts')
</body>
</html>

