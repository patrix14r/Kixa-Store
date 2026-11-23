// ==========================================================================
// TIENDA KIXA - ALERTAS FLOTANTES UNIFICADAS (TOAST.JS)
// Esquina superior derecha bajo el header, blur, ícono y cierre automático en 4 s.
// Uso: kxToast("Mensaje", "error" | "ok" | "info"). Los flash de Laravel llegan en window.KX_FLASH.
// ==========================================================================

(() => {
    const ICONOS = {
        ok: '<path d="m8 12 3 3 5-6"/><circle cx="12" cy="12" r="9"/>',
        error: '<circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 16.5v.5"/>',
        info: '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 7.5v.5"/>',
    };

    function contenedor() {
        let caja = document.getElementById("kx-toasts");
        if (!caja) {
            caja = document.createElement("div");
            caja.id = "kx-toasts";
            caja.className = "kx-toasts";
            caja.setAttribute("aria-live", "polite");
            document.body.appendChild(caja);
        }
        return caja;
    }

    window.kxToast = function (mensaje, tipo = "info", duracion = 4000) {
        if (!mensaje) return;
        const toast = document.createElement("div");
        toast.className = `kx-toast kx-toast--${tipo}`;
        toast.setAttribute("role", tipo === "error" ? "alert" : "status");

        const icono = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        icono.setAttribute("viewBox", "0 0 24 24");
        icono.setAttribute("width", "20");
        icono.setAttribute("height", "20");
        icono.setAttribute("aria-hidden", "true");
        icono.innerHTML = ICONOS[tipo] || ICONOS.info;

        const texto = document.createElement("p");
        texto.textContent = mensaje;

        const cerrar = document.createElement("button");
        cerrar.type = "button";
        cerrar.className = "kx-toast-cerrar";
        cerrar.setAttribute("aria-label", "Cerrar aviso");
        cerrar.textContent = "×";

        toast.append(icono, texto, cerrar);
        contenedor().appendChild(toast);
        requestAnimationFrame(() => toast.classList.add("visible"));

        const quitar = () => {
            toast.classList.remove("visible");
            setTimeout(() => toast.remove(), 300);
        };
        cerrar.addEventListener("click", quitar);
        setTimeout(quitar, duracion);
    };

    // Mensajes flash y errores de validación enviados por el servidor
    document.addEventListener("DOMContentLoaded", () => {
        const flash = window.KX_FLASH || {};
        if (flash.exito) kxToast(flash.exito, "ok");
        if (flash.error) kxToast(flash.error, "error");
        (flash.errores || []).forEach((e, i) => setTimeout(() => kxToast(e, "error", 5000), i * 150));
    });
})();
