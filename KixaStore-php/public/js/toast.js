// ==========================================================================
// TIENDA KIXA - ALERTAS FLOTANTES UNIFICADAS (TOAST.JS)
// Esquina superior derecha bajo el header, blur, ícono y cierre automático en 4 s.
// Uso: kxToast("Mensaje", "error" | "ok" | "info"). Los flash de Laravel llegan en window.KX_FLASH.
// ==========================================================================

(() => {
    // Clases de Tailwind: el aviso entra desde la derecha cuando recibe el atributo data-visible
    const CLASE_CAJA = "pointer-events-none fixed top-[calc(var(--spacing-navbar)_+_16px)] right-5 z-3000 flex w-[min(380px,calc(100vw_-_32px))] flex-col gap-2.5 max-escritorio:top-[120px] max-escritorio:right-4";
    const CLASE_TOAST = "pointer-events-auto grid grid-cols-[20px_1fr_auto] items-start gap-3 rounded-[14px] border bg-white/88 py-3.5 pr-3.5 pl-4 text-[14px] leading-[1.45] text-tinta opacity-0 shadow-[0_18px_40px_-16px_rgba(0,0,0,0.35)] backdrop-blur-[12px] translate-x-4 transition-[opacity,translate] duration-250 data-visible:translate-x-0 data-visible:opacity-100";
    const COLOR = { ok: "text-exito", error: "text-agotado", info: "text-tinta" };

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
            caja.className = CLASE_CAJA;
            caja.setAttribute("aria-live", "polite");
            document.body.appendChild(caja);
        }
        return caja;
    }

    window.kxToast = function (mensaje, tipo = "info", duracion = 4000) {
        if (!mensaje) return;
        const toast = document.createElement("div");
        toast.className = `${CLASE_TOAST} ${tipo === "error" ? "border-agotado/25" : "border-[rgba(17,17,17,0.08)]"}`;
        toast.setAttribute("role", tipo === "error" ? "alert" : "status");

        const icono = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        icono.setAttribute("viewBox", "0 0 24 24");
        icono.setAttribute("width", "20");
        icono.setAttribute("height", "20");
        icono.setAttribute("aria-hidden", "true");
        icono.setAttribute("fill", "none");
        icono.setAttribute("stroke", "currentColor");
        icono.setAttribute("stroke-width", "1.75");
        icono.setAttribute("stroke-linecap", "round");
        icono.setAttribute("stroke-linejoin", "round");
        icono.setAttribute("class", `mt-px ${COLOR[tipo] || COLOR.info}`);
        icono.innerHTML = ICONOS[tipo] || ICONOS.info;

        const texto = document.createElement("p");
        texto.textContent = mensaje;

        const cerrar = document.createElement("button");
        cerrar.type = "button";
        cerrar.className = "cursor-pointer border-none bg-transparent text-[20px] leading-none text-suave hover:text-tinta";
        cerrar.setAttribute("aria-label", "Cerrar aviso");
        cerrar.textContent = "×";

        toast.append(icono, texto, cerrar);
        contenedor().appendChild(toast);
        requestAnimationFrame(() => { toast.dataset.visible = ""; });

        const quitar = () => {
            delete toast.dataset.visible;
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
