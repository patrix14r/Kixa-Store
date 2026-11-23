// ==========================================================================
// TIENDA KIXA - GESTIÓN DEL CARRITO DE COMPRAS Y PAGOS (CARRITO.JS)
// Renderizado dinámico de productos, cálculo de totales y pasarela modal
// ==========================================================================

// Se descartan ítems corruptos y cantidades <= 0, decimales o exageradas
const cantidadValida = (n) => Math.min(10, Math.max(1, Math.floor(Number(n)) || 1));
let carrito = (JSON.parse(localStorage.getItem("carrito")) || [])
    .filter((p) => p && (p.sku || p.id) && Number(p.precio) > 0)
    .map((p) => ({ ...p, cantidad: cantidadValida(p.cantidad) }));

const contenedor = document.getElementById("carrito");
const subtotalBox = document.getElementById("subtotal");
const totalBox = document.getElementById("total");
const cantidadProductos = document.getElementById("cantidad-productos");
const modal = document.getElementById("modal");
const contenidoPago = document.getElementById("contenido-pago");
const alerta = document.getElementById("alerta");
const mensajeAlerta = document.getElementById("mensaje-alerta");

// --------------------------------------------------------------------------
// Render de ítems, totales con IGV y favoritos
// --------------------------------------------------------------------------
// Sin alert() nativos: todo aviso va al toast de la esquina
const avisar = (mensaje, tipo = "error") => (typeof kxToast === "function" ? kxToast(mensaje, tipo) : console.warn(mensaje));
const soles = (n) => `S/ ${Number(n || 0).toFixed(2)}`;
const escapar = (t) => String(t ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));

function guardarCarrito() {
    localStorage.setItem("carrito", JSON.stringify(carrito));
    if (typeof actualizarBadgesHeader === "function") actualizarBadgesHeader();
}

function totalCarrito() {
    return carrito.reduce((t, p) => t + (Number(p.precio) || 0) * (Number(p.cantidad) || 1), 0);
}

function pintarTotales() {
    const total = totalCarrito();
    const igv = total * 0.18 / 1.18;
    if (subtotalBox) subtotalBox.textContent = soles(total);
    if (totalBox) totalBox.textContent = soles(total);
    const igvBox = document.getElementById("igv");
    if (igvBox) igvBox.textContent = soles(igv);

    const barra = document.getElementById("envio-progreso");
    const envio = document.getElementById("envio-gratis");
    if (envio) envio.hidden = carrito.length === 0;
    if (barra) barra.style.width = carrito.length ? "100%" : "0";

    const btn = document.getElementById("btn-comprar");
    if (btn) btn.classList.toggle("deshabilitado", carrito.length === 0);
}

function opcionesTalla(p) {
    const tallas = Array.isArray(p.tallas) && p.tallas.length ? p.tallas : [p.talla];
    return tallas.map((t) => `<option value="${escapar(t)}" ${String(t) === String(p.talla) ? "selected" : ""}>${escapar(t)}</option>`).join("");
}

function opcionesCantidad(p) {
    const actual = Number(p.cantidad) || 1;
    return Array.from({ length: 10 }, (_, i) => i + 1)
        .map((n) => `<option value="${n}" ${n === actual ? "selected" : ""}>${n}</option>`).join("");
}

