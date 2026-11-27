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

// --------------------------------------------------------------------------
// Regla de envío (la misma de Pedido::calcularEnvio): llega en data-regla-envio desde CarritoController
// primera_compra es true/false con sesión y null para un invitado
// --------------------------------------------------------------------------
const reglaEnvio = (() => {
    const base = { costo: 15, gratis_desde: 200, primera_compra: null };
    try {
        return { ...base, ...JSON.parse(document.querySelector("[data-regla-envio]")?.dataset.reglaEnvio || "{}") };
    } catch (e) {
        return base;
    }
})();

// [Sem 08 - Rutas y Vistas Blade]: envío gratis en la primera compra o desde S/ 200; si no, S/ 15 (espejo visual de Pedido::calcularEnvio, el servidor recalcula al pagar).
function costoEnvio(montoProductos) {
    return reglaEnvio.primera_compra === true || montoProductos >= Number(reglaEnvio.gratis_desde) ? 0 : Number(reglaEnvio.costo);
}

function pintarTotales() {
    const productos = totalCarrito();
    const envio = carrito.length ? costoEnvio(productos) : 0;
    // Los precios ya incluyen IGV: el 18 % se separa del total (productos + envío), igual que en el servidor
    const total = productos + envio;
    const igv = total * 0.18 / 1.18;
    if (subtotalBox) subtotalBox.textContent = soles(productos);
    if (totalBox) totalBox.textContent = soles(total);
    const envioBox = document.getElementById("envio");
    if (envioBox) envioBox.textContent = envio === 0 ? "Gratis" : soles(envio);
    const igvBox = document.getElementById("igv");
    if (igvBox) igvBox.textContent = soles(igv);

    pintarBarraEnvio(productos);

    const btn = document.getElementById("btn-comprar");
    if (btn) btn.classList.toggle("deshabilitado", carrito.length === 0 || hayTallasAgotadas());
}

// Barra de progreso: llena en la primera compra o al llegar al mínimo; si no, muestra cuánto falta
function pintarBarraEnvio(productos) {
    const caja = document.getElementById("envio-gratis");
    const texto = document.getElementById("envio-texto");
    const barra = document.getElementById("envio-progreso");
    if (caja) caja.hidden = carrito.length === 0;

    const minimo = Number(reglaEnvio.gratis_desde);
    let mensaje = "¡Te ganaste el envío gratis!";
    let avance = 100;
    if (reglaEnvio.primera_compra === true) {
        mensaje = "¡Es tu primera compra: el envío es gratis!";
    } else if (productos < minimo) {
        mensaje = `Te faltan ${soles(minimo - productos)} para el envío gratis.`;
        if (reglaEnvio.primera_compra === null) mensaje += " ¿Es tu primera compra? Inicia sesión y el envío es gratis.";
        avance = (productos / minimo) * 100;
    }

    if (texto) texto.textContent = mensaje;
    if (barra) barra.style.width = `${Math.min(100, avance)}%`;
}

// --------------------------------------------------------------------------
// Stock real en vivo: POST /carrito/validar devuelve precio, tallas y stock de MySQL
// --------------------------------------------------------------------------
// { SKU: { "40": 3, "40.5": 0 } }: vive solo en memoria (no en localStorage) para que nunca quede desactualizado
const stockPorSku = {};
let maxPorTalla = 10;
let umbralEscasez = 3; // lo reemplaza ProductoTalla::ULTIMAS_UNIDADES al consultar el servidor

const claveDe = (p) => String(p.sku || p.id);
// Stock de la talla elegida: número real, 0 si la talla ya no existe, o null si aún no se consultó al servidor
const stockDe = (p) => {
    const tallas = stockPorSku[claveDe(p)];
    return tallas ? (tallas[String(p.talla)] ?? 0) : null;
};
const topeCantidad = (p) => (stockDe(p) === null ? maxPorTalla : Math.min(maxPorTalla, stockDe(p)));
const hayTallasAgotadas = () => carrito.some((p) => stockDe(p) === 0);

