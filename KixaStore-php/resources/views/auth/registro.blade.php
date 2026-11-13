@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos de registro y login de Tienda-Kixa_2/css/login.css --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <style>
        .alert-error {
            background: rgba(237, 0, 27, 0.15);
            border: 1px solid #ed001b;
            color: #ff4d5e;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
            text-align: left;
        }
        .alert-error ul {
            margin: 5px 0 0 18px;
            padding: 0;
        }
    </style>
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de Tienda-Kixa_2/login.html (Líneas 226 a 485) --}}
    {{-- [CAMBIO: FORMULARIO DE REGISTRO CONECTADO A MYSQL CON AUTHCONTROLLER] --}}
    <div class="background">
        <div class="overlay"></div>

        <div class="login-box" id="formContainer" style="max-width: 580px;">

            {{-- [CAMBIO: ACTION HACIA LA RUTA REGISTRO.POST CON METODO POST Y @csrf] --}}
            <form id="registerForm" class="form active" method="POST" action="{{ route('registro.post') }}">
                @csrf

                <div class="form-icon">
                    <i class="fa-solid fa-user-plus"></i>
                </div>

                <h2>
                    Crea tu cuenta en <span>Kixa</span>
                </h2>

                <p class="subtitle">
                    Completa tus datos para registrarte
                </p>

                {{-- [CAMBIO: MUESTRA DE ERRORES DE VALIDACION DE REGISTRO] --}}
                @if ($errors->any())
                    <div class="alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i> Corrige los siguientes errores:
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="registro-grid">

                    <!-- NOMBRE -->
                    <div class="input-group">
                        <input type="text" id="registerName" name="name" value="{{ old('name') }}" required>
                        <label for="registerName">
                            Nombre completo
                        </label>
                    </div>

                    <!-- CORREO -->
                    <div class="input-group">
                        <input type="email" id="registerEmail" name="email" value="{{ old('email') }}" required>
                        <label for="registerEmail">
                            Correo electrónico
                        </label>
                    </div>

                    <!-- TELEFONO (OPCIONAL O COMPLEMENTARIO) -->
                    {{-- // [VALIDACION FORMULARIO]: Telefono de exactamente 9 digitos numericos --}}
                    <div class="input-group">
                        <input type="tel" id="registerPhone" name="phone" value="{{ old('phone') }}"
                               maxlength="9" minlength="9" pattern="[0-9]{9}" inputmode="numeric"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               placeholder="9 dígitos numéricos">
                        <label for="registerPhone">
                            Número de teléfono (9 dígitos)
                        </label>
                        @error('phone')
                            <span class="error-text" style="color: #ff4d5e; font-size: 11px; display: block; margin-top: 4px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- DNI (OPCIONAL O COMPLEMENTARIO) -->
                    {{-- // [VALIDACION FORMULARIO]: DNI de exactamente 8 digitos numericos --}}
                    <div class="input-group">
                        <input type="text" id="registerDni" name="dni" value="{{ old('dni') }}"
                               maxlength="8" minlength="8" pattern="[0-9]{8}" inputmode="numeric"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               placeholder="8 dígitos numéricos">
                        <label for="registerDni">
                            DNI (8 dígitos)
                        </label>
                        @error('dni')
                            <span class="error-text" style="color: #ff4d5e; font-size: 11px; display: block; margin-top: 4px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- CONTRASEÑA -->
                    {{-- // [VALIDACION FORMULARIO]: Contrasenia con minimo 8 caracteres y mensaje de error --}}
                    <div class="input-group">
                        <input type="password" id="registerPassword" name="password" minlength="8" placeholder="Mínimo 8 caracteres" required>
                        <label for="registerPassword">
                            Contraseña (Mínimo 8 caracteres)
                        </label>
                        @error('password')
                            <span class="error-text" style="color: #ff4d5e; font-size: 11px; display: block; margin-top: 4px; font-weight: 600;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- CONFIRMACIÓN DE CONTRASEÑA -->
                    {{-- // [VALIDACION FORMULARIO]: Confirmacion con minimo 8 caracteres --}}
                    <div class="input-group">
                        <input type="password" id="registerPasswordConfirm" name="password_confirmation" minlength="8" placeholder="Mínimo 8 caracteres" required>
                        <label for="registerPasswordConfirm">
                            Confirmar contraseña
                        </label>
                        @error('password_confirmation')
                            <span class="error-text" style="color: #ff4d5e; font-size: 11px; display: block; margin-top: 4px; font-weight: 600;">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <button type="submit" class="btn">
                    <i class="fa-solid fa-user-plus"></i>
                    Crear cuenta
                </button>

                <p class="register-text">
                    ¿Ya tienes una cuenta?
                    <a href="{{ route('login') }}">
                        Inicia sesión
                    </a>
                </p>

            </form>

        </div>
    </div>
@endsection

@push('scripts')
    {{-- [ADAPTADO]: Script de interfaz en public/js/login.js --}}
    <script src="{{ asset('js/login.js') }}"></script>
@endpush
