@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cuenta.css') }}">
@endpush

@section('contenido')
    {{-- [Sem 08 - Rutas y Vistas Blade]: Registro con labels fijos arriba de cada input (no se montan sobre el placeholder) --}}
    <div class="auth-page">
        {{-- [Actualizado: Registro split-screen] Misma tarjeta editorial de 2 columnas que el Login --}}
        <div class="auth-card auth-card--registro">
            @include('auth._showcase')

            {{-- novalidate: validación propia con mensajes 100% en español (public/js/auth.js) --}}
            <form id="registerForm" class="auth-form" method="POST" action="{{ route('registro.post') }}" novalidate data-validar-auth>
                {{-- [Actualizado: Login → Checkout] Conserva ?redirect=checkout al registrarse --}}
                @if (request('redirect'))
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                @endif
                @csrf

                <h1>Crea tu cuenta en <span>Kixa</span></h1>
                <p class="auth-subtitulo">Completa tus datos para registrarte</p>

                {{-- [Actualizado: Toast único] Los errores de validación se muestran en el toast flotante del layout --}}

                <div class="auth-campo">
                    <label for="registerName">Nombre completo</label>
                    <input type="text" id="registerName" name="name" value="{{ old('name') }}" placeholder="Ej. Ana Torres" autocomplete="name" required>
                </div>

                <div class="auth-campo">
                    <label for="registerEmail">Correo electrónico</label>
                    <input type="email" id="registerEmail" name="email" value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" autocomplete="email" required>
                </div>

                {{-- // [VALIDACION FORMULARIO]: Teléfono de 9 dígitos y DNI de 8 dígitos (ambos opcionales) --}}
                <div class="auth-fila">
                    <div class="auth-campo">
                        <label for="registerPhone">Teléfono <small>(opcional)</small></label>
                        <input type="tel" id="registerPhone" name="phone" value="{{ old('phone') }}"
                               maxlength="9" minlength="9" pattern="[0-9]{9}" inputmode="numeric"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               placeholder="9 dígitos" class="@error('phone') input-invalido @enderror">
                    </div>

                    <div class="auth-campo">
                        <label for="registerDni">DNI <small>(opcional)</small></label>
                        <input type="text" id="registerDni" name="dni" value="{{ old('dni') }}"
                               maxlength="8" minlength="8" pattern="[0-9]{8}" inputmode="numeric"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               placeholder="8 dígitos" class="@error('dni') input-invalido @enderror">
                    </div>
                </div>

                {{-- // [VALIDACION FORMULARIO]: Contraseña con mínimo 8 caracteres y confirmación --}}
                <div class="auth-campo">
                    <label for="registerPassword">Contraseña</label>
                    <input type="password" id="registerPassword" name="password" minlength="8" placeholder="Mínimo 8 caracteres" autocomplete="new-password" required class="@error('password') input-invalido @enderror">
                </div>

                <div class="auth-campo">
                    <label for="registerPasswordConfirm">Confirmar contraseña</label>
                    <input type="password" id="registerPasswordConfirm" name="password_confirmation" minlength="8" placeholder="Repite tu contraseña" autocomplete="new-password" required>
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fa-solid fa-user-plus"></i> Crear cuenta
                </button>

                <p class="auth-pie">¿Ya tienes una cuenta? <a href="{{ route('login', request()->only('redirect')) }}">Inicia sesión</a></p>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth.js') }}"></script>
@endpush
