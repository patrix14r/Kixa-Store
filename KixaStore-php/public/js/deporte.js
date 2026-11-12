 
// TIENDA KIXA - CATÁLOGO DEPORTE 2026 (DEPORTE.JS)
// Slider deportivo, filtrado por marcas y barra de búsqueda en tiempo real
 s

document.addEventListener("DOMContentLoaded", () => {
    // [BLOQUE 1]: SLIDER DE CABECERA (SECCIÓN DEPORTIVO)
    const slides = document.querySelectorAll(".hero-slide");
    const dots = document.querySelectorAll(".dot");
    const nextButton = document.querySelector(".hero-next");
    const prevButton = document.querySelector(".hero-prev");

    let currentSlide = 0;
    let autoSlide = null;

    // [CAMBIO]: Mostrar slide activo y actualizar puntos
    function showSlide(index) {
        if (!slides || slides.length === 0) return;

        slides.forEach((slide, i) => {
            slide.classList.toggle("active", i === index);
        });

        dots.forEach((dot, i) => {
            dot.classList.toggle("active", i === index);
        });

        currentSlide = index;
    }

    function nextSlide() {
        if (!slides || slides.length === 0) return;
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    function prevSlide() {
        if (!slides || slides.length === 0) return;
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }

    function startSlider() {
        if (autoSlide) clearInterval(autoSlide);
        if (slides.length > 0) {
            autoSlide = setInterval(nextSlide, 5000);
        }
    }

    if (nextButton) {
        nextButton.addEventListener("click", () => {
            nextSlide();
            startSlider();
        });
    }

    if (prevButton) {
        prevButton.addEventListener("click", () => {
            prevSlide();
            startSlider();
        });
    }

    dots.forEach((dot, index) => {
        dot.addEventListener("click", () => {
            showSlide(index);
            startSlider();
        });
    });

    if (slides.length > 0) {
        showSlide(0);
        startSlider();
    }

    // ----------------------------------------------------------------------
    // [BLOQUE 2]: FILTRADO POR MARCAS DEPORTIVAS
    // ----------------------------------------------------------------------
    const filtros = document.querySelectorAll(".filtro");
    const productos = document.querySelectorAll(".producto");

    filtros.forEach((filtro) => {
        filtro.addEventListener("click", () => {
            filtros.forEach((boton) => boton.classList.remove("activo"));
            filtro.classList.add("activo");

            const marcaSeleccionada = filtro.dataset.filtro || "todos";

            productos.forEach((producto) => {
                const marcaProducto = producto.dataset.marca;

                if (marcaSeleccionada === "todos" || marcaProducto === marcaSeleccionada) {
                    producto.classList.remove("oculto");
                    producto.classList.add("mostrar");
                } else {
                    producto.classList.add("oculto");
                    producto.classList.remove("mostrar");
                }
            });
        });
    });

    // ----------------------------------------------------------------------
    // [BLOQUE 3]: BÚSQUEDA DINÁMICA DE CALZADO POR TEXTO
    // ----------------------------------------------------------------------
    const buscador = document.getElementById("buscador");

    if (buscador) {
        buscador.addEventListener("input", () => {
            const texto = buscador.value.toLowerCase().trim();

            productos.forEach((producto) => {
                const contenido = producto.textContent.toLowerCase();

                if (texto === "" || contenido.includes(texto)) {
                    producto.classList.remove("oculto");
                    producto.classList.add("mostrar");
                } else {
                    producto.classList.add("oculto");
                    producto.classList.remove("mostrar");
                }
            });
        });
    }
});
