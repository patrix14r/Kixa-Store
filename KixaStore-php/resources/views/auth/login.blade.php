@extends('layouts.app')

{{-- Fondo oscuro del body para que no asome blanco detrás de la portada --}}
@section('fondo', 'bg-[#090a0b] text-white')

@section('contenido')
    {{-- Fondo (black.jpg), cristal y bordes rounded-lg de los inputs se editan en components/auth-cristal.blade.php;
         kx-auth-input (resources/css/app.css) evita el fondo blanco del autocompletado del navegador. --}}
    {{-- [Sem 08 - Rutas y Vistas Blade]: Login de Fortify (POST /login) dentro de la tarjeta cristal <x-auth-cristal> (tarjeta vertical de 420px) --}}
    <x-auth-cristal titulo="Bienvenido a Kixa" subtitulo="Inicia sesión para continuar">
        {{-- novalidate: se reemplazan los globos del navegador ("Please fill out this field.") por mensajes en español (public/js/auth.js) --}}
        <form id="loginForm" method="POST" action="{{ route('login') }}" novalidate data-validar-auth>
            @csrf

            {{-- El error de credenciales aparece en el toast flotante del layout --}}
            {{-- ?redirect=checkout viaja oculto para volver al pago tras ingresar --}}
            @if (request('redirect'))
                <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            @endif

            <div class="auth-campo">
                <label for="loginEmail">Correo electrónico</label>
                <input class="kx-auth-input" type="email" id="loginEmail" name="email" value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" autocomplete="email" required autofocus>
            </div>

            <div class="auth-campo">
                <label for="loginPassword">Contraseña</label>
                <input class="kx-auth-input" type="password" id="loginPassword" name="password" placeholder="Tu contraseña" autocomplete="current-password" required>
            </div>

            {{-- remember: Fortify lo lee con $request->boolean('remember') y mantiene la sesión abierta --}}
            <div class="mt-1 mb-6 px-1 text-[13px] text-zinc-300">
                <label class="inline-flex cursor-pointer items-center gap-2 select-none">
                    <input type="checkbox" name="remember" value="1" class="size-4 cursor-pointer accent-white" @checked(old('remember'))>
                    Recuérdame
                </label>
            </div>

            <button type="submit" class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-white px-[18px] py-3.5 text-[15px] font-semibold text-tinta transition-all duration-200 hover:-translate-y-px hover:bg-zinc-200 hover:shadow-[0_10px_24px_rgba(0,0,0,0.35)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                <i class="fa-solid fa-right-to-bracket"></i> Ingresar
            </button>

            <p class="mt-6 text-center text-[13px] text-zinc-400 [&_a]:font-semibold [&_a]:text-white [&_a]:underline-offset-4 [&_a:hover]:underline">¿No tienes cuenta? <a href="{{ route('register', request()->only('redirect')) }}">Regístrate</a></p>
        </form>
    </x-auth-cristal>

@endsection

@push('scripts')
    <script src="{{ asset('js/auth.js') }}"></script>
@endpush
