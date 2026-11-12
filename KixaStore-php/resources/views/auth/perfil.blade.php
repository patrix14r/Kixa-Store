@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos de cuenta de Tienda-Kixa_2/css/cuenta.css --}}
    <link rel="stylesheet" href="{{ asset('css/cuenta.css') }}">
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de Tienda-Kixa_2/cuenta.html (Líneas 127 a 663) --}}
    {{-- [ADAPTADO]: Se adaptó a layouts.app eliminando header/footer y configurando enlaces y scripts de Laravel --}}
    <main class="cuenta-container">
        <div class="cuenta-wrapper">

            <!-- HEADER -->
            <div class="cuenta-header">
                <div>
                    <span class="mini-titulo">PERFIL</span>
                    <h1>Mi cuenta</h1>
                    <p>Administra tu información personal y preferencias.</p>
                </div>

                <div class="icono-header">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>

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
                                <label for="inputFoto" class="icono-camara" title="Cambiar foto">
                                    <i class="fa-solid fa-camera"></i>
                                </label>
                            </div>
                            <input type="file" id="inputFoto" accept="image/*">
                        </div>

                        <!-- NOMBRE -->
                        <div class="perfil-texto">
                            <h2 id="nombrePerfil">Mi perfil</h2>
                            <p>
                                <i class="fa-regular fa-user"></i>
                                Usuario Kixa
                            </p>
                        </div>

                        <!-- BOTÓN CAMBIAR FOTO -->
                        <label for="inputFoto" class="btn-foto">
                            <i class="fa-solid fa-camera"></i>
                            Cambiar foto
                        </label>

                        <!-- ESTADO -->
                        <div class="estado-cuenta">
                            <span class="punto"></span>
                            Cuenta activa
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

                    <div class="informacion">
                        <!-- NOMBRE -->
                        <div class="dato">
                            <label class="dato-label" for="nombreUsuario">
                                <i class="fa-regular fa-user"></i>
                                Nombre completo
                            </label>
                            <input type="text" id="nombreUsuario" class="campo" placeholder="Ingresa tu nombre completo">
                        </div>

                        <!-- EMAIL -->
                        <div class="dato">
                            <label class="dato-label">
                                <i class="fa-regular fa-envelope"></i>
                                Correo electrónico
                            </label>
                            <div class="email-box">
                                <p id="emailUsuario">-</p>
                                <span class="verificado">
                                    <i class="fa-solid fa-circle-check"></i>
                                    Cuenta activa
                                </span>
                            </div>
                        </div>

                        <!-- TELEFONO -->
                        <div class="dato">
                            <label class="dato-label" for="telefonoUsuario">
                                <i class="fa-solid fa-phone"></i>
                                Número de teléfono
                            </label>
                            <input type="tel" id="telefonoUsuario" class="campo" placeholder="Ej. 999 999 999">
                        </div>

                        <!-- DNI -->
                        <div class="dato">
                            <label class="dato-label" for="dniUsuario">
                                <i class="fa-solid fa-id-card"></i>
                                DNI
                            </label>
                            <input type="text" id="dniUsuario" class="campo" maxlength="8" placeholder="Ingresa tu DNI">
                        </div>

                        <!-- CIUDAD -->
                        <div class="dato">
                            <label class="dato-label" for="ciudadUsuario">
                                <i class="fa-solid fa-location-dot"></i>
                                Ciudad
                            </label>
                            <select id="ciudadUsuario" class="campo">
                                <option value="">Selecciona tu ciudad</option>
                                <option value="Lima">Lima</option>
                                <option value="Arequipa">Arequipa</option>
                                <option value="Trujillo">Trujillo</option>
                                <option value="Chiclayo">Chiclayo</option>
                                <option value="Piura">Piura</option>
                                <option value="Cusco">Cusco</option>
                                <option value="Iquitos">Iquitos</option>
                                <option value="Huancayo">Huancayo</option>
                                <option value="Tacna">Tacna</option>
                                <option value="Otra">Otra ciudad</option>
                            </select>
                        </div>

                        <!-- DIRECCION -->
                        <div class="dato dato-completo">
                            <label class="dato-label" for="direccionUsuario">
                                <i class="fa-solid fa-house"></i>
                                Dirección
                            </label>
                            <input type="text" id="direccionUsuario" class="campo" placeholder="Ej. Av. Los Olivos 123">
                        </div>

                        <!-- FECHA -->
                        <div class="dato">
                            <label class="dato-label" for="fechaNacimiento">
                                <i class="fa-regular fa-calendar"></i>
                                Fecha de nacimiento
                            </label>
                            <input type="date" id="fechaNacimiento" class="campo">
                        </div>

                        <!-- GENERO -->
                        <div class="dato">
                            <label class="dato-label" for="generoUsuario">
                                <i class="fa-solid fa-users"></i>
                                Género
                            </label>
                            <select id="generoUsuario" class="campo">
                                <option value="">Selecciona una opción</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Prefiero no decirlo">Prefiero no decirlo</option>
                            </select>
                        </div>
                    </div>

                    <!-- BOTÓN GUARDAR -->
                    <div class="guardar-container">
                        <button id="guardarNombre" class="btn-guardar" type="button">
                            <i class="fa-solid fa-check"></i>
                            Guardar cambios
                        </button>
                    </div>

                    <!-- OPCIONES Y ACCIONES -->
                    <div class="acciones">
                        <span class="acciones-titulo">OPCIONES</span>

                        <!-- MIS PEDIDOS -->
                        <a href="{{ route('carrito') }}" class="opcion">
                            <span class="opcion-icono">
                                <i class="fa-solid fa-bag-shopping"></i>
                            </span>
                            <span class="opcion-info">
                                <strong>Mis pedidos</strong>
                                <small>Revisa tus compras</small>
                            </span>
                            <i class="fa-solid fa-chevron-right flecha"></i>
                        </a>

                        <!-- CERRAR SESIÓN -->
                        <button id="cerrarSesion" class="cerrar-sesion" type="button">
                            <span class="opcion-icono">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            </span>
                            <span class="opcion-info">
                                <strong>Cerrar sesión</strong>
                                <small>Salir de tu cuenta</small>
                            </span>
                            <i class="fa-solid fa-chevron-right flecha"></i>
                        </button>
                    </div>
                </section>

            </div>
        </div>
    </main>
@endsection

@push('scripts')
    {{-- [ADAPTADO]: Lógica de perfil, carga y guardado local en public/js/cuenta.js --}}
    <script src="{{ asset('js/cuenta.js') }}"></script>
@endpush
