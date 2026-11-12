@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos de login de Tienda-Kixa_2/css/login.css --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de Tienda-Kixa_2/login.html (Líneas 130 a 220) --}}
    {{-- [SEPARADO]: El formulario de registro que estaba dentro de este contenedor se extrajo hacia resources/views/auth/registro.blade.php --}}
    {{-- [ADAPTADO]: Se agregó @csrf al <form>, se enlazó el registro con route('registro') y se removió header/footer --}}
    <div class="background">
        <div class="overlay"></div>

        <div class="login-box" id="formContainer">

            <form id="loginForm" class="form active" method="POST" action="#">
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

                <!-- CORREO -->
                <div class="input-group">
                    <input type="email" id="loginEmail" name="email" required>
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
    {{-- [ADAPTADO]: Script de autenticación e interactividad en public/js/login.js --}}
    <script src="{{ asset('js/login.js') }}"></script>
@endpush
