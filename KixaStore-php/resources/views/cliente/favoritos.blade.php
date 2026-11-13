@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos del catálogo y contenedor de productos de Tienda Kixa --}}
    <link rel="stylesheet" href="{{ asset('css/hombre.css') }}">
    <style>
        .favoritos-hero {
            background: linear-gradient(180deg, #111111 0%, #090a0b 100%);
            padding: 50px 20px 30px;
            text-align: center;
            border-bottom: 1px solid #222;
        }
        .favoritos-hero h1 {
            font-size: 2.2rem;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0 0 10px;
            color: #ffffff;
        }
        .favoritos-hero p {
            color: #888888;
            font-size: 1rem;
            margin: 0;
        }
        .favoritos-vacio {
            text-align: center;
            padding: 80px 20px;
            color: #aaa;
        }
        .favoritos-vacio i {
            font-size: 3.5rem;
            color: #ed001b;
            margin-bottom: 20px;
            display: inline-block;
        }
        .btn-explorar {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #ed001b;
            color: #ffffff;
            padding: 12px 28px;
            border-radius: 6px;
            font-weight: 700;
            text-decoration: none;
            margin-top: 20px;
            transition: all 0.2s ease;
        }
        .btn-explorar:hover {
            background: #ff1f35;
            transform: translateY(-2px);
        }
        .card-fav-actions {
            display: flex;
            gap: 8px;
            margin-top: 14px;
        }
        .btn-mover-carrito {
            flex: 1;
            background: #111;
            color: #fff;
            border: 1px solid #333;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-mover-carrito:hover {
            background: #ed001b;
            border-color: #ed001b;
        }
        .btn-quitar-fav {
            background: rgba(237, 0, 27, 0.15);
            color: #ff4d5e;
            border: 1px solid rgba(237, 0, 27, 0.3);
            width: 36px;
            height: 36px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all 0.2s;
        }
        .btn-quitar-fav:hover {
            background: #ed001b;
            color: #fff;
        }
    </style>
@endpush

@section('contenido')
    {{-- [ORIGEN]: Creado para la sección de Favoritos / Lista de Deseos de Tienda Kixa --}}
    {{-- [CAMBIO: VISTA DINAMICA DE FAVORITOS CONECTADA A LOCALSTORAGE Y COMPATIBLE CON EL CARRITO] --}}

    <section class="favoritos-hero">
        <div class="section-title" style="justify-content: center; display: flex; gap: 10px; align-items: center; color: #ed001b; font-size: 0.85rem; letter-spacing: 2px;">
            <span>—</span> MI LISTA DE DESEOS <span>—</span>
        </div>
        <h1>Tus Zapatillas <strong>Favoritas</strong></h1>
        <p>Modelos que has guardado. Pásalos a tu carrito o revísalos en cualquier momento.</p>
    </section>

    <main class="productos-container" style="padding-top: 40px; padding-bottom: 60px; min-height: 400px;">
        <div id="favoritos-contenido">
            <!-- Renderizado dinámico mediante JavaScript -->
        </div>
    </main>

    <!-- COMPONENTE TOAST DE CONFIRMACION -->
    <x-alerta 
        id="toast-fav" 
        tipo="toast" 
        mensaje="Producto agregado al carrito" 
        icono="fa-solid fa-circle-check" 
    />
@endsection

@push('scripts')
<script>
    // [CAMBIO: LOGICA COMPLETA DE GESTION DE FAVORITOS EN MODO CLIENTE]
    function cargarFavoritos() {
        const contenedor = document.getElementById('favoritos-contenido');
        if (!contenedor) return;

        const favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];

        if (favoritos.length === 0) {
            contenedor.innerHTML = `
                <div class="favoritos-vacio">
                    <i class="fa-regular fa-heart"></i>
                    <h2 style="color: #fff; font-size: 22px; margin-bottom: 10px;">Tu lista de favoritos está vacía</h2>
                    <p style="color: #888; font-size: 14px; max-width: 450px; margin: 0 auto;">
                        Explora nuestra tienda y presiona el ícono del corazón en las zapatillas que más te gusten para guardarlas aquí.
                    </p>
                    <a href="{{ route('catalogo') }}" class="btn-explorar">
                        <i class="fa-solid fa-bag-shopping"></i> Explorar Catálogo
                    </a>
                </div>
            `;
            return;
        }

        let html = '<div class="grid-productos">';
        favoritos.forEach((item, index) => {
            const precioFormatted = Number(item.precio || 0).toFixed(2);
            const detalleUrl = `/producto/${item.sku || item.id}`;

            html += `
                <article class="producto">
                    <div class="producto-img" style="position: relative;">
                        <img src="${item.imagen}" alt="${item.nombre}">
                        <div class="producto-action">
                            <a href="${detalleUrl}">
                                Ver producto
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="producto-info">
                        <div class="marca">${(item.marca || 'KIXA').toUpperCase()}</div>
                        <h3>${item.nombre}</h3>
                        <div class="producto-bottom">
                            <p class="precio">S/ ${precioFormatted}</p>
                            <a href="${detalleUrl}" class="mini-btn" title="Ver detalle">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>

                        
                        <div class="card-fav-actions">
                            <button class="btn-mover-carrito" onclick="moverAlCarrito(${index})" type="button">
                                <i class="fa-solid fa-cart-plus"></i> Agregar al carrito
                            </button>
                            <button class="btn-quitar-fav" onclick="quitarDeFavoritos(${index})" title="Eliminar de favoritos" type="button">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </article>
            `;
        });
        html += '</div>';

        contenedor.innerHTML = html;
    }

    function quitarDeFavoritos(index) {
        const favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];
        favoritos.splice(index, 1);
        localStorage.setItem('favoritos', JSON.stringify(favoritos));
        cargarFavoritos();
        if (typeof actualizarBadgesHeader === 'function') {
            actualizarBadgesHeader();
        }
    }

    function moverAlCarrito(index) {
        const favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];
        const item = favoritos[index];
        if (!item) return;

        const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
        carrito.push({
            id: item.id,
            sku: item.sku || item.id,
            nombre: item.nombre,
            precio: parseFloat(item.precio) || 0,
            talla: "40", // Talla promedio por defecto al mover directo de favoritos
            imagen: item.imagen,
            cantidad: 1
        });

        localStorage.setItem('carrito', JSON.stringify(carrito));
        if (typeof actualizarBadgesHeader === 'function') {
            actualizarBadgesHeader();
        }

        const toast = document.getElementById('toast-fav');
        if (toast) {
            const msg = toast.querySelector('.alerta-mensaje') || toast;
            msg.innerText = `${item.nombre} añadido al carrito con éxito ✅`;
            toast.style.display = 'flex';
            setTimeout(() => { toast.style.display = 'none'; }, 2500);
        }
    }

    document.addEventListener('DOMContentLoaded', cargarFavoritos);
</script>
@endpush
