@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos de carrito de Tienda-Kixa_2/css/carrito.css --}}
    <link rel="stylesheet" href="{{ asset('css/carrito.css') }}">
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de Tienda-Kixa_2/carrito.html (Líneas 74 a 154) --}}
    {{-- [SEPARADO]: La ventana modal de pago se separó hacia cliente/checkout.blade.php y la alerta de confirmación hacia components/alerta.blade.php --}}
    {{-- [ADAPTADO]: Se integró a layouts.app, eliminando header y footer repetidos --}}
    <main class="carrito-page">
        <div class="carrito-header">
            <div>
                <span class="mini-titulo">TU COMPRA</span>
                <h1>
                    <i class="fa-solid fa-cart-shopping"></i>
                    Carrito de compras
                </h1>
                <p>Revisa tus productos antes de realizar tu pedido.</p>
            </div>
        </div>

        <div class="carrito-layout">
            <!-- PRODUCTOS SELECCIONADOS -->
            <section class="productos-section">
                <div class="productos-header">
                    <h2>Productos seleccionados</h2>
                    <span id="cantidad-productos"></span>
                </div>

                <div id="carrito" class="carrito-contenedor">
                    <!-- Los artículos se generan dinámicamente con public/js/carrito.js -->
                </div>
            </section>

            <!-- RESUMEN DEL PEDIDO -->
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
                    <span>Total</span>
                    <strong id="total">S/ 0.00</strong>
                </div>

                {{-- [CAMBIO: REDIRECCION A LOGIN PARA INVITADOS O CHECKOUT PARA USUARIOS AUTENTICADOS] --}}
                @guest
                    <a href="{{ route('login') }}" class="btn-comprar-principal" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px;">
                        <span>Iniciar sesión para comprar</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                @endguest
                @auth
                    <a href="{{ route('checkout') }}" class="btn-comprar-principal" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px;">
                        <span>Continuar con la compra</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                @endauth

                <div class="seguridad">
                    <i class="fa-solid fa-shield-halved"></i>
                    <div>
                        <strong>Compra segura</strong>
                        <p>Tus datos están protegidos.</p>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <!-- MODAL DE SELECCIÓN DE PAGO -->
    <div id="modal" class="modal" style="display: none;">
        <div class="modal-contenido animacion-modal">
            <button class="cerrar" onclick="cerrarModal()" type="button" aria-label="Cerrar modal">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="modal-titulo">
                <div class="icono-pago">
                    <i class="fa-solid fa-credit-card"></i>
                </div>
                <div>
                    <h2>Selecciona tu método</h2>
                    <p>Elige cómo deseas realizar tu pago.</p>
                </div>
            </div>

            <div class="opciones">
                <button onclick="mostrarContraentrega()" class="opcion" type="button">
                    <span class="opcion-icono">
                        <i class="fa-solid fa-box"></i>
                    </span>
                    <span>
                        <strong>Contraentrega</strong>
                        <small>Paga cuando recibas tu pedido</small>
                    </span>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

                <button onclick="mostrarTarjeta()" class="opcion" type="button">
                    <span class="opcion-icono">
                        <i class="fa-solid fa-credit-card"></i>
                    </span>
                    <span>
                        <strong>Tarjeta</strong>
                        <small>Pago con tarjeta de débito o crédito</small>
                    </span>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

                <button onclick="mostrarYape()" class="opcion" type="button">
                    <span class="opcion-icono">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </span>
                    <span>
                        <strong>Yape</strong>
                        <small>Realiza tu pago instantáneo por Yape</small>
                    </span>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

            <div id="contenido-pago"></div>
        </div>
    </div>

    <!-- COMPONENTE DE ALERTA DE CONFIRMACIÓN -->
    <div id="alerta" class="alerta" style="display: none;">
        <div class="alerta-contenido animacion-alerta">
            <div class="check-confirmacion">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2>¡Pedido confirmado!</h2>
            <p id="mensaje-alerta"></p>
            <button onclick="cerrarAlerta()" class="btn-alerta" type="button">
                Volver a la tienda
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- [ADAPTADO]: Script de renderizado del carrito e interactividad de pagos en public/js/carrito.js --}}
    <script src="{{ asset('js/carrito.js') }}"></script>
@endpush
