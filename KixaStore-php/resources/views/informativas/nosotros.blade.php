@extends('layouts.app')

@section('titulo', 'Acerca de | KixaStore')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nosotros.css') }}">
    <link rel="stylesheet" href="{{ asset('css/formulario.css') }}">
@endpush

@section('contenido')
    {{-- Página "Acerca de" (rúbrica 5.2) con @extends, @section y anclas al Centro de Ayuda --}}
    <div class="nosotros">
        <section class="nosotros-hero">
            <span class="nosotros-etiqueta">ACERCA DE</span>
            <h1>Somos <span>KixaStore</span></h1>
            <p>Zapatillas originales, atención cercana y pasión por el calzado urbano y deportivo.</p>
        </section>

        <div class="nosotros-contenedor">
            <section class="nosotros-historia">
                <div class="nosotros-historia-texto">
                    <span class="nosotros-etiqueta">NUESTRA HISTORIA</span>
                    <h2>De una pequeña vitrina a tu tienda de confianza</h2>
                    <p>KixaStore nació como un emprendimiento familiar con una idea simple: que comprar zapatillas originales en el Perú sea fácil, seguro y a buen precio. Empezamos atendiendo a amigos y vecinos, y gracias a sus recomendaciones llegamos a las redes sociales y luego a nuestra tienda en línea.</p>
                    <p>Hoy trabajamos con Nike, Jordan, Adidas, Puma y Umbro, con calzado para hombre, mujer, niños y deporte, y envíos a todo el país.</p>
                </div>
                <div class="nosotros-cifras">
                    <div><strong>5</strong><span>Marcas oficiales</span></div>
                    <div><strong>4</strong><span>Colecciones</span></div>
                    <div><strong>100%</strong><span>Originales</span></div>
                    <div><strong>24/7</strong><span>Tienda en línea</span></div>
                </div>
            </section>

            <section class="nosotros-tarjetas">
                <article class="nosotros-tarjeta">
                    <span class="nosotros-icono"><i class="fa-solid fa-bullseye"></i></span>
                    <h3>Misión</h3>
                    <p>Ofrecer zapatillas 100% originales con una compra simple y segura, asesorando a cada cliente para que encuentre el modelo y la talla ideal para su estilo.</p>
                </article>
                <article class="nosotros-tarjeta">
                    <span class="nosotros-icono"><i class="fa-solid fa-eye"></i></span>
                    <h3>Visión</h3>
                    <p>Ser la tienda de zapatillas en línea de referencia en el Perú, reconocida por su autenticidad, la rapidez de sus envíos y la cercanía con su comunidad.</p>
                </article>
            </section>

            <section class="nosotros-valores">
                <span class="nosotros-etiqueta">NUESTROS VALORES</span>
                <h2>Lo que nos define</h2>
                <div class="nosotros-valores-grid">
                    <div class="nosotros-valor">
                        <i class="fa-solid fa-certificate"></i>
                        <h4>Autenticidad</h4>
                        <p>Solo productos originales de proveedores oficiales.</p>
                    </div>
                    <div class="nosotros-valor">
                        <i class="fa-solid fa-handshake"></i>
                        <h4>Confianza</h4>
                        <p>Precios claros, pagos seguros y cambios sin complicaciones.</p>
                    </div>
                    <div class="nosotros-valor">
                        <i class="fa-solid fa-fire"></i>
                        <h4>Pasión</h4>
                        <p>Amamos las zapatillas y compartimos ese entusiasmo.</p>
                    </div>
                    <div class="nosotros-valor">
                        <i class="fa-solid fa-truck-fast"></i>
                        <h4>Compromiso</h4>
                        <p>Cumplimos los tiempos de entrega de cada pedido.</p>
                    </div>
                </div>
            </section>

            {{-- Centro de Ayuda integrado (acordeón nativo <details>, sin JavaScript) --}}
            <section class="ayuda-centro" id="centro-ayuda">
                {{-- Ancla de los enlaces "Obtener ayuda" del footer --}}
                <div class="ayuda-centro-cabecera" id="preguntas-frecuentes">
                    <span class="nosotros-etiqueta">CENTRO DE AYUDA</span>
                    <h2>Preguntas frecuentes</h2>
                    <p>Todo lo que necesitas saber sobre tus pedidos, cambios y pagos en KixaStore.</p>
                </div>

                <div class="ayuda-grupos">
                    <div class="ayuda-grupo" id="faq-envios">
                        <h3><i class="fa-solid fa-truck-fast"></i> Envíos y estado del pedido</h3>
                        <details class="ayuda-item" open>
                            <summary>¿Cuánto tarda en llegar mi pedido?</summary>
                            <p>En Lima Metropolitana entregamos de 24 a 48 horas hábiles. Para provincias el plazo es de 3 a 5 días hábiles según el destino.</p>
                        </details>
                        <details class="ayuda-item">
                            <summary>¿Cuánto cuesta el envío?</summary>
                            <p>El envío en Lima cuesta S/ 10 y a provincias desde S/ 18. Las compras mayores a S/ 299 tienen envío gratis a todo el Perú.</p>
                        </details>
                        <details class="ayuda-item">
                            <summary>¿Cómo sé el estado de mi pedido?</summary>
                            <p>Ingresa a <a href="{{ route('perfil') }}">Mi cuenta</a> para ver el historial y estado de tus pedidos. También te avisamos por correo y WhatsApp cuando tu pedido sale a reparto.</p>
                        </details>
                    </div>

                    <div class="ayuda-grupo" id="faq-cambios">
                        <h3><i class="fa-solid fa-rotate-left"></i> Cambios y devoluciones</h3>
                        <details class="ayuda-item">
                            <summary>¿Puedo cambiar la talla de mis zapatillas?</summary>
                            <p>Sí. Tienes 30 días desde la entrega para solicitar un cambio de talla o modelo, siempre que el par esté sin uso y en su caja original.</p>
                        </details>
                        <details class="ayuda-item">
                            <summary>¿Cómo solicito una devolución?</summary>
                            <p>Escríbenos por WhatsApp o correo con tu número de pedido. Coordinamos el recojo y te devolvemos el dinero en un plazo de 5 a 7 días hábiles.</p>
                        </details>
                        <details class="ayuda-item">
                            <summary>¿Qué hago si mi producto llegó con fallas?</summary>
                            <p>Lo cambiamos sin costo. Escríbenos con tu número de pedido y fotos del producto.</p>
                        </details>
                    </div>

                    <div class="ayuda-grupo" id="faq-pagos">
                        <h3><i class="fa-regular fa-credit-card"></i> Opciones de pago</h3>
                        <details class="ayuda-item">
                            <summary>¿Qué medios de pago aceptan?</summary>
                            <p>Aceptamos tarjetas de crédito y débito Visa y Mastercard, Yape, Plin y transferencia bancaria.</p>
                        </details>
                        <details class="ayuda-item">
                            <summary>¿Es seguro pagar en KixaStore?</summary>
                            <p>Sí. Los pagos se procesan en una conexión cifrada y nunca almacenamos los datos completos de tu tarjeta.</p>
                        </details>
                        <details class="ayuda-item">
                            <summary>¿Emiten boleta o factura?</summary>
                            <p>Emitimos boleta electrónica en todas las compras. Si necesitas factura, indícanos tu RUC al momento del pago.</p>
                        </details>
                    </div>
                </div>

                <div class="ayuda-contacto">
                    <div>
                        <h3>¿No encontraste tu respuesta?</h3>
                        <p>Nuestro equipo te atiende de lunes a sábado de 9:00 AM a 8:00 PM.</p>
                    </div>
                    <div class="ayuda-contacto-enlaces">
                        <a href="https://wa.me/51931693020" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> +51 931 693 020</a>
                        <a href="mailto:zapatillaskixa@gmail.com"><i class="fa-regular fa-envelope"></i> zapatillaskixa@gmail.com</a>
                    </div>
                </div>
            </section>

            {{-- Ubícanos: mapa de la sede y datos de atención (antes estaba en /contacto) --}}
            <section class="glass-card contacto-ubicacion" id="ubicanos" aria-labelledby="titulo-sede">
                <div class="contacto-card-cabecera">
                    <span class="contacto-icono">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                    </span>
                    <div>
                        <h2 id="titulo-sede">Ubícanos</h2>
                        <p>Sede Central KixaStore: Av. Cascanueces 2221, Santa Anita, Lima</p>
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

            <section class="nosotros-cta">
                <h2>¿Listo para tu próximo par?</h2>
                <div>
                    <a href="{{ route('catalogo') }}" class="nosotros-btn">Ver catálogo <i class="fa-solid fa-arrow-right"></i></a>
                    <a href="#ubicanos" class="nosotros-btn nosotros-btn--borde">Ubícanos</a>
                </div>
            </section>
        </div>
    </div>
@endsection
