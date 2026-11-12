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

                <label for="telefono">Número de teléfono:</label>
                <input type="tel" id="telefono" name="telefono" placeholder="+51 000 000 000" required>

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
