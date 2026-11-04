// ELEMENTOSconst loginForm =
    document.getElementById("loginForm");

const registerForm =
    document.getElementById("registerForm");

const showRegister =
    document.getElementById("showRegister");

const showLogin =
    document.getElementById("showLogin");

// CAMBIAR ENTRE LOGIN Y REGISTROshowRegister.addEventListener("click", (e) => {

    e.preventDefault();

    loginForm.classList.remove("active");

    registerForm.classList.add("active");

});

showLogin.addEventListener("click", (e) => {

    e.preventDefault();

    registerForm.classList.remove("active");

    loginForm.classList.add("active");

});

// REGISTRO DE USUARIOregisterForm.addEventListener("submit", (e) => {

    e.preventDefault();

        // OBTENER DATOS
        const nombre =
        document
            .getElementById("registerName")
            .value
            .trim();

    const email =
        document
            .getElementById("registerEmail")
            .value
            .trim()
            .toLowerCase();

    const telefono =
        document
            .getElementById("registerPhone")
            .value
            .trim();

    const dni =
        document
            .getElementById("registerDni")
            .value
            .trim();

    const ciudad =
        document
            .getElementById("registerCity")
            .value;

    const direccion =
        document
            .getElementById("registerAddress")
            .value
            .trim();

    const fechaNacimiento =
        document
            .getElementById("registerBirth")
            .value;

    const genero =
        document
            .getElementById("registerGender")
            .value;

    const password =
        document
            .getElementById("registerPassword")
            .value;

        // VALIDAR CAMPOS
        if (
        nombre === "" ||
        email === "" ||
        telefono === "" ||
        dni === "" ||
        ciudad === "" ||
        direccion === "" ||
        fechaNacimiento === "" ||
        genero === "" ||
        password === ""
    ) {

        alert(
            "⚠️ Debes completar todos los campos."
        );

        return;

    }

        // VALIDAR TELEFONO
        if (!/^\d{9}$/.test(telefono)) {

        alert(
            "⚠️ El número de teléfono debe tener 9 números."
        );

        document
            .getElementById("registerPhone")
            .focus();

        return;

    }

        // VALIDAR DNI
        if (!/^\d{8}$/.test(dni)) {

        alert(
            "⚠️ El DNI debe tener exactamente 8 números."
        );

        document
            .getElementById("registerDni")
            .focus();

        return;

    }

        // VALIDAR CONTRASEÑA
        if (password.length < 5) {

        alert(
            "⚠️ La contraseña debe tener al menos 5 caracteres."
        );

        document
            .getElementById("registerPassword")
            .focus();

        return;

    }

        // VERIFICAR CUENTA EXISTENTE
        const usuarioGuardado =
        localStorage.getItem("usuarioKixa");

    if (usuarioGuardado) {

        const usuario =
            JSON.parse(usuarioGuardado);

        if (
            usuario.email.toLowerCase() ===
            email
        ) {

            alert(
                "⚠️ Este correo ya está registrado."
            );

            return;

        }

    }

        // CREAR USUARIO
        const nuevoUsuario = {

        nombre: nombre,

        email: email,

        password: password,

        telefono: telefono,

        dni: dni,

        ciudad: ciudad,

        direccion: direccion,

        fechaNacimiento: fechaNacimiento,

        genero: genero

    };

        // GUARDAR USUARIO
        localStorage.setItem(
        "usuarioKixa",
        JSON.stringify(nuevoUsuario)
    );

        // GUARDAR DATOS PARA CUENTA.HTML
        const datosCuenta = {

        telefono: telefono,

        dni: dni,

        ciudad: ciudad,

        direccion: direccion,

        fechaNacimiento: fechaNacimiento,

        genero: genero

    };

    localStorage.setItem(
        "datosCuentaKixa",
        JSON.stringify(datosCuenta)
    );

        // MENSAJE
        alert(
        `Cuenta creada correctamente, ${nombre} ✅`
    );

        // LIMPIAR REGISTRO
        registerForm.reset();

        // MOSTRAR LOGIN
        registerForm.classList.remove("active");

    loginForm.classList.add("active");

        // COLOCAR CORREO AUTOMÁTICAMENTE
        document
        .getElementById("loginEmail")
        .value = email;

    // Limpiar contraseña

    document
        .getElementById("loginPassword")
        .value = "";

});

// INICIO DE SESIÓNloginForm.addEventListener("submit", (e) => {

    e.preventDefault();

        // OBTENER DATOS
        const email =
        document
            .getElementById("loginEmail")
            .value
            .trim()
            .toLowerCase();

    const password =
        document
            .getElementById("loginPassword")
            .value;

        // CUENTA ADMINISTRADOR
        if (
        email === "admin@kixa.com" &&
        password === "12345"
    ) {

        const administrador = {

            nombre: "Administrador",

            email: email,

            tipo: "admin"

        };

        localStorage.setItem(
            "usuarioLogueado",
            JSON.stringify(administrador)
        );

        alert(
            "Bienvenido administrador 👑"
        );

        window.location.href =
            "index.html";

        return;

    }

        // BUSCAR USUARIO
        const usuarioGuardado =
        localStorage.getItem("usuarioKixa");

    if (!usuarioGuardado) {

        alert(
            "⚠️ No existe una cuenta registrada. Primero debes registrarte."
        );

        return;

    }

    const usuario =
        JSON.parse(usuarioGuardado);

        // COMPROBAR CREDENCIALES
        if (
        email === usuario.email.toLowerCase() &&
        password === usuario.password
    ) {

                // CREAR SESION
                const usuarioActivo = {

            nombre:
                usuario.nombre,

            email:
                usuario.email,

            tipo:
                "cliente"

        };

                // GUARDAR SESION
                localStorage.setItem(
            "usuarioLogueado",
            JSON.stringify(usuarioActivo)
        );

                // RECUPERAR DATOS DE CUENTA
                const datosCuenta = {

            telefono:
                usuario.telefono,

            dni:
                usuario.dni,

            ciudad:
                usuario.ciudad,

            direccion:
                usuario.direccion,

            fechaNacimiento:
                usuario.fechaNacimiento,

            genero:
                usuario.genero

        };

        localStorage.setItem(
            "datosCuentaKixa",
            JSON.stringify(datosCuenta)
        );

                // BIENVENIDA
                alert(
            `Bienvenido a Kixa, ${usuario.nombre} 👟`
        );

                // IR AL INICIO
                window.location.href =
            "index.html";

    } else {

        alert(
            "❌ Correo o contraseña incorrectos."
        );

    }

});