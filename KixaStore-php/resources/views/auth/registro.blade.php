@extends('layouts.app')

{{-- Fondo oscuro del body para que no asome blanco detrás de la portada --}}
@section('fondo', 'bg-[#090a0b] text-white')

@section('contenido')
    {{-- Fondo (fondo2.jpg), cristal y bordes rounded-lg de los inputs se editan en components/auth-cristal.blade.php;
         kx-auth-input (resources/css/app.css) evita el fondo blanco del autocompletado del navegador. --}}
    {{-- [Sem 08 - Rutas y Vistas Blade]: Registro de Fortify (POST /register) con la misma tarjeta cristal del Login, algo más ancha por la fila Teléfono/DNI --}}
    <x-auth-cristal ancho="max-w-[480px]" titulo="Crea tu cuenta en Kixa" subtitulo="Completa tus datos para registrarte">
        {{-- novalidate: validación propia con mensajes 100% en español (public/js/auth.js) --}}
        <form id="registerForm" method="POST" action="{{ route('register') }}" novalidate data-validar-auth>
            {{-- Conserva ?redirect=checkout al registrarse --}}
            @if (request('redirect'))
                <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            @endif
            @csrf

            {{-- Los errores de validación se muestran en el toast flotante del layout --}}

            <div class="auth-campo">
                <label for="registerName">Nombre completo</label>
                <input class="kx-auth-input" type="text" id="registerName" name="name" value="{{ old('name') }}" placeholder="Ej. Ana Torres" autocomplete="name" required>
            </div>

            <div class="auth-campo">
                <label for="registerEmail">Correo electrónico</label>
                <input class="kx-auth-input" type="email" id="registerEmail" name="email" value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" autocomplete="email" required>
            </div>

            {{-- [VALIDACION FORMULARIO]: Teléfono de 9 dígitos y DNI de 8 dígitos (ambos opcionales); data-solo-digitos borra letras al escribir (tienda-ui.js) --}}
            <div class="grid grid-cols-2 gap-x-3.5 max-[460px]:grid-cols-1">
                <div class="auth-campo">
                    <label for="registerPhone">Teléfono <small>(opcional)</small></label>
                    <input type="tel" id="registerPhone" name="phone" value="{{ old('phone') }}"
                           maxlength="9" minlength="9" pattern="[0-9]{9}" inputmode="numeric" data-solo-digitos
                           placeholder="9 dígitos" class="kx-auth-input @error('phone') input-invalido @enderror">
                </div>

                <div class="auth-campo">
                    <label for="registerDni">DNI <small>(opcional)</small></label>
                    <input type="text" id="registerDni" name="dni" value="{{ old('dni') }}"
                           maxlength="8" minlength="8" pattern="[0-9]{8}" inputmode="numeric" data-solo-digitos
                           placeholder="8 dígitos" class="kx-auth-input @error('dni') input-invalido @enderror">
                </div>
            </div>

            {{-- [VALIDACION FORMULARIO]: Contraseña con mínimo 8 caracteres y confirmación --}}
            <div class="auth-campo">
                <label for="registerPassword">Contraseña</label>
                <input type="password" id="registerPassword" name="password" minlength="8" placeholder="Mínimo 8 caracteres" autocomplete="new-password" required class="kx-auth-input @error('password') input-invalido @enderror">
            </div>

            <div class="auth-campo">
                <label for="registerPasswordConfirm">Confirmar contraseña</label>
                <input class="kx-auth-input" type="password" id="registerPasswordConfirm" name="password_confirmation" minlength="8" placeholder="Repite tu contraseña" autocomplete="new-password" required>
            </div>

            <button type="submit" class="mt-2 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-white px-[18px] py-3.5 text-[15px] font-semibold text-tinta transition-all duration-200 hover:-translate-y-px hover:bg-zinc-200 hover:shadow-[0_10px_24px_rgba(0,0,0,0.35)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                <i class="fa-solid fa-user-plus"></i> Crear cuenta
            </button>

            <p class="mt-6 text-center text-[13px] text-zinc-400 [&_a]:font-semibold [&_a]:text-white [&_a]:underline-offset-4 [&_a:hover]:underline">¿Ya tienes una cuenta? <a href="{{ route('login', request()->only('redirect')) }}">Inicia sesión</a></p>
        </form>
    </x-auth-cristal>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth.js') }}"></script>
@endpush
