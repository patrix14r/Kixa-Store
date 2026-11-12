// ==========================================================================
// TIENDA KIXA - GESTIÓN DE PERFIL Y CUENTA DE USUARIO (CUENTA.JS)
// Manejo de sesión local, actualización de datos personales y foto de perfil
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
    // ----------------------------------------------------------------------
    // [BLOQUE 1]: VERIFICACIÓN DE SESIÓN ACTIVA
    // ----------------------------------------------------------------------
    // [CORRECCIÓN]: Se corrigió la declaración de 'sesion' que estaba comentada
    const sesion = localStorage.getItem("usuarioLogueado");

    if (!sesion) {
        alert("⚠️ Debes iniciar sesión para entrar a tu cuenta.");
        window.location.href = "/login";
        return;
    }

    // [CAMBIO]: Parseo seguro del usuario en sesión y usuario registrado
    const usuarioActivo = JSON.parse(sesion);
    const usuarioGuardado = localStorage.getItem("usuarioKixa");
    let usuario = usuarioGuardado ? JSON.parse(usuarioGuardado) : null;

    // ----------------------------------------------------------------------
    // [BLOQUE 2]: OBTENCIÓN DE ELEMENTOS DEL FORMULARIO Y PERFIL
    // ----------------------------------------------------------------------
    const nombreUsuario = document.getElementById("nombreUsuario");
    const nombrePerfil = document.getElementById("nombrePerfil");
    const emailUsuario = document.getElementById("emailUsuario");
    const telefonoUsuario = document.getElementById("telefonoUsuario");
    const dniUsuario = document.getElementById("dniUsuario");
    const ciudadUsuario = document.getElementById("ciudadUsuario");
    const direccionUsuario = document.getElementById("direccionUsuario");
    const fechaNacimiento = document.getElementById("fechaNacimiento");
    const generoUsuario = document.getElementById("generoUsuario");
    const fotoPerfil = document.getElementById("fotoPerfil");
    const btnGuardarNombre = document.getElementById("guardarNombre");
    const inputFoto = document.getElementById("inputFoto");
    const btnCerrarSesion = document.getElementById("cerrarSesion");

    // ----------------------------------------------------------------------
    // [BLOQUE 3]: CARGA INICIAL DE DATOS EN LA PANTALLA
    // ----------------------------------------------------------------------
    if (nombreUsuario) nombreUsuario.value = usuarioActivo.nombre || "";
    if (nombrePerfil) nombrePerfil.textContent = usuarioActivo.nombre || "Mi perfil";
    if (emailUsuario) emailUsuario.textContent = usuarioActivo.email || "";

    const datosCuenta = JSON.parse(localStorage.getItem("datosCuentaKixa")) || {};

    if (telefonoUsuario) telefonoUsuario.value = datosCuenta.telefono || "";
    if (dniUsuario) dniUsuario.value = datosCuenta.dni || "";
    if (ciudadUsuario) ciudadUsuario.value = datosCuenta.ciudad || "";
    if (direccionUsuario) direccionUsuario.value = datosCuenta.direccion || "";
    if (fechaNacimiento) fechaNacimiento.value = datosCuenta.fechaNacimiento || "";
    if (generoUsuario) generoUsuario.value = datosCuenta.genero || "";

    // Foto de perfil almacenada previamente
    const fotoGuardada = localStorage.getItem("fotoPerfilKixa");
    if (fotoGuardada && fotoPerfil) {
        fotoPerfil.src = fotoGuardada;
    }

    // ----------------------------------------------------------------------
    // [BLOQUE 4]: GUARDAR CAMBIOS DE INFORMACIÓN PERSONAL
    // ----------------------------------------------------------------------
    if (btnGuardarNombre) {
        btnGuardarNombre.addEventListener("click", () => {
            const nuevoNombre = nombreUsuario ? nombreUsuario.value.trim() : "";
            const nuevoTelefono = telefonoUsuario ? telefonoUsuario.value.trim() : "";
            const nuevoDni = dniUsuario ? dniUsuario.value.trim() : "";
            const nuevaCiudad = ciudadUsuario ? ciudadUsuario.value : "";
            const nuevaDireccion = direccionUsuario ? direccionUsuario.value.trim() : "";
            const nuevaFecha = fechaNacimiento ? fechaNacimiento.value : "";
            const nuevoGenero = generoUsuario ? generoUsuario.value : "";

            // Validación de nombre
            if (!nuevoNombre) {
                alert("⚠️ Por favor escribe tu nombre completo.");
                if (nombreUsuario) nombreUsuario.focus();
                return;
            }

            // Validación de DNI peruano (8 dígitos numéricos)
            if (nuevoDni && !/^\d{8}$/.test(nuevoDni)) {
                alert("⚠️ El DNI debe tener exactamente 8 dígitos.");
                if (dniUsuario) dniUsuario.focus();
                return;
            }

            // Validación de teléfono / celular (9 dígitos numéricos)
            if (nuevoTelefono && !/^\d{9}$/.test(nuevoTelefono.replace(/\s+/g, ""))) {
                alert("⚠️ El número de celular debe tener 9 dígitos numéricos.");
                if (telefonoUsuario) telefonoUsuario.focus();
                return;
            }

            // Actualización de sesión activa
            usuarioActivo.nombre = nuevoNombre;
            localStorage.setItem("usuarioLogueado", JSON.stringify(usuarioActivo));

            // Actualización del registro general si existe
            if (usuario) {
                usuario.nombre = nuevoNombre;
                localStorage.setItem("usuarioKixa", JSON.stringify(usuario));
            }

            // Guardar datos complementarios en LocalStorage
            const nuevosDatos = {
                telefono: nuevoTelefono,
                dni: nuevoDni,
                ciudad: nuevaCiudad,
                direccion: nuevaDireccion,
                fechaNacimiento: nuevaFecha,
                genero: nuevoGenero
            };
            localStorage.setItem("datosCuentaKixa", JSON.stringify(nuevosDatos));

            if (nombrePerfil) nombrePerfil.textContent = nuevoNombre;
            alert("✅ Información actualizada correctamente.");
        });
    }

    // ----------------------------------------------------------------------
    // [BLOQUE 5]: CAMBIO DE FOTO DE PERFIL (FileReader)
    // ----------------------------------------------------------------------
    if (inputFoto) {
        inputFoto.addEventListener("change", function() {
            const archivo = this.files[0];
            if (!archivo) return;

            if (!archivo.type.startsWith("image/")) {
                alert("⚠️ Por favor selecciona un archivo de imagen válido (JPG, PNG, WEBP).");
                return;
            }

            if (archivo.size > 5 * 1024 * 1024) {
                alert("⚠️ La imagen seleccionada no debe superar los 5 MB.");
                return;
            }

            const lector = new FileReader();
            lector.onload = function(e) {
                const imagen = e.target.result;
                if (fotoPerfil) fotoPerfil.src = imagen;
                localStorage.setItem("fotoPerfilKixa", imagen);
            };
            lector.readAsDataURL(archivo);
        });
    }

    // ----------------------------------------------------------------------
    // [BLOQUE 6]: CERRAR SESIÓN DEL USUARIO
    // ----------------------------------------------------------------------
    if (btnCerrarSesion) {
        btnCerrarSesion.addEventListener("click", () => {
            const confirmar = confirm("¿Estás seguro de que deseas cerrar sesión?");
            if (!confirmar) return;

            localStorage.removeItem("usuarioLogueado");
            alert("Sesión cerrada correctamente 👋 ¡Hasta pronto!");
            window.location.href = "/";
        });
    }
});
