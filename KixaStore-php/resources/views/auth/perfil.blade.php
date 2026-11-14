@extends('layouts.app')

@section('titulo', 'Mi cuenta | KixaStore')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
@endpush

@section('contenido')
    {{-- [Actualizado: Perfil estilo Puma] 2 columnas: sidebar de cuenta + contenido de la pestaña elegida (ruta /perfil/{seccion?}) --}}
    <div class="kx-cuenta">
        <aside class="kx-cuenta-menu" aria-label="Mi cuenta">
            <div class="kx-cuenta-saludo">
                <span>Hola,</span>
                <strong>{{ $usuario->name }}</strong>
            </div>

            <nav>
                <a href="{{ route('perfil') }}" class="{{ $seccion === 'cuenta' ? 'activo' : '' }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                    Información de la cuenta
                </a>
                <a href="{{ route('perfil', 'favoritos') }}" class="{{ $seccion === 'favoritos' ? 'activo' : '' }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                    Favoritos
                </a>
                <a href="{{ route('perfil', 'pedidos') }}" class="{{ $seccion === 'pedidos' ? 'activo' : '' }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8 12 3 3 8v8l9 5 9-5V8z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
                    Historial de pedidos
                </a>
                <a href="{{ route('perfil', 'direcciones') }}" class="{{ $seccion === 'direcciones' ? 'activo' : '' }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                    Direcciones
                </a>
            </nav>

            {{-- [Sem 11 - Migraciones, Seeders y Roles]: Acceso al panel solo para el rol admin --}}
            @if ($usuario->esAdmin())
                {{-- [Actualizado: Botón admin] Rectángulo limpio alineado con el menú lateral e ícono de panel --}}
                <a href="{{ route('admin.home') }}" class="kx-cuenta-admin">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                    Ir al Panel de Administración
                </a>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="kx-cuenta-salir">CERRAR SESIÓN</button>
            </form>
        </aside>

        <main class="kx-cuenta-contenido">
            {{-- [Actualizado: Toast único] Avisos de éxito/error llegan al toast flotante del layout --}}

            {{-- ============ 1) INFORMACIÓN DE LA CUENTA ============ --}}
            @if ($seccion === 'cuenta')
                <h1>Detalles de la cuenta</h1>

                <section class="kx-tarjeta">
                    <div class="kx-avatar-fila">
                        <div class="kx-avatar">
                            @if ($usuario->urlAvatar())
                                <img id="fotoPerfil" src="{{ $usuario->urlAvatar() }}" alt="Foto de perfil de {{ $usuario->name }}">
                            @else
                                <span id="fotoInicial">{{ mb_strtoupper(mb_substr($usuario->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div>
                            <strong>{{ $usuario->name }}</strong>
                            <label for="inputAvatar" class="kx-enlace">Cambiar foto</label>
                            <small id="avatarNombre">JPG, PNG o WEBP · máximo 2 MB</small>
                        </div>
                    </div>
                </section>

                {{-- Tarjeta "Mi perfil": lectura + edición in-place (perfil.js alterna .editando) --}}
                <section class="kx-tarjeta {{ $errors->hasAny(['name', 'email', 'dni', 'telefono']) ? 'editando' : '' }}" data-editable>
                    <div class="kx-tarjeta-cabecera">
                        <h2>Mi perfil</h2>
                        <button type="button" class="kx-editar" data-alternar-edicion>EDITAR</button>
                    </div>

                    <dl class="kx-datos kx-solo-lectura">
                        <div><dt>Nombre completo</dt><dd>{{ $usuario->name }}</dd></div>
                        <div><dt>DNI</dt><dd>{{ $usuario->dni ?: '—' }}</dd></div>
                        <div><dt>Teléfono</dt><dd>{{ $usuario->telefono ?: '—' }}</dd></div>
                        <div><dt>Correo electrónico</dt><dd>{{ $usuario->email }}</dd></div>
                    </dl>

                    {{-- [Sem 09 - Controladores y MVC]: PUT a AuthController@actualizarPerfil (multipart por la foto) --}}
                    <form id="formPerfil" method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data" class="kx-form kx-solo-edicion">
                        @csrf
                        @method('PUT')
                        <input type="file" id="inputAvatar" name="avatar" accept="image/*" class="kx-oculto">
                        <div class="kx-form-grid">
                            <label>Nombre completo<input type="text" name="name" value="{{ old('name', $usuario->name) }}" required></label>
                            <label>Correo electrónico<input type="email" name="email" value="{{ old('email', $usuario->email) }}" required></label>
                            <label>DNI<input type="text" name="dni" value="{{ old('dni', $usuario->dni) }}" maxlength="8" inputmode="numeric" pattern="[0-9]{8}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"></label>
                            <label>Teléfono<input type="tel" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" maxlength="9" inputmode="numeric" pattern="[0-9]{9}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"></label>
                        </div>
                        <div class="kx-form-acciones">
                            <button type="button" class="kx-btn kx-btn--borde" data-alternar-edicion>Cancelar</button>
                            <button type="submit" class="kx-btn">Guardar</button>
                        </div>
                    </form>
                </section>

                <section class="kx-tarjeta {{ $errors->hasAny(['password_actual', 'password']) ? 'editando' : '' }}" data-editable>
                    <div class="kx-tarjeta-cabecera">
                        <h2>Contraseña</h2>
                        <button type="button" class="kx-editar" data-alternar-edicion>EDITAR</button>
                    </div>

                    <dl class="kx-datos kx-solo-lectura">
                        <div><dt>Contraseña</dt><dd class="kx-puntos">••••••••</dd></div>
                    </dl>

                    {{-- [VALIDACION FORMULARIO]: contraseña actual + nueva de mínimo 8 caracteres confirmada --}}
                    <form method="POST" action="{{ route('perfil.password') }}" class="kx-form kx-solo-edicion">
                        @csrf
                        @method('PUT')
                        <div class="kx-form-grid">
                            <label>Contraseña actual<input type="password" name="password_actual" required autocomplete="current-password"></label>
                            <span></span>
                            <label>Nueva contraseña<input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
                            <label>Confirmar nueva contraseña<input type="password" name="password_confirmation" minlength="8" required autocomplete="new-password"></label>
                        </div>
                        <div class="kx-form-acciones">
                            <button type="button" class="kx-btn kx-btn--borde" data-alternar-edicion>Cancelar</button>
                            <button type="submit" class="kx-btn">Actualizar contraseña</button>
                        </div>
                    </form>
                </section>
            @endif

            {{-- ============ 2) FAVORITOS ============ --}}
            @if ($seccion === 'favoritos')
                <h1>Favoritos</h1>
                <div id="favoritos-filas" class="kx-favoritos-filas"></div>
            @endif

            {{-- ============ 3) HISTORIAL Y SEGUIMIENTO DE PEDIDOS ============ --}}
            @if ($seccion === 'pedidos')
                <h1>Historial de pedidos</h1>

                @forelse ($pedidos as $pedido)
                    <article class="kx-tarjeta kx-pedido">
                        <div class="kx-tarjeta-cabecera">
                            <div>
                                <h2>Orden {{ $pedido->codigo_orden }}</h2>
                                <p class="kx-pedido-meta">{{ $pedido->created_at->format('d/m/Y') }} · {{ $pedido->metodo_pago }} · Estado: {{ $pedido->estado }}</p>
                            </div>
                            <strong class="kx-pedido-total">S/ {{ number_format($pedido->total, 2) }}</strong>
                        </div>

                        {{-- [Sem 12 - Eloquent ORM]: accessor paso_seguimiento traduce el estado a la barra de progreso --}}
                        @if ($pedido->paso_seguimiento > 0)
                            <ol class="kx-seguimiento" style="--paso: {{ $pedido->paso_seguimiento }}">
                                @foreach ($pasos as $i => $paso)
                                    <li class="{{ $i < $pedido->paso_seguimiento ? 'hecho' : '' }}">{{ $paso }}</li>
                                @endforeach
                            </ol>
                        @else
                            <p class="kx-pedido-cancelado">Pedido cancelado</p>
                        @endif

                        <ul class="kx-pedido-items">
                            @foreach ($pedido->items as $linea)
                                <li>
                                    <div class="kx-pedido-img">
                                        @if ($linea->producto)
                                            <img src="{{ str_starts_with($linea->producto->imagen, 'http') ? $linea->producto->imagen : asset($linea->producto->imagen) }}" alt="{{ $linea->producto->nombre }}">
                                        @endif
                                    </div>
                                    <div>
                                        <strong>{{ $linea->producto?->nombre ?? 'Producto no disponible' }}</strong>
                                        <span>Talla {{ rtrim(rtrim(number_format((float) $linea->talla, 1), '0'), '.') }} · Cantidad {{ $linea->cantidad }}</span>
                                    </div>
                                    <span class="kx-pedido-linea">S/ {{ number_format($linea->subtotal, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="kx-pedido-direccion">Entrega en: {{ $pedido->direccion_envio }}</p>
                    </article>
                @empty
                    <section class="kx-tarjeta kx-vacio-cuenta">
                        <p>Aún no has realizado ningún pedido.</p>
                        <a href="{{ route('catalogo') }}" class="kx-btn">Explorar catálogo</a>
                    </section>
                @endforelse
            @endif

            {{-- ============ 4) DIRECCIONES ============ --}}
            @if ($seccion === 'direcciones')
                <h1>Direcciones</h1>
                <section class="kx-tarjeta">
                    <div class="kx-tarjeta-cabecera">
                        <h2>Dirección de envío predeterminada</h2>
                    </div>
                    <form method="POST" action="{{ route('perfil.direccion') }}" class="kx-form">
                        @csrf
                        @method('PUT')
                        <label>Distrito, calle, número y referencia
                            <input type="text" name="direccion" value="{{ old('direccion', $usuario->direccion) }}" placeholder="Ej. Santa Anita, Av. Los Frutales 123" required minlength="5" maxlength="255">
                        </label>
                        <p class="kx-ayuda">La usaremos para pre-llenar tu checkout.</p>
                        <div class="kx-form-acciones">
                            <button type="submit" class="kx-btn">Guardar dirección</button>
                        </div>
                    </form>
                </section>
            @endif
        </main>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/perfil-avatar.js') }}"></script>
    <script src="{{ asset('js/favoritos.js') }}"></script>
    <script>
        // [Actualizado: Perfil] Botones EDITAR / Cancelar alternan la tarjeta entre lectura y edición
        document.querySelectorAll('[data-alternar-edicion]').forEach((btn) => {
            btn.addEventListener('click', () => btn.closest('[data-editable]')?.classList.toggle('editando'));
        });
    </script>
@endpush
