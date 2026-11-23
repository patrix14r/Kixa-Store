@extends('layouts.app')

@section('titulo', 'Favoritos | KixaStore')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
@endpush

@section('contenido')
    {{-- Filas horizontales: foto, nombre, precio, SELECCIONAR TALLA y ELIMINAR --}}
    <main class="kx-favoritos-pagina">
        <div class="kx-favoritos-pagina-cabecera">
            <h1>Favoritos</h1>
            @auth
                <a href="{{ route('perfil', 'favoritos') }}" class="kx-enlace">Ver en mi cuenta</a>
            @endauth
        </div>
        <div id="favoritos-filas" class="kx-favoritos-filas"></div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/favoritos.js') }}"></script>
@endpush
