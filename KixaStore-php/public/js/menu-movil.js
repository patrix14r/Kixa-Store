// ==========================================================================
// TIENDA KIXA - MENÚ HAMBURGUESA DEL HEADER EN TELÉFONOS (MENU-MOVIL.JS)
// Abre y cierra el menú lateral deslizable; se cierra con el fondo, la X o Escape
// ==========================================================================

(() => {
    const menu = document.getElementById("menu-movil");
    const fondo = document.getElementById("menu-movil-fondo");
    const btnAbrir = document.getElementById("btn-menu-movil");
    const btnCerrar = document.getElementById("btn-menu-cerrar");

    if (!menu || !btnAbrir) return;

    const alternar = (abrir) => {
        menu.classList.toggle("abierto", abrir);
        menu.setAttribute("aria-hidden", abrir ? "false" : "true");
        btnAbrir.setAttribute("aria-expanded", abrir ? "true" : "false");
        if (fondo) fondo.hidden = !abrir;
        document.body.classList.toggle("menu-movil-activo", abrir);
    };

    btnAbrir.addEventListener("click", () => alternar(true));
    btnCerrar?.addEventListener("click", () => alternar(false));
    fondo?.addEventListener("click", () => alternar(false));
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") alternar(false);
    });
    window.addEventListener("resize", () => {
        if (window.innerWidth > 768) alternar(false);
    });
})();
