// Carrusel de destacados del home

(() => {
    const pista = document.getElementById("destacado-pista");
    const prev = document.getElementById("destacado-prev");
    const next = document.getElementById("destacado-next");
    const puntos = document.getElementById("destacado-puntos");
    if (!pista || !prev || !next || !puntos) return;

    const tarjetas = [...pista.children].filter((el) => el.tagName === "ARTICLE");
    if (tarjetas.length === 0) return;

    const INTERVALO = 4000;
    const reducirMovimiento = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const CLASE_PUNTO = "h-2 w-2 cursor-pointer rounded-full border-none bg-[#cacacb] p-0 transition-[width,background-color] duration-300 hover:bg-suave aria-[current=true]:w-6 aria-[current=true]:bg-tinta";

    let porVista = 1;
    let paginas = 1;
    let actual = 0;
    let temporizador = null;
    let pausado = false;

    const calcularPorVista = () => {
        const ancho = tarjetas[0].getBoundingClientRect().width;
        const gap = parseFloat(getComputedStyle(pista).columnGap) || 0;
        return Math.max(1, Math.floor((pista.clientWidth + gap) / (ancho + gap) + 0.01));
    };

    const inicioDePagina = (p) => Math.min(p * porVista, Math.max(0, tarjetas.length - porVista));
    const desplazamiento = (p) => tarjetas[inicioDePagina(p)].offsetLeft - tarjetas[0].offsetLeft;

    const pintarPuntos = () => {
        [...puntos.children].forEach((punto, i) => punto.setAttribute("aria-current", i === actual ? "true" : "false"));
    };

    const crearPuntos = () => {
        porVista = calcularPorVista();
        paginas = Math.max(1, Math.ceil(tarjetas.length / porVista));
        actual = Math.min(actual, paginas - 1);
        puntos.innerHTML = Array.from({ length: paginas }, (_, i) =>
            `<button type="button" class="${CLASE_PUNTO}" aria-label="Ver grupo ${i + 1} de ${paginas}"></button>`).join("");
        puntos.hidden = paginas <= 1;
        prev.disabled = next.disabled = paginas <= 1;
        pintarPuntos();
    };

    // Va a una página del carrusel
    const irA = (p, suave = true) => {
        actual = (p + paginas) % paginas;
        pista.scrollTo({ left: desplazamiento(actual), behavior: suave && !reducirMovimiento ? "smooth" : "auto" });
        pintarPuntos();
    };

    // Avance automático cada 4 segundos
    const detener = () => {
        clearInterval(temporizador);
        temporizador = null;
    };
    const iniciar = () => {
        detener();
        if (reducirMovimiento || paginas <= 1 || pausado || document.hidden) return;
        temporizador = setInterval(() => irA(actual + 1), INTERVALO);
    };
    const pausar = () => {
        pausado = true;
        detener();
    };
    const reanudar = () => {
        pausado = false;
        iniciar();
    };

    const seccion = pista.closest("section") || pista;
    seccion.addEventListener("mouseenter", pausar);
    seccion.addEventListener("mouseleave", reanudar);
    seccion.addEventListener("focusin", pausar);
    seccion.addEventListener("focusout", (e) => {
        if (!seccion.contains(e.relatedTarget)) reanudar();
    });
    pista.addEventListener("touchstart", pausar, { passive: true });
    document.addEventListener("visibilitychange", () => (document.hidden ? detener() : iniciar()));

    // Flechas e indicadores
    prev.addEventListener("click", () => { irA(actual - 1); iniciar(); });
    next.addEventListener("click", () => { irA(actual + 1); iniciar(); });
    puntos.addEventListener("click", (e) => {
        const indice = [...puntos.children].indexOf(e.target.closest("button"));
        if (indice >= 0) {
            irA(indice);
            iniciar();
        }
    });

    // El indicador sigue al deslizar
    let esperaScroll;
    pista.addEventListener("scroll", () => {
        clearTimeout(esperaScroll);
        esperaScroll = setTimeout(() => {
            let cercana = 0;
            for (let p = 1; p < paginas; p++) {
                if (Math.abs(desplazamiento(p) - pista.scrollLeft) < Math.abs(desplazamiento(cercana) - pista.scrollLeft)) cercana = p;
            }
            actual = cercana;
            pintarPuntos();
        }, 120);
    }, { passive: true });

    let esperaResize;
    window.addEventListener("resize", () => {
        clearTimeout(esperaResize);
        esperaResize = setTimeout(() => {
            crearPuntos();
            irA(actual, false);
            iniciar();
        }, 150);
    });

    crearPuntos();
    iniciar();
})();
