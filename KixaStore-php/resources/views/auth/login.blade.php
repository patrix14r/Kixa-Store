@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos de login de Tienda-Kixa_2/css/login.css --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <style>
        .error-message {
            color: #ff3344;
            font-size: 13px;
            margin-top: 5px;
            display: block;
            font-weight: 600;
        }
        .alert-error {
            background: rgba(237, 0, 27, 0.15);
            border: 1px solid #ed001b;
            color: #ff4d5e;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
            text-align: center;
        }
    </style>
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de Tienda-Kixa_2/login.html (Líneas 130 a 220) --}}
    {{-- [CAMBIO: CONECTADO AL CONTROLADOR AUTHCONTROLLER CON RUTA LOGIN.POST Y VALIDACION MYSQL] --}}
    <div class="background">
        <div class="overlay"></div>

        <div class="login-box" id="formContainer">

            {{-- [CAMBIO: ACTION HACIA LA RUTA LOGIN.POST CON METODO POST Y @csrf] --}}
            <form id="loginForm" class="form active" method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="form-icon">
                    <i class="fa-solid fa-user"></i>
                </div>

                <h2>
                    Bienvenido a <span>Kixa</span>
                </h2>

                <p class="subtitle">
                    Inicia sesión para continuar
                </p>

                {{-- [CAMBIO: ALERTA VISUAL DE ERROR DE AUTENTICACION CON ERROR DE EMAIL] --}}
                @error('email')
                    <div class="alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                    </div>
                @enderror

                <!-- CORREO -->
                <div class="input-group">
                    <input type="email" id="loginEmail" name="email" value="{{ old('email') }}" required autofocus>
                    <label for="loginEmail">
                        Correo electrónico
                    </label>
                </div>

                <!-- CONTRASEÑA -->
                <div class="input-group">
                    <input type="password" id="loginPassword" name="password" required>
                    <label for="loginPassword">
                        Contraseña
                    </label>
                </div>

                <button type="submit" class="btn">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Ingresar
                </button>

                <p class="register-text">
                    ¿No tienes cuenta?
                    <a href="{{ route('registro') }}">
                        Regístrate
                    </a>
                </p>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    {{-- [ADAPTADO]: Script de interactividad en public/js/login.js --}}
    <script src="{{ asset('js/login.js') }}"></script>
@endpush