async function sincronizarStock() {
    if (!contenedor || carrito.length === 0) return;

    let datos;
    try {
        const respuesta = await fetch("/carrito/validar", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
            },
            body: JSON.stringify({ items: carrito.map((p) => ({ id: claveDe(p) })) }),
        });
        if (!respuesta.ok) return;
        datos = await respuesta.json();
    } catch (e) {
        return; // Sin conexión: el servidor vuelve a validar stock y precios al pagar
    }

    maxPorTalla = Number(datos.max_por_talla) || 10;
    umbralEscasez = Number(datos.ultimas_unidades) || umbralEscasez;
    const avisos = [];

    // Producto desactivado o eliminado: se quita del carrito
    carrito = carrito.filter((p) => {
        if (datos.productos?.[claveDe(p)]?.disponible) return true;
        avisos.push(`${p.nombre} ya no está disponible y se quitó de tu carrito.`);
        return false;
    });

    carrito.forEach((p) => {
        const info = datos.productos[claveDe(p)];
        stockPorSku[claveDe(p)] = Object.fromEntries(info.tallas.map((t) => [String(t.talla), Number(t.stock)]));
        // Arreglo (no Object.keys) para conservar el orden 38, 38.5, 39...
        p.tallas = info.tallas.map((t) => String(t.talla));

        if (Number(info.precio) !== Number(p.precio)) {
            p.precio = Number(info.precio);
            avisos.push(`El precio de ${info.nombre} se actualizó a ${soles(info.precio)}.`);
        }

        if (stockDe(p) === 0) {
            avisos.push(`La talla ${p.talla} de ${p.nombre} se agotó: elige otra talla.`);
        } else if (p.cantidad > topeCantidad(p)) {
            p.cantidad = topeCantidad(p);
            avisos.push(`${p.nombre} (talla ${p.talla}) bajó de stock: ajustamos la cantidad a ${p.cantidad}.`);
        }
    });

    guardarCarrito();
    mostrarCarrito();
    renderFavoritos("favoritos-carrito", 4);
    avisos.slice(0, 3).forEach((mensaje) => avisar(mensaje, "info"));
}

// Tallas sin stock aparecen deshabilitadas (se ven, pero no se pueden elegir)
function opcionesTalla(p) {
    const tallas = Array.isArray(p.tallas) && p.tallas.length ? [...p.tallas] : [String(p.talla)];
    if (!tallas.includes(String(p.talla))) tallas.unshift(String(p.talla));
    const stock = stockPorSku[claveDe(p)];

    return tallas.map((t) => {
        const agotada = stock ? (stock[String(t)] ?? 0) <= 0 : false;
        const elegida = String(t) === String(p.talla);
        return `<option value="${escapar(t)}" ${elegida ? "selected" : ""} ${agotada ? "disabled" : ""}>${escapar(t)}${agotada ? " · Agotada" : ""}</option>`;
    }).join("");
}

// La cantidad llega hasta el stock real de la talla (máx. 10 por talla)
function opcionesCantidad(p) {
    const actual = Number(p.cantidad) || 1;
    const tope = Math.max(1, topeCantidad(p), stockDe(p) === 0 ? actual : 1);
    return Array.from({ length: tope }, (_, i) => i + 1)
        .map((n) => `<option value="${n}" ${n === actual ? "selected" : ""}>${n}</option>`).join("");
}

// Aviso bajo la línea: talla agotada (bloquea el pago) o pocas unidades
function avisoStock(p) {
    const stock = stockDe(p);
    if (stock === 0) {
        return `<p class="mt-2.5 text-[13px] font-medium text-agotado">Talla agotada: elige otra talla para continuar.</p>`;
    }
    if (stock !== null && stock <= umbralEscasez) {
        return `<p class="mt-2.5 text-[13px] font-medium text-[#b45309]">¡Solo ${stock === 1 ? "queda 1 unidad" : `quedan ${stock} unidades`} en esta talla!</p>`;
    }
    return "";
}

const leerFavoritos = () => JSON.parse(localStorage.getItem("favoritos")) || [];
const esFavorito = (p) => leerFavoritos().some((f) => String(f.sku || f.id) === claveDe(p));

const RUTA_CORAZON = "M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z";

// Corazón de la línea: guarda en favoritos SIN quitar el producto del carrito; relleno = ya está en favoritos
function botonFavorito(p, i) {
    const lleno = esFavorito(p);
    const texto = lleno ? "Quitar de favoritos" : "Guardar en favoritos";
    return `<button type="button" onclick="alternarFavorito(${i})" title="${texto}" aria-label="${texto}" aria-pressed="${lleno}">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="${lleno ? "currentColor" : "none"}" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="${RUTA_CORAZON}"/></svg>
    </button>`;
}

