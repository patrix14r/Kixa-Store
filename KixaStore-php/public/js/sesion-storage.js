// ==========================================================================
// TIENDA KIXA - MEMORIA DE CARRITO Y FAVORITOS POR USUARIO (SESION-STORAGE.JS)
// Extraído de <head> en resources/views/layouts/app.blade.php para mantener el layout limpio.
// Corre en <head> antes de pintar la página usando window.KX_SESION que envía Blade.
// ==========================================================================

(() => {
  try {
    const sesion = window.KX_SESION || {};
    const actual = sesion.actual || 'invitado';
    const previo = localStorage.getItem('kixa_usuario');
    const claves = ['carrito', 'favoritos'];
    const guardar = (quien) => claves.forEach((k) => localStorage.setItem(`kixa:${quien}:${k}`, localStorage.getItem(k) || '[]'));

    if (previo && previo !== actual) {
      guardar(previo);
      claves.forEach((k) => {
        const propio = JSON.parse(localStorage.getItem(`kixa:${actual}:${k}`) || '[]');
        const deInvitado = previo === 'invitado' ? JSON.parse(localStorage.getItem(k) || '[]') : [];
        // Al iniciar sesión, lo que agregó como invitado pasa a su cuenta si estaba vacía
        localStorage.setItem(k, JSON.stringify(propio.length ? propio : deInvitado));
      });
      // Lo del invitado ya pasó a la cuenta: se vacía su copia para que no reaparezca al salir
      if (previo === 'invitado') localStorage.setItem('kixa:invitado:carrito', '[]');
    }

    // Tras una compra confirmada, Laravel marca carrito_vaciado y se limpia el carrito activo y el de la cuenta actual
    if (sesion.carritoVaciado) {
      localStorage.setItem('carrito', '[]');
      localStorage.setItem(`kixa:${actual}:carrito`, '[]');
    }

    localStorage.setItem('kixa_usuario', actual);
    window.addEventListener('pagehide', () => guardar(actual));
  } catch (e) {
    /* almacenamiento bloqueado: la tienda sigue funcionando sin memoria */
  }
})();
