@extends('layouts.app')

@section('contenido')

    <main class="mx-auto w-[min(1200px,92%)] pt-14 pb-[72px] text-tinta max-[650px]:pt-8 max-[650px]:pb-14">
        <div class="grid grid-cols-[1.2fr_0.8fr] items-start gap-14 max-[980px]:grid-cols-1 max-[980px]:gap-8">

            {{-- Galería de imágenes --}}
            <div class="flex min-w-0 gap-4 max-[650px]:flex-col-reverse">
                <div id="thumbs" class="flex w-[76px] flex-col gap-3 max-[650px]:w-full max-[650px]:flex-row max-[650px]:overflow-x-auto [&>img]:size-[76px] [&>img]:cursor-pointer [&>img]:rounded-[10px] [&>img]:border [&>img]:border-transparent [&>img]:bg-perla [&>img]:object-contain [&>img]:p-1.5 [&>img]:mix-blend-multiply [&>img]:transition-[border-color] [&>img]:duration-200 [&>img:hover]:border-tinta [&>img.active]:border-tinta max-[650px]:[&>img]:size-16 max-[650px]:[&>img]:flex-[0_0_64px]">
                    <img src="{{ $producto->url_imagen }}" class="active" alt="{{ $producto->nombre }}">
                    @foreach ($producto->galeria_imagenes ?? [] as $ruta)
                        <img src="{{ asset($ruta) }}" alt="{{ $producto->nombre }}" loading="lazy">
                    @endforeach
                </div>

                <div id="mainImg" class="group relative flex aspect-square max-h-[640px] flex-1 items-center justify-center overflow-hidden rounded-2xl bg-perla p-8 max-[650px]:p-5">
                    <img id="mainImage" src="{{ $producto->url_imagen }}" alt="{{ $producto->nombre }}" class="size-full object-contain mix-blend-multiply transition-[scale] duration-400 group-hover:scale-103">
                </div>
            </div>

            {{-- Información y compra --}}
            <aside class="sticky top-[calc(var(--spacing-navbar)_+_24px)] py-1 max-[980px]:static">
                <div class="mb-2.5 flex flex-wrap items-center gap-2 text-[13px] text-suave">
                    <span class="text-[12px] font-semibold tracking-[0.16em] text-tinta" id="productBrand">{{ strtoupper($producto->marca) }}</span>
                    <span class="text-[#cacacb]">•</span>
                    <span>{{ $producto->tipo_calzado }} · {{ $producto->categoria->nombre }}</span>
                    <span class="text-[#cacacb]">•</span>
                    <span>SKU: <strong id="productSku" class="font-medium text-[#3f3f46]">{{ $producto->sku }}</strong></span>
                </div>

                <h1 id="productName" class="mt-0 mb-2.5 text-[30px] leading-[1.2] font-semibold tracking-[-0.02em] text-tinta max-[650px]:text-[24px]">
                    {{ $producto->nombre }}
                </h1>

                <div class="mt-3 mb-5 text-[20px] font-medium text-tinta" id="productPrice">
                    S/ {{ number_format((float)$producto->precio, 2) }}
                </div>

                <p id="productDescription" class="mb-7 text-[15px] leading-[1.75] text-[#52525b]">
                    {{ $producto->descripcion ?? 'Zapatilla original con la mejor tecnología de amortiguación, materiales de alta resistencia y diseño moderno.' }}
                </p>

                {{-- Selector de tallas --}}
                <div>
                    <label class="flex items-center justify-between text-[15px] font-semibold text-tinta">
                        <span>Selecciona tu talla</span>
                        <span class="text-[13px] font-normal text-suave">Tallas Perú / US</span>
                    </label>

                    <div class="mt-3 grid grid-cols-4 gap-2 rounded-[10px] p-0.5 transition-shadow duration-300 max-[650px]:grid-cols-3" id="sizes" data-ultimas-unidades="{{ $ultimasUnidades }}">
                        @foreach ($producto->tallas as $tallaObj)
                            <button
                                type="button"
                                class="size-btn {{ $tallaObj->stock <= 0 ? 'agotado disabled' : '' }} min-h-12 cursor-pointer rounded-lg border border-[#e4e4e7] bg-white p-2.5 text-[15px] font-medium text-tinta transition-colors duration-200 hover:border-tinta [&.selected]:border-tinta [&.selected]:bg-tinta [&.selected]:text-white disabled:cursor-not-allowed disabled:border-[#f1f1f1] disabled:bg-perla disabled:text-[#b4b4b8] disabled:line-through"
                                {{ $tallaObj->stock <= 0 ? 'disabled' : '' }}
                                data-talla="{{ $tallaObj->talla_etiqueta }}"
                                data-stock="{{ $tallaObj->stock }}"
                                title="{{ $tallaObj->stock <= 0 ? 'Agotado' : 'Disponible (' . $tallaObj->stock . ' pares)' }}">
                                {{ $tallaObj->talla_etiqueta }}
                            </button>
                        @endforeach
                    </div>
                    <p id="aviso-escasez" class="mt-3 flex w-fit items-center gap-2 rounded-full bg-[#fff7ed] px-3 py-1.5 text-[13px] font-semibold text-[#b45309]" role="status" hidden>
                        <i class="fa-solid fa-fire-flame-curved"></i><span></span>
                    </p>
                    <button type="button" class="mt-3 cursor-pointer border-0 bg-transparent p-0 text-[14px] font-semibold text-tinta underline" id="abrirGuiaTallas" aria-haspopup="dialog" aria-controls="modalGuiaTallas">
                        <i class="fa-solid fa-ruler mr-1.5"></i> Guía de tallas
                    </button>
                </div>

                {{-- Carrito y favoritos --}}
                <div class="mt-7 flex items-center gap-3">
                    @if (auth()->user()?->esAdmin())
                        <a href="{{ route('admin.productos.edit', $producto) }}" class="inline-flex min-h-14 flex-1 cursor-pointer items-center justify-center gap-2.5 rounded-full border-none bg-tinta text-[16px] font-medium text-white transition-colors duration-200 hover:bg-[#333]">
                            ⚙️ Gestionar este producto en Panel Admin
                        </a>
                    @else
                        <button
                            class="inline-flex min-h-14 flex-1 cursor-pointer items-center justify-center gap-2.5 rounded-full border-none bg-tinta text-[16px] font-medium text-white transition-colors duration-200 hover:bg-[#333]"
                            id="addBtn"
                            type="button"
                            data-id="{{ $producto->sku }}"
                            data-sku="{{ $producto->sku }}"
                            data-nombre="{{ $producto->nombre }}"
                            data-precio="{{ $producto->precio }}"
                            data-imagen="{{ $producto->url_imagen }}"
                            data-marca="{{ $producto->marca }}"
                            data-tipo="{{ $producto->tipo_calzado }} • {{ $producto->categoria->nombre }}">
                            <i class="fa-solid fa-cart-shopping"></i> Agregar al Carrito
                        </button>
                    @endif

                    <button
                        class="flex size-14 shrink-0 cursor-pointer items-center justify-center rounded-full border border-[#e4e4e7] bg-white text-[20px] text-tinta transition-[border-color] duration-200 hover:border-tinta [&.activo]:border-tinta"
                        id="favBtn"
                        type="button"
                        title="Guardar en favoritos"
                        data-id="{{ $producto->sku }}"
                        data-sku="{{ $producto->sku }}"
                        data-nombre="{{ $producto->nombre }}"
                        data-precio="{{ $producto->precio }}"
                        data-imagen="{{ $producto->url_imagen }}"
                        data-marca="{{ $producto->marca }}">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                </div>

                {{-- Información de envío --}}
                <div class="mt-6 flex gap-2.5 border-t border-[#ececec] pt-5 text-[14px] leading-[1.6] text-[#52525b]">
                    <i class="fa-solid fa-truck"></i>
                    {{ $reglaEnvio }} Entrega en 24-48 horas a todo el Perú.
                </div>
            </aside>

        </div>
    </main>

    {{-- Productos recomendados --}}
    <section class="mx-auto w-[min(1200px,92%)] border-t border-[#ececec] pt-12 pb-20">
        <div class="mb-6 flex items-baseline justify-between gap-4">
            <h2 class="text-[24px] font-semibold tracking-[-0.02em]">También te puede interesar</h2>
            <a href="{{ route('catalogo', ['categoria' => $producto->categoria->slug]) }}" class="text-[15px] font-medium text-tinta underline underline-offset-4">Ver más de {{ $producto->categoria->nombre }}</a>
        </div>

        <div class="grid grid-cols-4 gap-x-4 gap-y-8 max-[980px]:grid-cols-2">
            @foreach ($recomendados as $rec)
                <x-tarjeta-producto :producto="$rec" />
            @endforeach
        </div>
    </section>

    <x-alerta
        id="toast"
        tipo="toast"
        mensaje="Añadido al carrito"
        icono="fa-solid fa-circle-check"
    />

    {{-- Modal guía de tallas --}}
    <div class="fixed inset-0 z-1200 flex items-center justify-center p-4 opacity-0 transition-opacity duration-250 [&.abierto]:opacity-100" id="modalGuiaTallas" hidden>
        <div class="absolute inset-0 bg-black/55 backdrop-blur-[4px]" data-cerrar-guia></div>
        <div class="relative max-h-[90vh] w-full max-w-[480px] overflow-y-auto rounded-2xl bg-white p-7 text-tinta" role="dialog" aria-modal="true" aria-labelledby="guiaTallasTitulo">
            <div class="mb-4 flex items-center justify-between">
                <h2 id="guiaTallasTitulo" class="m-0 text-[22px] font-extrabold tracking-[-0.03em]">Guía de tallas</h2>
                <button type="button" class="guia-tallas-cerrar cursor-pointer border-0 bg-transparent text-tinta" data-cerrar-guia aria-label="Cerrar">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
            <table class="w-full border-collapse text-center text-[14px] [&_td]:border-b [&_td]:border-[#e5e5e5] [&_td]:px-2 [&_td]:py-2.5 [&_th]:border-b [&_th]:border-[#e5e5e5] [&_th]:bg-perla [&_th]:px-2 [&_th]:py-2.5 [&_th]:font-bold">
                <thead><tr><th>Talla US / Perú</th><th>Talla EU</th><th>Largo del pie (cm)</th></tr></thead>
                <tbody>
                    @foreach ($guiaTallas as $fila)
                        <tr><td>{{ $fila['us'] }}</td><td>{{ $fila['eu'] }}</td><td>{{ $fila['cm'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <p class="mt-4 mb-0 text-[13px] leading-[1.5] text-suave"><strong>¿Cómo medir?</strong> Apoya el talón en una pared, marca la punta del dedo más largo y mide hasta la pared. Si estás entre dos tallas, elige la mayor.</p>
        </div>
    </div>

    {{-- Modal agregado al carrito --}}
    <div class="group fixed inset-0 z-2000" id="modalCarrito" hidden>
        <div class="absolute inset-0 bg-[rgba(10,10,14,0.35)] opacity-0 backdrop-blur-[10px] transition-opacity duration-250 group-[.abierto]:opacity-100" data-cerrar-modal></div>
        <div class="absolute top-6 right-6 w-[min(440px,calc(100vw_-_32px))] -translate-y-3 rounded-2xl bg-white px-6 pt-[22px] pb-6 text-tinta opacity-0 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.45)] transition-[opacity,translate] duration-250 group-[.abierto]:translate-y-0 group-[.abierto]:opacity-100 max-[520px]:top-auto max-[520px]:right-4 max-[520px]:bottom-4" role="dialog" aria-modal="true" aria-labelledby="modalCarritoTitulo">
            <div class="flex items-center justify-between border-b border-[#e5e5e5] pb-4">
                <h2 id="modalCarritoTitulo" class="flex items-center gap-2.5 text-[16px] font-semibold text-exito">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                    1 artículo agregado al carrito
                </h2>
                <button type="button" class="inline-flex size-9 cursor-pointer items-center justify-center rounded-full border-none bg-transparent text-tinta hover:bg-perla" data-cerrar-modal aria-label="Cerrar">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <div class="grid grid-cols-[88px_1fr_auto] gap-3.5 border-b border-[#e5e5e5] py-[18px] max-[520px]:grid-cols-[72px_1fr]">
                <div class="aspect-square rounded-[10px] bg-perla p-2"><img id="modalImg" src="" alt="" class="size-full object-contain mix-blend-multiply"></div>
                <div class="flex flex-col gap-[3px] text-[13px] text-suave [&_b]:font-medium [&_b]:text-tinta">
                    <strong id="modalNombre" class="text-[15px] font-semibold text-tinta"></strong>
                    <span id="modalTipo"></span>
                    <span>Talla: <b id="modalTalla"></b></span>
                    <span>Cantidad: <b id="modalCantidad">1</b></span>
                </div>
                <strong class="text-[15px] font-semibold max-[520px]:col-start-2" id="modalPrecio"></strong>
            </div>

            <div class="flex justify-between py-4 text-[15px] font-semibold">
                <span>Subtotal del carrito (<span id="modalArticulos">1 artículo</span>)</span>
                <strong id="modalSubtotal"></strong>
            </div>

            <a href="{{ route('carrito') }}" data-enfocar class="flex w-full cursor-pointer items-center justify-center rounded-full border border-tinta bg-tinta p-[15px] text-[14px] font-semibold tracking-[0.04em] text-white transition-colors duration-200 hover:bg-[#333]">VER CARRITO Y PAGAR</a>
            <button type="button" class="mt-2.5 flex w-full cursor-pointer items-center justify-center rounded-full border border-[#cacacb] bg-white p-[15px] text-[14px] font-semibold text-tinta transition-colors duration-200 hover:border-tinta hover:bg-perla" data-cerrar-modal>Seguir comprando</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/producto-dinamico.js') }}"></script>
    <script src="{{ asset('js/guia-tallas.js') }}"></script>
@endpush
