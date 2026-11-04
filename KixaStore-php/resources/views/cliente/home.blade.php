@extends('layouts.app')

@section('contenido')
    <!-- Carrusel / Slider principal -->
    <section class="hero">
      <div class="slider">
        <div class="slide active">
          <img src="{{ asset('images/banners/home/dsa6.jpg') }}" alt="Zapatillas Kixa" />
        </div>

        <div class="slide">
          <img src="{{ asset('images/banners/home/dsa9.webp') }}" alt="Zapatillas Kixa" />
        </div>

        <div class="slide">
          <img src="{{ asset('images/banners/home/dsa7.jpg') }}" alt="Zapatillas Kixa" />
        </div>

        <div class="slide">
          <img src="{{ asset('images/banners/home/zapatillas retro homnbre.webp') }}" alt="Zapatillas retro" />
        </div>

        <div class="slide">
          <img src="{{ asset('images/banners/home/das11.jpeg') }}" alt="Zapatillas Kixa" />
        </div>

        <div class="slide">
          <img src="{{ asset('images/banners/home/dsa5.webp') }}" alt="Zapatillas Kixa" />
        </div>

        <!-- Controles del carrusel -->
        <button class="prev" aria-label="Anterior">&#10094;</button>
        <button class="next" aria-label="Siguiente">&#10095;</button>

        <!-- Indicadores de posición -->
        <div class="slider-dots">
          <span class="dot active"></span>
          <span class="dot"></span>
          <span class="dot"></span>
          <span class="dot"></span>
          <span class="dot"></span>
          <span class="dot"></span>
        </div>
      </div>

      <div class="hero-content">
        <h1>Nueva Colección 2026</h1>
        <p>Descubre las últimas tendencias en zapatillas</p>
        <a href="#" class="btn-hero"> Comprar ahora </a>
      </div>
    </section>

    <!-- Sección de ofertas especiales -->
    <section class="ofertas">
      <div class="ofertas-header">
        <span> KIXA </span>
        <h2>OFERTAS ESPECIALES</h2>
        <p>Aprovecha nuestras promociones exclusivas</p>
      </div>

      <div class="contenedor-ofertas">
        <div class="oferta-card">
          <img src="{{ asset('images/promotions/oferta.png') }}" alt="Oferta especial" />
        </div>

        <div class="oferta-card">
          <img src="{{ asset('images/promotions/oferta4.jpg') }}" alt="Envío gratis" />
        </div>

        <div class="oferta-card">
          <img src="{{ asset('images/promotions/oferta2.png') }}" alt="Promoción 2x1" />
        </div>
      </div>
    </section>
@endsection