function mostrarCarrito() {
    if (!contenedor) return;

    if (carrito.length === 0) {
        if (cantidadProductos) cantidadProductos.textContent = "";
        contenedor.innerHTML = `
            <div class="kx-carrito-vacio">
                <h2>Tu carrito está vacío</h2>
                <p>Agrega tus zapatillas favoritas y aparecerán aquí.</p>
                <a href="/catalogo" class="kx-btn-comprar">Explorar catálogo</a>
            </div>`;
        pintarTotales();
        return;
    }

    const unidades = carrito.reduce((t, p) => t + (Number(p.cantidad) || 1), 0);
    if (cantidadProductos) cantidadProductos.textContent = `${unidades} ${unidades === 1 ? "artículo" : "artículos"}`;

    contenedor.innerHTML = carrito.map((p, i) => `
        <article class="kx-item">
            <a href="/producto/${encodeURIComponent(p.sku || p.id)}" class="kx-item-img">
                <img src="${escapar(p.imagen)}" alt="${escapar(p.nombre)}">
            </a>
            <div class="kx-item-info">
                <div class="kx-item-fila">
                    <h3>${escapar(p.nombre)}</h3>
                    <strong class="kx-item-precio">${soles((Number(p.precio) || 0) * (Number(p.cantidad) || 1))}</strong>
                </div>
                <p class="kx-item-tipo">${escapar(p.tipo || p.marca || "Calzado KixaStore")}</p>
                <div class="kx-item-selectores">
                    <label>Talla
                        <select onchange="cambiarTalla(${i}, this.value)" aria-label="Talla de ${escapar(p.nombre)}">${opcionesTalla(p)}</select>
                    </label>
                    <label>Cantidad
                        <select onchange="cambiarCantidad(${i}, this.value)" aria-label="Cantidad de ${escapar(p.nombre)}">${opcionesCantidad(p)}</select>
                    </label>
                </div>
                <div class="kx-item-acciones">
                    <button type="button" onclick="moverAFavoritos(${i})" title="Mover a Favoritos" aria-label="Mover a Favoritos">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                    </button>
                    <button type="button" onclick="eliminar(${i})" title="Eliminar" aria-label="Eliminar del carrito">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                    </button>
                </div>
            </div>
        </article>`).join("");

    pintarTotales();
}

function cambiarTalla(i, talla) {
    if (!carrito[i]) return;
    carrito[i].talla = talla;
    guardarCarrito();
}

function cambiarCantidad(i, cantidad) {
    if (!carrito[i]) return;
    carrito[i].cantidad = cantidadValida(cantidad);
    guardarCarrito();
    mostrarCarrito();
}

function eliminar(i) {
    carrito.splice(i, 1);
    guardarCarrito();
    mostrarCarrito();
}

function moverAFavoritos(i) {
    const p = carrito[i];
    if (!p) return;
    const favs = JSON.parse(localStorage.getItem("favoritos")) || [];
    if (!favs.some((f) => f.sku == p.sku || f.id == p.id)) {
        favs.push({ id: p.id, sku: p.sku, nombre: p.nombre, marca: p.marca, precio: p.precio, imagen: p.imagen, tipo: p.tipo });
        localStorage.setItem("favoritos", JSON.stringify(favs));
    }
    eliminar(i);
    renderFavoritos("favoritos-carrito", 4);
}

// Tarjetas de favoritos con el mismo estilo Nike (.kx-card); se usan en el carrito y en /favoritos
function renderFavoritos(idContenedor, limite = null) {
    const caja = document.getElementById(idContenedor);
    if (!caja) return;

    const favs = JSON.parse(localStorage.getItem("favoritos")) || [];
    if (favs.length === 0) {
        caja.innerHTML = `<p class="kx-favoritos-vacio">Aún no guardas favoritos. Toca el ♡ en cualquier zapatilla para verla aquí.</p>`;
        return;
    }

    const lista = limite ? favs.slice(0, limite) : favs;
    caja.innerHTML = `<div class="kx-favoritos-grid">${lista.map((f, i) => `
        <article class="kx-card">
            <a href="/producto/${encodeURIComponent(f.sku || f.id)}" class="kx-card-enlace">
                <div class="kx-card-img"><img src="${escapar(f.imagen)}" alt="${escapar(f.nombre)}" loading="lazy"></div>
                <div class="kx-card-info">
                    <h3 class="kx-card-nombre">${escapar(f.nombre)}</h3>
                    <p class="kx-card-tipo">${escapar(f.tipo || f.marca || "KixaStore")}</p>
                    <div class="kx-card-precio"><span>${soles(f.precio)}</span></div>
                </div>
            </a>
            <button type="button" class="kx-fav-quitar" onclick="quitarFavorito(${i}, '${idContenedor}', ${limite || "null"})" aria-label="Quitar de favoritos" title="Quitar de favoritos">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>
            </button>
        </article>`).join("")}</div>`;
}

function quitarFavorito(i, idContenedor, limite) {
    const favs = JSON.parse(localStorage.getItem("favoritos")) || [];
    favs.splice(i, 1);
    localStorage.setItem("favoritos", JSON.stringify(favs));
    if (typeof actualizarBadgesHeader === "function") actualizarBadgesHeader();
    renderFavoritos(idContenedor, limite);
}

