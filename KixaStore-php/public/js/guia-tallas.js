// Modal "Guía de tallas" de la ficha de producto: se abre con el enlace, se cierra con la X, el fondo o Escape.
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modalGuiaTallas");
    const abrir = document.getElementById("abrirGuiaTallas");
    if (!modal || !abrir) return;

    const cerrar = () => {
        modal.classList.remove("abierto");
        setTimeout(() => { modal.hidden = true; }, 250);
        document.removeEventListener("keydown", alEscape);
        abrir.focus();
    };
    const alEscape = (e) => { if (e.key === "Escape") cerrar(); };

    abrir.addEventListener("click", () => {
        modal.hidden = false;
        requestAnimationFrame(() => modal.classList.add("abierto"));
        document.addEventListener("keydown", alEscape);
        modal.querySelector(".guia-tallas-cerrar").focus();
    });
    modal.querySelectorAll("[data-cerrar-guia]").forEach((el) => el.addEventListener("click", cerrar));
});
