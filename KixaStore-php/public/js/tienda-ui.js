// ==========================================================================
// TIENDA KIXA - UI GLOBAL DEL MODO CLIENTE (TIENDA-UI.JS)
// Extraído de resources/views/layouts/app.blade.php para separar la lógica JS de las vistas Blade.
// Incluye:
//   1. Contadores del header (#cart-count y #fav-count) -> actualizarBadgesHeader()
//   2. Efecto vidrio al hacer scroll en el navbar del home (#navbar-principal)
//   3. Botones de corazón en tarjetas (.btn-fav-card) -> kxPintarFavoritos()
//   4. Campos con data-solo-digitos (DNI y teléfono en registro, perfil y checkout): borran todo lo que no sea número
// ==========================================================================

// Actualiza los globos rojos de favoritos y carrito en el header (también lo llaman carrito.js, favoritos.js y producto-dinamico.js)
function actualizarBadgesHeader() {
  try {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    const favs = JSON.parse(localStorage.getItem('favoritos')) || [];

    const cartBadge = document.getElementById('cart-count');
    if (cartBadge) {
      if (carrito.length > 0) {
        cartBadge.textContent = carrito.length;
        cartBadge.style.display = 'flex';
      } else {
        cartBadge.style.display = 'none';
      }
    }

    const favBadge = document.getElementById('fav-count');
    if (favBadge) {
      if (favs.length > 0) {
        favBadge.textContent = favs.length;
        favBadge.style.display = 'flex';
      } else {
        favBadge.style.display = 'none';
      }
    }
  } catch (e) {
    console.error('Error sincronizando badges:', e);
  }
}

// Header de vidrio del home: al bajar más de 40px adopta el carbón casi opaco
(() => {
  const navbar = document.getElementById('navbar-principal');
  if (!navbar || !navbar.classList.contains('navbar--glass')) return;
  const alScroll = () => navbar.classList.toggle('navbar--scroll', window.scrollY > 40);
  alScroll();
  window.addEventListener('scroll', alScroll, { passive: true });
})();

// Corazón de las tarjetas (.btn-fav-card): se pinta al cargar y tras recargas AJAX (catalogo-filtros.js)
const corazonLleno = '<i class="fa-solid fa-heart text-[#ed001b]"></i>';
function kxPintarFavoritos(raiz = document) {
  const favs = JSON.parse(localStorage.getItem('favoritos')) || [];
  raiz.querySelectorAll('.btn-fav-card').forEach((btn) => {
    if (favs.some((f) => f.id == btn.dataset.id || f.sku == btn.dataset.sku)) {
      btn.innerHTML = corazonLleno;
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  actualizarBadgesHeader();
  kxPintarFavoritos();
});

// Delegación de clics para agregar o quitar favoritos desde cualquier tarjeta de producto
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.btn-fav-card');
  if (!btn) return;
  e.preventDefault();

  let currentFavs = JSON.parse(localStorage.getItem('favoritos')) || [];
  const pId = btn.dataset.id;
  const pSku = btn.dataset.sku || pId;
  const pNombre = btn.dataset.nombre || 'Zapatilla Kixa';
  const pMarca = btn.dataset.marca || 'KIXA';
  const pPrecio = parseFloat(btn.dataset.precio) || 0;
  const pImagen = btn.dataset.imagen || '';

  const idx = currentFavs.findIndex((f) => f.id == pId || f.sku == pSku);
  if (idx >= 0) {
    currentFavs.splice(idx, 1);
    btn.innerHTML = '<i class="fa-regular fa-heart"></i>';
  } else {
    currentFavs.push({
      id: pId,
      sku: pSku,
      nombre: pNombre,
      marca: pMarca,
      precio: pPrecio,
      imagen: pImagen,
    });
    btn.innerHTML = corazonLleno;
  }

  localStorage.setItem('favoritos', JSON.stringify(currentFavs));
  actualizarBadgesHeader();
});

// Mantiene sincronizados los contadores si el cliente abre varias pestañas
window.addEventListener('storage', actualizarBadgesHeader);

// Campos con data-solo-digitos: un solo listener delegado reemplaza los oninput repetidos en registro, perfil y checkout
document.addEventListener('input', (e) => {
  if (e.target.matches?.('[data-solo-digitos]')) e.target.value = e.target.value.replace(/\D/g, '');
});
