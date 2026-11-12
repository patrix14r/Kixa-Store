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
            padding: 60px 20px;
            color: #aaa;
        }
        .favoritos-vacio i {
            font-size: 3.5rem;
            color: #e50914;
            margin-bottom: 15px;
            display: inline-block;
        }
    </style>
@endpush

@section('contenido')
    {{-- [ORIGEN]: Creado para la sección de Favoritos / Lista de Deseos de Tienda Kixa --}}
    {{-- [ADAPTADO]: Integrado con layouts.app y reutilizando el componente <x-tarjeta-producto> --}}

    <section class="favoritos-hero">
        <div class="section-title" style="justify-content: center; display: flex; gap: 10px; align-items: center; color: #e50914; font-size: 0.85rem; letter-spacing: 2px;">
            <span>—</span> MI LISTA DE DESEOS <span>—</span>
        </div>
        <h1>Tus Zapatillas <strong>Favoritas</strong></h1>
        <p>Guarda y gestiona los modelos que más te gustan para comprarlos cuando quieras.</p>
    </section>

    <main class="productos-container" style="padding-top: 40px; padding-bottom: 60px;">
        <div class="grid-productos">
            {{-- PRODUCTOS GUARDADOS DE EJEMPLO USANDO EL COMPONENTE REUTILIZABLE --}}
            <x-tarjeta-producto 
                id="jordan-retro-black"
                nombre="Jordan Retro Black"
                marca="Jordan"
                dataMarca="jordan"
                precio="259.00"
                imagen="images/products/hombre/h1.jpg"
            />

            <x-tarjeta-producto 
                id="nike-air-max-90"
                nombre="Nike Air Max 90"
                marca="Nike"
                dataMarca="nike"
                precio="199.00"
                imagen="images/products/mujer/m2.jpg"
            />

            <x-tarjeta-producto 
                id="puma-suede-classic"
                nombre="Puma Suede Classic"
                marca="Puma"
                dataMarca="puma"
                precio="110.00"
                imagen="images/products/hombre/h5.jpg"
            />
        </div>
    </main>
@endsection
