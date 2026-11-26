@extends('layouts.app')

@section('titulo', 'Favoritos | KixaStore')

@section('contenido')
    {{-- Filas horizontales: foto, nombre, precio, SELECCIONAR TALLA y ELIMINAR (las arma public/js/favoritos.js) --}}
    <main class="mx-auto w-full max-w-[1080px] px-[5%] pt-12 pb-[72px] text-tinta">
        <div class="mb-6 flex items-baseline justify-between">
            <h1 class="text-[26px] font-bold uppercase">Favoritos</h1>
            @auth
                <a href="{{ route('perfil', 'favoritos') }}" class="cursor-pointer text-[14px] font-medium text-tinta underline underline-offset-4">Ver en mi cuenta</a>
            @endauth
        </div>
        <div id="favoritos-filas" class="flex flex-col gap-3.5"></div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/favoritos.js') }}"></script>
@endpush
