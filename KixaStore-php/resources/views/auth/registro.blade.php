@extends('layouts.app')

{{-- Fondo oscuro del body como en el diseño original (la tarjeta va sobre gris perla) --}}
@section('fondo', 'bg-[#090a0b] text-white')

@section('contenido')
    {{-- Registro con labels fijos arriba de cada input (no se montan sobre el placeholder) --}}
    <div class="relative flex min-h-[calc(100vh_-_70px)] items-center justify-center bg-perla px-4 py-14 max-[760px]:py-8">
        {{-- Misma tarjeta editorial de 2 columnas que el Login --}}
        <div class="grid w-full max-w-[880px] grid-cols-[minmax(0,1fr)_minmax(0,1fr)] overflow-hidden rounded-3xl border border-[#ececec] bg-white text-tinta shadow-[0_24px_48px_rgba(0,0,0,0.08)] max-[760px]:max-w-[480px] max-[760px]:grid-cols-1">
            @include('auth._portada')

            {{-- novalidate: validación propia con mensajes 100% en español (public/js/auth.js) --}}
            <form id="registerForm" class="px-10 pt-11 pb-9 max-[760px]:px-6 max-[760px]:pt-8 max-[760px]:pb-7 [&_.auth-campo]:mb-4 [&_label]:mb-1.5 [&_label]:block [&_label]:text-[13px] [&_label]:font-medium [&_label]:text-[#3f3f46] [&_label_small]:[font-size:smaller] [&_label_small]:font-normal [&_label_small]:text-[#a1a1aa] [&_input]:w-full [&_input]:rounded-[10px] [&_input]:border [&_input]:border-[#e4e4e7] [&_input]:bg-white [&_input]:px-3.5 [&_input]:py-3 [&_input]:text-[14px] [&_input]:text-tinta [&_input]:outline-none [&_input]:transition-[border-color,box-shadow] [&_input]:duration-200 [&_input::placeholder]:text-[#a1a1aa] [&_input:focus]:border-tinta [&_input:focus]:shadow-[0_0_0_3px_rgba(17,17,17,0.06)] [&_.auth-campo_input.input-invalido]:border-agotado [&_.auth-campo_input.input-invalido]:shadow-[0_0_0_3px_rgba(239,68,68,0.1)]" method="POST" action="{{ route('register') }}" novalidate data-validar-auth>
                {{-- Conserva ?redirect=checkout al registrarse --}}
                @if (request('redirect'))
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                @endif
                @csrf

                <h1 class="m-0 text-[26px] font-bold tracking-[-0.03em] text-tinta">Crea tu cuenta en <span class="text-kixa">Kixa</span></h1>
                <p class="mt-1.5 mb-7 text-[14px] text-suave">Completa tus datos para registrarte</p>

                {{-- Los errores de validación se muestran en el toast flotante del layout --}}

                <div class="auth-campo">
                    <label for="registerName">Nombre completo</label>
                    <input type="text" id="registerName" name="name" value="{{ old('name') }}" placeholder="Ej. Ana Torres" autocomplete="name" required>
                </div>

                <div class="auth-campo">
                    <label for="registerEmail">Correo electrónico</label>
                    <input type="email" id="registerEmail" name="email" value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" autocomplete="email" required>
                </div>

                {{-- // [VALIDACION FORMULARIO]: Teléfono de 9 dígitos y DNI de 8 dígitos (ambos opcionales) --}}
                <div class="grid grid-cols-2 gap-x-3.5 max-[460px]:grid-cols-1">
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

                <button type="submit" class="mt-2 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-full border-none bg-tinta px-[18px] py-3.5 text-[15px] font-semibold text-white transition-all duration-200 hover:-translate-y-px hover:bg-[#2a2a2e] hover:shadow-[0_10px_20px_rgba(17,17,17,0.18)]">
                    <i class="fa-solid fa-user-plus"></i> Crear cuenta
                </button>

                <p class="mt-[22px] mb-0 text-center text-[13px] text-suave [&_a]:font-semibold [&_a]:text-tinta [&_a]:underline [&_a]:underline-offset-[3px]">¿Ya tienes una cuenta? <a href="{{ route('login', request()->only('redirect')) }}">Inicia sesión</a></p>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth.js') }}"></script>
@endpush
