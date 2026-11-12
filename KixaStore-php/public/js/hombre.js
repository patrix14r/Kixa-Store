
// TIENDA KIXA - CATÁLOGO HOMBRE 2026 (HOMBRE.JS)
// Slider de cabecera y filtrado dinámico de zapatillas por marca


document.addEventListener("DOMContentLoaded", () => {

    // [BLOQUE 1]: CONTROL DEL SLIDER HERO (SECCIÓN HOMBRE)
    
    const slides = document.querySelectorAll(".hero-slide");
    const dots = document.querySelectorAll(".hero-dots .dot");
    const nextButton = document.querySelector(".hero-next");
    const prevButton = document.querySelector(".hero-prev");

    let currentSlide = 0;
    let slideInterval = null;

    // [CAMBIO] Victor : Función para mostrar el slide correspondiente al índice
    function showSlide(index) {
        if (!slides || slides.length === 0) return;

        if (index >= slides.length) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = slides.length - 1;
        } else {
            currentSlide = index;
        }

        slides.forEach((slide, i) => {
            slide.classList.toggle("active", i === currentSlide);
        });

        dots.forEach((dot, i) => {
            dot.classList.toggle("active", i === currentSlide);
        });
    }

    // Funciones de navegación del slider
    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function prevSlide() {
        showSlide(currentSlide - 1);
    }

    // [CAMBIO]: Reinicio del temporizador automático
    function restartSlider() {
        if (slideInterval) clearInterval(slideInterval);
        if (slides.length > 0) {
            slideInterval = setInterval(nextSlide, 4000);
        }
    }

    // Asignación de eventos en botones del slider
    if (nextButton) {
        nextButton.addEventListener("click", () => {
            nextSlide();
            restartSlider();
        });
    }

    if (prevButton) {
        prevButton.addEventListener("click", () => {
            prevSlide();
            restartSlider();
        });
    }

    dots.forEach((dot, index) => {
        dot.addEventListener("click", () => {
            showSlide(index);
            restartSlider();
        });
    });

    if (slides.length > 0) {
        showSlide(0);
        restartSlider();
    }

    // [BLOQUE 2]: FILTRADO DINÁMICO DE PRODUCTOS POR MARCA
    
    // Victor: Se corrigió la línea donde el comentario ocultaba 'const filtros' y 'const productos' en la línea siguiente
    const filtros = document.querySelectorAll(".filtros button");
    const productos = document.querySelectorAll(".producto");

    // Victor: Función para filtrar y mostrar productos según la marca elegida
    function filtrarProductos(marcaSeleccionada) {
        productos.forEach((producto) => {
            const marcaProducto = producto.dataset.marca;

            if (marcaSeleccionada === "todos" || marcaProducto === marcaSeleccionada) {
                producto.style.display = "";
            } else {
                producto.style.display = "none";
            }
        });
    }

    // Eventos para cada botón de filtro (Nike, Adidas, Puma, etc.)
    filtros.forEach((boton) => {
        boton.addEventListener("click", () => {
            filtros.forEach((btn) => btn.classList.remove("active"));
            boton.classList.add("active");

            const marca = boton.dataset.filtro || "todos";
            filtrarProductos(marca);
        });
    });

    // Filtro inicial: mostrar todos los productos
    if (productos.length > 0) {
        filtrarProductos("todos");
    }
});
