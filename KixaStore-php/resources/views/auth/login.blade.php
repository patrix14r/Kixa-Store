@extends('layouts.app')

{{-- Fondo oscuro del body como en el diseño original (la tarjeta va sobre gris perla) --}}
@section('fondo', 'bg-[#090a0b] text-white')

@section('contenido')
    {{-- Login con labels fijos arriba de cada input y errores con @error --}}
    <div class="relative flex min-h-[calc(100vh_-_70px)] items-center justify-center bg-perla px-4 py-14 max-[760px]:py-8">
        {{-- Tarjeta editorial de 2 columnas: marca a la izquierda y formulario a la derecha --}}
        <div class="grid w-full max-w-[880px] grid-cols-[minmax(0,1fr)_minmax(0,1fr)] overflow-hidden rounded-3xl border border-[#ececec] bg-white text-tinta shadow-[0_24px_48px_rgba(0,0,0,0.08)] max-[760px]:max-w-[480px] max-[760px]:grid-cols-1">
            @include('auth._portada')

            {{-- novalidate: se reemplazan los globos del navegador ("Please fill out this field.") por mensajes en español --}}
            <form id="loginForm" class="px-10 pt-11 pb-9 max-[760px]:px-6 max-[760px]:pt-8 max-[760px]:pb-7 [&_.auth-campo]:mb-4 [&_label]:mb-1.5 [&_label]:block [&_label]:text-[13px] [&_label]:font-medium [&_label]:text-[#3f3f46] [&_label_small]:[font-size:smaller] [&_label_small]:font-normal [&_label_small]:text-[#a1a1aa] [&_input]:w-full [&_input]:rounded-[10px] [&_input]:border [&_input]:border-[#e4e4e7] [&_input]:bg-white [&_input]:px-3.5 [&_input]:py-3 [&_input]:text-[14px] [&_input]:text-tinta [&_input]:outline-none [&_input]:transition-[border-color,box-shadow] [&_input]:duration-200 [&_input::placeholder]:text-[#a1a1aa] [&_input:focus]:border-tinta [&_input:focus]:shadow-[0_0_0_3px_rgba(17,17,17,0.06)] [&_.auth-campo_input.input-invalido]:border-agotado [&_.auth-campo_input.input-invalido]:shadow-[0_0_0_3px_rgba(239,68,68,0.1)]" method="POST" action="{{ route('login') }}" novalidate data-validar-auth>
                @csrf

                <h1 class="m-0 text-[26px] font-bold tracking-[-0.03em] text-tinta">Bienvenido a <span class="text-kixa">Kixa</span></h1>
                <p class="mt-1.5 mb-7 text-[14px] text-suave">Inicia sesión para continuar</p>

                {{-- El error de credenciales aparece en el toast flotante del layout --}}
                {{-- ?redirect=checkout viaja oculto para volver al pago tras ingresar --}}
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

                <button type="submit" class="mt-2 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-full border-none bg-tinta px-[18px] py-3.5 text-[15px] font-semibold text-white transition-all duration-200 hover:-translate-y-px hover:bg-[#2a2a2e] hover:shadow-[0_10px_20px_rgba(17,17,17,0.18)]">
                    <i class="fa-solid fa-right-to-bracket"></i> Ingresar
                </button>

                <p class="mt-[22px] mb-0 text-center text-[13px] text-suave [&_a]:font-semibold [&_a]:text-tinta [&_a]:underline [&_a]:underline-offset-[3px]">¿No tienes cuenta? <a href="{{ route('register', request()->only('redirect')) }}">Regístrate</a></p>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth.js') }}"></script>
@endpush
