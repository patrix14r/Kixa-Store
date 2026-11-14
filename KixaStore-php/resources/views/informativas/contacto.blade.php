@extends('layouts.app')

@section('titulo', 'Contáctanos | KixaStore')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/formulario.css') }}">
@endpush

@section('contenido')
    {{-- [Sem 08 - Rutas y Vistas Blade]: Contáctanos en 2 columnas (mapa de la sede + formulario) con @extends y @section --}}
    <div class="contacto-page">
        <header class="contacto-cabecera">
            <span class="contacto-etiqueta">ATENCIÓN AL CLIENTE</span>
            <h1>Contáctanos</h1>
            <p>Visítanos en nuestra sede de Lima o escríbenos: respondemos en menos de 24 horas.</p>
        </header>

        <div class="contacto-grid">
            {{-- Columna izquierda: ubicación, mapa y datos de atención --}}
            <section class="glass-card contacto-ubicacion" id="mapa-tienda" aria-labelledby="titulo-sede">
                <div class="contacto-card-cabecera">
                    <span class="contacto-icono">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                    </span>
                    <div>
                        <h2 id="titulo-sede">Sede Central KixaStore</h2>
                        <p>Av. Cascanueces 2221, Santa Anita, Lima</p>
                    </div>
                </div>

                <div class="contacto-mapa">
                    <iframe
                        title="Sede Central KixaStore - Av. Cascanueces 2221, Santa Anita, Lima"
                        src="https://maps.google.com/maps?q=Av.%20Cascanueces%202221%2C%20Santa%20Anita%2C%20Lima%2C%20Per%C3%BA&z=16&hl=es&output=embed"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen></iframe>
                </div>

                <div class="contacto-datos">
                    <div class="contacto-dato">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        <div>
                            <span>Horario de atención</span>
                            <strong>Lun - Sáb: 9:00 AM - 8:00 PM</strong>
                        </div>
                    </div>
                    <a class="contacto-dato" href="tel:+51931693020">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
                        <div>
                            <span>Teléfono / WhatsApp</span>
                            <strong>+51 931 693 020</strong>
                        </div>
                    </a>
                    <a class="contacto-dato" href="mailto:zapatillaskixa@gmail.com">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        <div>
                            <span>Correo</span>
                            <strong>zapatillaskixa@gmail.com</strong>
                        </div>
                    </a>
                </div>
            </section>

            {{-- Columna derecha: formulario de contacto / Libro de Reclamaciones --}}
            <section class="glass-card contacto-formulario" id="formulario-contacto" aria-labelledby="titulo-formulario">
                <h2 id="titulo-formulario">Envíanos un mensaje</h2>
                <p class="contacto-subtitulo">Los campos marcados con * son obligatorios.</p>

                {{-- [Actualizado: Toast único] Confirmación y errores del formulario se muestran en el toast flotante --}}

                <form id="contactForm" method="POST" action="#" novalidate>
                    @csrf
                    <div class="campo">
                        <label for="motivo">Motivo *</label>
                        <select id="motivo" name="motivo" required>
                            <option value="consulta" @selected(request('tipo') !== 'reclamo' && request('tipo') !== 'queja')>Consulta general</option>
                            <option value="reclamo" @selected(request('tipo') === 'reclamo')>Reclamo (Libro de Reclamaciones)</option>
                            <option value="queja" @selected(request('tipo') === 'queja')>Queja</option>
                            <option value="sugerencia">Sugerencia</option>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="nombre">Nombre completo *</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej. María Torres" autocomplete="name" required>
                    </div>

                    <div class="campo-fila">
                        {{-- [VALIDACION FORMULARIO]: DNI de exactamente 8 dígitos numéricos --}}
                        <div class="campo">
                            <label for="dni">DNI *</label>
                            <input type="text" id="dni" name="dni" placeholder="8 dígitos"
                                   maxlength="8" minlength="8" pattern="[0-9]{8}" inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            </div>

                        {{-- [VALIDACION FORMULARIO]: Teléfono de exactamente 9 dígitos numéricos --}}
                        <div class="campo">
                            <label for="telefono">Celular *</label>
                            <input type="tel" id="telefono" name="telefono" placeholder="987654321"
                                   maxlength="9" minlength="9" pattern="[0-9]{9}" inputmode="numeric" autocomplete="tel"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            </div>
                    </div>

                    <div class="campo">
                        <label for="direccion">Dirección *</label>
                        <input type="text" id="direccion" name="direccion" placeholder="Distrito, calle y número" autocomplete="street-address" required>
                    </div>

                    <div class="campo">
                        <label for="mensaje">Mensaje *</label>
                        <textarea id="mensaje" name="mensaje" placeholder="Cuéntanos en qué podemos ayudarte" required></textarea>
                    </div>

                    <button type="submit" class="btn-enviar">
                        Enviar mensaje
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </form>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/formulario.js') }}"></script>
@endpush
