// KIXA — HOMBRE 2026// SLIDER + FILTROS DE MARCA
// SLIDER
const slides = document.querySelectorAll(".hero-slide");
const dots = document.querySelectorAll(".hero-dots .dot");
const nextButton = document.querySelector(".hero-next");
const prevButton = document.querySelector(".hero-prev");

let currentSlide = 0;
let slideInterval;

// Mostrar slide
function showSlide(index) {

  if (slides.length === 0) return;

  // Mantener el índice dentro del rango
  if (index >= slides.length) {
    currentSlide = 0;

  } else if (index < 0) {
    currentSlide = slides.length - 1;

  } else {
    currentSlide = index;
  }

  // Cambiar imágenes
  slides.forEach((slide, i) => {

    slide.classList.toggle(
      "active",
      i === currentSlide
    );

  });

  // Cambiar indicadores
  dots.forEach((dot, i) => {

    dot.classList.toggle(
      "active",
      i === currentSlide
    );

  });

}

// Siguiente
function nextSlide() {

  showSlide(currentSlide + 1);

}

// Anterior
function prevSlide() {

  showSlide(currentSlide - 1);

}

// Reiniciar temporizador
function restartSlider() {

  clearInterval(slideInterval);

  slideInterval = setInterval(() => {

    nextSlide();

  }, 4000);

}

// Botón siguiente
if (nextButton) {

  nextButton.addEventListener("click", () => {

    nextSlide();
    restartSlider();

  });

}

// Botón anterior
if (prevButton) {

  prevButton.addEventListener("click", () => {

    prevSlide();
    restartSlider();

  });

}

// Puntos del slider
dots.forEach((dot, index) => {

  dot.addEventListener("click", () => {

    showSlide(index);
    restartSlider();

  });

});

// Mostrar primera imagen
showSlide(0);

// Iniciar slider automático
restartSlider();

// FILTROS DE PRODUCTOSconst filtros = document.querySelectorAll(".filtros button");
const productos = document.querySelectorAll(".producto");

// Función para filtrar productos
function filtrarProductos(marcaSeleccionada) {

  productos.forEach((producto) => {

    const marcaProducto = producto.dataset.marca;

    // Mostrar todos
    if (marcaSeleccionada === "todos") {

      producto.style.display = "";

    }

    // Mostrar solamente la marca seleccionada
    else if (marcaProducto === marcaSeleccionada) {

      producto.style.display = "";

    }

    // Ocultar los demás
    else {

      producto.style.display = "none";

    }

  });

}

// Detectar clic en cada filtro
filtros.forEach((boton) => {

  boton.addEventListener("click", () => {

    // Quitar active de todos
    filtros.forEach((btn) => {

      btn.classList.remove("active");

    });

    // Activar botón seleccionado
    boton.classList.add("active");

    // Obtener la marca
    const marca = boton.dataset.filtro;

    // Filtrar productos
    filtrarProductos(marca);

  });

});

// FILTRO INICIAL// Mostrar todos al cargar
filtrarProductos("todos");