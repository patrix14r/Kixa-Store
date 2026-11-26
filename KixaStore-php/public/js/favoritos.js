// ==========================================================================
// TIENDA KIXA - FAVORITOS EN FILAS ESTILO PUMA (FAVORITOS.JS)
// Foto | Nombre y categoría | precio + SELECCIONAR TALLA + ELIMINAR
// Se usa en /favoritos y en la pestaña /perfil/favoritos (contenedor #favoritos-filas)
// ==========================================================================

(() => {
    const caja = document.getElementById("favoritos-filas");
    if (!caja) return;

    const leer = (k) => { try { return JSON.parse(localStorage.getItem(k)) || []; } catch { return []; } };
    const soles = (n) => `S/ ${Number(n || 0).toFixed(2)}`;
    const esc = (t) => String(t ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
    const refrescarBadges = () => { if (typeof actualizarBadgesHeader === "function") actualizarBadgesHeader(); };

    // Botones negros (y versión con borde) con clases de Tailwind
    const BOTON = "inline-flex cursor-pointer items-center justify-center gap-2 rounded-md border px-[22px] py-3 text-[13px] font-bold tracking-[0.04em] transition-colors duration-200";
    const BOTON_NEGRO = `${BOTON} border-tinta bg-tinta text-white hover:bg-[#333]`;
    const BOTON_BORDE = `${BOTON} border-[#cacacb] bg-white text-tinta hover:border-tinta hover:bg-perla`;

    function pintar() {
        const favs = leer("favoritos");
        if (favs.length === 0) {
            caja.innerHTML = `
                <div class="rounded-xl border border-dashed border-[#d4d4d8] p-8 text-center text-suave">
                    <p>No tienes favoritos guardados. Toca el ♡ en cualquier zapatilla para verla aquí.</p>
                    <a href="/catalogo" class="${BOTON_NEGRO} mt-4">Explorar catálogo</a>
                </div>`;
            return;
        }

        caja.innerHTML = favs.map((f, i) => `
            <article class="kx-fav-fila grid max-w-full min-w-0 grid-cols-[120px_minmax(0,1fr)_auto] items-start gap-[22px] rounded-xl border border-[#ececec] bg-white p-4 max-[860px]:grid-cols-[96px_minmax(0,1fr)]" data-indice="${i}">
                <a href="/producto/${encodeURIComponent(f.sku || f.id)}" class="block aspect-square rounded-[10px] bg-perla p-2.5"><img src="${esc(f.imagen)}" alt="${esc(f.nombre)}" class="inline size-full align-baseline object-contain mix-blend-multiply"></a>
                <div>
                    <h3 class="text-[16px] font-semibold"><a href="/producto/${encodeURIComponent(f.sku || f.id)}">${esc(f.nombre)}</a></h3>
                    <p class="mt-1 text-[13px] text-suave">${esc(f.tipo || f.marca || "KixaStore")}</p>
                    <div class="kx-fav-tallas mt-3.5 flex flex-wrap gap-2 text-[13px] text-suave" hidden></div>
                    <p class="kx-fav-mensaje mt-1 text-[13px] text-[#047857]" role="status"></p>
                </div>
                <div class="flex min-w-[190px] flex-col items-stretch gap-2.5 text-right max-[860px]:col-span-full max-[860px]:min-w-0 max-[860px]:text-left">
                    <strong class="text-[16px] font-semibold">${soles(f.precio)}</strong>
                    <button type="button" class="${BOTON_NEGRO}" data-accion="talla">SELECCIONAR TALLA</button>
                    <button type="button" class="${BOTON_BORDE}" data-accion="eliminar">
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

            zona.innerHTML = datos.tallas.map((t) => `<button type="button" class="kx-fav-talla h-[42px] min-w-[46px] cursor-pointer rounded-md border border-[#e5e5e5] bg-white px-2.5 text-[14px] text-tinta hover:border-tinta hover:bg-tinta hover:text-white" data-talla="${esc(t)}">${esc(t)}</button>`).join("");
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
                mensaje.innerHTML = `Talla ${esc(b.dataset.talla)} agregada al carrito · <a href="/carrito" class="underline">Ver carrito</a>`;
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