// --------------------------------------------------------------------------
// [CAMBIO]: Apertura y validación previa del modal de Checkout
// --------------------------------------------------------------------------
function abrirModal() {
    if (carrito.length === 0) {
        avisar("Tu carrito está vacío. Agrega productos antes de continuar.");
        return;
    }

    const sesion = localStorage.getItem("usuarioLogueado");
    if (!sesion) {
        avisar("Debes iniciar sesión antes de realizar la compra.");
        window.location.href = "/login";
        return;
    }

    if (modal) {
        modal.style.display = "flex";
        if (contenidoPago) contenidoPago.innerHTML = "";
    }
}

function cerrarModal() {
    if (modal) modal.style.display = "none";
}

// Cerrar al hacer clic fuera del contenido del modal
window.addEventListener("click", function(event) {
    if (event.target === modal) {
        cerrarModal();
    }
});

// --------------------------------------------------------------------------
// [CAMBIO]: Opciones de métodos de pago dentro del modal
// --------------------------------------------------------------------------
function mostrarContraentrega() {
    if (!contenidoPago) return;
    contenidoPago.innerHTML = `
        <div class="pago-form">
            <h3>
                <i class="fa-solid fa-box"></i>
                Pago Contraentrega
            </h3>
            <p>Pagarás en efectivo o POS al momento de recibir tu pedido en tu domicilio.</p>
            <button class="btn-confirmar" onclick="confirmar('Contraentrega')" type="button">
                Confirmar pedido
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    `;
}

function mostrarTarjeta() {
    if (!contenidoPago) return;
    contenidoPago.innerHTML = `
        <div class="pago-form">
            <h3>
                <i class="fa-solid fa-credit-card"></i>
                Pago con Tarjeta
            </h3>
            <label>Número de tarjeta</label>
            <input type="text" placeholder="0000 0000 0000 0000" maxlength="19">

            <div class="fila">
                <div>
                    <label>MM/AA</label>
                    <input type="text" placeholder="05/28" maxlength="5">
                </div>
                <div>
                    <label>CVV</label>
                    <input type="password" placeholder="***" maxlength="4">
                </div>
            </div>

            <button class="btn-confirmar" onclick="confirmar('Tarjeta')" type="button">
                Pagar ahora
                <i class="fa-solid fa-lock"></i>
            </button>
        </div>
    `;
}

function mostrarYape() {
    if (!contenidoPago) return;
    contenidoPago.innerHTML = `
        <div class="pago-form yape-form">
            <h3>
                <i class="fa-solid fa-mobile-screen-button"></i>
                Pago con Yape
            </h3>
            <p>Escanea el código QR desde tu app Yape para completar el pago.</p>
            <img src="/images/payments/yape.jpg" class="qr" alt="Código QR de Yape">
            <div class="numero-yape">
                <i class="fa-solid fa-phone"></i>
                916 934 205
            </div>
            <button class="btn-confirmar" onclick="confirmar('Yape')" type="button">
                He realizado el pago
                <i class="fa-solid fa-check"></i>
            </button>
        </div>
    `;
}

