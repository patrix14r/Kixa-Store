// ==========================================================================
// TIENDA KIXA - AUTENTICACIÓN Y REGISTRO DE USUARIOS (LOGIN.JS)
// Conmutación entre formularios, validaciones de seguridad y login local
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
    // ----------------------------------------------------------------------
    // [BLOQUE 1]: ELEMENTOS DEL DOM
    // ----------------------------------------------------------------------
    // [CORRECCIÓN]: Se corrigieron las declaraciones donde '//' comentaba la variable
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");
    const showRegister = document.getElementById("showRegister");
    const showLogin = document.getElementById("showLogin");

    // ----------------------------------------------------------------------
    // [BLOQUE 2]: CAMBIO ENTRE PESTAÑA LOGIN Y REGISTRO
    // ----------------------------------------------------------------------
    if (showRegister && loginForm && registerForm) {
        showRegister.addEventListener("click", (e) => {
            e.preventDefault();
            loginForm.classList.remove("active");
            registerForm.classList.add("active");
        });
    }

    if (showLogin && loginForm && registerForm) {
        showLogin.addEventListener("click", (e) => {
            e.preventDefault();
            registerForm.classList.remove("active");
            loginForm.classList.add("active");
        });
    }

    // ----------------------------------------------------------------------
    // [BLOQUE 3]: REGISTRO DE NUEVO USUARIO
    // ----------------------------------------------------------------------
    if (registerForm) {
        registerForm.addEventListener("submit", (e) => {
            e.preventDefault();

            // Obtención de valores ingresados
            const nombre = document.getElementById("registerName") ? document.getElementById("registerName").value.trim() : "";
            const email = document.getElementById("registerEmail") ? document.getElementById("registerEmail").value.trim().toLowerCase() : "";
            const telefono = document.getElementById("registerPhone") ? document.getElementById("registerPhone").value.trim() : "";
            const dni = document.getElementById("registerDni") ? document.getElementById("registerDni").value.trim() : "";
            const ciudad = document.getElementById("registerCity") ? document.getElementById("registerCity").value : "";
            const direccion = document.getElementById("registerAddress") ? document.getElementById("registerAddress").value.trim() : "";
            const fechaNacimiento = document.getElementById("registerBirth") ? document.getElementById("registerBirth").value : "";
            const genero = document.getElementById("registerGender") ? document.getElementById("registerGender").value : "";
            const password = document.getElementById("registerPassword") ? document.getElementById("registerPassword").value : "";

            // Validación de campos vacíos
            if (!nombre || !email || !telefono || !dni || !ciudad || !direccion || !fechaNacimiento || !genero || !password) {
                alert("⚠️ Debes completar todos los campos del registro.");
                return;
            }

            // Validación de celular (9 dígitos)
            if (!/^\d{9}$/.test(telefono.replace(/\s+/g, ""))) {
                alert("⚠️ El número de celular debe contener 9 dígitos.");
                if (document.getElementById("registerPhone")) document.getElementById("registerPhone").focus();
                return;
            }

            // Validación de DNI (8 dígitos)
            if (!/^\d{8}$/.test(dni)) {
                alert("⚠️ El DNI debe contener 8 dígitos numéricos.");
                if (document.getElementById("registerDni")) document.getElementById("registerDni").focus();
                return;
            }

            // Validación de longitud mínima de contraseña
            if (password.length < 5) {
                alert("⚠️ La contraseña debe tener al menos 5 caracteres.");
                if (document.getElementById("registerPassword")) document.getElementById("registerPassword").focus();
                return;
            }

            // Verificar si el correo ya existe registrado en LocalStorage
            const usuarioGuardado = localStorage.getItem("usuarioKixa");
            if (usuarioGuardado) {
                const usuario = JSON.parse(usuarioGuardado);
                if (usuario.email && usuario.email.toLowerCase() === email) {
                    alert("⚠️ Este correo ya se encuentra registrado.");
                    return;
                }
            }

            // Estructura del nuevo usuario
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

            // Almacenar usuario y datos complementarios
            localStorage.setItem("usuarioKixa", JSON.stringify(nuevoUsuario));

            const datosCuenta = {
                telefono: telefono,
                dni: dni,
                ciudad: ciudad,
                direccion: direccion,
                fechaNacimiento: fechaNacimiento,
                genero: genero
            };
            localStorage.setItem("datosCuentaKixa", JSON.stringify(datosCuenta));

            alert(`¡Cuenta creada correctamente, ${nombre}! ✅`);

            // Limpiar formulario y pasar a vista de login
            registerForm.reset();
            registerForm.classList.remove("active");
            if (loginForm) {
                loginForm.classList.add("active");
                const loginEmailInput = document.getElementById("loginEmail");
                if (loginEmailInput) loginEmailInput.value = email;
            }
        });
    }

    // ----------------------------------------------------------------------
    // [BLOQUE 4]: INICIO DE SESIÓN
    // ----------------------------------------------------------------------
    if (loginForm) {
        loginForm.addEventListener("submit", (e) => {
            e.preventDefault();

            const email = document.getElementById("loginEmail") ? document.getElementById("loginEmail").value.trim().toLowerCase() : "";
            const password = document.getElementById("loginPassword") ? document.getElementById("loginPassword").value : "";

            // Credenciales de Administrador para pruebas
            if (email === "admin@kixa.com" && password === "12345") {
                const administrador = {
                    nombre: "Administrador Kixa",
                    email: email,
                    tipo: "admin"
                };

                localStorage.setItem("usuarioLogueado", JSON.stringify(administrador));
                alert("Bienvenido administrador 👑");
                window.location.href = "/";
                return;
            }

            // Validar existencia de usuario en LocalStorage
            const usuarioGuardado = localStorage.getItem("usuarioKixa");
            if (!usuarioGuardado) {
                alert("⚠️ No existe una cuenta registrada. Por favor regístrate primero.");
                return;
            }

            const usuario = JSON.parse(usuarioGuardado);

            // Verificación de credenciales
            if (email === usuario.email.toLowerCase() && password === usuario.password) {
                const usuarioActivo = {
                    nombre: usuario.nombre,
                    email: usuario.email,
                    tipo: "cliente"
                };

                localStorage.setItem("usuarioLogueado", JSON.stringify(usuarioActivo));

                const datosCuenta = {
                    telefono: usuario.telefono,
                    dni: usuario.dni,
                    ciudad: usuario.ciudad,
                    direccion: usuario.direccion,
                    fechaNacimiento: usuario.fechaNacimiento,
                    genero: usuario.genero
                };
                localStorage.setItem("datosCuentaKixa", JSON.stringify(datosCuenta));

                alert(`Bienvenido a Tienda Kixa, ${usuario.nombre} 👟`);
                window.location.href = "/";
            } else {
                alert("❌ Correo electrónico o contraseña incorrectos.");
            }
        });
    }
});
