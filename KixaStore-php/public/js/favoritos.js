// ==========================================================================
// TIENDA KIXA - FAVORITOS EN FILAS ESTILO PUMA (FAVORITOS.JS)
// [Actualizado: Favoritos] Foto | nombre y categoría | precio + SELECCIONAR TALLA + ELIMINAR
// Se usa en /favoritos y en la pestaña /perfil/favoritos (contenedor #favoritos-filas)
// ==========================================================================

(() => {
    const caja = document.getElementById("favoritos-filas");
    if (!caja) return;

    const leer = (k) => { try { return JSON.parse(localStorage.getItem(k)) || []; } catch { return []; } };
    const soles = (n) => `S/ ${Number(n || 0).toFixed(2)}`;
    const esc = (t) => String(t ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
    const refrescarBadges = () => { if (typeof actualizarBadgesHeader === "function") actualizarBadgesHeader(); };

    function pintar() {
        const favs = leer("favoritos");
        if (favs.length === 0) {
            caja.innerHTML = `
                <div class="kx-fav-vacio">
                    <p>No tienes favoritos guardados. Toca el ♡ en cualquier zapatilla para verla aquí.</p>
                    <a href="/catalogo" class="kx-btn">Explorar catálogo</a>
                </div>`;
            return;
        }

        caja.innerHTML = favs.map((f, i) => `
            <article class="kx-fav-fila" data-indice="${i}">
                <a href="/producto/${encodeURIComponent(f.sku || f.id)}" class="kx-fav-img"><img src="${esc(f.imagen)}" alt="${esc(f.nombre)}"></a>
                <div class="kx-fav-info">
                    <h3><a href="/producto/${encodeURIComponent(f.sku || f.id)}">${esc(f.nombre)}</a></h3>
                    <p>${esc(f.tipo || f.marca || "KixaStore")}</p>
                    <div class="kx-fav-tallas" hidden></div>
                    <p class="kx-fav-mensaje" role="status"></p>
                </div>
                <div class="kx-fav-acciones">
                    <strong>${soles(f.precio)}</strong>
                    <button type="button" class="kx-btn" data-accion="talla">SELECCIONAR TALLA</button>
                    <button type="button" class="kx-btn kx-btn--borde" data-accion="eliminar">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                        ELIMINAR
                    </button>
                </div>
            </article>`).join("");
    }

    async function abrirTallas(fila, fav) {
        const zona = fila.querySelector(".kx-fav-tallas");
        const mensaje = fila.querySelector(".kx-fav-mensaje");
        if (!zona.hidden) { zona.hidden = true; return; }

        zona.hidden = false;
        zona.innerHTML = "<span>Cargando tallas…</span>";
        try {
            const resp = await fetch(`/producto/${encodeURIComponent(fav.sku || fav.id)}/tallas`, { headers: { Accept: "application/json" } });
            if (!resp.ok) throw new Error();
            const datos = await resp.json();
            if (!datos.tallas.length) { zona.innerHTML = "<span>Agotado en todas las tallas</span>"; return; }

            zona.innerHTML = datos.tallas.map((t) => `<button type="button" class="kx-fav-talla" data-talla="${esc(t)}">${esc(t)}</button>`).join("");
            zona.querySelectorAll(".kx-fav-talla").forEach((b) => b.addEventListener("click", () => {
                const carrito = leer("carrito");
                carrito.push({
                    id: datos.sku, sku: datos.sku, nombre: datos.nombre, precio: datos.precio,
                    imagen: fav.imagen, marca: fav.marca, tipo: datos.tipo,
                    talla: b.dataset.talla, tallas: datos.tallas, cantidad: 1,
                });
                localStorage.setItem("carrito", JSON.stringify(carrito));
                refrescarBadges();
                zona.hidden = true;
                mensaje.innerHTML = `Talla ${esc(b.dataset.talla)} agregada al carrito · <a href="/carrito">Ver carrito</a>`;
            }));
        } catch {
            zona.innerHTML = "<span>No se pudieron cargar las tallas.</span>";
        }
    }

    caja.addEventListener("click", (e) => {
        const btn = e.target.closest("button[data-accion]");
        if (!btn) return;
        const fila = btn.closest(".kx-fav-fila");
        const indice = Number(fila.dataset.indice);
        const favs = leer("favoritos");

        if (btn.dataset.accion === "eliminar") {
            favs.splice(indice, 1);
            localStorage.setItem("favoritos", JSON.stringify(favs));
            refrescarBadges();
            pintar();
        } else if (favs[indice]) {
            abrirTallas(fila, favs[indice]);
        }
    });

    pintar();
})();
