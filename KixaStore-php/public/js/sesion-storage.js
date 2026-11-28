// Carrito y favoritos por usuario

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
        // Pasa el carrito de invitado a la cuenta
        localStorage.setItem(k, JSON.stringify(propio.length ? propio : deInvitado));
      });
      if (previo === 'invitado') localStorage.setItem('kixa:invitado:carrito', '[]');
    }

    // Limpia el carrito tras comprar
    if (sesion.carritoVaciado) {
      localStorage.setItem('carrito', '[]');
      localStorage.setItem(`kixa:${actual}:carrito`, '[]');
    }

    localStorage.setItem('kixa_usuario', actual);
    window.addEventListener('pagehide', () => guardar(actual));
  } catch (e) {
    // Sin localStorage la tienda sigue funcionando
  }
})();
