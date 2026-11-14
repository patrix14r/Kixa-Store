@extends('layouts.app')

@section('titulo', 'Checkout | KixaStore')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/carrito.css') }}">
@endpush

@section('contenido')
    {{-- [Actualizado: Checkout] Datos pre-llenados del usuario; el cliente solo indica dirección y método de pago --}}
    <main class="kx-carrito-page">
        <div class="kx-checkout-cabecera">
            <h1>Finalizar compra</h1>
            <a href="{{ route('nosotros') }}#centro-ayuda" class="kx-checkout-ayuda">¿Necesitas ayuda? Centro de soporte</a>
        </div>

        <div class="kx-carrito-layout">
            <section class="kx-checkout-pasos">
                {{-- [Actualizado: Blindaje] Token de un solo uso generado por CarritoController@checkout --}}
                <input type="hidden" id="tokenCompra" value="{{ $tokenCompra }}">
                <div class="kx-paso">
                    <h2><span>1</span> Tus datos</h2>
                    <div class="kx-campos">
                        <label class="kx-campo">
                            <span>Nombre</span>
                            <input type="text" id="checkoutNombre" name="nombre" value="{{ $usuario->name }}" autocomplete="name">
                        </label>
                        {{-- [VALIDACION FORMULARIO]: DNI de 8 dígitos y celular de 9 dígitos --}}
                        <label class="kx-campo">
                            <span>DNI</span>
                            <input type="text" id="checkoutDni" name="dni" value="{{ $usuario->dni }}" placeholder="8 dígitos"
                                   maxlength="8" pattern="[0-9]{8}" inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </label>
                        <label class="kx-campo">
                            <span>Teléfono</span>
                            <input type="tel" id="checkoutTelefono" name="telefono" value="{{ $usuario->telefono }}" placeholder="9 dígitos"
                                   maxlength="9" pattern="[0-9]{9}" inputmode="numeric" autocomplete="tel"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </label>
                    </div>
                </div>

                <div class="kx-paso">
                    <h2><span>2</span> Dirección de entrega</h2>
                    <label class="kx-campo kx-campo--ancho">
                        <span>Distrito, calle, número y referencia</span>
                        <input type="text" id="checkoutDireccion" name="direccion_envio" value="{{ $usuario->direccion }}" placeholder="Ej. Santa Anita, Av. Los Frutales 123, frente al parque" autocomplete="street-address" required>
                    </label>
                </div>

                <div class="kx-paso">
                    <h2><span>3</span> Método de pago</h2>
                    <div class="opciones">
                        <button onclick="mostrarContraentrega()" class="opcion" type="button">
                            <span class="opcion-icono"><i class="fa-solid fa-box"></i></span>
                            <span><strong>Contraentrega</strong><small>Paga en efectivo o POS al recibir tu pedido</small></span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                        <button onclick="mostrarTarjeta()" class="opcion" type="button">
                            <span class="opcion-icono"><i class="fa-solid fa-credit-card"></i></span>
                            <span><strong>Tarjeta de débito / crédito</strong><small>Visa, Mastercard o American Express</small></span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                        <button onclick="mostrarYape()" class="opcion" type="button">
                            <span class="opcion-icono"><i class="fa-solid fa-mobile-screen-button"></i></span>
                            <span><strong>Yape / Plin</strong><small>Escanea el código QR desde tu app</small></span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>

                    <div id="contenido-pago"></div>
                </div>
            </section>

            <aside class="kx-resumen">
                <h2>Resumen</h2>
                <div class="kx-resumen-linea"><span>Precio con IGV</span><span id="subtotal">S/ 0.00</span></div>
                <div class="kx-resumen-linea"><span>Envío</span><span>Gratis</span></div>
                <div class="kx-resumen-linea"><span>IGV (18%)</span><span id="igv">S/ 0.00</span></div>
                <div class="kx-resumen-total"><span>Total de la orden</span><strong id="total">S/ 0.00</strong></div>
                <a href="{{ route('carrito') }}" class="kx-resumen-volver">Volver al carrito</a>
            </aside>
        </div>
    </main>

    <div id="alerta" class="alerta" style="display: none;">
        <div class="alerta-contenido">
            <h2>¡Pedido confirmado!</h2>
            <p id="mensaje-alerta"></p>
            <a href="{{ route('home') }}" class="kx-btn-comprar">Volver a la tienda</a>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/carrito.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => mostrarContraentrega());
    </script>
@endpush
