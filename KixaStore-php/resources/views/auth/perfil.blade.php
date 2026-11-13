@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos de cuenta de Tienda-Kixa_2/css/cuenta.css --}}
    <link rel="stylesheet" href="{{ asset('css/cuenta.css') }}">
    <style>
        .alert-success {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid #28a745;
            color: #2ecc71;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
        }
        .alert-error {
            background: rgba(237, 0, 27, 0.15);
            border: 1px solid #ed001b;
            color: #ff4d5e;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }
    </style>
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de Tienda-Kixa_2/cuenta.html (Líneas 127 a 663) --}}
    {{-- [CAMBIO: VISTA DE PERFIL VINCULADA A AUTHCONTROLLER Y TABLA USERS EN MYSQL] --}}
    <main class="cuenta-container">
        <div class="cuenta-wrapper">

            <!-- HEADER -->
            <div class="cuenta-header">
                <div>
                    <span class="mini-titulo">PERFIL</span>
                    <h1>Mi cuenta</h1>
                    <p>Administra tu información personal y credenciales en KixaStore.</p>
                </div>

                <div class="icono-header">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>

            {{-- [CAMBIO: MENSAJE FLASH DE ACTUALIZACION EXITOSA] --}}
            @if (session('exito'))
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i> {{ session('exito') }}
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    @foreach ($errors->all() as $error)
                        <p style="margin: 0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- GRID PRINCIPAL -->
            <div class="cuenta-grid">

                <!-- TARJETA PERFIL (FOTO Y ESTADO) -->
                <section class="perfil-card">
                    <div class="perfil-fondo">
                        <div class="perfil-fondo-icono">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    </div>

                    <div class="perfil-contenido">
                        <!-- FOTO -->
                        <div class="foto-container">
                            <div class="foto-wrapper">
                                <img id="fotoPerfil" src="{{ asset('images/ui/web.jpg') }}" alt="Foto de perfil">
                            </div>
                        </div>

                        <!-- NOMBRE REAL DE MYSQL -->
                        <div class="perfil-texto">
                            <h2 id="nombrePerfil">{{ $usuario->name }}</h2>
                            <p>
                                <i class="fa-regular fa-user"></i>
                                Rol: <strong style="color: #ed001b; text-transform: uppercase;">{{ $usuario->rol ?? 'cliente' }}</strong>
                            </p>
                        </div>

                        <!-- ESTADO -->
                        <div class="estado-cuenta">
                            <span class="punto"></span>
                            Cuenta activa en MySQL
                        </div>
                    </div>
                </section>

                <!-- TARJETA INFORMACIÓN PERSONAL -->
                <section class="informacion-card">
                    <div class="seccion-titulo">
                        <div>
                            <span>INFORMACIÓN PERSONAL</span>
                            <h2>Datos de la cuenta</h2>
                        </div>
                        <i class="fa-solid fa-id-card"></i>
                    </div>

                    {{-- [CAMBIO: FORMULARIO REAL CONECTADO A ROUTE PERFIL.UPDATE CON METODO PUT Y @csrf] --}}
                    <form method="POST" action="{{ route('perfil.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="informacion">
                            <!-- NOMBRE -->
                            <div class="dato">
                                <label class="dato-label" for="nombreUsuario">
                                    <i class="fa-regular fa-user"></i>
                                    Nombre completo
                                </label>
                                <input type="text" id="nombreUsuario" name="name" class="campo" value="{{ old('name', $usuario->name) }}" required>
                            </div>

                            <!-- EMAIL -->
                            <div class="dato">
                                <label class="dato-label" for="emailUsuario">
                                    <i class="fa-regular fa-envelope"></i>
                                    Correo electrónico
                                </label>
                                <input type="email" id="emailUsuario" name="email" class="campo" value="{{ old('email', $usuario->email) }}" required>
                            </div>

                            <!-- NUEVA CONTRASEÑA -->
                            {{-- // [VALIDACION FORMULARIO]: Nueva contrasenia opcional con minimo 8 caracteres y mensaje de error --}}
                            <div class="dato dato-completo">
                                <label class="dato-label" for="password">
                                    <i class="fa-solid fa-key"></i>
                                    Nueva contraseña (Mínimo 8 caracteres, opcional)
                                </label>
                                <input type="password" id="password" name="password" class="campo" minlength="8" placeholder="Mínimo 8 caracteres (dejar en blanco para mantener la contraseña actual)">
                                @error('password')
                                    <span class="error-text" style="color: #ff4d5e; font-size: 12px; display: block; margin-top: 6px; font-weight: 600;">
                                        <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- BOTÓN GUARDAR -->
                        <div class="guardar-container" style="margin-top: 20px;">
                            <button class="btn-guardar" type="submit">
                                <i class="fa-solid fa-check"></i>
                                Guardar cambios
                            </button>
                        </div>
                    </form>

                    <!-- OPCIONES Y ACCIONES -->
                    <div class="acciones" style="margin-top: 30px;">
                        <span class="acciones-titulo">OPCIONES Y GESTIÓN</span>

                        {{-- [CAMBIO: CONDICION PARA ACCESO AL PANEL DE ADMINISTRACION O HISTORIAL DE CLIENTE] --}}
                        @if ($usuario->rol === 'admin')
                            <a href="{{ route('admin.home') }}" class="opcion" style="border: 2px solid #ed001b; background: rgba(237, 0, 27, 0.05);">
                                <span class="opcion-icono" style="background: #ed001b; color: #ffffff;">
                                    <i class="fa-solid fa-gauge-high"></i>
                                </span>
                                <span class="opcion-info">
                                    <strong style="color: #ed001b;">Ir al Panel de Administración</strong>
                                    <small>Gestionar productos, categorías, tallas e inventario</small>
                                </span>
                                <i class="fa-solid fa-chevron-right flecha" style="color: #ed001b;"></i>
                            </a>
                        @else
                            <!-- HISTORIAL DE PEDIDOS DEL CLIENTE -->
                            <div class="pedidos-seccion" style="margin-bottom: 20px;">
                                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; color: #ffffff;">
                                    <i class="fa-solid fa-bag-shopping" style="color: #ed001b;"></i> Mis Pedidos Recientes
                                </h3>
                                @if (isset($pedidos) && $pedidos->isNotEmpty())
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        @foreach ($pedidos as $pedido)
                                            {{-- /* [FIX VISUAL] Tarjeta de pedido en fondo oscuro #18181b y texto legible */ --}}
                                            <div style="background: #18181b; border: 1px solid #27272a; border-radius: 8px; padding: 14px 18px; font-size: 13px; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                                    <span style="color: #ffffff; font-weight: 700; font-size: 14px; letter-spacing: 0.5px;">
                                                        Orden: {{ $pedido->codigo_orden }}
                                                    </span>
                                                    <span style="color: #22c55e; font-weight: 600; font-size: 12px; background: rgba(34, 197, 94, 0.12); padding: 3px 8px; border-radius: 4px;">
                                                        <i class="fa-solid fa-circle-check" style="font-size: 10px; margin-right: 4px;"></i>{{ $pedido->estado }}
                                                    </span>
                                                </div>
                                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                                                    <span style="color: #a1a1aa;">
                                                        <i class="fa-regular fa-calendar" style="margin-right: 5px;"></i>Fecha: {{ $pedido->created_at->format('d/m/Y') }}
                                                    </span>
                                                    <span style="color: #ffffff;">
                                                        Total: <strong style="color: #e60012; font-weight: 700; font-size: 14px;">S/ {{ number_format($pedido->total, 2) }}</strong>
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{-- /* [FIX VISUAL] Estado vacío en tono oscuro */ --}}
                                    <div style="background: #18181b; border: 1px solid #27272a; padding: 16px; border-radius: 8px; font-size: 13px; color: #a1a1aa; text-align: center;">
                                        Aún no has realizado ningún pedido. ¡Explora nuestro catálogo!
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- [CAMBIO: BOTON DE CIERRE DE SESION LIMPIO Y UNICO] --}}
                        <form method="POST" action="{{ route('logout') }}" style="display: block; margin-top: 15px;">
                            @csrf
                            <button class="cerrar-sesion" type="submit" style="width: 100%; text-align: left; background: transparent; border: none; cursor: pointer;">
                                <span class="opcion-icono">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                </span>
                                <span class="opcion-info">
                                    <strong>Cerrar sesión</strong>
                                    <small>Salir de tu cuenta de forma segura</small>
                                </span>
                                <i class="fa-solid fa-chevron-right flecha"></i>
                            </button>
                        </form>
                    </div>
                </section>

            </div>
        </div>
    </main>
@endsection
