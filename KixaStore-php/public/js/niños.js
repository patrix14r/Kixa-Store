// ==========================================================================
// TIENDA KIXA - CATÁLOGO NIÑOS 2026 (NIÑOS.JS)
// Slider interactivo y filtrado visual de zapatillas infantiles
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
    // ----------------------------------------------------------------------
    // [BLOQUE 1]: CONTROL DEL SLIDER HERO (SECCIÓN NIÑOS)
    // ----------------------------------------------------------------------
    const slides = document.querySelectorAll(".hero-slide");
    const dots = document.querySelectorAll(".dot");
    const nextButton = document.querySelector(".hero-next");
    const prevButton = document.querySelector(".hero-prev");

    let currentSlide = 0;
    let autoSlide = null;

    // [CAMBIO]: Función para activar el slide actual e indicador
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

    // [CAMBIO]: Control de temporizador automático
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
    // [BLOQUE 2]: FILTRADO POR MARCA CON TRANSICIONES CSS
    // ----------------------------------------------------------------------
    const filtros = document.querySelectorAll(".filtro");
    const productos = document.querySelectorAll(".producto");

    filtros.forEach((filtro) => {
        filtro.addEventListener("click", () => {
            filtros.forEach((boton) => boton.classList.remove("activo"));
            filtro.classList.add("activo");

            const marca = filtro.dataset.filtro || "todos";

            productos.forEach((producto) => {
                const marcaProducto = producto.dataset.marca;

                if (marca === "todos" || marcaProducto === marca) {
                    producto.classList.remove("oculto");
                    producto.classList.remove("mostrar");

                    setTimeout(() => {
                        producto.classList.add("mostrar");
                    }, 10);
                } else {
                    producto.classList.add("oculto");
                    producto.classList.remove("mostrar");
                }
            });
        });
    });
});