function mostrarCarrito() {
    if (!contenedor) return;

    if (carrito.length === 0) {
        if (cantidadProductos) cantidadProductos.textContent = "";
        contenedor.innerHTML = `
            <div class="py-10">
                <h2 class="text-[18px] font-semibold">Tu carrito está vacío</h2>
                <p class="mt-1.5 mb-5 text-suave">Agrega tus zapatillas favoritas y aparecerán aquí.</p>
                <a href="/catalogo" class="mt-2 inline-flex w-auto cursor-pointer items-center justify-center rounded-full border-none bg-tinta px-7 py-3.5 text-[16px] font-medium text-white transition-colors duration-200 hover:bg-[#333]">Explorar catálogo</a>
            </div>`;
        pintarTotales();
        return;
    }

    const unidades = carrito.reduce((t, p) => t + (Number(p.cantidad) || 1), 0);
    if (cantidadProductos) cantidadProductos.textContent = `${unidades} ${unidades === 1 ? "artículo" : "artículos"}`;

    contenedor.innerHTML = carrito.map((p, i) => `
        <article class="grid grid-cols-[150px_minmax(0,1fr)] gap-5 border-b border-[#e5e5e5] py-6 *:max-w-full *:min-w-0 max-escritorio:grid-cols-[96px_minmax(0,1fr)] max-escritorio:gap-3.5">
            <a href="/producto/${encodeURIComponent(p.sku || p.id)}" class="block aspect-square rounded-xl bg-perla p-3">
                <img src="${escapar(p.imagen)}" alt="${escapar(p.nombre)}" class="inline size-full align-baseline object-contain mix-blend-multiply">
            </a>
            <div>
                <div class="flex justify-between gap-4 max-[600px]:flex-col max-[600px]:gap-1">
                    <h3 class="text-[16px] leading-[1.35] font-semibold">${escapar(p.nombre)}</h3>
                    <strong class="text-[16px] font-medium whitespace-nowrap">${soles((Number(p.precio) || 0) * (Number(p.cantidad) || 1))}</strong>
                </div>
                <p class="mt-0.5 text-[15px] text-suave">${escapar(p.tipo || p.marca || "Calzado KixaStore")}</p>
                <div class="mt-3 flex flex-wrap gap-6 [&_label]:inline-flex [&_label]:items-center [&_label]:gap-2 [&_label]:text-[15px] [&_label]:text-suave [&_select]:cursor-pointer [&_select]:appearance-none [&_select]:rounded-md [&_select]:border-none [&_select]:bg-transparent [&_select]:bg-(image:--kx-icono-flecha) [&_select]:bg-[position:right_4px_center] [&_select]:bg-no-repeat [&_select]:py-1 [&_select]:pr-[22px] [&_select]:pl-1 [&_select]:text-[15px] [&_select]:text-tinta [&_select:hover]:bg-perla">
                    <label>Talla
                        <select onchange="cambiarTalla(${i}, this.value)" aria-label="Talla de ${escapar(p.nombre)}">${opcionesTalla(p)}</select>
                    </label>
                    <label>Cantidad
                        <select onchange="cambiarCantidad(${i}, this.value)" aria-label="Cantidad de ${escapar(p.nombre)}" ${stockDe(p) === 0 ? "disabled" : ""}>${opcionesCantidad(p)}</select>
                    </label>
                </div>
                ${avisoStock(p)}
                <div class="mt-3 flex gap-1.5 [&>button]:inline-flex [&>button]:size-10 [&>button]:cursor-pointer [&>button]:items-center [&>button]:justify-center [&>button]:rounded-full [&>button]:border-none [&>button]:bg-transparent [&>button]:text-tinta [&>button]:transition-colors [&>button]:duration-200 [&>button:hover]:bg-perla">
                    ${botonFavorito(p, i)}
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
    // Si ya hay otra línea del mismo producto con esa talla, se juntan en una sola (máx. 10)
    const clave = String(carrito[i].sku || carrito[i].id);
    const j = carrito.findIndex((p, k) => k !== i && String(p.sku || p.id) === clave && String(p.talla) === String(talla));
    if (j !== -1) {
        carrito[j].cantidad = cantidadValida((Number(carrito[j].cantidad) || 1) + (Number(carrito[i].cantidad) || 1));
        carrito.splice(i, 1);
        guardarCarrito();
        mostrarCarrito();
        return;
    }
    carrito[i].talla = talla;
    // Si la nueva talla tiene menos stock, la cantidad baja al máximo disponible
    if (topeCantidad(carrito[i]) > 0) carrito[i].cantidad = Math.min(carrito[i].cantidad, topeCantidad(carrito[i]));
    guardarCarrito();
    mostrarCarrito();
}

function cambiarCantidad(i, cantidad) {
    if (!carrito[i]) return;
    carrito[i].cantidad = Math.min(cantidadValida(cantidad), Math.max(1, topeCantidad(carrito[i])));
    guardarCarrito();
    mostrarCarrito();
}

function eliminar(i) {
    carrito.splice(i, 1);
    guardarCarrito();
    mostrarCarrito();
}

// Guarda (o quita) el producto de Favoritos sin sacarlo del carrito, para que el carrito no se vacíe de golpe
function alternarFavorito(i) {
    const p = carrito[i];
    if (!p) return;
    const favs = leerFavoritos();
    const indice = favs.findIndex((f) => String(f.sku || f.id) === claveDe(p));
    if (indice >= 0) {
        favs.splice(indice, 1);
        avisar(`${p.nombre} se quitó de tus favoritos.`, "info");
    } else {
        favs.push({ id: p.id, sku: p.sku, nombre: p.nombre, marca: p.marca, precio: p.precio, imagen: p.imagen, tipo: p.tipo });
        avisar(`${p.nombre} se guardó en favoritos y sigue en tu carrito.`, "ok");
    }
    localStorage.setItem("favoritos", JSON.stringify(favs));
    if (typeof actualizarBadgesHeader === "function") actualizarBadgesHeader();
    mostrarCarrito();
    renderFavoritos("favoritos-carrito", 4);
}

// Tarjetas de favoritos con el mismo estilo que <x-tarjeta-producto>; se usan en el carrito
function renderFavoritos(idContenedor, limite = null) {
    const caja = document.getElementById(idContenedor);
    if (!caja) return;

    const favs = JSON.parse(localStorage.getItem("favoritos")) || [];
    if (favs.length === 0) {
        caja.innerHTML = `<p class="text-[15px] text-suave">Aún no guardas favoritos. Toca el ♡ en cualquier zapatilla para verla aquí.</p>`;
        return;
    }

    const lista = limite ? favs.slice(0, limite) : favs;
    caja.innerHTML = `<div class="grid grid-cols-4 gap-x-4 gap-y-8 max-[960px]:grid-cols-2">${lista.map((f, i) => `
        <article class="group relative text-tinta transition-[translate] duration-300 hover:-translate-y-1">
            <a href="/producto/${encodeURIComponent(f.sku || f.id)}" class="block">
                <div class="aspect-square overflow-hidden rounded-xl bg-perla p-5"><img src="${escapar(f.imagen)}" alt="${escapar(f.nombre)}" loading="lazy" class="block size-full object-contain mix-blend-multiply transition-[scale] duration-500 group-hover:scale-104"></div>
                <div class="px-0.5 pt-3.5 text-left">
                    <h3 class="text-[15px] leading-[1.35] font-semibold tracking-[-0.01em] text-tinta max-escritorio:text-[14px]">${escapar(f.nombre)}</h3>
                    <p class="mt-0.5 truncate text-[14px] text-suave max-escritorio:text-[13px]">${escapar(f.tipo || f.marca || "KixaStore")}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-[15px] font-medium text-tinta max-escritorio:text-[14px]"><span>${soles(f.precio)}</span></div>
                </div>
            </a>
            <button type="button" class="absolute top-3 right-3 flex size-9 cursor-pointer items-center justify-center rounded-full border-none bg-white text-tinta shadow-[0_1px_4px_rgba(0,0,0,0.08)]" onclick="quitarFavorito(${i}, '${idContenedor}', ${limite || "null"})" aria-label="Quitar de favoritos" title="Quitar de favoritos">
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
    mostrarCarrito(); // el corazón de la línea del carrito vuelve a quedar vacío
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
// Clases de Tailwind compartidas por los 2 formularios de pago (Tarjeta y Contraentrega) (los inputs toman el estilo de la sección del checkout)
const CLASE_PAGO = "mt-[18px] max-w-full min-w-0 rounded-[14px] bg-perla p-5 [&>p]:text-[14px] [&>p]:leading-[1.6] [&>p]:text-suave [&_label]:mt-3 [&_label]:mb-1.5 [&_label]:block [&_label]:text-[13px] [&_label]:text-suave";
const CLASE_CONFIRMAR = "mt-[18px] flex w-full cursor-pointer items-center justify-center gap-2.5 rounded-full border-none bg-tinta p-4 text-[15px] font-medium text-white hover:bg-[#333]";

// Marca el botón del método elegido (aria-pressed lo resalta con Tailwind en checkout.blade.php)
function marcarMetodo(metodo) {
    document.querySelectorAll("[data-metodo]").forEach((boton) => {
        boton.setAttribute("aria-pressed", boton.dataset.metodo === metodo ? "true" : "false");
    });
}

function mostrarContraentrega() {
    if (!contenidoPago) return;
    marcarMetodo("Contraentrega");
    contenidoPago.innerHTML = `
        <div class="${CLASE_PAGO}">
            <h3 class="mb-2 flex items-center gap-2 text-[16px] font-semibold">
                <i class="fa-solid fa-money-bill-wave"></i>
                Contraentrega (Efectivo)
            </h3>
            <p>Pagarás en efectivo al momento de recibir tu pedido. Tu pedido quedará <strong class="inline font-semibold text-tinta">Pendiente</strong> hasta la entrega.</p>
            <button class="${CLASE_CONFIRMAR}" onclick="confirmar('Contraentrega')" type="button">
                Confirmar pedido
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    `;
}

// --------------------------------------------------------------------------
// Simulador de tarjeta: vista previa interactiva + validaciones (el servidor vuelve a validar con Luhn)
// --------------------------------------------------------------------------
// Visa empieza en 4; Mastercard en 51-55 o 2221-2720
function detectarMarca(numero) {
    if (/^4/.test(numero)) return "visa";
    if (/^(5[1-5]|222[1-9]|22[3-9]\d|2[3-6]\d{2}|27[01]\d|2720)/.test(numero)) return "mastercard";
    return "";
}

// Algoritmo de Luhn (igual que CarritoController::pasaLuhn)
function pasaLuhn(numero) {
    if (!/^\d{13,19}$/.test(numero)) return false;
    let suma = 0;
    numero.split("").reverse().forEach((d, i) => {
        let digito = Number(d);
        if (i % 2 === 1) {
            digito *= 2;
            if (digito > 9) digito -= 9;
        }
        suma += digito;
    });
    return suma % 10 === 0;
}

// "MM/AA" vigente hasta el último día de ese mes
function tarjetaVigente(vencimiento) {
    const partes = /^(0[1-9]|1[0-2])\/(\d{2})$/.exec(vencimiento);
    if (!partes) return false;
    const finDeMes = new Date(2000 + Number(partes[2]), Number(partes[1]), 0, 23, 59, 59);
    return finDeMes >= new Date();
}

const ICONO_MARCA = {
    visa: '<i class="fa-brands fa-cc-visa"></i>',
    mastercard: '<i class="fa-brands fa-cc-mastercard"></i>',
    "": '<i class="fa-solid fa-credit-card opacity-70"></i>',
};

function mostrarTarjeta() {
    if (!contenidoPago) return;
    marcarMetodo("Tarjeta");
    // Las caras usan group-data-[marca=...] para cambiar de color según la marca detectada
    contenidoPago.innerHTML = `
        <div class="${CLASE_PAGO}">
            <h3 class="mb-4 flex items-center gap-2 text-[16px] font-semibold">
                <i class="fa-solid fa-credit-card"></i>
                Tarjeta de Crédito / Débito
            </h3>

            <div class="mx-auto mb-2 w-full max-w-[360px] perspective-[1000px]">
                <div id="tarjetaVisual" data-marca="" class="group relative aspect-[1.586/1] w-full transition-transform duration-500 transform-3d">
                    <div class="absolute inset-0 flex flex-col justify-between rounded-2xl bg-linear-to-br from-[#2a2a30] to-noche p-5 text-white shadow-[0_18px_40px_rgba(0,0,0,0.22)] backface-hidden group-data-[marca=visa]:from-[#1f3a8a] group-data-[marca=visa]:to-[#0b1437] group-data-[marca=mastercard]:from-[#3b2a26] group-data-[marca=mastercard]:to-[#120d0c] max-escritorio:p-4">
                        <div class="flex items-start justify-between">
                            <span class="h-8 w-11 rounded-md bg-linear-to-br from-[#e9d48b] to-[#b8953f]"></span>
                            <span id="tarjetaMarca" class="text-[38px] leading-none max-escritorio:text-[32px]">${ICONO_MARCA[""]}</span>
                        </div>
                        <p id="tarjetaNumeroVista" class="font-mono text-[20px] tracking-[0.12em] whitespace-nowrap max-escritorio:text-[16px]">•••• •••• •••• ••••</p>
                        <div class="flex items-end justify-between gap-4 text-[11px] uppercase tracking-[0.08em]">
                            <div class="min-w-0">
                                <span class="block text-white/60">Titular</span>
                                <span id="tarjetaTitularVista" class="block truncate text-[14px] font-medium">NOMBRE APELLIDO</span>
                            </div>
                            <div class="shrink-0 text-right">
                                <span class="block text-white/60">Vence</span>
                                <span id="tarjetaVenceVista" class="block font-mono text-[14px]">MM/AA</span>
                            </div>
                        </div>
                    </div>
                    <div class="absolute inset-0 flex rotate-y-180 flex-col justify-center gap-4 rounded-2xl bg-linear-to-br from-[#2a2a30] to-noche text-white shadow-[0_18px_40px_rgba(0,0,0,0.22)] backface-hidden group-data-[marca=visa]:from-[#1f3a8a] group-data-[marca=visa]:to-[#0b1437] group-data-[marca=mastercard]:from-[#3b2a26] group-data-[marca=mastercard]:to-[#120d0c]">
                        <span class="block h-10 w-full bg-black/80"></span>
                        <div class="flex items-center justify-end gap-3 px-5">
                            <span class="text-[11px] uppercase tracking-[0.08em] text-white/60">CVV</span>
                            <span id="tarjetaCvvVista" class="min-w-16 rounded bg-white px-3 py-1.5 text-right font-mono text-[15px] text-tinta">•••</span>
                        </div>
                    </div>
                </div>
            </div>

            <label for="tarjetaNumero">Número de tarjeta</label>
            <input type="text" id="tarjetaNumero" placeholder="0000 0000 0000 0000" maxlength="23" inputmode="numeric" autocomplete="cc-number">

            <label for="tarjetaTitular">Titular (como figura en la tarjeta)</label>
            <input type="text" id="tarjetaTitular" placeholder="Ej. Ana Torres" maxlength="100" autocomplete="cc-name">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="tarjetaVencimiento">Vencimiento (MM/AA)</label>
                    <input type="text" id="tarjetaVencimiento" placeholder="05/28" maxlength="5" inputmode="numeric" autocomplete="cc-exp">
                </div>
                <div>
                    <label for="tarjetaCvv">CVV</label>
                    <input type="password" id="tarjetaCvv" placeholder="•••" maxlength="4" inputmode="numeric" autocomplete="cc-csc">
                </div>
            </div>

            <p class="mt-3 flex items-center gap-2 text-[12px]"><i class="fa-solid fa-shield-halved"></i> Simulación: no se realiza ningún cargo real. Prueba con 4242 4242 4242 4242.</p>

            <div id="errorPago" role="alert" class="mt-4 flex items-start gap-2 rounded-[10px] border border-red-200 bg-red-50 px-3.5 py-2.5 text-[13px] font-medium text-red-700" hidden>
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span id="errorPagoTexto"></span>
            </div>

            <button class="${CLASE_CONFIRMAR}" onclick="confirmar('Tarjeta')" type="button">
                Pagar ahora
                <i class="fa-solid fa-lock"></i>
            </button>
        </div>
    `;
    activarTarjeta();
}

// Conecta los inputs con la tarjeta visual: formatea mientras se escribe y gira la tarjeta al llegar al CVV
function activarTarjeta() {
    const tarjeta = document.getElementById("tarjetaVisual");
    const numero = document.getElementById("tarjetaNumero");
    const titular = document.getElementById("tarjetaTitular");
    const vence = document.getElementById("tarjetaVencimiento");
    const cvv = document.getElementById("tarjetaCvv");
    if (!tarjeta || !numero) return;

    numero.addEventListener("input", () => {
        const digitos = numero.value.replace(/\D/g, "").slice(0, 19);
        numero.value = digitos.replace(/(\d{4})(?=\d)/g, "$1 ");
        const marca = detectarMarca(digitos);
        tarjeta.dataset.marca = marca;
        document.getElementById("tarjetaMarca").innerHTML = ICONO_MARCA[marca];
        // Muestra los dígitos escritos y completa con puntos hasta 16
        const relleno = digitos.padEnd(Math.max(16, digitos.length), "•");
        document.getElementById("tarjetaNumeroVista").textContent = relleno.replace(/(.{4})(?=.)/g, "$1 ");
    });

    titular.addEventListener("input", () => {
        titular.value = titular.value.replace(/[^\p{L}\s.'-]/gu, "");
        document.getElementById("tarjetaTitularVista").textContent = titular.value.trim().toUpperCase() || "NOMBRE APELLIDO";
    });

    vence.addEventListener("input", (e) => {
        let digitos = vence.value.replace(/\D/g, "").slice(0, 4);
        // Al borrar no se vuelve a poner la barra automáticamente
        if (digitos.length >= 3 || (digitos.length === 2 && e.inputType !== "deleteContentBackward")) {
            digitos = digitos.slice(0, 2) + "/" + digitos.slice(2);
        }
        vence.value = digitos;
        document.getElementById("tarjetaVenceVista").textContent = vence.value || "MM/AA";
    });

    cvv.addEventListener("input", () => {
        cvv.value = cvv.value.replace(/\D/g, "").slice(0, 4);
        document.getElementById("tarjetaCvvVista").textContent = cvv.value ? "•".repeat(cvv.value.length) : "•••";
    });
    [numero, titular, vence, cvv].forEach((input) => input.addEventListener("input", () => {
        limpiarErrorCampo(input.id);
        mostrarErrorPago("");
    }));

    cvv.addEventListener("focus", () => tarjeta.classList.add("rotate-y-180"));
    cvv.addEventListener("blur", () => tarjeta.classList.remove("rotate-y-180"));
}

// --------------------------------------------------------------------------
// Errores en rojo por campo: borde + mensaje debajo del input (se limpian al volver a escribir)
// --------------------------------------------------------------------------
// border-red-500! lleva "!" porque el contenedor del checkout ya pinta el borde de todos sus inputs ([&_input]:border-...)
const CLASES_ERROR_CAMPO = ["border-red-500!", "ring-1", "ring-red-500"];

function marcarErrorCampo(id, mensaje) {
    const input = document.getElementById(id);
    if (!input) return;
    input.classList.add(...CLASES_ERROR_CAMPO);
    input.setAttribute("aria-invalid", "true");
    input.setAttribute("aria-describedby", `${id}Error`);

    let aviso = document.getElementById(`${id}Error`);
    if (!aviso) {
        aviso = document.createElement("span");
        aviso.id = `${id}Error`;
        aviso.className = "mt-1 block text-xs font-medium text-red-600";
        input.insertAdjacentElement("afterend", aviso);
    }
    aviso.textContent = mensaje;
}

function limpiarErrorCampo(id) {
    const input = document.getElementById(id);
    if (!input) return;
    input.classList.remove(...CLASES_ERROR_CAMPO);
    input.removeAttribute("aria-invalid");
    input.removeAttribute("aria-describedby");
    document.getElementById(`${id}Error`)?.remove();
}

// Banner rojo compacto arriba de "Pagar ahora" para los errores que devuelve el servidor ("" lo oculta)
function mostrarErrorPago(mensaje) {
    const banner = document.getElementById("errorPago");
    if (!banner) return false;
    document.getElementById("errorPagoTexto").textContent = mensaje;
    banner.hidden = !mensaje;
    return true;
}

// Campo del backend (validate()) → id del input en la tarjeta
const CAMPOS_TARJETA = {
    tarjeta_numero: "tarjetaNumero",
    tarjeta_titular: "tarjetaTitular",
    tarjeta_vencimiento: "tarjetaVencimiento",
    tarjeta_cvv: "tarjetaCvv",
};

// Valida la tarjeta antes de enviarla: marca en rojo cada campo con error y devuelve los datos o null
function datosTarjeta() {
    const campo = (id) => document.getElementById(id);
    const numero = (campo("tarjetaNumero")?.value || "").replace(/\D/g, "");
    const titular = (campo("tarjetaTitular")?.value || "").trim();
    const vencimiento = (campo("tarjetaVencimiento")?.value || "").trim();
    const cvv = (campo("tarjetaCvv")?.value || "").trim();
    const formatoMMAA = /^(0[1-9]|1[0-2])\/\d{2}$/.test(vencimiento);

    // [id del input, mensaje] solo de los campos que fallan
    const errores = [
        !numero ? ["tarjetaNumero", "Ingresa el número de tu tarjeta."]
            : !pasaLuhn(numero) ? ["tarjetaNumero", "El número de tarjeta no es válido."] : null,
        titular.length < 3 ? ["tarjetaTitular", "Ingresa el nombre del titular de la tarjeta."] : null,
        !formatoMMAA ? ["tarjetaVencimiento", "Usa el formato MM/AA."]
            : !tarjetaVigente(vencimiento) ? ["tarjetaVencimiento", "La tarjeta está vencida."] : null,
        !/^\d{3,4}$/.test(cvv) ? ["tarjetaCvv", "El CVV debe tener 3 o 4 dígitos."] : null,
    ].filter(Boolean);

    Object.values(CAMPOS_TARJETA).forEach(limpiarErrorCampo);
    errores.forEach(([id, mensaje]) => marcarErrorCampo(id, mensaje));
    if (errores.length) {
        campo(errores[0][0])?.focus();
        return null;
    }

    return { tarjeta_numero: numero, tarjeta_titular: titular, tarjeta_vencimiento: vencimiento, tarjeta_cvv: cvv };
}

// Capa "Procesando pago..." de checkout.blade.php
function mostrarProcesando(visible, detalle = "") {
    const capa = document.getElementById("procesandoPago");
    if (!capa) return;
    const texto = document.getElementById("procesandoDetalle");
    if (texto && detalle) texto.textContent = detalle;
    capa.style.display = visible ? "flex" : "none";
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

    // Con tarjeta se validan número (Luhn), titular, vencimiento y CVV antes de enviar
    mostrarErrorPago("");
    const tarjeta = metodo === "Tarjeta" ? datosTarjeta() : {};
    if (!tarjeta) return;

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const token = csrfMeta ? csrfMeta.getAttribute("content") : "";

    // Animación breve de "Procesando pago..." (mínimo 1.4 s) mientras responde el servidor
    mostrarProcesando(true, metodo === "Tarjeta" ? "Validando tu tarjeta de forma segura" : "Registrando tu pedido");
    const esperaMinima = new Promise((resolver) => setTimeout(resolver, 1400));

    const peticion = fetch("/checkout/procesar", {
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
            ...tarjeta,
            items: carrito.map((p) => ({ id: p.sku || p.id, talla: p.talla, cantidad: cantidadValida(p.cantidad) }))
        })
    });

    Promise.all([peticion, esperaMinima])
    .then(async ([response]) => {
        if (response.status === 401) {
            avisar("Debes iniciar sesión para completar tu compra.");
            permitirSalida();
            window.location.href = "/login";
            return;
        }
        const data = await response.json();
        // Compra ya procesada (409): se limpia el carrito y se va al historial
        if (response.status === 409) {
            vaciarCarritoCompleto();
            permitirSalida();
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
            permitirSalida();
            window.location.replace(data.redirect_url || "/perfil/pedidos");
        } else {
            mostrarProcesando(false);
            // 422 de validate() trae "message" y "errors"; los rechazos propios traen "mensaje"
            const mensaje = data.mensaje || data.message || "Ocurrió un error al procesar el pedido.";
            Object.entries(data.errors || {}).forEach(([clave, lista]) => {
                if (CAMPOS_TARJETA[clave]) marcarErrorCampo(CAMPOS_TARJETA[clave], lista[0]);
            });
            // Con tarjeta el error se muestra en el banner rojo sobre "Pagar ahora"; en Contraentrega, en el toast
            if (metodo !== "Tarjeta" || !mostrarErrorPago(mensaje)) avisar(mensaje);
        }
    })
    .catch(error => {
        mostrarProcesando(false);
        console.error("Error al procesar la compra:", error);
        const mensaje = "No se pudo conectar con el servidor. Tu pedido NO se registró; inténtalo de nuevo.";
        if (metodo !== "Tarjeta" || !mostrarErrorPago(mensaje)) avisar(mensaje);
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

// --------------------------------------------------------------------------
// Confirmación al salir del checkout sin haber pagado
// --------------------------------------------------------------------------
let salidaPermitida = false;
const permitirSalida = () => { salidaPermitida = true; };

function activarConfirmacionSalida() {
    const dialogo = document.getElementById("confirmarSalida");
    if (!dialogo || !document.getElementById("tokenCompra")) return;
    let destino = null;

    const abrir = (url) => {
        destino = url;
        dialogo.hidden = false;
        requestAnimationFrame(() => dialogo.classList.add("abierto"));
        dialogo.querySelector("button[data-seguir-pagando]")?.focus();
    };
    const cerrar = () => {
        dialogo.classList.remove("abierto");
        setTimeout(() => { dialogo.hidden = true; }, 200);
        destino = null;
    };

    // Enlaces de la página (header, footer, "Volver al carrito"): se muestra el diálogo propio antes de salir
    document.addEventListener("click", (e) => {
        const enlace = e.target.closest("a[href]");
        if (!enlace || salidaPermitida || e.defaultPrevented) return;
        // Abrir en otra pestaña (Ctrl/Cmd + clic o target="_blank") no abandona el pago
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || enlace.target === "_blank") return;
        const url = new URL(enlace.href, location.href);
        if (url.pathname === location.pathname && url.hash) return;
        e.preventDefault();
        abrir(url.href);
    });

    dialogo.querySelector("[data-salir]")?.addEventListener("click", () => {
        permitirSalida();
        window.location.href = destino || "/carrito";
    });
    dialogo.querySelectorAll("[data-seguir-pagando]").forEach((el) => el.addEventListener("click", cerrar));
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !dialogo.hidden) cerrar();
    });

    // Cerrar la pestaña, recargar, "Atrás" o usar el buscador: el navegador muestra su propio aviso
    window.addEventListener("beforeunload", (e) => {
        if (salidaPermitida || carrito.length === 0) return;
        e.preventDefault();
        e.returnValue = "";
    });
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
    sincronizarStock();
    activarConfirmacionSalida();
});


// Si el navegador restaura la página desde su caché (botón Atrás), se recarga para revalidar con el servidor
window.addEventListener("pageshow", (e) => {
    if (e.persisted && document.getElementById("tokenCompra")) {
        permitirSalida();
        window.location.reload();
    }
});
