@extends('layouts.app')

@section('titulo', 'Carrito de compras | KixaStore')

@section('contenido')
    {{-- Ítems a la izquierda, Resumen a la derecha y Favoritos abajo (en móvil todo en una sola columna, sin scroll horizontal) --}}
    <main class="max-w-full min-w-0 bg-white px-6 pt-12 pb-[72px] text-tinta max-escritorio:w-full max-escritorio:px-4 max-escritorio:pt-7 max-escritorio:pb-12">
        {{-- Contenedor centrado de 1080px (igual que Favoritos): evita el hueco vacío entre columnas en pantallas anchas --}}
        <div class="mx-auto w-full max-w-[1080px]">
        <div class="grid grid-cols-[minmax(0,1fr)_340px] items-start gap-10 lg:gap-12 *:max-w-full *:min-w-0 max-[960px]:grid-cols-1 max-[960px]:gap-8 max-escritorio:flex max-escritorio:flex-col max-escritorio:gap-7 max-escritorio:*:w-full">
            <section aria-labelledby="titulo-carrito">
                <div class="mb-2 flex items-baseline gap-3">
                    <h1 id="titulo-carrito" class="text-[24px] font-semibold tracking-[-0.02em]">Carrito de compras</h1>
                    <span id="cantidad-productos" class="text-[15px] text-suave"></span>
                </div>

                {{-- Los artículos se generan con public/js/carrito.js (id="carrito") --}}
                <div id="carrito"></div>
            </section>

            {{-- data-regla-envio: costo, mínimo y primera compra (CarritoController::reglaEnvio); carrito.js aplica la misma regla de Pedido::calcularEnvio --}}
            <aside class="sticky top-[calc(var(--spacing-navbar)_+_24px)] max-[960px]:static" aria-labelledby="titulo-resumen" data-regla-envio='@json($reglaEnvio)'>
                <h2 id="titulo-resumen" class="mb-4 text-[24px] font-semibold tracking-[-0.02em]">Resumen</h2>

                <div class="flex justify-between py-2 text-[15px]">
                    <span>Precio con IGV</span>
                    <span id="subtotal">S/ 0.00</span>
                </div>
                <div class="flex justify-between py-2 text-[15px]">
                    <span>Envío</span>
                    <span id="envio">Gratis</span>
                </div>
                <div class="flex justify-between py-2 text-[15px]">
                    <span>IGV (18%)</span>
                    <span id="igv">S/ 0.00</span>
                </div>

                <div class="mt-3 flex justify-between border-y border-[#e5e5e5] py-4 text-[15px] font-semibold">
                    <span>Total de la orden</span>
                    <strong id="total">S/ 0.00</strong>
                </div>

                {{-- Barra de envío gratis: carrito.js escribe cuánto falta para llegar al mínimo (o la llena en la primera compra) --}}
                <div class="mt-5 mb-7 text-[13px]" id="envio-gratis">
                    <p id="envio-texto">¡Te ganaste el envío gratis!</p>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-[#e5e5e5]"><span id="envio-progreso" class="block h-full w-0 rounded-[inherit] bg-exito transition-[width] duration-600"></span></div>
                    <p class="mt-2 text-[12px] text-suave">Envío gratis en tu primera compra o desde S/ {{ number_format($reglaEnvio['gratis_desde'], 2) }}; si no, S/ {{ number_format($reglaEnvio['costo'], 2) }}.</p>
                </div>

                @guest
                    {{-- Tras iniciar sesión vuelve directo al pago --}}
                    <a href="{{ route('login', ['redirect' => 'checkout']) }}" class="mt-2 flex w-full cursor-pointer items-center justify-center rounded-full border-none bg-tinta p-[18px] text-[16px] font-medium text-white transition-colors duration-200 hover:bg-[#333]">Iniciar sesión para comprar</a>
                @endguest
                @auth
                    @if (auth()->user()->esAdmin())
                        {{-- El admin no puede pagar; se le ofrece el panel --}}
                        <a href="{{ route('admin.productos.index') }}" class="mt-2 flex w-full cursor-pointer items-center justify-center rounded-full border-none bg-tinta p-[18px] text-[16px] font-medium text-white transition-colors duration-200 hover:bg-[#333]">Gestionar productos en Panel Admin</a>
                        <p class="mt-2.5 text-center text-[12px] text-suave">La cuenta Administrador es solo de gestión y no puede realizar compras.</p>
                    @else
                        {{-- carrito.js le agrega la clase "deshabilitado" si el carrito está vacío --}}
                        <a href="{{ route('checkout') }}" id="btn-comprar" class="mt-2 flex w-full cursor-pointer items-center justify-center rounded-full border-none bg-tinta p-[18px] text-[16px] font-medium text-white transition-colors duration-200 hover:bg-[#333] [&.deshabilitado]:pointer-events-none [&.deshabilitado]:bg-[#e5e5e5] [&.deshabilitado]:text-[#9e9ea0]">Comprar</a>
                    @endif
                @endauth
                <x-metodos-pago class="mt-6 border-t border-[#e5e5e5] pt-4" />
            </aside>
        </div>

        <section class="mt-[72px]" aria-labelledby="titulo-favoritos">
            <div class="mb-5 flex items-baseline justify-between">
                <h2 id="titulo-favoritos" class="text-[24px] font-semibold tracking-[-0.02em]">Favoritos</h2>
                <a href="{{ route('favoritos') }}" class="text-[15px] font-medium text-tinta underline underline-offset-4">Ir a Favoritos</a>
            </div>
            <div id="favoritos-carrito"></div>
        </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/carrito.js') }}"></script>
@endpush
