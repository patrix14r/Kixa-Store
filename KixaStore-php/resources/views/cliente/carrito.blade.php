@extends('layouts.app')

@section('titulo', 'Carrito de compras | KixaStore')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/carrito.css') }}">
@endpush

@section('contenido')
    {{-- Ítems a la izquierda, Resumen a la derecha y Favoritos abajo --}}
    <main class="kx-carrito-page">
        <div class="kx-carrito-layout">
            <section class="kx-carrito-items" aria-labelledby="titulo-carrito">
                <div class="kx-carrito-titulo">
                    <h1 id="titulo-carrito">Carrito de compras</h1>
                    <span id="cantidad-productos"></span>
                </div>

                {{-- Los artículos se generan con public/js/carrito.js (id="carrito") --}}
                <div id="carrito"></div>
            </section>

            <aside class="kx-resumen" aria-labelledby="titulo-resumen">
                <h2 id="titulo-resumen">Resumen</h2>

                <div class="kx-resumen-linea">
                    <span>Precio con IGV</span>
                    <span id="subtotal">S/ 0.00</span>
                </div>
                <div class="kx-resumen-linea">
                    <span>Envío</span>
                    <span>Gratis</span>
                </div>
                <div class="kx-resumen-linea">
                    <span>IGV (18%)</span>
                    <span id="igv">S/ 0.00</span>
                </div>

                <div class="kx-resumen-total">
                    <span>Total de la orden</span>
                    <strong id="total">S/ 0.00</strong>
                </div>

                <div class="kx-envio-gratis" id="envio-gratis">
                    <p id="envio-texto">¡Te ganaste el envío gratis!</p>
                    <div class="kx-envio-barra"><span id="envio-progreso"></span></div>
                </div>

                @guest
                    {{-- Tras iniciar sesión vuelve directo al pago --}}
                    <a href="{{ route('login', ['redirect' => 'checkout']) }}" class="kx-btn-comprar">Iniciar sesión para comprar</a>
                @endguest
                @auth
                    @if (auth()->user()->esAdmin())
                        {{-- El admin no puede pagar; se le ofrece el panel --}}
                        <a href="{{ route('admin.productos.index') }}" class="kx-btn-comprar">Gestionar productos en Panel Admin</a>
                        <p class="kx-resumen-nota">La cuenta Administrador es solo de gestión y no puede realizar compras.</p>
                    @else
                        <a href="{{ route('checkout') }}" class="kx-btn-comprar" id="btn-comprar">Comprar</a>
                    @endif
                @endauth
            </aside>
        </div>

        <section class="kx-favoritos-carrito" aria-labelledby="titulo-favoritos">
            <div class="kx-favoritos-cabecera">
                <h2 id="titulo-favoritos">Favoritos</h2>
                <a href="{{ route('favoritos') }}">Ir a Favoritos</a>
            </div>
            <div id="favoritos-carrito"></div>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/carrito.js') }}"></script>
@endpush
