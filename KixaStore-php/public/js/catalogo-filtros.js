// ==========================================================================
// TIENDA KIXA - FILTROS DEL CATÁLOGO ESTILO NIKE (CATALOGO-FILTROS.JS)
// Desktop: oculta/muestra el sidebar. Móvil: abre un drawer.
// ==========================================================================

(() => {
    const boton = document.getElementById("btn-filtros");
    const cuerpo = document.getElementById("kx-catalogo-cuerpo");
    const fondo = document.getElementById("kx-filtros-fondo");
    const cerrar = document.getElementById("kx-filtros-cerrar");
    if (!boton || !cuerpo) return;

    // Si la URL trae filtros u orden, se queda en la grilla y no salta al banner
    const zona = document.getElementById("zona-catalogo");
    const params = new URLSearchParams(location.search);
    if (zona && ["marca", "sub", "talla", "orden", "buscar"].some((p) => params.has(p))) {
        zona.scrollIntoView({ behavior: "instant", block: "start" });
    }

    // El cambio de orden conserva el ancla #zona-catalogo
    document.getElementById("orden")?.addEventListener("change", (e) => {
        const form = e.target.form;
        if (form && !form.action.includes("#zona-catalogo")) form.action += "#zona-catalogo";
    });

    const texto = boton.querySelector(".kx-btn-filtros-texto");
    const esMovil = () => window.matchMedia("(max-width: 900px)").matches;

    const pintarBoton = (visible) => {
        boton.setAttribute("aria-expanded", visible ? "true" : "false");
        if (texto) texto.textContent = visible ? texto.dataset.ocultar : texto.dataset.mostrar;
    };

    const alternarDrawer = (abrir) => {
        cuerpo.classList.toggle("drawer-abierto", abrir);
        if (fondo) fondo.hidden = !abrir;
        document.body.classList.toggle("menu-movil-activo", abrir);
        pintarBoton(abrir);
    };

    // En móvil el sidebar arranca cerrado
    if (esMovil()) pintarBoton(false);

    boton.addEventListener("click", () => {
        if (esMovil()) {
            alternarDrawer(!cuerpo.classList.contains("drawer-abierto"));
        } else {
            const oculto = cuerpo.classList.toggle("filtros-ocultos");
            pintarBoton(!oculto);
        }
    });

    cerrar?.addEventListener("click", () => alternarDrawer(false));
    fondo?.addEventListener("click", () => alternarDrawer(false));
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && cuerpo.classList.contains("drawer-abierto")) alternarDrawer(false);
    });
})();
