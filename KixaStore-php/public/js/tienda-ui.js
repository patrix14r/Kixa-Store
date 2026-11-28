
// Actualiza contadores de favoritos y carrito
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

// Header de vidrio al hacer scroll
(() => {
  const navbar = document.getElementById('navbar-principal');
  if (!navbar || !navbar.classList.contains('navbar--glass')) return;
  const alScroll = () => navbar.classList.toggle('navbar--scroll', window.scrollY > 40);
  alScroll();
  window.addEventListener('scroll', alScroll, { passive: true });
})();

const corazonLleno = '<i class="fa-solid fa-heart text-[#ed001b]"></i>';
// Pinta los corazones de favoritos
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

// Agrega o quita favoritos desde tarjetas
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

// Sincroniza contadores entre pestañas
window.addEventListener('storage', actualizarBadgesHeader);

// Campos que solo aceptan dígitos
document.addEventListener('input', (e) => {
  if (e.target.matches?.('[data-solo-digitos]')) e.target.value = e.target.value.replace(/\D/g, '');
});
