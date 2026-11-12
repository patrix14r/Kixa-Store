// ==========================================================================
// TIENDA KIXA - GESTIÓN DEL CARRITO DE COMPRAS Y PAGOS (CARRITO.JS)
// Renderizado dinámico de productos, cálculo de totales y pasarela modal
// ==========================================================================

let carrito = JSON.parse(localStorage.getItem("carrito")) || [];

const contenedor = document.getElementById("carrito");
const subtotalBox = document.getElementById("subtotal");
const totalBox = document.getElementById("total");
const cantidadProductos = document.getElementById("cantidad-productos");
const modal = document.getElementById("modal");
const contenidoPago = document.getElementById("contenido-pago");
const alerta = document.getElementById("alerta");
const mensajeAlerta = document.getElementById("mensaje-alerta");

// --------------------------------------------------------------------------
// [CAMBIO]: Función para renderizar los productos almacenados en el carrito
// --------------------------------------------------------------------------
function mostrarCarrito() {
    if (!contenedor) return;

    contenedor.innerHTML = "";
    let total = 0;

    if (carrito.length === 0) {
        if (cantidadProductos) cantidadProductos.textContent = "";

        contenedor.innerHTML = `
            <div class="carrito-vacio">
                <div class="icono-vacio">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <h2>Tu carrito está vacío</h2>
                <p>Parece que todavía no has agregado productos a tu compra.</p>
                <a href="/catalogo" class="btn-seguir">
                    <i class="fa-solid fa-arrow-left"></i>
                    Explorar catálogo
                </a>
            </div>
        `;

        if (subtotalBox) subtotalBox.textContent = "S/ 0.00";
        if (totalBox) totalBox.textContent = "S/ 0.00";
        return;
    }

    if (cantidadProductos) {
        cantidadProductos.textContent = `${carrito.length} ${carrito.length === 1 ? "producto" : "productos"}`;
    }

    carrito.forEach((p, index) => {
        total += Number(p.precio) || 0;

        contenedor.innerHTML += `
            <article class="item">
                <div class="imagen-producto">
                    <img src="${p.imagen}" alt="${p.nombre}" class="img-item">
                </div>

                <div class="info-producto">
                    <span class="categoria-producto">CALZADO KIXA</span>
                    <h3 class="nombre">${p.nombre}</h3>

                    <div class="detalle-producto">
                        <span>
                            <i class="fa-solid fa-ruler-horizontal"></i>
                            Talla: <strong>${p.talla}</strong>
                        </span>
                    </div>

                    <button class="eliminar" onclick="eliminar(${index})" type="button">
                        <i class="fa-regular fa-trash-can"></i>
                        Eliminar
                    </button>
                </div>

                <div class="precio-producto">
                    <span>Precio</span>
                    <strong>S/ ${Number(p.precio).toFixed(2)}</strong>
                </div>
            </article>
        `;
    });

    if (subtotalBox) subtotalBox.textContent = `S/ ${total.toFixed(2)}`;
    if (totalBox) totalBox.textContent = `S/ ${total.toFixed(2)}`;
}

// --------------------------------------------------------------------------
// [CAMBIO]: Eliminar producto individual del carrito
// --------------------------------------------------------------------------
function eliminar(i) {
    carrito.splice(i, 1);
    localStorage.setItem("carrito", JSON.stringify(carrito));
    mostrarCarrito();
}

// --------------------------------------------------------------------------
// [CAMBIO]: Apertura y validación previa del modal de Checkout
// --------------------------------------------------------------------------
function abrirModal() {
    if (carrito.length === 0) {
        alert("⚠️ Tu carrito está vacío. Agrega productos antes de continuar.");
        return;
    }

    const sesion = localStorage.getItem("usuarioLogueado");
    if (!sesion) {
        alert("⚠️ Debes iniciar sesión antes de realizar la compra.");
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
function generarCodigo() {
    return "PED-" + Math.floor(Math.random() * 90000 + 10000);
}

function confirmar(metodo) {
    const codigo = generarCodigo();
    cerrarModal();
    if (contenidoPago) contenidoPago.innerHTML = "";

    mostrarAlerta(
        `Método de pago: <b>${metodo}</b><br>Código de pedido: <b>${codigo}</b>`
    );
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
    mostrarCarrito();
});

