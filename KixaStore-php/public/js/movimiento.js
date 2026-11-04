// Slider principal
const slides = document.querySelectorAll(".slide");

const prevBtn = document.querySelector(".prev");

const nextBtn = document.querySelector(".next");

const dots = document.querySelectorAll(".dot");

let currentSlide = 0;

function showSlide(index) {

    const total = slides.length;

    slides.forEach((slide, i) => {

        // Limpiar clases

        slide.classList.remove(
            "active",
            "prev-slide",
            "next-slide",
            "far-left",
            "far-right"
        );

      

        let difference = i - index;

       

        if (difference > total / 2) {
            difference -= total;
        }

        if (difference < -total / 2) {
            difference += total;
        }

        // Slide principal

        if (difference === 0) {

            slide.classList.add("active");

        }

        // Slide de la izquierda

        else if (difference === -1) {

            slide.classList.add("prev-slide");

        }

        // Slide de la derecha

        else if (difference === 1) {

            slide.classList.add("next-slide");

        }

        // Slide lejano izquierda

        else if (difference === -2) {

            slide.classList.add("far-left");

        }

        // Slide lejano derecha

        else if (difference === 2) {

            slide.classList.add("far-right");

        }

    });

    dots.forEach((dot, i) => {

        dot.classList.remove("active");

        if (i === index) {

            dot.classList.add("active");

        }

    });

}

function nextSlide() {

    currentSlide++;

    if (currentSlide >= slides.length) {

        currentSlide = 0;

    }

    showSlide(currentSlide);

}

function prevSlide() {

    currentSlide--;

    if (currentSlide < 0) {

        currentSlide = slides.length - 1;

    }

    showSlide(currentSlide);

}

nextBtn.addEventListener("click", () => {

    nextSlide();

});

prevBtn.addEventListener("click", () => {

    prevSlide();

});

dots.forEach((dot, index) => {

    dot.addEventListener("click", () => {

        currentSlide = index;

        showSlide(currentSlide);

    });

});

setInterval(() => {

    nextSlide();

}, 4000);

showSlide(currentSlide);