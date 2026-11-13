@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos reales de Tienda-Kixa_2/css/formulario.css --}}
    <link rel="stylesheet" href="{{ asset('css/formulario.css') }}">
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de Tienda-Kixa_2/formulario.html (Líneas 65 a 82) --}}
    {{-- [ADAPTADO]: Se agregó @csrf al formulario y se integró dentro del layout maestro layouts.app --}}
    <div class="contacto-page">
        <div class="form-container">
            <h2>Contáctanos - Kixa</h2>
            <form id="contactForm" method="POST" action="#">
                @csrf
                <label for="nombre">Nombre completo:</label>
                <input type="text" id="nombre" name="nombre" placeholder="Nombre completo" required>

                {{-- // [VALIDACION FORMULARIO]: DNI de exactamente 8 digitos numericos --}}
                <label for="dni">DNI:</label>
                <input type="text" id="dni" name="dni" placeholder="8 dígitos numéricos"
                       maxlength="8" minlength="8" pattern="[0-9]{8}" inputmode="numeric"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>

                {{-- // [VALIDACION FORMULARIO]: Telefono de exactamente 9 digitos numericos --}}
                <label for="telefono">Número de teléfono / Celular:</label>
                <input type="tel" id="telefono" name="telefono" placeholder="9 dígitos numéricos (ej. 987654321)"
                       maxlength="9" minlength="9" pattern="[0-9]{9}" inputmode="numeric"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>

                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion" placeholder="Tu dirección" required>

                <label for="mensaje">Mensaje:</label>
                <textarea id="mensaje" name="mensaje" placeholder="Escribe tu mensaje" required></textarea>

                <button type="submit">Enviar</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- [ADAPTADO]: Lógica de validación del formulario en public/js/formulario.js --}}
    <script src="{{ asset('js/formulario.js') }}"></script>
@endpush
