@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos de checkout y pasarela de pago de Tienda-Kixa_2/css/carrito.css --}}
    <link rel="stylesheet" href="{{ asset('css/carrito.css') }}">
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de la sección de pasarela y pago de Tienda-Kixa_2/carrito.html (Líneas 159 a 237) --}}
    {{-- [SEPARADO]: Se transformó el modal flotante de pago en una vista dedicada de Checkout / Pasarela de Pago --}}
    {{-- [ADAPTADO]: Se integró a layouts.app, agregando opciones de pago, resumen y confirmación interactiva --}}
    <main class="carrito-page">
        <div class="carrito-header">
            <div>
                <span class="mini-titulo">CHECKOUT</span>
                <h1>
                    <i class="fa-solid fa-credit-card"></i>
                    Finalizar Compra
                </h1>
                <p>Selecciona tu método de pago preferido para confirmar tu pedido en Tienda Kixa.</p>
            </div>
        </div>

        <div class="carrito-layout">
            <!-- MÉTODOS DE PAGO DISPONIBLES -->
            <section class="productos-section">
                <div class="modal-contenido" style="background: #111315; border: 1px solid #24272a; border-radius: 17px; padding: 30px;">
                    <div class="modal-titulo" style="margin-bottom: 25px;">
                        <div class="icono-pago">
                            <i class="fa-solid fa-credit-card"></i>
                        </div>
                        <div>
                            <h2 style="color: #fff; font-size: 20px;">Método de Pago</h2>
                            <p style="color: #85878a; font-size: 13px;">Elige cómo deseas realizar tu abono seguro.</p>
                        </div>
                    </div>

                    <div class="opciones" style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 25px;">
                        <button onclick="mostrarContraentrega()" class="opcion" type="button">
                            <span class="opcion-icono">
                                <i class="fa-solid fa-box"></i>
                            </span>
                            <span>
                                <strong>Contraentrega</strong>
                                <small>Paga en efectivo o POS al recibir tu pedido en tu domicilio</small>
                            </span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>

                        <button onclick="mostrarTarjeta()" class="opcion" type="button">
                            <span class="opcion-icono">
                                <i class="fa-solid fa-credit-card"></i>
                            </span>
                            <span>
                                <strong>Tarjeta de Débito / Crédito</strong>
                                <small>Pago en línea seguro mediante Visa, Mastercard o American Express</small>
                            </span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>

                        <button onclick="mostrarYape()" class="opcion" type="button">
                            <span class="opcion-icono">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                            </span>
                            <span>
                                <strong>Yape / Plin</strong>
                                <small>Escanea el código QR con tu app móvil y adjunta tu comprobante</small>
                            </span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>

                    <!-- CONTENEDOR DINÁMICO DE FORMULARIO DE PAGO -->
                    <div id="contenido-pago"></div>
                </div>
            </section>

            <!-- RESUMEN DE LA ORDEN -->
            <aside class="resumen">
                <div class="resumen-header">
                    <h2>Resumen del pedido</h2>
                    <i class="fa-solid fa-receipt"></i>
                </div>

                <div class="resumen-linea">
                    <span>Subtotal</span>
                    <strong id="subtotal">S/ 0.00</strong>
                </div>

                <div class="resumen-linea">
                    <span>Envío</span>
                    <strong class="envio">GRATIS</strong>
                </div>

                <div class="separador"></div>

                <div class="total-final">
                    <span>Total a pagar</span>
                    <strong id="total">S/ 0.00</strong>
                </div>

                <div class="seguridad" style="margin-top: 25px;">
                    <i class="fa-solid fa-shield-halved"></i>
                    <div>
                        <strong>Garantía de Satisfacción Kixa</strong>
                        <p>30 días para cambios y devolución garantizada.</p>
                    </div>
                </div>

                <div style="margin-top: 20px; text-align: center;">
                    <a href="{{ route('carrito') }}" style="color: #ed001b; font-size: 13px; text-decoration: none; font-weight: 600;">
                        <i class="fa-solid fa-arrow-left"></i> Volver a revisar carrito
                    </a>
                </div>
            </aside>
        </div>
    </main>

    <!-- COMPONENTE DE ALERTA DE CONFIRMACIÓN -->
    <div id="alerta" class="alerta" style="display: none;">
        <div class="alerta-contenido animacion-alerta">
            <div class="check-confirmacion">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2>¡Pedido confirmado!</h2>
            <p id="mensaje-alerta"></p>
            <a href="{{ route('home') }}" class="btn-alerta" style="display: inline-block; text-align: center; text-decoration: none;">
                Volver a la tienda
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- [ADAPTADO]: Script de cálculo de importes y métodos de pago en public/js/carrito.js --}}
    <script src="{{ asset('js/carrito.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Mostrar por defecto la opción de contraentrega al entrar al checkout
            mostrarContraentrega();
        });
    </script>
@endpush
