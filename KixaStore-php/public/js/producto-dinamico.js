// ==========================================================================
// TIENDA KIXA - FICHA DE PRODUCTO (PRODUCTO-DINAMICO.JS)
// Los datos del producto llegan desde Blade (atributos data-* Que arma CatalogoController@show)
// ==========================================================================

// ==========================================================================
// [BLOQUE]: INTERACTIVIDAD DE FICHA DE PRODUCTO Y AGREGAR AL CARRITO
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
    // [CAMBIO: SE DESACTIVA LA VALIDACION ANTIGUA QUE SOBREESCRIBIA EL HTML CON ERROR ESTATICO]
    // [CAMBIO: LA INFORMACION AHORA SE MUESTRA DIRECTAMENTE DESDE EL MODELO ELOQUENT EN BLADE]

    // [CAMBIO: SELECCION INTERACTIVA DE TALLAS RENDERIZADAS POR BLADE]
    let selectedSize = null;
    let selectedStock = 0;
    const sizeButtons = document.querySelectorAll(".size-btn");
    const avisoEscasez = document.getElementById("aviso-escasez");
    const ultimasUnidades = Number(document.getElementById("sizes")?.dataset.ultimasUnidades) || 3;

    // Marketing de escasez: con 1 a 3 pares en la talla elegida aparece "¡Últimas X unidades disponibles!"
    const pintarEscasez = (stock) => {
        if (!avisoEscasez) return;
        const mostrar = stock >= 1 && stock <= ultimasUnidades;
        avisoEscasez.hidden = !mostrar;
        if (mostrar) {
            avisoEscasez.querySelector("span").textContent = stock === 1 ? "¡Última unidad disponible!" : `¡Últimas ${stock} unidades disponibles!`;
        }
    };

    sizeButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            // [CAMBIO: NO PERMITIR SELECCIONAR TALLAS AGOTADAS O DESHABILITADAS]
            if (btn.disabled || btn.classList.contains("disabled") || btn.classList.contains("agotado")) {
                return;
            }

            sizeButtons.forEach(b => b.classList.remove("selected"));
            btn.classList.add("selected");
            selectedSize = btn.dataset.talla || btn.innerText.trim();
            selectedStock = Number(btn.dataset.stock) || 0;
            pintarEscasez(selectedStock);
        });
    });

    // [CAMBIO: BOTON PARA AGREGAR AL CARRITO CON DATOS REALES DE MYSQL]
    const addBtn = document.getElementById("addBtn");
    const favBtn = document.getElementById("favBtn");
    const toast = document.getElementById("toast");
    const mainImage = document.getElementById("mainImage");
    const nameEl = document.getElementById("productName");
    const priceEl = document.getElementById("productPrice");
    const skuEl = document.getElementById("productSku");

    // Verificar si el producto ya está en favoritos para pintar el corazón
    if (favBtn) {
        const prodId = favBtn.dataset.id || (skuEl ? skuEl.innerText.trim() : "");
        const favoritos = JSON.parse(localStorage.getItem("favoritos")) || [];
        if (favoritos.some(f => f.id == prodId || f.sku == prodId)) {
            favBtn.innerHTML = '<i class="fa-solid fa-heart"></i>';
            favBtn.classList.add("activo");
        }

        favBtn.addEventListener("click", () => {
            let favs = JSON.parse(localStorage.getItem("favoritos")) || [];
            const pId = favBtn.dataset.id || (skuEl ? skuEl.innerText.trim() : "");
            const pSku = favBtn.dataset.sku || (skuEl ? skuEl.innerText.trim() : "");
            const pNombre = favBtn.dataset.nombre || (nameEl ? nameEl.innerText.trim() : "Zapatilla");
            const pPrecio = parseFloat(favBtn.dataset.precio || (priceEl ? priceEl.innerText.replace(/[^0-9.]/g, '') : "0")) || 0;
            const pImagen = favBtn.dataset.imagen || (mainImage ? mainImage.src : "");
            const pMarca = favBtn.dataset.marca || "KIXA";

            const idx = favs.findIndex(f => f.id == pId || f.sku == pSku);
            if (idx >= 0) {
                favs.splice(idx, 1);
                favBtn.innerHTML = '<i class="fa-regular fa-heart"></i>';
                favBtn.classList.remove("activo");
            } else {
                favs.push({
                    id: pId,
                    sku: pSku,
                    nombre: pNombre,
                    precio: pPrecio,
                    imagen: pImagen,
                    marca: pMarca
                });
                favBtn.innerHTML = '<i class="fa-solid fa-heart"></i>';
                favBtn.classList.add("activo");
            }

            localStorage.setItem("favoritos", JSON.stringify(favs));
            if (typeof actualizarBadgesHeader === "function") {
                actualizarBadgesHeader();
            }
        });
    }

    if (addBtn) {
        addBtn.addEventListener("click", () => {
            // [CAMBIO: VALIDACION DE TALLA SELECCIONADA ANTES DE AGREGAR AL CARRITO]
            if (!selectedSize) {
                // Aviso flotante + sacudida suave (animate-sacudir del tema) y borde rojo (ring) en el bloque de tallas
                if (typeof kxToast === "function") kxToast("Selecciona una talla disponible antes de agregar al carrito.", "error");
                const bloqueTallas = document.getElementById("sizes");
                if (bloqueTallas) {
                    const alerta = ["ring-2", "ring-agotado/45"];
                    bloqueTallas.classList.remove("animate-sacudir");
                    void bloqueTallas.offsetWidth;
                    bloqueTallas.classList.add("animate-sacudir", ...alerta);
                    setTimeout(() => bloqueTallas.classList.remove(...alerta), 1600);
                }
                return;
            }

            // Extraer datos con respaldo robusto
            const rawPrice = addBtn.dataset.precio || (priceEl ? priceEl.innerText.replace(/[^0-9.]/g, '') : "0");
            const pId = addBtn.dataset.id || (skuEl ? skuEl.innerText.trim() : "PROD");
            const pSku = addBtn.dataset.sku || (skuEl ? skuEl.innerText.trim() : "");
            const pNombre = addBtn.dataset.nombre || (nameEl ? nameEl.innerText.trim() : "Zapatilla Kixa");
            const pImagen = addBtn.dataset.imagen || (mainImage ? mainImage.src : "");

            const item = {
                id: pId,
                sku: pSku,
                nombre: pNombre,
                precio: parseFloat(rawPrice) || 0,
                talla: selectedSize,
                imagen: pImagen,
                cantidad: 1,
                // Datos extra para el selector de talla y la línea de categoría
                marca: addBtn.dataset.marca || "",
                tipo: addBtn.dataset.tipo || "",
                tallas: [...document.querySelectorAll(".size-btn:not([disabled])")].map((b) => b.dataset.talla)
            };

            const carrito = JSON.parse(localStorage.getItem("carrito")) || [];
            // Si ya existe el mismo producto en la misma talla, se suma la cantidad (máx. 10 y nunca más que el stock real) en vez de crear otra línea
            const tope = Math.min(10, selectedStock || 10);
            const existente = carrito.find((p) => String(p.sku || p.id) === String(pSku || pId) && String(p.talla) === String(selectedSize));
            if (existente && (Number(existente.cantidad) || 1) >= tope) {
                if (typeof kxToast === "function") kxToast(`Ya tienes en tu carrito todas las unidades disponibles de la talla ${selectedSize}.`, "info");
                return;
            }
            if (existente) {
                existente.cantidad = Math.min(tope, (Number(existente.cantidad) || 1) + 1);
            } else {
                carrito.push(item);
            }
            localStorage.setItem("carrito", JSON.stringify(carrito));

            // [CAMBIO: ACTUALIZAR BADGE DE CONTADOR DEL CARRITO EN EL HEADER]
            if (typeof actualizarBadgesHeader === "function") {
                actualizarBadgesHeader();
            }

            // Confirmación flotante con el resumen del carrito
            mostrarModalCarrito(item, addBtn.dataset.tipo || "", carrito);
        });
    }
});

