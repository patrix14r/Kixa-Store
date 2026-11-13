// ==========================================================================
// TIENDA KIXA - AUTENTICACIÓN Y REGISTRO DE USUARIOS (LOGIN.JS)
// Conmutación visual entre formularios y envío real a Laravel 13
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
    // ----------------------------------------------------------------------
    // [BLOQUE 1]: ELEMENTOS DEL DOM
    // ----------------------------------------------------------------------
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");
    const showRegister = document.getElementById("showRegister");
    const showLogin = document.getElementById("showLogin");

    // ----------------------------------------------------------------------
    // [BLOQUE 2]: CAMBIO ENTRE PESTAÑA LOGIN Y REGISTRO (SI COEXISTEN EN LA MISMA PAGINA)
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
    // [CAMBIO: SE DESACTIVO EL e.preventDefault() EN LOGIN Y REGISTRO]
    // Los formularios ahora se procesan directamente por AuthController en Laravel 13
    // contra la base de datos MySQL (tabla users).
    // ----------------------------------------------------------------------
});
