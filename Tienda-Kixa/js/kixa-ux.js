/* ============================================================
   kixa-ux.js  —  Mejoras globales de UX para Tienda Kixa
   Vanilla ES6+ · Sin dependencias
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ─── 1. STICKY HEADER — backdrop blur on scroll ─── */
  const navbar = document.querySelector('.navbar');
  if (navbar) {
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
      const y = window.scrollY;
      if (y > 40) {
        navbar.classList.add('navbar--scrolled');
      } else {
        navbar.classList.remove('navbar--scrolled');
      }
      lastScroll = y;
    }, { passive: true });
  }

  /* ─── 2. SCROLL-TO-TOP BUTTON ─── */
  const scrollBtn = document.createElement('button');
  scrollBtn.id = 'scrollTopBtn';
  scrollBtn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>';
  scrollBtn.setAttribute('aria-label', 'Volver arriba');
  document.body.appendChild(scrollBtn);

  window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
      scrollBtn.classList.add('visible');
    } else {
      scrollBtn.classList.remove('visible');
    }
  }, { passive: true });

  scrollBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  /* ─── 3. SEARCH BAR — live filtering + focus expansion ─── */
  const searchInput = document.querySelector('.search-bar input');
  if (searchInput) {
    // Create results dropdown
    const dropdown = document.createElement('div');
    dropdown.className = 'search-dropdown';
    const searchBarContainer = searchInput.closest('.search-bar');
    searchBarContainer.appendChild(dropdown);
    searchBarContainer.style.position = 'relative';

    // Clear Button (X)
    const clearBtn = document.createElement('button');
    clearBtn.innerHTML = '×';
    clearBtn.className = 'search-clear-btn';
    clearBtn.style.display = 'none';
    searchBarContainer.appendChild(clearBtn);

    // Catalog pages data (name -> url)
    const catalogPages = [
      { name: 'Mujer', url: 'mujer.html' },
      { name: 'Hombre', url: 'hombre.html' },
      { name: 'Niños', url: 'niños.html' },
      { name: 'Deporte', url: 'deporte.html' },
      { name: 'Carrito', url: 'carrito.html' },
      { name: 'Ayuda', url: 'ayuda.html' },
      { name: 'Contacto', url: 'formulario.html' },
    ];

    // Get product cards on current page
    function getProductCards() {
      return document.querySelectorAll('.producto');
    }

    const performSearch = () => {
      const query = searchInput.value.trim().toLowerCase();
      dropdown.innerHTML = '';
      
      if (!query) {
        dropdown.classList.remove('active');
        clearBtn.style.display = 'none';
        // Reset card visibility
        getProductCards().forEach(card => {
          card.style.display = '';
          card.classList.remove('search-highlight');
        });
        
        // Remove "no results" message if exists
        const noResultsMsg = document.querySelector('.no-results-msg');
        if (noResultsMsg) noResultsMsg.remove();
        
        return;
      }
      
      clearBtn.style.display = 'block';

      // Filter product cards on current page
      const cards = getProductCards();
      let visibleCards = 0;
      
      if (cards.length > 0) {
        cards.forEach(card => {
          const name = card.querySelector('h3')?.textContent.toLowerCase() || '';
          const price = card.querySelector('.precio')?.textContent.toLowerCase() || '';
          const matches = name.includes(query) || price.includes(query);
          card.style.display = matches ? '' : 'none';
          card.classList.toggle('search-highlight', matches && query.length > 1);
          if (matches) visibleCards++;
        });
        
        // Handle "No results" message for cards
        let noResultsMsg = document.querySelector('.no-results-msg');
        if (visibleCards === 0) {
          if (!noResultsMsg) {
            noResultsMsg = document.createElement('p');
            noResultsMsg.className = 'no-results-msg subtitulo';
            noResultsMsg.style.textAlign = 'center';
            noResultsMsg.style.width = '100%';
            noResultsMsg.style.marginTop = '2rem';
            noResultsMsg.textContent = 'No se encontraron zapatillas que coincidan con tu búsqueda.';
            const grid = document.querySelector('.grid-productos');
            if(grid) grid.parentElement.insertBefore(noResultsMsg, grid);
          }
        } else {
          if (noResultsMsg) noResultsMsg.remove();
        }
      }

      // Show matching pages in dropdown
      const matchingPages = catalogPages.filter(p => p.name.toLowerCase().includes(query));
      if (matchingPages.length > 0 || query.length >= 2) {
        matchingPages.forEach(page => {
          const item = document.createElement('a');
          item.href = page.url;
          item.className = 'search-dropdown-item';
          item.innerHTML = `<i class="fa fa-arrow-right" style="font-size:12px;opacity:.5"></i> ${page.name}`;
          dropdown.appendChild(item);
        });
        
        if (matchingPages.length === 0 && visibleCards === 0) {
           const item = document.createElement('div');
           item.className = 'search-dropdown-item';
           item.style.color = '#777';
           item.innerHTML = `Sin resultados`;
           dropdown.appendChild(item);
        }
        
        dropdown.classList.add('active');
      }

      if (dropdown.children.length === 0) {
        dropdown.classList.remove('active');
      }
    };

    searchInput.addEventListener('input', performSearch);
    
    // Clear search functionality
    const clearSearch = () => {
      searchInput.value = '';
      performSearch();
      searchInput.focus();
    };

    clearBtn.addEventListener('click', (e) => {
      e.preventDefault();
      clearSearch();
    });

    // Close dropdown on outside click
    document.addEventListener('click', (e) => {
      if (!searchInput.closest('.search-bar').contains(e.target)) {
        dropdown.classList.remove('active');
      }
    });

    // Handle Escape and Enter keys
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        const first = dropdown.querySelector('a.search-dropdown-item');
        if (first) window.location.href = first.href;
      } else if (e.key === 'Escape') {
        clearSearch();
      }
    });
  }

  /* ─── 4. PRODUCT CARD IMAGE ZOOM ON HOVER ─── */
  // The CSS handles the hover effects; JS adds the overflow:hidden wrapper
  document.querySelectorAll('.producto img').forEach(img => {
    img.style.transition = 'transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
  });

  /* ─── 5. CART COUNTER BADGE ─── */
  function updateCartBadge() {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    const count = carrito.length;

    document.querySelectorAll('.icons a[href="carrito.html"]').forEach(cartLink => {
      let badge = cartLink.querySelector('.cart-badge');
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'cart-badge';
        cartLink.style.position = 'relative';
        cartLink.appendChild(badge);
      }
      badge.textContent = count;
      badge.style.display = count > 0 ? 'flex' : 'none';
    });
  }
  updateCartBadge();

  // Listen for storage changes (when cart updates in another tab or same page)
  window.addEventListener('storage', updateCartBadge);

  // Patch localStorage.setItem to detect cart changes on same page
  const originalSetItem = localStorage.setItem.bind(localStorage);
  localStorage.setItem = function(key, value) {
    originalSetItem(key, value);
    if (key === 'carrito') {
      updateCartBadge();
      // Pop animation on badge
      document.querySelectorAll('.cart-badge').forEach(b => {
        b.classList.remove('pop');
        void b.offsetWidth; // Force reflow
        b.classList.add('pop');
      });
    }
  };

});


