@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos reales de Tienda-Kixa_2/css/ayuda.css --}}
    <link rel="stylesheet" href="{{ asset('css/ayuda.css') }}">
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de Tienda-Kixa_2/ayuda.html (Líneas 63 a 106) --}}
    {{-- [ADAPTADO]: Se integró dentro del layout maestro de Laravel (layouts.app), eliminando header y footer repetidos --}}
    <div class="ayuda-container">

        <h1 class="ayuda-hero-titulo">Zapatillas kixa</h1>

        <section class="ayuda-seccion">
            <h2>¿Qué tipo de zapatillas ofrecemos?</h2>
            <p>Ofrecemos zapatillas deportivas, urbanas y casuales. Contamos con marcas reconocidas y productos para hombre, mujer y niños. Nuestro catálogo incluye opciones para correr, caminar y uso diario.</p>
        </section>

        <section class="ayuda-seccion">
            <h2>¿Por qué comprar con nosotros?</h2>
            <p>Brindamos atención personalizada, productos originales, promociones exclusivas y envíos a todo el país. Nuestra tienda tiene años de experiencia en el rubro del calzado.</p>
        </section>

        <section class="ayuda-seccion">
            <h2>¿Dónde comprar nuestras zapatillas?</h2>
            <p>Puedes visitarnos en nuestra tienda física o realizar tu compra en línea. Nuestro sitio web permite seleccionar modelos, tallas y realizar pagos de forma segura.</p>
        </section>

        <section class="ayuda-seccion modelos">
            <h2>¿Qué zapatillas están disponibles actualmente?</h2>
            <p>Zapatillas Urbanas AirFlex (disponibles en negro, blanco y azul).</p>
            <p>Zapatillas Deportivas Air Run (para entrenamiento, ligeras y cómodas).</p>
            <p>Zapatillas Clásicas (estilo vintage, muy buscadas por su diseño único).</p>
            <p>Todos los modelos están disponibles en tallas desde la 36 hasta la 45.</p>
        </section>

        <section class="ayuda-seccion">
            <h2>¿Qué opinan nuestros clientes?</h2>
            <div class="testimonio">"Excelente calidad y el envío fue muy rápido. Sin duda volveré a comprar." - Laura G.</div>
            <div class="testimonio">"Las zapatillas llegaron en perfectas condiciones y son muy cómodas. Recomendado." - Martin R.</div>
        </section>

        <section class="ayuda-seccion contacto">
            <h2>¿Cómo puedes contactarnos?</h2>
            <p>Correo electrónico: <a href="mailto:zapatillaskixa@gmail.com">zapatillaskixa@gmail.com</a></p>
            <p>Teléfono/WhatsApp: <a href="tel:+51931693020">+51 931693020</a></p>
            <p>Horarios de atención: Lunes a viernes de 9:00 a 18:00 h.</p>
        </section>

        <section class="ayuda-seccion redes">
            <h2>Síguenos en redes sociales</h2>
            <p>Instagram: <a href="https://www.instagram.com/zapatillas.kixa/" target="_blank">@zapatillaskixa</a></p>
            <p>Facebook: <a href="https://www.facebook.com/profile.php?id=61582103174166" target="_blank">/zapatillaskixa</a></p>
            <p>TikTok: <a href="https://www.tiktok.com/@zapatillaskixa" target="_blank">@zapatillaskixa</a></p>
        </section>

    </div>
@endsection
