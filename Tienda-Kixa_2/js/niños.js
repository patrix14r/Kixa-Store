/* =========================================================
   SLIDER
========================================================= */

const slides = document.querySelectorAll(".hero-slide");

const dots = document.querySelectorAll(".dot");

const nextButton =
  document.querySelector(".hero-next");

const prevButton =
  document.querySelector(".hero-prev");

let currentSlide = 0;

let autoSlide;


/* =========================================================
   MOSTRAR SLIDE
========================================================= */

function showSlide(index) {

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


/* =========================================================
   SIGUIENTE SLIDE
========================================================= */

function nextSlide() {

  currentSlide++;

  if (currentSlide >= slides.length) {
    currentSlide = 0;
  }

  showSlide(currentSlide);

}


/* =========================================================
   SLIDE ANTERIOR
========================================================= */

function prevSlide() {

  currentSlide--;

  if (currentSlide < 0) {
    currentSlide = slides.length - 1;
  }

  showSlide(currentSlide);

}


/* =========================================================
   SLIDER AUTOMÁTICO
========================================================= */

function startSlider() {

  clearInterval(autoSlide);

  autoSlide = setInterval(
    nextSlide,
    5000
  );

}


/* =========================================================
   BOTÓN SIGUIENTE
========================================================= */

nextButton.addEventListener(
  "click",
  () => {

    nextSlide();

    startSlider();

  }
);


/* =========================================================
   BOTÓN ANTERIOR
========================================================= */

prevButton.addEventListener(
  "click",
  () => {

    prevSlide();

    startSlider();

  }
);


/* =========================================================
   PUNTOS
========================================================= */

dots.forEach(
  (dot, index) => {

    dot.addEventListener(
      "click",
      () => {

        showSlide(index);

        startSlider();

      }
    );

  }
);


/* =========================================================
   INICIAR SLIDER
========================================================= */

showSlide(0);

startSlider();



/* =========================================================
   FILTROS POR MARCA
========================================================= */

const filtros =
  document.querySelectorAll(".filtro");


const productos =
  document.querySelectorAll(".producto");


filtros.forEach(
  (filtro) => {

    filtro.addEventListener(
      "click",
      () => {

        /* -----------------------------
           BOTÓN ACTIVO
        ----------------------------- */

        filtros.forEach(
          (boton) => {

            boton.classList.remove(
              "activo"
            );

          }
        );


        filtro.classList.add(
          "activo"
        );


        /* -----------------------------
           OBTENER MARCA
        ----------------------------- */

        const marca =
          filtro.dataset.filtro;


        /* -----------------------------
           FILTRAR PRODUCTOS
        ----------------------------- */

        productos.forEach(
          (producto) => {

            const marcaProducto =
              producto.dataset.marca;


            if (
              marca === "todos" ||
              marcaProducto === marca
            ) {

              producto.classList.remove(
                "oculto"
              );

              producto.classList.remove(
                "mostrar"
              );


              setTimeout(
                () => {

                  producto.classList.add(
                    "mostrar"
                  );

                },
                10
              );

            }

            else {

              producto.classList.add(
                "oculto"
              );

              producto.classList.remove(
                "mostrar"
              );

            }

          }
        );

      }
    );

  }
);