document.addEventListener("DOMContentLoaded", () => {
  const filterUso = document.getElementById("filter-uso");
  const filterBrand = document.getElementById("filter-brand");
  const btnLimpiar = document.getElementById("btn-limpiar");
  const productos = document.querySelectorAll(".producto");

  if (!filterUso || !filterBrand || !btnLimpiar || productos.length === 0) {
    return;
  }

  // Inject data attributes based on title and image
  productos.forEach((producto) => {
    const title = (producto.querySelector("h3")?.textContent || "").toLowerCase();
    
    // Assign Brand
    let brand = "otra";
    if (title.includes("nike") || title.includes("air max") || title.includes("force")) brand = "nike";
    else if (title.includes("adidas") || title.includes("campus") || title.includes("tensaur")) brand = "adidas";
    else if (title.includes("puma")) brand = "puma";
    else if (title.includes("jordan")) brand = "jordan";
    else if (title.includes("umbro")) brand = "umbro";
    
    if (!producto.hasAttribute("data-brand")) {
      producto.setAttribute("data-brand", brand);
    }

    // Assign Uso
    let uso = "urbano"; // default
    if (title.includes("tiempo") || title.includes("gato") || title.includes("phantom") || title.includes("mercurial") || title.includes("tf") || title.includes("fg") || title.includes("umbro")) uso = "futbol";
    else if (title.includes("running") || title.includes("wearallday")) uso = "running";
    else if (title.includes("jordan") && !title.includes("retro 1")) uso = "basquet";
    
    if (!producto.hasAttribute("data-uso")) {
      producto.setAttribute("data-uso", uso);
    }
  });

  const filterProductos = () => {
    const usoValue = filterUso.value;
    const brandValue = filterBrand.value;

    productos.forEach(producto => {
      const pUso = producto.getAttribute("data-uso");
      const pBrand = producto.getAttribute("data-brand");
      
      const matchUso = (usoValue === "todas" || !usoValue) ? true : (pUso === usoValue);
      const matchBrand = (brandValue === "todas" || !brandValue) ? true : (pBrand === brandValue);

      if (matchUso && matchBrand) {
        producto.style.display = "";
        setTimeout(() => producto.style.opacity = "1", 10);
      } else {
        producto.style.opacity = "0";
        setTimeout(() => producto.style.display = "none", 300);
      }
    });
  };

  filterUso.addEventListener("change", filterProductos);
  filterBrand.addEventListener("change", filterProductos);

  btnLimpiar.addEventListener("click", () => {
    filterUso.value = "todas";
    filterBrand.value = "todas";
    filterProductos();
  });
});
