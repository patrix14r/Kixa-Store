// ==========================================================================
// TIENDA KIXA - CONTROL DEL CARRUSEL 3D DEL HOME (MOVIMIENTO.JS)
// Manejo de slides en perspectiva, navegación circular y animación automática
// ==========================================================================

// [CAMBIO]: Selección segura de elementos del DOM
const slides = document.querySelectorAll(".slide");
const prevBtn = document.querySelector(".prev");
const nextBtn = document.querySelector(".next");
const dots = document.querySelectorAll(".dot");

let currentSlide = 0;
let carouselTimer = null;

// [CAMBIO]: Función para mostrar el slide central y posicionar los laterales en 3D
function showSlide(index) {
    if (!slides || slides.length === 0) return;

    const total = slides.length;

    slides.forEach((slide, i) => {
        // Limpiamos clases de posición previas
        slide.classList.remove(
            "active",
            "prev-slide",
            "next-slide",
            "far-left",
            "far-right"
        );

        let difference = i - index;

        // Ajustamos la diferencia para efecto de carrusel infinito
        if (difference > total / 2) {
            difference -= total;
        }
        if (difference < -total / 2) {
            difference += total;
        }

        // Asignamos la clase adecuada según su distancia relativa
        if (difference === 0) {
            slide.classList.add("active");
        } else if (difference === -1) {
            slide.classList.add("prev-slide");
        } else if (difference === 1) {
            slide.classList.add("next-slide");
        } else if (difference === -2) {
            slide.classList.add("far-left");
        } else if (difference === 2) {
            slide.classList.add("far-right");
        }
    });

    // Actualizamos el estado visual del indicador de puntos
    dots.forEach((dot, i) => {
        dot.classList.toggle("active", i === index);
    });
}

// [CAMBIO]: Función para avanzar al siguiente slide
function nextSlide() {
    if (!slides || slides.length === 0) return;
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}

// [CAMBIO]: Función para retroceder al slide anterior
function prevSlide() {
    if (!slides || slides.length === 0) return;
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    showSlide(currentSlide);
}

// [CAMBIO]: Reinicio del temporizador para evitar saltos bruscos tras clics del usuario
function resetTimer() {
    if (carouselTimer) clearInterval(carouselTimer);
    carouselTimer = setInterval(nextSlide, 4500);
}

// [CAMBIO]: Inicialización protegida contra errores en páginas sin carrusel
if (slides.length > 0) {
    if (nextBtn) {
        nextBtn.addEventListener("click", () => {
            nextSlide();
            resetTimer();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener("click", () => {
            prevSlide();
            resetTimer();
        });
    }

    dots.forEach((dot, index) => {
        dot.addEventListener("click", () => {
            currentSlide = index;
            showSlide(currentSlide);
            resetTimer();
        });
    });

    // Mostramos el slide inicial e iniciamos la rotación automática
    showSlide(currentSlide);
    resetTimer();
}
