// =====================================================
// COMPROBAR SESIÓN
// =====================================================

const sesion = localStorage.getItem("usuarioLogueado");

if (!sesion) {

    alert("⚠️ Debes iniciar sesión para entrar a tu cuenta.");

    window.location.href = "login.html";

}


// =====================================================
// OBTENER USUARIO ACTIVO
// =====================================================

const usuarioActivo = JSON.parse(sesion);


// =====================================================
// OBTENER USUARIO GUARDADO
// =====================================================

const usuarioGuardado = localStorage.getItem("usuarioKixa");

let usuario = null;

if (usuarioGuardado) {

    usuario = JSON.parse(usuarioGuardado);

}


// =====================================================
// ELEMENTOS
// =====================================================

const nombreUsuario =
    document.getElementById("nombreUsuario");

const nombrePerfil =
    document.getElementById("nombrePerfil");

const emailUsuario =
    document.getElementById("emailUsuario");

const telefonoUsuario =
    document.getElementById("telefonoUsuario");

const dniUsuario =
    document.getElementById("dniUsuario");

const ciudadUsuario =
    document.getElementById("ciudadUsuario");

const direccionUsuario =
    document.getElementById("direccionUsuario");

const fechaNacimiento =
    document.getElementById("fechaNacimiento");

const generoUsuario =
    document.getElementById("generoUsuario");

const fotoPerfil =
    document.getElementById("fotoPerfil");


// =====================================================
// MOSTRAR INFORMACIÓN DEL USUARIO
// =====================================================

// NOMBRE

nombreUsuario.value =
    usuarioActivo.nombre || "";


// NOMBRE EN PERFIL

nombrePerfil.textContent =
    usuarioActivo.nombre || "Mi perfil";


// EMAIL

emailUsuario.textContent =
    usuarioActivo.email || "";


// =====================================================
// CARGAR DATOS ADICIONALES
// =====================================================

const datosCuenta =
    JSON.parse(
        localStorage.getItem("datosCuentaKixa")
    ) || {};


// TELEFONO

telefonoUsuario.value =
    datosCuenta.telefono || "";


// DNI

dniUsuario.value =
    datosCuenta.dni || "";


// CIUDAD

ciudadUsuario.value =
    datosCuenta.ciudad || "";


// DIRECCION

direccionUsuario.value =
    datosCuenta.direccion || "";


// FECHA DE NACIMIENTO

fechaNacimiento.value =
    datosCuenta.fechaNacimiento || "";


// GENERO

generoUsuario.value =
    datosCuenta.genero || "";


// =====================================================
// MOSTRAR FOTO GUARDADA
// =====================================================

const fotoGuardada =
    localStorage.getItem("fotoPerfilKixa");


if (fotoGuardada) {

    fotoPerfil.src = fotoGuardada;

}


// =====================================================
// GUARDAR CAMBIOS
// =====================================================

document
    .getElementById("guardarNombre")
    .addEventListener("click", () => {


        // =============================================
        // OBTENER DATOS
        // =============================================

        const nuevoNombre =
            nombreUsuario.value.trim();


        const nuevoTelefono =
            telefonoUsuario.value.trim();


        const nuevoDni =
            dniUsuario.value.trim();


        const nuevaCiudad =
            ciudadUsuario.value;


        const nuevaDireccion =
            direccionUsuario.value.trim();


        const nuevaFecha =
            fechaNacimiento.value;


        const nuevoGenero =
            generoUsuario.value;



        // =============================================
        // VALIDAR NOMBRE
        // =============================================

        if (nuevoNombre === "") {

            alert("⚠️ Escribe tu nombre.");

            nombreUsuario.focus();

            return;

        }



        // =============================================
        // VALIDAR DNI
        // =============================================

        if (
            nuevoDni !== "" &&
            !/^\d{8}$/.test(nuevoDni)
        ) {

            alert(
                "⚠️ El DNI debe tener exactamente 8 números."
            );

            dniUsuario.focus();

            return;

        }



        // =============================================
        // VALIDAR TELEFONO
        // =============================================

        if (
            nuevoTelefono !== "" &&
            !/^\d{9}$/.test(
                nuevoTelefono.replace(/\s/g, "")
            )
        ) {

            alert(
                "⚠️ El número de teléfono debe tener 9 números."
            );

            telefonoUsuario.focus();

            return;

        }



        // =============================================
        // ACTUALIZAR SESIÓN
        // =============================================

        usuarioActivo.nombre =
            nuevoNombre;


        localStorage.setItem(
            "usuarioLogueado",
            JSON.stringify(usuarioActivo)
        );



        // =============================================
        // ACTUALIZAR USUARIO REGISTRADO
        // =============================================

        if (usuario) {

            usuario.nombre =
                nuevoNombre;


            localStorage.setItem(
                "usuarioKixa",
                JSON.stringify(usuario)
            );

        }



        // =============================================
        // GUARDAR INFORMACIÓN ADICIONAL
        // =============================================

        const nuevosDatos = {

            telefono:
                nuevoTelefono,

            dni:
                nuevoDni,

            ciudad:
                nuevaCiudad,

            direccion:
                nuevaDireccion,

            fechaNacimiento:
                nuevaFecha,

            genero:
                nuevoGenero

        };


        localStorage.setItem(
            "datosCuentaKixa",
            JSON.stringify(nuevosDatos)
        );



        // =============================================
        // ACTUALIZAR NOMBRE EN PERFIL
        // =============================================

        nombrePerfil.textContent =
            nuevoNombre;



        // =============================================
        // MENSAJE
        // =============================================

        alert(
            "✅ Información actualizada correctamente."
        );

    });


// =====================================================
// CAMBIAR FOTO
// =====================================================

document
    .getElementById("inputFoto")
    .addEventListener("change", function () {


        const archivo =
            this.files[0];


        if (!archivo) {

            return;

        }



        // =============================================
        // VALIDAR TIPO DE ARCHIVO
        // =============================================

        if (
            !archivo.type.startsWith("image/")
        ) {

            alert(
                "⚠️ Selecciona una imagen válida."
            );

            return;

        }



        // =============================================
        // VALIDAR TAMAÑO
        // =============================================

        if (
            archivo.size > 5 * 1024 * 1024
        ) {

            alert(
                "⚠️ La imagen no debe superar los 5 MB."
            );

            return;

        }



        // =============================================
        // LEER IMAGEN
        // =============================================

        const lector =
            new FileReader();


        lector.onload = function (e) {


            const imagen =
                e.target.result;


            // Mostrar imagen

            fotoPerfil.src =
                imagen;


            // Guardar imagen

            localStorage.setItem(
                "fotoPerfilKixa",
                imagen
            );

        };


        lector.readAsDataURL(
            archivo
        );

    });


// =====================================================
// CERRAR SESIÓN
// =====================================================

document
    .getElementById("cerrarSesion")
    .addEventListener("click", () => {


        const confirmar =
            confirm(
                "¿Estás seguro de que quieres cerrar sesión?"
            );


        if (!confirmar) {

            return;

        }



        // =============================================
        // ELIMINAR SOLAMENTE LA SESIÓN
        // =============================================

        localStorage.removeItem(
            "usuarioLogueado"
        );


        // =============================================
        // MENSAJE
        // =============================================

        alert(
            "Sesión cerrada correctamente 👋"
        );


        // =============================================
        // VOLVER AL INICIO
        // =============================================

        window.location.href =
            "index.html";

    });