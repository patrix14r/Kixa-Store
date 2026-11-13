// ==========================================================================
// TIENDA KIXA - INTERACTIVIDAD DEL FORMULARIO DE CONTACTO (FORMULARIO.JS)
// Validación de campos, formato de teléfono y envío amigable
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
    // [CAMBIO]: Selección protegida del formulario de contacto
    const contactForm = document.getElementById("contactForm");

    if (!contactForm) return;

    // [CAMBIO]: Escucha del evento submit con validaciones claras en español
    contactForm.addEventListener("submit", function(e) {
        e.preventDefault();

        const nombre = this.nombre ? this.nombre.value.trim() : "";
        const dni = this.dni ? this.dni.value.trim() : "";
        const telefono = this.telefono ? this.telefono.value.trim() : "";
        const direccion = this.direccion ? this.direccion.value.trim() : "";
        const mensaje = this.mensaje ? this.mensaje.value.trim() : "";

        // 1. Validar que no existan campos vacíos
        if (!nombre || !telefono || !direccion || !mensaje) {
            alert("⚠️ Por favor completa todos los campos del formulario.");
            return;
        }

        // // [VALIDACION FORMULARIO]: DNI de exactamente 8 digitos numericos
        if (dni && !/^\d{8}$/.test(dni)) {
            alert("⚠️ Por favor ingresa un DNI válido de exactamente 8 dígitos numéricos.");
            if (this.dni) this.dni.focus();
            return;
        }

        // // [VALIDACION FORMULARIO]: Telefono de exactamente 9 digitos numericos
        const telLimpio = telefono.replace(/\s+/g, "");
        if (!/^\d{9}$/.test(telLimpio)) {
            alert("⚠️ Por favor ingresa un número de teléfono / celular válido de exactamente 9 dígitos.");
            if (this.telefono) this.telefono.focus();
            return;
        }

        // 3. Confirmación de envío exitoso y reseteo de campos
        alert("¡Mensaje enviado con éxito! Un asesor de Tienda Kixa se pondrá en contacto contigo muy pronto.");
        contactForm.reset();
    });
});
