// ==========================================================================
// TIENDA KIXA - VALIDACIÓN DE LOGIN Y REGISTRO (AUTH.JS)
// [Actualizado: Login split-screen] Los formularios usan novalidate: aquí se validan con mensajes 100% en español
// (sin "Please fill out this field." del navegador). El servidor vuelve a validar todo en AuthController.
// ==========================================================================

document.querySelectorAll("[data-validar-auth]").forEach((form) => {
    const correoValido = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v);

    // Reglas por name del input: devuelven el mensaje de error o "" si es válido
    const reglas = {
        name: (v) => (v.length < 3 ? "Ingresa tu nombre completo." : ""),
        email: (v) => (!v ? "Ingresa tu correo electrónico." : !correoValido(v) ? "El correo no tiene un formato válido." : ""),
        phone: (v) => (v && !/^9\d{8}$/.test(v) ? "El teléfono debe tener 9 dígitos y empezar con 9." : ""),
        dni: (v) => (v && !/^\d{8}$/.test(v) ? "El DNI debe tener exactamente 8 dígitos." : ""),
        password: (v) => (!v ? "Ingresa tu contraseña." : form.id === "registerForm" && v.length < 8 ? "La contraseña debe tener al menos 8 caracteres." : ""),
        password_confirmation: (v) => (v !== (form.elements.password?.value || "") ? "Las contraseñas no coinciden." : ""),
    };

    const validar = (input) => {
        const regla = reglas[input.name];
        if (!regla) return "";
        const error = regla(input.value.trim());
        const campo = input.closest(".auth-campo");
        let aviso = campo?.querySelector(".auth-error");
        if (campo && !aviso) {
            aviso = document.createElement("small");
            aviso.className = "auth-error";
            campo.appendChild(aviso);
        }
        if (aviso) aviso.textContent = error;
        input.classList.toggle("input-invalido", Boolean(error));
        input.setAttribute("aria-invalid", error ? "true" : "false");
        return error;
    };

    const campos = [...form.querySelectorAll("input")].filter((i) => reglas[i.name]);

    campos.forEach((input) => {
        input.addEventListener("blur", () => input.value && validar(input));
        input.addEventListener("input", () => input.classList.contains("input-invalido") && validar(input));
    });

    form.addEventListener("submit", (e) => {
        const errores = campos.map(validar).filter(Boolean);
        if (errores.length) {
            e.preventDefault();
            form.querySelector(".input-invalido")?.focus();
            if (typeof kxToast === "function") kxToast(errores[0], "error");
        }
    });
});
