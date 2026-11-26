<!DOCTYPE html>
<html lang="es"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- [Semana 8: Rutas y Herencia Blade] Layout maestro del admin: cada pantalla hija lo hereda con @extends('layouts.admin') y rellena @yield('titulo') y @yield('contenido'). --}}
    <title>@yield('titulo', 'Kixa Admin')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/ui/favicon-32x32.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <!-- esto es para iconos -->
    {{-- @vite carga el Tailwind compilado (resources/css/app.css) y resources/js/app.js; el mismo Tailwind lo usa también el Modo Cliente (layouts/app.blade.php). --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#09090b] text-zinc-100 antialiased font-sans">
<div class="flex min-h-screen">

    {{-- [FASE 2 - Paso 1]: Sidebar vertical fijo (estilo Workly/TailAdmin); en móvil se abre con el botón del header --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-zinc-800/80 bg-[#09090b] py-4 transition-transform lg:translate-x-0">

        {{-- [FASE 2 - Paso 2]: Logo real de la tienda --}}
        <a href="{{ route('admin.home') }}" class="mx-4 flex items-center gap-3 border-b border-zinc-800/80 pb-4">
            <img src="{{ asset('images/ui/web.jpg') }}" alt="Logo Kixa" loading="eager" class="h-9 w-9 rounded-xl object-cover">
            <span class="text-lg font-bold">Kixa Admin</span>
        </a>

        {{-- El enlace activo tiene márgenes laterales (mx-3) y no toca la línea divisoria --}}
        <p class="px-6 pt-5 pb-1 text-[11px] font-semibold tracking-widest kx-muted">MENÚ</p>
        <nav class="flex-1 space-y-1 overflow-y-auto text-sm">
            {{-- [Semana 8: Rutas y Herencia Blade] $menuPrincipal y $menuOperaciones los entrega AppServiceProvider con un View Composer, así Blade solo recorre con @foreach y marca el enlace activo. --}}
            @foreach ($menuPrincipal as $item)
                <a href="{{ $item['ruta'] }}"
                   class="mx-3 flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition {{ $item['activo'] ? 'bg-indigo-500/15 ring-1 ring-indigo-500/30 shadow-[0_0_10px_rgba(99,102,241,0.18)] font-semibold text-white' : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}">
                    @if ($item['icono'] === 'svg-paquete')
                        {{-- SVG de caja de producto con trazo uniforme 1.75 --}}
                        <svg viewBox="0 0 24 24" class="h-4 w-4 {{ $item['activo'] ? 'text-indigo-400' : '' }}" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8 12 3 3 8v8l9 5 9-5V8z"/><path d="m3 8 9 5 9-5M12 13v8M7.5 5.5l9 5"/></svg>
                    @else
                        <i class="{{ $item['icono'] }} w-4 text-center {{ $item['activo'] ? 'text-indigo-400' : '' }}"></i>
                    @endif{{ $item['texto'] }}
                </a>
            @endforeach

            {{-- [FASE 2 - Paso 5]: Sección OPERACIONES --}}
            <p class="px-6 pt-5 pb-1 text-[11px] font-semibold tracking-widest kx-muted">OPERACIONES</p>
            @foreach ($menuOperaciones as $item)
                <a href="{{ $item['ruta'] }}"
                   class="mx-3 flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition {{ $item['activo'] ? 'bg-indigo-500/15 ring-1 ring-indigo-500/30 shadow-[0_0_10px_rgba(99,102,241,0.18)] font-semibold text-white' : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}">
                    @if ($item['icono'] === 'svg-paquete')
                        {{-- SVG de caja de producto con trazo uniforme 1.75 --}}
                        <svg viewBox="0 0 24 24" class="h-4 w-4 {{ $item['activo'] ? 'text-indigo-400' : '' }}" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8 12 3 3 8v8l9 5 9-5V8z"/><path d="m3 8 9 5 9-5M12 13v8M7.5 5.5l9 5"/></svg>
                    @else
                        <i class="{{ $item['icono'] }} w-4 text-center {{ $item['activo'] ? 'text-indigo-400' : '' }}"></i>
                    @endif{{ $item['texto'] }}
                </a>
            @endforeach
        </nav>

        {{-- [FASE 2 - Paso 6]: Acceso rápido a la tienda pública --}}
        <a href="{{ route('home') }}" class="kx-btn-ghost mx-4 mt-3"><i class="fa-solid fa-store"></i>Ver Tienda</a>
    </aside>
    <div id="sidebar-fondo" class="fixed inset-0 z-30 hidden bg-black/60 lg:hidden"></div>

    <div class="flex min-w-0 flex-1 flex-col lg:ml-64">

        {{-- [FASE 3 - Paso 2]: Barra superior (Topbar) estilo TailAdmin --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-zinc-800/80 bg-[#09090b]/90 px-4 backdrop-blur md:px-6">
            <button type="button" id="btn-sidebar" class="kx-icon-btn lg:hidden" aria-label="Abrir menú"><i class="fa-solid fa-bars"></i></button>

            {{-- [Semana 9-10: Controladores, Request y Componentes] El buscador global es un <form method="GET"> hacia admin.productos.index; ProductoController lo lee con $request->query('buscar'). --}}
            {{-- [FASE 3 - Paso 3]: Buscador global; envía ?buscar= al listado de productos. ⌘K / Ctrl+K lo enfoca --}}
            <form action="{{ route('admin.productos.index') }}" method="GET" class="flex-1 max-w-lg">
                <label class="flex items-center gap-2 rounded-xl border border-zinc-800 bg-[#121215] px-3 py-2 text-sm transition focus-within:border-indigo-500 focus-within:shadow-[0_0_12px_rgba(99,102,241,0.45)]">
                    <i class="fa-solid fa-magnifying-glass text-xs kx-muted"></i>
                    <input type="search" name="buscar" id="buscador-global" value="{{ request('buscar') }}" placeholder="Buscar productos, SKU o categoría..." class="w-full bg-transparent outline-none placeholder:text-zinc-500">
                    <kbd class="hidden rounded border border-zinc-700 px-1.5 py-0.5 text-[10px] kx-muted sm:inline">⌘K</kbd> 
                </label>
            </form>

            <div class="ml-auto flex items-center gap-2">
                {{-- [FASE 3 - Paso 5]: Campanita con alertas reales de stock y últimos pedidos --}}
                <div class="relative" data-dropdown>
                    <button type="button" class="kx-icon-btn relative" data-dropdown-boton aria-label="Notificaciones">
                        <i class="fa-regular fa-bell"></i>
                        @if ($notificaciones['total'] > 0)
                            <span class="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-indigo-500 px-1 text-[10px] font-bold text-white">{{ $notificaciones['total'] }}</span>
                        @endif
                    </button>
                    {{-- Panel anclado justo debajo de la campana (absolute right-0 top-full) --}}
                    <div class="kx-card absolute right-0 top-full z-50 mt-2 hidden w-80 overflow-hidden shadow-2xl shadow-black/50" data-dropdown-menu>
                        <p class="border-b border-zinc-800/80 px-4 py-3 text-sm font-semibold">Notificaciones</p>
                        <div class="max-h-80 overflow-y-auto text-sm">
                            @if ($notificaciones['agotadas'] > 0)
                                <a href="{{ route('admin.productos.index', ['stock' => 'critico']) }}" class="flex gap-3 px-4 py-3 hover:bg-white/5">
                                    <span class="kx-badge-agotado h-8 w-8 justify-center"><i class="fa-solid fa-ban"></i></span>
                                    <span><strong>{{ $notificaciones['agotadas'] }} tallas agotadas</strong><br><span class="text-xs kx-muted">Sin stock disponible</span></span>
                                </a>
                            @endif
                            @if ($notificaciones['criticas'] > 0)
                                <a href="{{ route('admin.productos.index', ['stock' => 'critico']) }}" class="flex gap-3 px-4 py-3 hover:bg-white/5">
                                    <span class="kx-badge-bajo h-8 w-8 justify-center"><i class="fa-solid fa-triangle-exclamation"></i></span>
                                    <span><strong>{{ $notificaciones['criticas'] }} tallas en stock crítico</strong><br><span class="text-xs kx-muted">1 o 2 pares restantes</span></span>
                                </a>
                            @endif
                            @foreach ($notificaciones['pedidos'] as $pedido)
                                <a href="{{ route('admin.pedidos.index') }}" class="flex gap-3 px-4 py-3 hover:bg-white/5">
                                    <span class="kx-badge-ok h-8 w-8 justify-center"><i class="fa-solid fa-receipt"></i></span>
                                    <span><strong>Pedido {{ $pedido->codigo_orden }}</strong> · S/ {{ number_format($pedido->total, 2) }}<br><span class="text-xs kx-muted">{{ $pedido->usuario?->name }} · {{ $pedido->created_at?->diffForHumans() }}</span></span>
                                </a>
                            @endforeach
                            @if ($notificaciones['total'] === 0)
                                <p class="px-4 py-6 text-center kx-muted">Sin notificaciones nuevas.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Desplegable con Mi Perfil, Ver Tienda Pública y Cerrar Sesión (POST logout) --}}
                <div class="relative" data-dropdown>
                    <button type="button" class="flex items-center gap-2 rounded-xl px-2 py-1 transition hover:bg-white/5" data-dropdown-boton>
                        @if (Auth::user()->urlAvatar())
                            <img src="{{ Auth::user()->urlAvatar() }}" alt="Foto de perfil" class="h-9 w-9 rounded-full object-cover">
                        @else
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-600 font-bold text-white">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        @endif
                        <span class="hidden text-left md:block">
                            <span class="block text-sm font-semibold leading-tight">{{ Auth::user()->name }}</span>
                            <span class="block text-xs capitalize kx-muted">{{ Auth::user()->nombreRol() }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-down text-xs kx-muted"></i>
                    </button>
                    <div class="kx-card absolute right-0 top-full z-50 mt-2 hidden w-60 p-2 shadow-2xl shadow-black/50" data-dropdown-menu>
                        <div class="px-3 py-2">
                            <p class="truncate text-sm font-semibold">{{ Auth::user()->name }}</p>
                            <p class="truncate text-xs kx-muted">{{ Auth::user()->email }}</p>
                        </div>
                        <div class="my-1 border-t border-zinc-800/80"></div>
                        <a href="{{ route('admin.perfil') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm hover:bg-white/5"><i class="fa-regular fa-user w-4"></i>Mi Perfil</a>
                        <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm hover:bg-white/5"><i class="fa-solid fa-store w-4"></i>Ver Tienda Pública</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm text-red-400 hover:bg-red-500/10"><i class="fa-solid fa-right-from-bracket w-4"></i>Cerrar Sesión</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Contenido de cada pantalla con @yield --}}
        <main class="flex-1 p-4 md:p-6 lg:p-8">
            @yield('contenido')
        </main>
    </div>
</div>
{{-- Mensajes flash como aviso flotante discreto arriba a la derecha (se cierra solo) --}}
{{-- [Semana 9-10: Controladores, Request y Componentes] session('exito') y session('error') son mensajes flash: los controladores los envían con redirect()->with() y duran una sola petición. --}}
@if (session('exito') || session('error'))
    <div class="fixed top-20 right-4 z-50 flex max-w-sm items-center gap-3 rounded-xl border border-zinc-800 bg-[#121215]/95 px-4 py-3 text-sm shadow-2xl shadow-black/50 backdrop-blur transition duration-300" data-toast>
        <i class="fa-solid {{ session('error') ? 'fa-circle-exclamation text-red-400' : 'fa-circle-check text-emerald-400' }}"></i>
        <span class="flex-1">{{ session('error') ?? session('exito') }}</span>
        <button type="button" class="kx-muted hover:text-white" data-toast-cerrar aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    </div>
@endif

@stack('scripts')
</body>
</html>
