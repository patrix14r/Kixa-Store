@extends('layouts.app')

@section('titulo', 'Mi cuenta | KixaStore')


@section('contenido')
    {{-- 2 columnas: sidebar de cuenta + contenido de la pestaña elegida (ruta /perfil/{seccion?}); en móvil todo apilado sin desborde --}}
    <div class="grid min-h-full max-w-full min-w-0 grid-cols-[300px_minmax(0,1fr)] bg-white text-tinta max-[860px]:grid-cols-1 max-escritorio:block max-escritorio:w-full">
        <aside class="max-w-full min-w-0 bg-[#f8f9fa] pt-14 pr-6 pb-10 pl-[5vw] max-[860px]:px-4 max-[860px]:py-6 max-escritorio:px-4 max-escritorio:py-5" aria-label="Mi cuenta">
            <div class="mb-7 flex flex-col pl-[18px] max-escritorio:mb-4 max-escritorio:pl-0">
                <span class="text-[14px] text-suave">Hola,</span>
                <strong class="text-[18px] font-semibold">{{ $usuario->name }}</strong>
            </div>

            {{-- La pestaña actual lleva la clase "activo": borde izquierdo negro y fondo blanco --}}
            <nav class="flex flex-col gap-1 max-[860px]:flex-row max-[860px]:overflow-x-auto max-[860px]:[scrollbar-width:none] max-escritorio:flex-col max-escritorio:gap-1.5 max-escritorio:overflow-visible [&>a]:flex [&>a]:items-center [&>a]:gap-3.5 [&>a]:rounded-r-[10px] [&>a]:border-l-2 [&>a]:border-transparent [&>a]:px-4 [&>a]:py-[13px] [&>a]:text-[15px] [&>a]:font-medium [&>a]:text-[#3f3f46] [&>a]:transition-colors [&>a]:duration-200 [&>a:hover]:bg-[#eef0f2] [&>a:hover]:text-tinta [&>a.activo]:border-l-tinta [&>a.activo]:bg-white [&>a.activo]:font-semibold [&>a.activo]:text-tinta max-[860px]:[&>a]:shrink-0 max-[860px]:[&>a]:rounded-t-lg max-[860px]:[&>a]:rounded-b-none max-[860px]:[&>a]:border-b-2 max-[860px]:[&>a]:border-l-0 max-[860px]:[&>a.activo]:border-b-tinta max-escritorio:[&>a]:w-full max-escritorio:[&>a]:rounded-[10px] max-escritorio:[&>a]:border-b-0 max-escritorio:[&>a]:border-l-2 max-escritorio:[&>a]:bg-white max-escritorio:[&>a.activo]:border-b-transparent">
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

            {{-- Acceso al panel solo para el rol admin --}}
            @if ($usuario->esAdmin())
                {{-- Rectángulo limpio alineado con el menú lateral e ícono de panel --}}
                <a href="{{ route('admin.home') }}" class="mt-6 ml-0.5 flex items-center gap-2.5 rounded-[10px] bg-tinta px-3.5 py-2.5 text-[13px] leading-[1.3] font-semibold text-white transition-colors duration-200 hover:bg-[#333]">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0 opacity-80"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                    Ir al Panel de Administración
                </a>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="mt-7 ml-[18px] cursor-pointer border-none bg-transparent p-0 text-[14px] font-bold tracking-[0.04em] text-tinta underline underline-offset-[5px]">CERRAR SESIÓN</button>
            </form>
        </aside>

        <main class="max-w-full min-w-0 px-[5vw] pt-14 pb-[72px] max-[860px]:px-4 max-[860px]:pt-8 max-[860px]:pb-14 max-escritorio:px-4 max-escritorio:pt-6 max-escritorio:pb-12">
            {{-- Avisos de éxito/error llegan al toast flotante del layout --}}
            {{-- max-w-4xl centrado: las tarjetas de cuenta e historial no se estiran en pantallas anchas --}}
            <div class="mx-auto w-full max-w-4xl [&>h1]:mb-8 [&>section]:mb-6 [&>article]:mb-6">

            {{-- ============ 1) INFORMACIÓN DE LA CUENTA ============ --}}
            @if ($seccion === 'cuenta')
                <h1 class="mb-6 text-[26px] font-bold tracking-[-0.02em] uppercase">Detalles de la cuenta</h1>

                <section class="mb-5 max-w-full min-w-0 rounded-[14px] border border-[#ececec] bg-white px-7 py-6 shadow-[0_2px_10px_rgba(0,0,0,0.04)] max-escritorio:px-4 max-escritorio:py-[18px]">
                    <div class="flex items-center gap-[18px]">
                        {{-- perfil-avatar.js reemplaza la inicial por un <img> nuevo: [&_img] le da el tamaño redondo --}}
                        <div class="flex size-[72px] shrink-0 items-center justify-center overflow-hidden rounded-full bg-perla text-[28px] font-semibold [&_img]:size-full [&_img]:object-cover">
                            @if ($usuario->urlAvatar())
                                <img id="fotoPerfil" src="{{ $usuario->urlAvatar() }}" alt="Foto de perfil de {{ $usuario->name }}">
                            @else
                                <span id="fotoInicial">{{ mb_strtoupper(mb_substr($usuario->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="flex flex-col gap-1">
                            <strong>{{ $usuario->name }}</strong>
                            <label for="inputAvatar" class="cursor-pointer text-[14px] font-medium text-tinta underline underline-offset-4">Cambiar foto</label>
                            <small id="avatarNombre" class="text-[12px] text-suave">JPG, PNG o WEBP · máximo 2 MB</small>
                        </div>
                    </div>
                </section>

                {{-- Tarjeta "Mi perfil": lectura + edición in-place. El script alterna la clase .editando y group-[.editando] muestra/oculta cada parte --}}
                <section class="group mb-5 max-w-full min-w-0 rounded-[14px] border border-[#ececec] bg-white px-7 py-6 shadow-[0_2px_10px_rgba(0,0,0,0.04)] max-escritorio:px-4 max-escritorio:py-[18px] {{ $errors->hasAny(['name', 'email', 'dni', 'telefono']) ? 'editando' : '' }}" data-editable>
                    <div class="-mx-7 -mt-6 mb-5 flex items-start justify-between gap-4 border-b border-[#f1f1f1] px-7 py-[22px] max-escritorio:-mx-4 max-escritorio:-mt-[18px] max-escritorio:mb-4 max-escritorio:flex-wrap max-escritorio:p-4 [&_h2]:text-[18px] [&_h2]:font-semibold">
                        <h2>Mi perfil</h2>
                        <button type="button" class="cursor-pointer border-none bg-transparent p-0 text-[13px] font-bold tracking-[0.04em] text-tinta underline underline-offset-[5px] group-[.editando]:hidden" data-alternar-edicion>EDITAR</button>
                    </div>

                    <dl class="grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-x-6 gap-y-[18px] max-escritorio:grid-cols-1 group-[.editando]:hidden [&_dt]:mb-1 [&_dt]:text-[14px] [&_dt]:font-semibold [&_dd]:text-[15px] [&_dd]:text-[#52525b] [&_dd]:[word-break:break-word] max-escritorio:[&_dd]:[overflow-wrap:anywhere]">
                        <div><dt>Nombre completo</dt><dd>{{ $usuario->name }}</dd></div>
                        <div><dt>DNI</dt><dd>{{ $usuario->dni ?: '—' }}</dd></div>
                        <div><dt>Teléfono</dt><dd>{{ $usuario->telefono ?: '—' }}</dd></div>
                        <div><dt>Correo electrónico</dt><dd>{{ $usuario->email }}</dd></div>
                    </dl>

                    {{-- PUT a AuthController@actualizarPerfil (multipart por la foto) --}}
                    <form id="formPerfil" method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data" class="hidden group-[.editando]:block [&_label]:flex [&_label]:max-w-full [&_label]:min-w-0 [&_label]:flex-col [&_label]:gap-1.5 [&_label]:text-[13px] [&_label]:font-medium [&_label]:text-[#3f3f46] [&_input]:w-full [&_input]:rounded-[10px] [&_input]:border [&_input]:border-[#cacacb] [&_input]:bg-white [&_input]:px-3.5 [&_input]:py-3 [&_input]:text-[15px] [&_input]:font-normal [&_input]:text-tinta [&_input]:outline-none [&_input]:transition-[border-color,box-shadow] [&_input]:duration-200 [&_input:focus]:border-tinta [&_input:focus]:shadow-[0_0_0_3px_rgba(17,17,17,0.08)]">
                        @csrf
                        @method('PUT')
                        <input type="file" id="inputAvatar" name="avatar" accept="image/*" class="hidden">
                        <div class="grid grid-cols-2 gap-4 max-[860px]:grid-cols-1">
                            <label>Nombre completo<input type="text" name="name" value="{{ old('name', $usuario->name) }}" required></label>
                            <label>Correo electrónico<input type="email" name="email" value="{{ old('email', $usuario->email) }}" required></label>
                            <label>DNI<input type="text" name="dni" value="{{ old('dni', $usuario->dni) }}" maxlength="8" inputmode="numeric" pattern="[0-9]{8}" data-solo-digitos></label>
                            <label>Teléfono<input type="tel" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" maxlength="9" inputmode="numeric" pattern="[0-9]{9}" data-solo-digitos></label>
                        </div>
                        <div class="mt-5 flex justify-end gap-2.5">
                            <button type="button" class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border px-[22px] py-3 text-[13px] font-bold tracking-[0.04em] transition-colors duration-200 border-[#cacacb] bg-white text-tinta hover:border-tinta hover:bg-perla" data-alternar-edicion>Cancelar</button>
                            <button type="submit" class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border px-[22px] py-3 text-[13px] font-bold tracking-[0.04em] transition-colors duration-200 border-tinta bg-tinta text-white hover:bg-[#333]">Guardar</button>
                        </div>
                    </form>
                </section>

                <section class="group mb-5 max-w-full min-w-0 rounded-[14px] border border-[#ececec] bg-white px-7 py-6 shadow-[0_2px_10px_rgba(0,0,0,0.04)] max-escritorio:px-4 max-escritorio:py-[18px] {{ $errors->hasAny(['password_actual', 'password']) ? 'editando' : '' }}" data-editable>
                    <div class="-mx-7 -mt-6 mb-5 flex items-start justify-between gap-4 border-b border-[#f1f1f1] px-7 py-[22px] max-escritorio:-mx-4 max-escritorio:-mt-[18px] max-escritorio:mb-4 max-escritorio:flex-wrap max-escritorio:p-4 [&_h2]:text-[18px] [&_h2]:font-semibold">
                        <h2>Contraseña</h2>
                        <button type="button" class="cursor-pointer border-none bg-transparent p-0 text-[13px] font-bold tracking-[0.04em] text-tinta underline underline-offset-[5px] group-[.editando]:hidden" data-alternar-edicion>EDITAR</button>
                    </div>

                    <dl class="grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-x-6 gap-y-[18px] max-escritorio:grid-cols-1 group-[.editando]:hidden [&_dt]:mb-1 [&_dt]:text-[14px] [&_dt]:font-semibold [&_dd]:text-[15px] [&_dd]:text-[#52525b] [&_dd]:[word-break:break-word] max-escritorio:[&_dd]:[overflow-wrap:anywhere]">
                        <div><dt>Contraseña</dt><dd class="tracking-[0.2em]">••••••••</dd></div>
                    </dl>

                    {{-- [VALIDACION FORMULARIO]: contraseña actual + nueva de mínimo 8 caracteres confirmada --}}
                    <form method="POST" action="{{ route('perfil.password') }}" class="hidden group-[.editando]:block [&_label]:flex [&_label]:max-w-full [&_label]:min-w-0 [&_label]:flex-col [&_label]:gap-1.5 [&_label]:text-[13px] [&_label]:font-medium [&_label]:text-[#3f3f46] [&_input]:w-full [&_input]:rounded-[10px] [&_input]:border [&_input]:border-[#cacacb] [&_input]:bg-white [&_input]:px-3.5 [&_input]:py-3 [&_input]:text-[15px] [&_input]:font-normal [&_input]:text-tinta [&_input]:outline-none [&_input]:transition-[border-color,box-shadow] [&_input]:duration-200 [&_input:focus]:border-tinta [&_input:focus]:shadow-[0_0_0_3px_rgba(17,17,17,0.08)]">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-2 gap-4 max-[860px]:grid-cols-1">
                            <label>Contraseña actual<input type="password" name="password_actual" required autocomplete="current-password"></label>
                            <span></span>
                            <label>Nueva contraseña<input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
                            <label>Confirmar nueva contraseña<input type="password" name="password_confirmation" minlength="8" required autocomplete="new-password"></label>
                        </div>
                        <div class="mt-5 flex justify-end gap-2.5">
                            <button type="button" class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border px-[22px] py-3 text-[13px] font-bold tracking-[0.04em] transition-colors duration-200 border-[#cacacb] bg-white text-tinta hover:border-tinta hover:bg-perla" data-alternar-edicion>Cancelar</button>
                            <button type="submit" class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border px-[22px] py-3 text-[13px] font-bold tracking-[0.04em] transition-colors duration-200 border-tinta bg-tinta text-white hover:bg-[#333]">Actualizar contraseña</button>
                        </div>
                    </form>
                </section>
            @endif

            {{-- ============ 2) FAVORITOS ============ --}}
            @if ($seccion === 'favoritos')
                <h1 class="mb-6 text-[26px] font-bold tracking-[-0.02em] uppercase">Favoritos</h1>
                <div id="favoritos-filas" class="flex flex-col gap-3.5"></div>
            @endif

            {{-- ============ 3) HISTORIAL Y SEGUIMIENTO DE PEDIDOS ============ --}}
            @if ($seccion === 'pedidos')
                <h1 class="mb-6 text-[26px] font-bold tracking-[-0.02em] uppercase">Historial de pedidos</h1>

                @forelse ($pedidos as $pedido)
                    <article class="mb-5 max-w-full min-w-0 rounded-[14px] border border-[#ececec] bg-white px-7 py-6 shadow-[0_2px_10px_rgba(0,0,0,0.04)] max-escritorio:px-4 max-escritorio:py-[18px]">
                        <div class="-mx-7 -mt-6 mb-5 flex items-start justify-between gap-4 border-b border-[#f1f1f1] px-7 py-[22px] max-escritorio:-mx-4 max-escritorio:-mt-[18px] max-escritorio:mb-4 max-escritorio:flex-wrap max-escritorio:p-4 [&_h2]:text-[18px] [&_h2]:font-semibold">
                            <div>
                                <h2>Orden {{ $pedido->codigo_orden }}</h2>
                                <p class="mt-1 text-[13px] text-suave max-escritorio:[overflow-wrap:anywhere]">{{ $pedido->created_at->format('d/m/Y') }} · {{ $pedido->metodo_pago_detalle }} · Estado: {{ $pedido->estado }}</p>
                            </div>
                            {{-- Total + boleta PDF (GET /pedidos/{codigo_orden}/boleta, solo el dueño o un admin): "Ver boleta" abre la vista previa en otra pestaña y "Descargar" agrega ?descargar=1 --}}
                            <div class="flex flex-col items-end gap-2 max-escritorio:items-start">
                                <strong class="text-[18px] font-semibold whitespace-nowrap">S/ {{ number_format($pedido->total, 2) }}</strong>
                                <div class="flex items-center gap-1.5 [&>a]:inline-flex [&>a]:items-center [&>a]:gap-1.5 [&>a]:rounded-md [&>a]:border [&>a]:border-[#cacacb] [&>a]:px-3 [&>a]:py-1.5 [&>a]:text-[12px] [&>a]:font-bold [&>a]:tracking-[0.04em] [&>a]:text-tinta [&>a]:transition-colors [&>a]:duration-200 [&>a:hover]:border-tinta [&>a:hover]:bg-perla">
                                    <a href="{{ route('pedidos.boleta', $pedido) }}" target="_blank" rel="noopener"><i class="fa-regular fa-eye"></i> Ver boleta</a>
                                    <a href="{{ route('pedidos.boleta', ['pedido' => $pedido, 'descargar' => 1]) }}" title="Descargar boleta {{ $pedido->codigo_orden }} en PDF"><i class="fa-solid fa-download"></i> Descargar</a>
                                </div>
                            </div>
                        </div>

                        {{-- Accessor paso_seguimiento traduce el estado a la barra de progreso --}}
                        {{-- Barra de progreso: before: es la línea gris completa y after: la parte verde (ancho según --paso) --}}
                        @if ($pedido->paso_seguimiento > 0)
                            <ol class="relative mt-1 mb-6 grid grid-cols-4 before:absolute before:top-[7px] before:right-[12.5%] before:left-[12.5%] before:h-[3px] before:rounded-[3px] before:bg-[#e5e5e5] before:content-[''] after:absolute after:top-[7px] after:left-[12.5%] after:h-[3px] after:w-[calc((var(--paso)_-_1)_*_25%)] after:rounded-[3px] after:bg-exito after:content-['']" style="--paso: {{ $pedido->paso_seguimiento }}">
                                @foreach ($pasos as $i => $paso)
                                    <li @class([
                                        "relative z-1 flex flex-col items-center gap-2 text-center text-[12.5px] before:size-[23px] before:rounded-full before:border-3 before:content-[''] max-escritorio:text-[11px]",
                                        'font-semibold text-tinta before:border-exito before:bg-exito' => $i < $pedido->paso_seguimiento,
                                        'text-[#9e9ea0] before:border-[#e5e5e5] before:bg-white' => $i >= $pedido->paso_seguimiento,
                                    ])>{{ $paso }}</li>
                                @endforeach
                            </ol>
                        @else
                            <p class="mb-4 font-semibold text-[#b91c1c]">Pedido cancelado</p>
                        @endif

                        <ul class="flex flex-col gap-3 [&>li]:grid [&>li]:grid-cols-[64px_minmax(0,1fr)_auto] [&>li]:items-center [&>li]:gap-3.5 [&_strong]:block [&_strong]:text-[14px] [&_strong]:font-semibold [&_span]:text-[13px] [&_span]:text-suave">
                            @foreach ($pedido->items as $linea)
                                <li>
                                    <div class="aspect-square rounded-lg bg-perla p-1.5">
                                        @if ($linea->producto)
                                            <img src="{{ str_starts_with($linea->producto->imagen, 'http') ? $linea->producto->imagen : asset($linea->producto->imagen) }}" alt="{{ $linea->producto->nombre }}" class="size-full object-contain mix-blend-multiply">
                                        @endif
                                    </div>
                                    <div>
                                        <strong>{{ $linea->producto?->nombre ?? 'Producto no disponible' }}</strong>
                                        <span>Talla {{ $linea->talla_etiqueta }} · Cantidad {{ $linea->cantidad }}</span>
                                    </div>
                                    <span class="font-medium text-tinta!">S/ {{ number_format($linea->subtotal, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-4 border-t border-[#f1f1f1] pt-3.5 text-[13px] text-suave">Entrega en: {{ $pedido->direccion_envio }}</p>
                    </article>
                @empty
                    <section class="mb-5 max-w-full min-w-0 rounded-[14px] border border-[#ececec] bg-white px-7 py-6 shadow-[0_2px_10px_rgba(0,0,0,0.04)] max-escritorio:px-4 max-escritorio:py-[18px] text-center text-suave">
                        <p>Aún no has realizado ningún pedido.</p>
                        <a href="{{ route('catalogo') }}" class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border px-[22px] py-3 text-[13px] font-bold tracking-[0.04em] transition-colors duration-200 border-tinta bg-tinta text-white hover:bg-[#333] mt-3.5">Explorar catálogo</a>
                    </section>
                @endforelse
            @endif

            {{-- ============ 4) DIRECCIONES ============ --}}
            @if ($seccion === 'direcciones')
                <h1 class="mb-6 text-[26px] font-bold tracking-[-0.02em] uppercase">Direcciones</h1>
                <section class="mb-5 max-w-full min-w-0 rounded-[14px] border border-[#ececec] bg-white px-7 py-6 shadow-[0_2px_10px_rgba(0,0,0,0.04)] max-escritorio:px-4 max-escritorio:py-[18px]">
                    <div class="-mx-7 -mt-6 mb-5 flex items-start justify-between gap-4 border-b border-[#f1f1f1] px-7 py-[22px] max-escritorio:-mx-4 max-escritorio:-mt-[18px] max-escritorio:mb-4 max-escritorio:flex-wrap max-escritorio:p-4 [&_h2]:text-[18px] [&_h2]:font-semibold">
                        <h2>Dirección de envío predeterminada</h2>
                    </div>
                    <form method="POST" action="{{ route('perfil.direccion') }}" class="[&_label]:flex [&_label]:max-w-full [&_label]:min-w-0 [&_label]:flex-col [&_label]:gap-1.5 [&_label]:text-[13px] [&_label]:font-medium [&_label]:text-[#3f3f46] [&_input]:w-full [&_input]:rounded-[10px] [&_input]:border [&_input]:border-[#cacacb] [&_input]:bg-white [&_input]:px-3.5 [&_input]:py-3 [&_input]:text-[15px] [&_input]:font-normal [&_input]:text-tinta [&_input]:outline-none [&_input]:transition-[border-color,box-shadow] [&_input]:duration-200 [&_input:focus]:border-tinta [&_input:focus]:shadow-[0_0_0_3px_rgba(17,17,17,0.08)]">
                        @csrf
                        @method('PUT')
                        <label>Distrito, calle, número y referencia
                            <input type="text" name="direccion" value="{{ old('direccion', $usuario->direccion) }}" placeholder="Ej. Santa Anita, Av. Los Frutales 123" required minlength="5" maxlength="255">
                        </label>
                        <p class="mt-2 text-[13px] text-suave">La usaremos para pre-llenar tu checkout.</p>
                        <div class="mt-5 flex justify-end gap-2.5">
                            <button type="submit" class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border px-[22px] py-3 text-[13px] font-bold tracking-[0.04em] transition-colors duration-200 border-tinta bg-tinta text-white hover:bg-[#333]">Guardar dirección</button>
                        </div>
                    </form>
                </section>
            @endif
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/perfil-avatar.js') }}"></script>
    <script src="{{ asset('js/favoritos.js') }}"></script>
@endpush
