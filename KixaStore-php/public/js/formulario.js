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
        const telefono = this.telefono ? this.telefono.value.trim() : "";
        const direccion = this.direccion ? this.direccion.value.trim() : "";
        const mensaje = this.mensaje ? this.mensaje.value.trim() : "";

        // 1. Validar que no existan campos vacíos
        if (!nombre || !telefono || !direccion || !mensaje) {
            alert("⚠️ Por favor completa todos los campos del formulario.");
            return;
        }

        // 2. Validar formato de teléfono (de 7 a 15 dígitos con prefijo opcional)
        if (!/^\+?\d{7,15}$/.test(telefono.replace(/\s+/g, ""))) {
            alert("⚠️ Por favor ingresa un número de teléfono o celular válido.");
            if (this.telefono) this.telefono.focus();
            return;
        }

        // 3. Confirmación de envío exitoso y reseteo de campos
        alert("¡Mensaje enviado con éxito! Un asesor de Tienda Kixa se pondrá en contacto contigo muy pronto.");
        contactForm.reset();
    });
});
