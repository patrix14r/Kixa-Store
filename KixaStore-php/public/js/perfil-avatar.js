// Foto de perfil y modo edición

(() => {
    const input = document.getElementById("inputAvatar");
    const formulario = document.getElementById("formPerfil");
    const nombre = document.getElementById("avatarNombre");

    if (!input || !formulario) return;

    input.addEventListener("change", () => {
        const archivo = input.files[0];
        if (!archivo) return;

        if (nombre) nombre.textContent = `Subiendo: ${archivo.name}...`;

        // Vista previa de la foto
        let foto = document.getElementById("fotoPerfil");
        if (!foto) {
            foto = document.createElement("img");
            foto.id = "fotoPerfil";
            foto.alt = "Foto de perfil";
            document.getElementById("fotoInicial")?.replaceWith(foto);
        }
        foto.src = URL.createObjectURL(archivo);

        formulario.requestSubmit();
    });
})();

// Alterna entre ver y editar
document.querySelectorAll("[data-alternar-edicion]").forEach((btn) => {
    btn.addEventListener("click", () => btn.closest("[data-editable]")?.classList.toggle("editando"));
});
