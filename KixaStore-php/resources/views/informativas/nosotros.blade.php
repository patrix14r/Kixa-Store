@extends('layouts.app')

@section('titulo', 'Acerca de | KixaStore')

@section('contenido')
    {{-- Página Acerca de --}}
    <div class="bg-white text-tinta">
        <section class="border-b border-[#ececec] bg-perla px-5 pt-20 pb-16 text-center">
            <span class="inline-block text-[11px] font-semibold tracking-[0.2em] text-suave">ACERCA DE</span>
            <h1 class="mt-1.5 text-[length:clamp(2.2rem,5vw,3.6rem)] font-bold tracking-[-0.03em] max-escritorio:text-[1.6rem]">Somos <span class="text-suave">KixaStore</span></h1>
            <p class="mx-auto mt-2 max-w-[480px] text-[14px] leading-[1.6] text-[#52525b]">Zapatillas originales, atención cercana y pasión por el calzado urbano y deportivo.</p>
        </section>

        <div class="mx-auto max-w-[1080px] px-[5%] pt-16 pb-20">
            <section class="grid grid-cols-[1.5fr_1fr] items-center gap-8 max-escritorio:grid-cols-1">
                <div class="[&>p]:mb-2.5 [&>p]:text-[14px] [&>p]:leading-[1.7] [&>p]:text-[#52525b]">
                    <span class="inline-block text-[11px] font-semibold tracking-[0.2em] text-suave">NUESTRA HISTORIA</span>
                    <h2 class="mt-1.5 mb-3 text-[1.6rem] leading-[1.3] font-bold tracking-[-0.02em]">De una pequeña vitrina a tu tienda de confianza</h2>
                    <p>KixaStore nació como un emprendimiento familiar con una idea simple: que comprar zapatillas originales en el Perú sea fácil, seguro y a buen precio. Empezamos atendiendo a amigos y vecinos, y gracias a sus recomendaciones llegamos a las redes sociales y luego a nuestra tienda en línea.</p>
                    <p>Hoy trabajamos con Nike, Jordan, Adidas, Puma y Umbro, con calzado para hombre, mujer, niños y deporte, y envíos a todo el país.</p>
                </div>
                <div class="grid grid-cols-2 gap-3 [&>div]:rounded-[14px] [&>div]:border [&>div]:border-[#ececec] [&>div]:bg-perla [&>div]:px-4 [&>div]:py-[18px] [&_strong]:block [&_strong]:text-[1.5rem] [&_strong]:font-bold [&_strong]:text-tinta [&_span]:text-[12px] [&_span]:text-suave">
                    <div><strong>5</strong><span>Marcas oficiales</span></div>
                    <div><strong>4</strong><span>Colecciones</span></div>
                    <div><strong>100%</strong><span>Originales</span></div>
                    <div><strong>24/7</strong><span>Tienda en línea</span></div>
                </div>
            </section>

            {{-- Misión y visión --}}
            <section class="mt-10 grid grid-cols-2 gap-4 max-escritorio:grid-cols-1 [&>article]:relative [&>article]:overflow-hidden [&>article]:rounded-2xl [&>article]:border [&>article]:border-[#ececec] [&>article]:bg-perla [&>article]:p-6 [&>article]:before:absolute [&>article]:before:top-0 [&>article]:before:left-0 [&>article]:before:h-[3px] [&>article]:before:w-full [&>article]:before:bg-tinta [&>article]:before:content-[''] [&_h3]:mb-1.5 [&_h3]:text-[1.05rem] [&_h3]:font-bold [&_h3]:tracking-[0.5px] [&_h3]:uppercase [&_p]:text-[13px] [&_p]:leading-[1.7] [&_p]:text-[#52525b]">
                <article>
                    <span class="mb-3.5 flex size-10 items-center justify-center rounded-xl bg-white text-[16px] text-tinta"><i class="fa-solid fa-bullseye"></i></span>
                    <h3>Misión</h3>
                    <p>Ofrecer zapatillas 100% originales con una compra simple y segura, asesorando a cada cliente para que encuentre el modelo y la talla ideal para su estilo.</p>
                </article>
                <article>
                    <span class="mb-3.5 flex size-10 items-center justify-center rounded-xl bg-white text-[16px] text-tinta"><i class="fa-solid fa-eye"></i></span>
                    <h3>Visión</h3>
                    <p>Ser la tienda de zapatillas en línea de referencia en el Perú, reconocida por su autenticidad, la rapidez de sus envíos y la cercanía con su comunidad.</p>
                </article>
            </section>

            <section class="mt-11 text-center">
                <span class="inline-block text-[11px] font-semibold tracking-[0.2em] text-suave">NUESTROS VALORES</span>
                <h2 class="mt-1.5 mb-3 text-[1.6rem] leading-[1.3] font-bold tracking-[-0.02em]">Lo que nos define</h2>
                <div class="mt-[18px] grid grid-cols-4 gap-3 max-[860px]:grid-cols-2 max-[420px]:grid-cols-1 [&>div]:rounded-[14px] [&>div]:border [&>div]:border-[#ececec] [&>div]:bg-perla [&>div]:px-4 [&>div]:py-5 [&>div]:transition-[border-color,translate] [&>div]:duration-200 [&>div:hover]:-translate-y-[3px] [&>div:hover]:border-tinta [&_i]:text-[18px] [&_i]:text-tinta [&_h4]:mt-2.5 [&_h4]:mb-1 [&_h4]:text-[14px] [&_h4]:font-bold [&_p]:text-[12px] [&_p]:leading-[1.55] [&_p]:text-suave">
                    <div>
                        <i class="fa-solid fa-certificate"></i>
                        <h4>Autenticidad</h4>
                        <p>Solo productos originales de proveedores oficiales.</p>
                    </div>
                    <div>
                        <i class="fa-solid fa-handshake"></i>
                        <h4>Confianza</h4>
                        <p>Precios claros, pagos seguros y cambios sin complicaciones.</p>
                    </div>
                    <div>
                        <i class="fa-solid fa-fire"></i>
                        <h4>Pasión</h4>
                        <p>Amamos las zapatillas y compartimos ese entusiasmo.</p>
                    </div>
                    <div>
                        <i class="fa-solid fa-truck-fast"></i>
                        <h4>Compromiso</h4>
                        <p>Cumplimos los tiempos de entrega de cada pedido.</p>
                    </div>
                </div>
            </section>

            {{-- Centro de ayuda --}}
            <section class="-mx-[5%] mt-[72px] scroll-mt-24 rounded-3xl bg-perla px-[5%] py-16" id="centro-ayuda">
                <div class="mx-auto mb-8 max-w-[560px] scroll-mt-[110px] text-center" id="preguntas-frecuentes">
                    <span class="inline-block text-[11px] font-semibold tracking-[0.2em] text-suave">CENTRO DE AYUDA</span>
                    <h2 class="mt-1.5 mb-3 text-[1.6rem] leading-[1.3] font-bold tracking-[-0.02em]">Preguntas frecuentes</h2>
                    <p class="text-[14px] leading-[1.6] text-[#52525b]">Todo lo que necesitas saber sobre tus pedidos, cambios y pagos en KixaStore.</p>
                </div>

                <div class="grid grid-cols-3 items-start gap-4 max-[860px]:grid-cols-1 [&>div]:scroll-mt-24 [&>div]:rounded-[18px] [&>div]:border [&>div]:border-[#ececec] [&>div]:bg-white [&>div]:p-5 [&_h3]:mb-2 [&_h3]:flex [&_h3]:items-center [&_h3]:gap-2.5 [&_h3]:text-[14px] [&_h3]:font-bold [&_h3]:tracking-[0.02em] [&_h3_i]:[--fa-display:inline-flex] [&_h3_i]:size-8 [&_h3_i]:items-center [&_h3_i]:justify-center [&_h3_i]:rounded-[10px] [&_h3_i]:bg-perla [&_h3_i]:text-[13px] [&_h3_i]:text-tinta">
                    <div id="faq-envios">
                        <h3><i class="fa-solid fa-truck-fast"></i> Envíos y estado del pedido</h3>
                        <x-pregunta-frecuente pregunta="¿Cuánto tarda en llegar mi pedido?" abierta>En Lima Metropolitana entregamos de 24 a 48 horas hábiles. Para provincias el plazo es de 3 a 5 días hábiles según el destino.</x-pregunta-frecuente>
                        <x-pregunta-frecuente pregunta="¿Cuánto cuesta el envío?">{{ $reglaEnvio }} Aplica a Lima y a provincias, y el total que ves en el carrito ya incluye el envío y el IGV.</x-pregunta-frecuente>
                        <x-pregunta-frecuente pregunta="¿Cómo sé el estado de mi pedido?">Ingresa a <a href="{{ route('perfil') }}">Mi cuenta</a> para ver el historial y estado de tus pedidos. También te avisamos por correo y WhatsApp cuando tu pedido sale a reparto.</x-pregunta-frecuente>
                    </div>

                    <div id="faq-cambios">
                        <h3><i class="fa-solid fa-rotate-left"></i> Cambios y devoluciones</h3>
                        <x-pregunta-frecuente pregunta="¿Puedo cambiar la talla de mis zapatillas?">Sí. Tienes 30 días desde la entrega para solicitar un cambio de talla o modelo, siempre que el par esté sin uso y en su caja original.</x-pregunta-frecuente>
                        <x-pregunta-frecuente pregunta="¿Cómo solicito una devolución?">Escríbenos por WhatsApp o correo con tu número de pedido. Coordinamos el recojo y te devolvemos el dinero en un plazo de 5 a 7 días hábiles.</x-pregunta-frecuente>
                        <x-pregunta-frecuente pregunta="¿Qué hago si mi producto llegó con fallas?">Lo cambiamos sin costo. Escríbenos con tu número de pedido y fotos del producto.</x-pregunta-frecuente>
                    </div>

                    <div id="faq-pagos">
                        <h3><i class="fa-regular fa-credit-card"></i> Opciones de pago</h3>
                        <x-pregunta-frecuente pregunta="¿Qué medios de pago aceptan?">Aceptamos tarjetas de crédito y débito Visa y Mastercard, y pago Contraentrega (pagas al recibir tu pedido).</x-pregunta-frecuente>
                        <x-pregunta-frecuente pregunta="¿Es seguro pagar en KixaStore?">Sí. Los pagos se procesan en una conexión cifrada y nunca almacenamos los datos completos de tu tarjeta.</x-pregunta-frecuente>
                        <x-pregunta-frecuente pregunta="¿Emiten boleta o factura?">Emitimos boleta electrónica en todas las compras. Si necesitas factura, indícanos tu RUC al momento del pago.</x-pregunta-frecuente>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-4 rounded-[18px] border border-[#ececec] bg-white px-6 py-5">
                    <div>
                        <h3 class="text-[15px] font-bold">¿No encontraste tu respuesta?</h3>
                        <p class="text-[13px] text-[#52525b]">Nuestro equipo te atiende de lunes a sábado de 9:00 AM a 8:00 PM.</p>
                    </div>
                    <div class="flex flex-wrap gap-2.5 [&>a]:inline-flex [&>a]:items-center [&>a]:gap-2 [&>a]:rounded-full [&>a]:border [&>a]:border-[#cacacb] [&>a]:px-4 [&>a]:py-[9px] [&>a]:text-[13px] [&>a]:text-tinta [&>a]:transition-colors [&>a]:duration-200 [&>a:hover]:border-tinta [&>a:hover]:bg-white">
                        <a href="https://wa.me/51931693020" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> +51 931 693 020</a>
                        <a href="mailto:zapatillaskixa@gmail.com"><i class="fa-regular fa-envelope"></i> zapatillaskixa@gmail.com</a>
                    </div>
                </div>
            </section>

            {{-- Ubícanos: mapa de la sede --}}
            <section class="relative flex scroll-mt-[100px] flex-col gap-5 rounded-3xl border border-white/60 bg-white/90 p-7 text-tinta shadow-[0_24px_60px_-24px_rgba(0,0,0,0.45)] backdrop-blur-[18px] max-[520px]:p-5" id="ubicanos" aria-labelledby="titulo-sede">
                <div class="flex items-center gap-3.5">
                    <span class="inline-flex size-[42px] shrink-0 items-center justify-center rounded-xl border border-[#e4e4e7] bg-perla text-tinta">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                    </span>
                    <div>
                        <h2 id="titulo-sede" class="mt-1.5 mb-3 text-[1.25rem] leading-[1.3] font-bold tracking-[-0.02em]">Ubícanos</h2>
                        <p class="mt-0.5 text-[13px] text-suave">Sede Central KixaStore: Av. Cascanueces 2221, Santa Anita, Lima</p>
                    </div>
                </div>

                <div class="relative min-h-[320px] flex-1 overflow-hidden rounded-[14px] border border-[#e4e4e7] bg-perla max-[520px]:min-h-[260px]">
                    <iframe
                        class="absolute inset-0 size-full border-0"
                        title="Sede Central KixaStore - Av. Cascanueces 2221, Santa Anita, Lima"
                        src="https://maps.google.com/maps?q=Av.%20Cascanueces%202221%2C%20Santa%20Anita%2C%20Lima%2C%20Per%C3%BA&z=16&hl=es&output=embed"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen></iframe>
                </div>

                <div class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-2.5 [&>*]:flex [&>*]:items-start [&>*]:gap-2.5 [&>*]:rounded-[14px] [&>*]:border [&>*]:border-[#ececec] [&>*]:bg-[#f8f9fa] [&>*]:p-3.5 [&>*]:text-tinta [&>*]:transition-[border-color,background-color] [&>*]:duration-200 [&>a:hover]:border-tinta [&>a:hover]:bg-white [&_svg]:mt-0.5 [&_svg]:shrink-0 [&_svg]:text-[#52525b] [&_span]:block [&_span]:text-[11px] [&_span]:font-semibold [&_span]:tracking-[0.06em] [&_span]:text-suave [&_span]:uppercase [&_strong]:mt-[3px] [&_strong]:block [&_strong]:text-[13px] [&_strong]:font-semibold [&_strong]:[word-break:break-word]">
                    <div>
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        <div>
                            <span>Horario de atención</span>
                            <strong>Lun - Sáb: 9:00 AM - 8:00 PM</strong>
                        </div>
                    </div>
                    <a href="tel:+51931693020">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
                        <div>
                            <span>Teléfono / WhatsApp</span>
                            <strong>+51 931 693 020</strong>
                        </div>
                    </a>
                    <a href="mailto:zapatillaskixa@gmail.com">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        <div>
                            <span>Correo</span>
                            <strong>zapatillaskixa@gmail.com</strong>
                        </div>
                    </a>
                </div>
            </section>

            <section class="mt-11 flex flex-wrap items-center justify-between gap-4 rounded-[20px] border-none bg-tinta px-7 py-6 text-white max-escritorio:justify-center max-escritorio:text-center">
                <h2 class="m-0 text-[1.6rem] leading-[1.3] font-bold tracking-[-0.02em] text-white">¿Listo para tu próximo par?</h2>
                <div>
                    <a href="{{ route('catalogo') }}" class="m-1 inline-flex items-center gap-2 rounded-full border border-white bg-white px-5 py-2.5 text-[13px] font-semibold text-tinta transition-colors duration-200 hover:bg-[#e5e5e5]">Ver catálogo <i class="fa-solid fa-arrow-right"></i></a>
                    <a href="#ubicanos" class="m-1 inline-flex items-center gap-2 rounded-full border border-white/35 bg-transparent px-5 py-2.5 text-[13px] font-semibold text-white transition-colors duration-200 hover:border-white">Ubícanos</a>
                </div>
            </section>
        </div>
    </div>
@endsection
