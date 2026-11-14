@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cuenta.css') }}">
@endpush

@section('contenido')
    {{-- [Sem 08 - Rutas y Vistas Blade]: Login con labels fijos arriba de cada input y errores con @error --}}
    <div class="auth-page">
        {{-- [Actualizado: Login split-screen] Tarjeta editorial de 2 columnas: marca a la izquierda y formulario a la derecha --}}
        <div class="auth-card">
            @include('auth._showcase')

            {{-- novalidate: se reemplazan los globos del navegador ("Please fill out this field.") por mensajes en español --}}
            <form id="loginForm" class="auth-form" method="POST" action="{{ route('login.post') }}" novalidate data-validar-auth>
                @csrf

                <h1>Bienvenido a <span>Kixa</span></h1>
                <p class="auth-subtitulo">Inicia sesión para continuar</p>

                {{-- [Actualizado: Toast único] El error de credenciales aparece en el toast flotante del layout --}}
                {{-- [Actualizado: Login → Checkout] ?redirect=checkout viaja oculto para volver al pago tras ingresar --}}
                @if (request('redirect'))
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                @endif

                <div class="auth-campo">
                    <label for="loginEmail">Correo electrónico</label>
                    <input type="email" id="loginEmail" name="email" value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" autocomplete="email" required autofocus>
                </div>

                <div class="auth-campo">
                    <label for="loginPassword">Contraseña</label>
                    <input type="password" id="loginPassword" name="password" placeholder="Tu contraseña" autocomplete="current-password" required>
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fa-solid fa-right-to-bracket"></i> Ingresar
                </button>

                <p class="auth-pie">¿No tienes cuenta? <a href="{{ route('registro', request()->only('redirect')) }}">Regístrate</a></p>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth.js') }}"></script>
@endpush
