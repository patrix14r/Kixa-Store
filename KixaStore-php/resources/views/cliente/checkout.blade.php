@extends('layouts.app')

@section('titulo', 'Checkout | KixaStore')

@section('contenido')
    {{-- Datos pre-llenados del usuario; el cliente solo indica dirección y método de pago --}}
    <main class="max-w-full min-w-0 bg-white px-6 pt-12 pb-[72px] text-tinta max-escritorio:w-full max-escritorio:px-4 max-escritorio:pt-7 max-escritorio:pb-12">
        {{-- Contenedor centrado de 1080px (igual que Favoritos): evita el hueco vacío entre columnas en pantallas anchas --}}
        <div class="mx-auto w-full max-w-[1080px]">
        <div class="mb-7 flex flex-wrap items-baseline justify-between gap-3">
            <h1 class="text-[24px] font-semibold tracking-[-0.02em]">Finalizar compra</h1>
            <a href="{{ route('nosotros') }}#centro-ayuda" class="text-[14px] text-suave underline underline-offset-4 hover:text-tinta">¿Necesitas ayuda? Centro de soporte</a>
        </div>

        <div class="grid grid-cols-[minmax(0,1fr)_340px] items-start gap-10 lg:gap-12 *:max-w-full *:min-w-0 max-[960px]:grid-cols-1 max-[960px]:gap-8 max-escritorio:flex max-escritorio:flex-col max-escritorio:gap-7 max-escritorio:*:w-full">
            {{-- Los inputs de texto comparten estilo: borde gris y anillo suave al enfocar --}}
            <section class="[&_input]:w-full [&_input]:rounded-[10px] [&_input]:border [&_input]:border-[#cacacb] [&_input]:bg-white [&_input]:px-3.5 [&_input]:py-3 [&_input]:text-[15px] [&_input]:text-tinta [&_input]:outline-none [&_input]:transition-[border-color,box-shadow] [&_input]:duration-200 [&_input:focus]:border-tinta [&_input:focus]:shadow-[0_0_0_3px_rgba(17,17,17,0.08)]">
                {{-- Token de un solo uso generado por CarritoController@checkout --}}
                <input type="hidden" id="tokenCompra" value="{{ $tokenCompra }}">
                <div class="border-b border-[#e5e5e5] py-6">
                    <h2 class="mb-4 flex items-center gap-2.5 text-[18px] font-semibold"><span class="inline-flex size-[26px] items-center justify-center rounded-full bg-tinta text-[13px] text-white">1</span> Tus datos</h2>
                    <div class="grid grid-cols-[2fr_1fr_1fr] gap-3 max-escritorio:grid-cols-1 [&>label]:flex [&>label]:flex-col [&>label]:gap-1.5 [&>label]:text-[13px] [&>label]:text-suave">
                        <label>
                            <span>Nombre</span>
                            <input type="text" id="checkoutNombre" name="nombre" value="{{ $usuario->name }}" autocomplete="name">
                        </label>
                        {{-- [VALIDACION FORMULARIO]: DNI de 8 dígitos y celular de 9 dígitos --}}
                        <label>
                            <span>DNI</span>
                            <input type="text" id="checkoutDni" name="dni" value="{{ $usuario->dni }}" placeholder="8 dígitos"
                                   maxlength="8" pattern="[0-9]{8}" inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </label>
                        <label>
                            <span>Teléfono</span>
                            <input type="tel" id="checkoutTelefono" name="telefono" value="{{ $usuario->telefono }}" placeholder="9 dígitos"
                                   maxlength="9" pattern="[0-9]{9}" inputmode="numeric" autocomplete="tel"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </label>
                    </div>
                </div>

                <div class="border-b border-[#e5e5e5] py-6">
                    <h2 class="mb-4 flex items-center gap-2.5 text-[18px] font-semibold"><span class="inline-flex size-[26px] items-center justify-center rounded-full bg-tinta text-[13px] text-white">2</span> Dirección de entrega</h2>
                    <label class="flex flex-col gap-1.5 text-[13px] text-suave">
                        <span>Distrito, calle, número y referencia</span>
                        <input type="text" id="checkoutDireccion" name="direccion_envio" value="{{ $usuario->direccion }}" placeholder="Ej. Santa Anita, Av. Los Frutales 123, frente al parque" autocomplete="street-address" required>
                    </label>
                </div>

                <div class="border-b border-[#e5e5e5] py-6">
                    <h2 class="mb-4 flex items-center gap-2.5 text-[18px] font-semibold"><span class="inline-flex size-[26px] items-center justify-center rounded-full bg-tinta text-[13px] text-white">3</span> Método de pago</h2>
                    {{-- Simulador de pago: cada opción pinta su formulario en #contenido-pago (funciones de public/js/carrito.js); aria-pressed marca la opción elegida --}}
                    <div class="flex flex-col gap-2.5 [&>button]:grid [&>button]:w-full [&>button]:max-w-full [&>button]:min-w-0 [&>button]:cursor-pointer [&>button]:grid-cols-[40px_1fr_auto] [&>button]:items-center [&>button]:gap-3.5 [&>button]:rounded-xl [&>button]:border [&>button]:border-[#e5e5e5] [&>button]:bg-white [&>button]:px-4 [&>button]:py-3.5 [&>button]:text-left [&>button]:text-[13.3333px] [&>button]:tracking-normal [&>button]:text-tinta [&>button]:transition-colors [&>button]:duration-200 [&>button:hover]:border-tinta [&>button:hover]:bg-perla [&>button:focus-visible]:border-tinta [&>button:focus-visible]:bg-perla [&>button[aria-pressed=true]]:border-tinta [&>button[aria-pressed=true]]:bg-perla [&>button[aria-pressed=true]]:shadow-[inset_0_0_0_1px_#111] max-escritorio:[&>button]:grid-cols-[36px_minmax(0,1fr)_auto] max-escritorio:[&>button]:gap-2.5 max-escritorio:[&>button]:p-3 [&_strong]:block [&_strong]:text-[15px] [&_strong]:font-semibold [&_small]:text-[13px] [&_small]:text-suave">
                        <button onclick="mostrarTarjeta()" type="button" data-metodo="Tarjeta" aria-pressed="false">
                            <span class="inline-flex size-10 items-center justify-center rounded-[10px] bg-perla"><i class="fa-solid fa-credit-card"></i></span>
                            <span><strong>Tarjeta de Crédito / Débito</strong><small>Visa o Mastercard · pago inmediato</small></span>
                            <span class="flex items-center gap-1.5">
                                <x-logo-tarjeta marca="visa" class="max-escritorio:hidden" />
                                <x-logo-tarjeta marca="mastercard" class="max-escritorio:hidden" />
                                <i class="fa-solid fa-chevron-right ml-1"></i>
                            </span>
                        </button>
                        <button onclick="mostrarContraentrega()" type="button" data-metodo="Contraentrega" aria-pressed="false">
                            <span class="inline-flex size-10 items-center justify-center rounded-[10px] bg-perla"><i class="fa-solid fa-money-bill-wave"></i></span>
                            <span><strong>Contraentrega (Efectivo)</strong><small>Pagas en efectivo al recibir tu pedido</small></span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>

                    <div id="contenido-pago"></div>
                </div>
            </section>

            {{-- Misma regla de envío que el carrito y CarritoController@procesar (data-regla-envio la lee carrito.js) --}}
            <aside class="sticky top-[calc(var(--spacing-navbar)_+_24px)] max-[960px]:static" data-regla-envio='@json($reglaEnvio)'>
                <h2 class="mb-4 text-[24px] font-semibold tracking-[-0.02em]">Resumen</h2>
                <div class="flex justify-between py-2 text-[15px]"><span>Precio con IGV</span><span id="subtotal">S/ 0.00</span></div>
                <div class="flex justify-between py-2 text-[15px]"><span>Envío</span><span id="envio">Gratis</span></div>
                <div class="flex justify-between py-2 text-[15px]"><span>IGV (18%)</span><span id="igv">S/ 0.00</span></div>
                <div class="mt-3 flex justify-between border-y border-[#e5e5e5] py-4 text-[15px] font-semibold"><span>Total de la orden</span><strong id="total">S/ 0.00</strong></div>
                <p id="envio-texto" class="mt-3 text-[13px] text-suave"></p>
                <a href="{{ route('carrito') }}" class="mt-[18px] block text-center text-[14px] text-tinta underline underline-offset-4">Volver al carrito</a>
                <x-metodos-pago class="mt-6 border-t border-[#e5e5e5] pt-4" />
            </aside>
        </div>
        </div>
    </main>

    {{-- Simulador: carrito.js muestra esta capa (style.display = "flex") mientras se procesa el pago --}}
    <div id="procesandoPago" class="fixed inset-0 z-2000 flex-col items-center justify-center gap-3 bg-white/85 px-6 text-center backdrop-blur-[6px]" style="display: none;" role="status" aria-live="polite">
        <span class="size-12 animate-spin rounded-full border-4 border-[#e5e5e5] border-t-tinta"></span>
        <p class="mt-2 text-[17px] font-semibold text-tinta">Procesando pago...</p>
        <p id="procesandoDetalle" class="text-[14px] text-suave">Validando tu tarjeta de forma segura</p>
    </div>

    {{-- Diálogo "¿Deseas salir del pago?": carrito.js (activarConfirmacionSalida) lo abre al pulsar un enlace antes de pagar; la clase "abierto" anima fondo y panel --}}
    <div id="confirmarSalida" class="group fixed inset-0 z-2500 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="confirmarSalidaTitulo" hidden>
        <div class="absolute inset-0 bg-[rgba(10,10,14,0.35)] opacity-0 backdrop-blur-[10px] transition-opacity duration-200 group-[.abierto]:opacity-100" data-seguir-pagando></div>
        <div class="relative w-[min(420px,100%)] translate-y-2 rounded-2xl bg-white p-7 text-center text-tinta opacity-0 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.45)] transition-[opacity,translate] duration-200 group-[.abierto]:translate-y-0 group-[.abierto]:opacity-100">
            <span class="mx-auto mb-4 inline-flex size-12 items-center justify-center rounded-full bg-perla text-[18px]"><i class="fa-solid fa-bag-shopping"></i></span>
            <h2 id="confirmarSalidaTitulo" class="text-[18px] font-semibold">¿Deseas salir del pago?</h2>
            <p class="mt-2 text-[14px] leading-[1.6] text-suave">Tus productos seguirán guardados en el carrito.</p>
            <div class="mt-6 flex flex-col gap-2.5">
                <button type="button" data-seguir-pagando class="flex w-full cursor-pointer items-center justify-center rounded-full border-none bg-tinta p-[15px] text-[15px] font-medium text-white transition-colors duration-200 hover:bg-[#333]">Seguir con el pago</button>
                <button type="button" data-salir class="flex w-full cursor-pointer items-center justify-center rounded-full border border-[#cacacb] bg-white p-[15px] text-[15px] font-medium text-tinta transition-colors duration-200 hover:border-tinta hover:bg-perla">Salir del pago</button>
            </div>
        </div>
    </div>

    {{-- Confirmación de respaldo: carrito.js cambia style.display a "flex" para mostrarla --}}
    <div id="alerta" class="fixed inset-0 z-2000 items-center justify-center bg-[rgba(10,10,14,0.35)] backdrop-blur-[10px]" style="display: none;">
        <div class="w-[min(420px,90vw)] rounded-2xl bg-white p-7 text-center text-tinta">
            <h2 class="text-[1.5em] font-bold">¡Pedido confirmado!</h2>
            <p id="mensaje-alerta" class="mt-2.5 mb-[18px] text-suave"></p>
            <a href="{{ route('home') }}" class="mt-2 flex w-full cursor-pointer items-center justify-center rounded-full border-none bg-tinta p-[18px] text-[16px] font-medium text-white transition-colors duration-200 hover:bg-[#333]">Volver a la tienda</a>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/carrito.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => mostrarTarjeta());
    </script>
@endpush