// --------------------------------------------------------------------------
// [CAMBIO]: Confirmación del pedido y generación de código de seguimiento
// --------------------------------------------------------------------------
// [CAMBIO: CONFIRMACION DE COMPRA CONECTADA CON CHECKOUT.PROCESAR EN MYSQL]
function confirmar(metodo) {
    if (!carrito || carrito.length === 0) {
        avisar("El carrito está vacío.");
        return;
    }

    // // [VALIDACION FORMULARIO]: Validacion estricta de DNI (8 digitos) y Celular (9 digitos)
    const dniInput = document.getElementById("checkoutDni") || document.querySelector("input[name='dni']");
    const telInput = document.getElementById("checkoutTelefono") || document.querySelector("input[name='telefono']");

    if (dniInput && dniInput.value.trim()) {
        if (!/^\d{8}$/.test(dniInput.value.trim())) {
            avisar("El DNI ingresado debe tener exactamente 8 dígitos numéricos.");
            dniInput.focus();
            return;
        }
    }

    if (telInput && telInput.value.trim()) {
        if (!/^\d{9}$/.test(telInput.value.trim())) {
            avisar("El teléfono / celular ingresado debe tener exactamente 9 dígitos numéricos.");
            telInput.focus();
            return;
        }
    }

    // La dirección de entrega es obligatoria
    const direccionInput = document.getElementById("checkoutDireccion");
    const direccion = direccionInput ? direccionInput.value.trim() : "";
    if (direccionInput && direccion.length < 5) {
        avisar("Ingresa tu dirección de entrega.");
        direccionInput.focus();
        return;
    }

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const token = csrfMeta ? csrfMeta.getAttribute("content") : "";

    fetch("/checkout/procesar", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": token,
            "Accept": "application/json"
        },
        body: JSON.stringify({
            metodo_pago: metodo,
            token_compra: document.getElementById("tokenCompra")?.value || "",
            direccion_envio: direccion,
            dni: dniInput ? dniInput.value.trim() : null,
            telefono: telInput ? telInput.value.trim() : null,
            items: carrito.map((p) => ({ id: p.sku || p.id, talla: p.talla, cantidad: cantidadValida(p.cantidad) }))
        })
    })
    .then(async response => {
        if (response.status === 401) {
            avisar("Debes iniciar sesión para completar tu compra.");
            window.location.href = "/login";
            return;
        }
        const data = await response.json();
        // Compra ya procesada (409): se limpia el carrito y se va al historial
        if (response.status === 409) {
            vaciarCarritoCompleto();
            window.location.href = data.redirect_url || "/perfil/pedidos";
            return;
        }
        if (response.ok && data.exito) {
            cerrarModal();
            if (contenidoPago) contenidoPago.innerHTML = "";
            
            // [CAMBIO: VACIAR CARRITO Y ACTUALIZAR CONTADOR DE HEADER]
            vaciarCarritoCompleto();
            if (typeof actualizarBadgesHeader === "function") {
                actualizarBadgesHeader();
            }

            // [CAMBIO: REDIRECCION DIRECTA A PERFIL CON EL HISTORIAL DE COMPRAS ACTUALIZADO]
            // replace(): el checkout no queda en el historial, así "Atrás" no vuelve a la pantalla de pago
            window.location.replace(data.redirect_url || "/perfil/pedidos");
        } else {
            avisar(data.mensaje || "Ocurrió un error al procesar el pedido.");
        }
    })
    .catch(error => {
        console.error("Error al procesar la compra:", error);
        avisar("No se pudo conectar con el servidor. Tu pedido NO se registró; inténtalo de nuevo.");
    });
}

// Tras comprar se borran el carrito activo y sus copias por usuario (kixa:<usuario>:carrito)
// así cada compra nueva crea un pedido independiente sin arrastrar zapatillas de compras anteriores
function vaciarCarritoCompleto() {
    carrito = [];
    try {
        Object.keys(localStorage)
            .filter((clave) => clave === "carrito" || /^kixa:.+:carrito$/.test(clave))
            .forEach((clave) => localStorage.setItem(clave, "[]"));
    } catch (e) { /* almacenamiento bloqueado */ }
}

function mostrarAlerta(mensaje) {
    if (mensajeAlerta) mensajeAlerta.innerHTML = mensaje;
    if (alerta) alerta.style.display = "flex";

    carrito = [];
    localStorage.setItem("carrito", JSON.stringify([]));
    mostrarCarrito();
}

function cerrarAlerta() {
    if (alerta) alerta.style.display = "none";
    cerrarModal();
}

document.addEventListener("DOMContentLoaded", () => {
    // Checkout sin productos (p. ej. tras comprar y pulsar "Atrás") vuelve al carrito
    if (document.getElementById("tokenCompra") && carrito.length === 0) {
        window.location.replace("/carrito");
        return;
    }
    guardarCarrito();
    mostrarCarrito();
    pintarTotales();
    renderFavoritos("favoritos-carrito", 4);
    renderFavoritos("favoritos-contenido");
});


// Si el navegador restaura la página desde su caché (botón Atrás), se recarga para revalidar con el servidor
window.addEventListener("pageshow", (e) => {
    if (e.persisted && document.getElementById("tokenCompra")) window.location.reload();
});
