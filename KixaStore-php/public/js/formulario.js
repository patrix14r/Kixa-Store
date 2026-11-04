document.getElementById("contactForm").addEventListener("submit", function(e){
    e.preventDefault();

    const nombre = this.nombre.value.trim();
    const telefono = this.telefono.value.trim();
    const direccion = this.direccion.value.trim();
    const mensaje = this.mensaje.value.trim();

    // Validar campos vacíos
    if(!nombre || !telefono || !direccion || !mensaje){
        alert("Por favor completa todos los campos.");
        return;
    }

    // Validar teléfono básico (solo números y +)
    if(!/^\+?\d{7,15}$/.test(telefono)){
        alert("Por favor ingresa un número de teléfono válido.");
        return;
    }

    alert("Formulario enviado correctamente. ¡Gracias por contactarnos!");
    this.reset();
});