// Rellena y abre el modal con blur; se cierra con la X, el fondo, "Seguir comprando" o Escape
function mostrarModalCarrito(item, tipo, carrito) {
    const modal = document.getElementById("modalCarrito");
    if (!modal) return;
    // Los avisos de error previos (p. ej. "Selecciona una talla") no deben quedar encima del modal
    if (typeof kxToastLimpiar === "function") kxToastLimpiar("error");

    const soles = (n) => `S/ ${Number(n).toFixed(2)}`;
    const unidades = carrito.reduce((t, p) => t + (Number(p.cantidad) || 1), 0);
    const subtotal = carrito.reduce((t, p) => t + (Number(p.precio) || 0) * (Number(p.cantidad) || 1), 0);

    document.getElementById("modalImg").src = item.imagen;
    document.getElementById("modalImg").alt = item.nombre;
    document.getElementById("modalNombre").textContent = item.nombre;
    document.getElementById("modalTipo").textContent = tipo;
    document.getElementById("modalTalla").textContent = item.talla;
    document.getElementById("modalCantidad").textContent = item.cantidad;
    document.getElementById("modalPrecio").textContent = soles(item.precio);
    document.getElementById("modalArticulos").textContent = `${unidades} ${unidades === 1 ? "artículo" : "artículos"}`;
    document.getElementById("modalSubtotal").textContent = soles(subtotal);

    const cerrar = () => {
        modal.classList.remove("abierto");
        setTimeout(() => { modal.hidden = true; }, 250);
        document.removeEventListener("keydown", alEscape);
    };
    const alEscape = (e) => { if (e.key === "Escape") cerrar(); };

    modal.querySelectorAll("[data-cerrar-modal]").forEach((el) => { el.onclick = cerrar; });
    document.addEventListener("keydown", alEscape);

    modal.hidden = false;
    requestAnimationFrame(() => modal.classList.add("abierto"));
    modal.querySelector("[data-enfocar]")?.focus();
}

// Al hacer clic en una miniatura se muestra como foto principal
document.addEventListener("DOMContentLoaded", () => {
    const thumbs = document.getElementById("thumbs");
    const principal = document.getElementById("mainImage");
    if (!thumbs || !principal) return;
    thumbs.addEventListener("click", (e) => {
        const img = e.target.closest("img");
        if (!img) return;
        principal.src = img.src;
        thumbs.querySelectorAll("img").forEach((t) => t.classList.toggle("active", t === img));
    });
});
