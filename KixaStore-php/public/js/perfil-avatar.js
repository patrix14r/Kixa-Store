// ==========================================================================
// TIENDA KIXA - FOTO DE PERFIL (PERFIL-AVATAR.JS)
// Al elegir una imagen muestra el nombre y la vista previa, y envía el formulario para guardarla
// ==========================================================================

(() => {
    const input = document.getElementById("inputAvatar");
    const formulario = document.getElementById("formPerfil");
    const nombre = document.getElementById("avatarNombre");

    if (!input || !formulario) return;

    input.addEventListener("change", () => {
        const archivo = input.files[0];
        if (!archivo) return;

        if (nombre) nombre.textContent = `Subiendo: ${archivo.name}...`;

        // Vista previa inmediata (reemplaza la inicial si aún no había foto)
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
