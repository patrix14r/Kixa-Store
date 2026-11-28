// Filtros del catálogo sin recargar

(() => {
    const zona = document.getElementById("zona-catalogo");
    if (!zona) return;

    const cuerpo = () => document.getElementById("kx-catalogo-cuerpo");
    const fondo = () => document.getElementById("kx-filtros-fondo");
    const esMovil = () => window.matchMedia("(max-width: 900px)").matches;
    const ruta = (pathname) => pathname.replace(/\/todos\/?$/, "").replace(/\/$/, "");

    // Con filtros se queda en la grilla
    const params = new URLSearchParams(location.search);
    if (["marca", "sub", "talla", "orden", "buscar", "page"].some((p) => params.has(p))) {
        zona.scrollIntoView({ behavior: "instant", block: "start" });
    }

    const pintarBoton = (visible) => {
        const boton = document.getElementById("btn-filtros");
        const texto = boton?.querySelector(".kx-btn-filtros-texto");
        boton?.setAttribute("aria-expanded", visible ? "true" : "false");
        if (texto) texto.textContent = visible ? texto.dataset.ocultar : texto.dataset.mostrar;
    };

    const alternarDrawer = (abrir) => {
        cuerpo()?.classList.toggle("drawer-abierto", abrir);
        if (fondo()) fondo().hidden = !abrir;
        document.body.classList.toggle("menu-movil-activo", abrir);
        pintarBoton(abrir);
    };

    if (esMovil()) pintarBoton(false);

    // Guarda el estado visual de los filtros
    const leerEstado = () => ({
        ocultos: cuerpo()?.classList.contains("filtros-ocultos") ?? false,
        drawer: cuerpo()?.classList.contains("drawer-abierto") ?? false,
        acordeones: Object.fromEntries([...zona.querySelectorAll("details[data-filtro]")].map((d) => [d.dataset.filtro, d.open])),
        scrollFiltros: zona.querySelector(".kx-filtros")?.scrollTop || 0,
    });

    const aplicarEstado = (estado) => {
        cuerpo()?.classList.toggle("filtros-ocultos", estado.ocultos && !esMovil());
        if (estado.drawer && esMovil()) {
            alternarDrawer(true);
        } else {
            pintarBoton(esMovil() ? false : !estado.ocultos);
        }
        zona.querySelectorAll("details[data-filtro]").forEach((d) => {
            if (d.dataset.filtro in estado.acordeones) d.open = estado.acordeones[d.dataset.filtro];
        });
        const filtros = zona.querySelector(".kx-filtros");
        if (filtros) filtros.scrollTop = estado.scrollFiltros;
    };

    // Carga de la grilla por fetch
    let peticion = null;

    async function cargar(url) {
        const destino = new URL(url, location.href);
        peticion?.abort();
        const actual = (peticion = new AbortController());
        zona.setAttribute("aria-busy", "true");

        try {
            const respuesta = await fetch(destino.pathname + destino.search, {
                headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "text/html" },
                signal: actual.signal,
            });
            if (!respuesta.ok) throw new Error(`HTTP ${respuesta.status}`);
            const html = await respuesta.text();

            const estado = leerEstado();
            zona.innerHTML = html;
            aplicarEstado(estado);

            history.replaceState(history.state, "", `${destino.pathname}${destino.search}#zona-catalogo`);
            const titulo = zona.querySelector("[data-titulo-pagina]")?.dataset.tituloPagina;
            if (titulo) document.title = titulo;
            if (typeof kxPintarFavoritos === "function") kxPintarFavoritos(zona);

            if (zona.getBoundingClientRect().top < 0) zona.scrollIntoView({ behavior: "smooth", block: "start" });
        } catch (error) {
            // Si falla, navega normal
            if (error.name !== "AbortError") window.location.href = destino.href;
        } finally {
            if (peticion === actual) zona.removeAttribute("aria-busy");
        }
    }

    zona.addEventListener("click", (e) => {
        if (e.target.closest("#btn-filtros")) {
            if (esMovil()) {
                alternarDrawer(!cuerpo()?.classList.contains("drawer-abierto"));
            } else {
                pintarBoton(!cuerpo()?.classList.toggle("filtros-ocultos"));
            }
            return;
        }
        if (e.target.closest("#kx-filtros-cerrar, #kx-filtros-fondo")) {
            alternarDrawer(false);
            return;
        }

        // Filtros y paginación por fetch
        const enlace = e.target.closest("a[href]");
        if (!enlace || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        const url = new URL(enlace.href, location.href);
        if (url.origin !== location.origin || ruta(url.pathname) !== ruta(location.pathname)) return;
        e.preventDefault();
        cargar(url.href);
    });

    // Cambiar el orden vuelve a página 1
    zona.addEventListener("change", (e) => {
        const select = e.target.closest("[data-form-orden] select");
        if (!select) return;
        const datos = new URLSearchParams(new FormData(select.form));
        datos.delete("page");
        const url = new URL(select.form.action, location.href);
        url.search = datos.toString();
        cargar(url.href);
    });

    // Buscador del header también por fetch
    document.querySelector('form[role="search"]')?.addEventListener("submit", (e) => {
        e.preventDefault();
        const url = new URL(e.currentTarget.action, location.href);
        url.search = new URLSearchParams(new FormData(e.currentTarget)).toString();
        cargar(url.href);
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && cuerpo()?.classList.contains("drawer-abierto")) alternarDrawer(false);
    });
})();
