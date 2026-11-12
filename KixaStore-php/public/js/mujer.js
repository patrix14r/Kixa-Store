// ==========================================================================
// TIENDA KIXA - CATÁLOGO MUJER 2026 (MUJER.JS)
// Slider de cabecera y filtro interactivo con animaciones suaves
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
    // ----------------------------------------------------------------------
    // [BLOQUE 1]: CONTROL DEL SLIDER HERO (SECCIÓN MUJER)
    // ----------------------------------------------------------------------
    const slides = document.querySelectorAll(".hero-slide");
    const dots = document.querySelectorAll(".dot");
    const nextButton = document.querySelector(".hero-next");
    const prevButton = document.querySelector(".hero-prev");

    let currentSlide = 0;
    let autoSlide = null;

    // [CAMBIO]: Mostrar el slide correspondiente
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

    // [CAMBIO]: Temporizador automático de 5 segundos
    function startSlider() {
        if (autoSlide) clearInterval(autoSlide);
        if (slides.length > 0) {
            autoSlide = setInterval(nextSlide, 5000);
        }
    }

    // Eventos protegidos para los botones del slider
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
    // [BLOQUE 2]: FILTROS DE PRODUCTO POR MARCA CON ANIMACIÓN
    // ----------------------------------------------------------------------
    const filtros = document.querySelectorAll(".filtro");
    const productos = document.querySelectorAll(".producto");

    filtros.forEach((filtro) => {
        filtro.addEventListener("click", () => {
            // Actualizamos la clase activa en los botones de filtro
            filtros.forEach((boton) => boton.classList.remove("activo"));
            filtro.classList.add("activo");

            const marca = filtro.dataset.filtro || "todos";

            // Animamos la visibilidad de los productos filtrados
            productos.forEach((producto) => {
                const marcaProducto = producto.dataset.marca;

                if (marca === "todos" || marcaProducto === marca) {
                    producto.classList.remove("oculto");
                    producto.classList.remove("mostrar");

                    // Pequeño retardo para reiniciar la animación CSS de entrada
                    setTimeout(() => {
                        producto.classList.add("mostrar");
                    }, 15);
                } else {
                    producto.classList.add("oculto");
                    producto.classList.remove("mostrar");
                }
            });
        });
    });
});
