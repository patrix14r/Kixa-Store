/* =========================================================
   KIXA - DEPORTE
   SLIDER + FILTROS + BUSCADOR
========================================================= */

/*SLIDER*/

const slides = document.querySelectorAll(".hero-slide");
const dots = document.querySelectorAll(".dot");

const nextButton = document.querySelector(".hero-next");
const prevButton = document.querySelector(".hero-prev");

let currentSlide = 0;
let autoSlide;

/* Mostrar slide */

function showSlide(index) {

  if (slides.length === 0) return;

  slides.forEach((slide, i) => {

    slide.classList.toggle(
      "active",
      i === index
    );

  });

  dots.forEach((dot, i) => {

    dot.classList.toggle(
      "active",
      i === index
    );

  });

  currentSlide = index;
}

/* Siguiente */

function nextSlide() {

  if (slides.length === 0) return;

  currentSlide++;

  if (currentSlide >= slides.length) {
    currentSlide = 0;
  }

  showSlide(currentSlide);
}

/* Anterior */

function prevSlide() {

  if (slides.length === 0) return;

  currentSlide--;

  if (currentSlide < 0) {
    currentSlide = slides.length - 1;
  }

  showSlide(currentSlide);
}

/* Reiniciar reproducción */

function startSlider() {

  clearInterval(autoSlide);

  autoSlide = setInterval(
    nextSlide,
    5000
  );
}

/* Botón siguiente */

if (nextButton) {

  nextButton.addEventListener(
    "click",
    () => {

      nextSlide();
      startSlider();

    }
  );

}

/* Botón anterior */

if (prevButton) {

  prevButton.addEventListener(
    "click",
    () => {

      prevSlide();
      startSlider();

    }
  );

}

/* Puntos */

dots.forEach((dot, index) => {

  dot.addEventListener(
    "click",
    () => {

      showSlide(index);
      startSlider();

    }
  );

});

/* Iniciar slider */

if (slides.length > 0) {

  showSlide(0);
  startSlider();

}

/* =========================================================
   FILTROS DE MARCA
========================================================= */

const filtros = document.querySelectorAll(".filtro");
const productos = document.querySelectorAll(".producto");

filtros.forEach((filtro) => {

  filtro.addEventListener(
    "click",
    () => {

      /* Quitar activo */

      filtros.forEach((boton) => {

        boton.classList.remove("activo");

      });

      /* Activar seleccionado */

      filtro.classList.add("activo");

      /* Obtener marca */

      const marcaSeleccionada =
        filtro.dataset.filtro;

      /* Filtrar */

      productos.forEach((producto) => {

        const marcaProducto =
          producto.dataset.marca;

        if (
          marcaSeleccionada === "todos" ||
          marcaProducto === marcaSeleccionada
        ) {

          producto.classList.remove("oculto");

          producto.classList.add("mostrar");

        } else {

          producto.classList.add("oculto");

          producto.classList.remove("mostrar");

        }

      });

    }
  );

});

/* =========================================================
   BUSCADOR
========================================================= */

const buscador =
  document.getElementById("buscador");

if (buscador) {

  buscador.addEventListener(
    "input",
    () => {

      const texto =
        buscador.value
          .toLowerCase()
          .trim();

      /* Si está vacío, mostrar todos */

      if (texto === "") {

        productos.forEach((producto) => {

          producto.classList.remove("oculto");
          producto.classList.add("mostrar");

        });

        return;
      }

      /* Buscar dentro de las tarjetas */

      productos.forEach((producto) => {

        const contenido =
          producto.textContent.toLowerCase();

        if (contenido.includes(texto)) {

          producto.classList.remove("oculto");
          producto.classList.add("mostrar");

        } else {

          producto.classList.add("oculto");
          producto.classList.remove("mostrar");

        }

      });

    }
  );

}