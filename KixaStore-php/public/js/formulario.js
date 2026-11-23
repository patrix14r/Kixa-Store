// ==========================================================================
// TIENDA KIXA - VALIDACIÓN DEL FORMULARIO DE CONTÁCTANOS (FORMULARIO.JS)
// Campos inválidos en rojo y mensajes en el toast flotante (sin alert)
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
    const contactForm = document.getElementById("contactForm");

    if (!contactForm) return;

    // Reglas por campo: devuelven el mensaje de error o "" si es válido
    const reglas = {
        nombre: (v) => (v.length < 3 ? "Ingresa tu nombre completo." : ""),
        // [VALIDACION FORMULARIO]: DNI de exactamente 8 dígitos numéricos
        dni: (v) => (!/^\d{8}$/.test(v) ? "El DNI debe tener exactamente 8 dígitos." : ""),
        // [VALIDACION FORMULARIO]: Teléfono de exactamente 9 dígitos numéricos
        telefono: (v) => (!/^9\d{8}$/.test(v) ? "Ingresa un celular válido de 9 dígitos (empieza con 9)." : ""),
        direccion: (v) => (v.length < 5 ? "Ingresa tu dirección." : ""),
        mensaje: (v) => (v.length < 10 ? "Cuéntanos un poco más (mínimo 10 caracteres)." : ""),
    };

    const validarCampo = (nombre) => {
        const input = contactForm.elements[nombre];
        if (!input) return "";

        const error = reglas[nombre](input.value.trim());
        const campo = input.closest(".campo");

        // El campo solo se marca en rojo; el mensaje va al toast
        campo?.classList.toggle("invalido", Boolean(error));
        input.setAttribute("aria-invalid", error ? "true" : "false");
        if (input.title !== error) input.title = error;

        return error;
    };

    // Valida al salir del campo y limpia el error mientras el usuario corrige
    Object.keys(reglas).forEach((nombre) => {
        const input = contactForm.elements[nombre];
        if (!input) return;
        input.addEventListener("blur", () => {
            const error = validarCampo(nombre);
            if (error && input.value.trim() !== "") kxToast(error, "error");
        });
        input.addEventListener("input", () => {
            if (input.closest(".campo")?.classList.contains("invalido")) validarCampo(nombre);
        });
    });

    contactForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const errores = Object.keys(reglas).map(validarCampo).filter(Boolean);
        if (errores.length) {
            kxToast(errores[0], "error");
            contactForm.querySelector(".campo.invalido input, .campo.invalido textarea")?.focus();
            return;
        }

        contactForm.reset();
        kxToast("¡Mensaje enviado! Un asesor de KixaStore se comunicará contigo muy pronto.", "ok");
    });
});
