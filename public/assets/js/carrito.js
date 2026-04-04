/* ============================================================
    VARIABLES DEL CARRITO
============================================================ */
let cart = [];
const cartItems   = document.getElementById("cartItems");
const cartTotal   = document.getElementById("cartTotal");
const cartCount   = document.getElementById("cartCount");
const checkoutBtn = document.getElementById("checkoutBtn");

/* ============================================================
    AÑADIR PRODUCTO AL CARRITO
============================================================ */
function addToCart(id, nombre, precio, imagen) {
    let item = cart.find(p => p.id === id);

    if (item) {
        item.cantidad++;
    } else {
        cart.push({ id, nombre, precio, imagen, cantidad: 1 });
    }

    renderCart();
}

/* ============================================================
    QUITAR PRODUCTO DEL CARRITO
============================================================ */
function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    renderCart();
}

/* ============================================================
    ACTUALIZAR CANTIDAD
============================================================ */
function updateQuantity(id, change) {
    let item = cart.find(p => p.id === id);
    if (!item) return;

    item.cantidad += change;
    if (item.cantidad <= 0) {
        removeFromCart(id);
        return;
    }

    renderCart();
}

/* ============================================================
    RENDERIZAR CARRITO
============================================================ */
function renderCart() {
    cartItems.innerHTML = "";

    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="cart-empty">
                <p>Tu carrito está vacío</p>
                <p style="font-size:48px;margin-top:20px;">🛍️</p>
            </div>`;
        cartTotal.textContent = "$0";
        cartCount.textContent = "0";
        if (checkoutBtn) checkoutBtn.disabled = true;
        return;
    }

    let total = 0;

    cart.forEach(item => {
        let subtotal = item.precio * item.cantidad;
        total += subtotal;

        cartItems.innerHTML += `
            <div class="cart-item">
                <img src="${item.imagen}" class="cart-item-img">
                <div class="cart-info">
                    <strong>${item.nombre}</strong>
                    <p>$${item.precio.toLocaleString()}</p>
                    <div class="qty-controls">
                        <button onclick="updateQuantity(${item.id}, -1)">−</button>
                        <span>${item.cantidad}</span>
                        <button onclick="updateQuantity(${item.id}, 1)">+</button>
                    </div>
                </div>
                <button class="remove-btn" onclick="removeFromCart(${item.id})">🗑</button>
            </div>`;
    });

    cartTotal.textContent = "$" + total.toLocaleString();
    cartCount.textContent = String(cart.length);
    if (checkoutBtn) checkoutBtn.disabled = false;
}

/* ============================================================
    CHECKOUT → ABRIR MODAL DE DATOS
============================================================ */
function checkout() {
    if (cart.length === 0) return;

    const modal = document.getElementById("checkoutModal");
    if (modal) {
        modal.style.display = "flex";
    }
}

/* ============================================================
    CERRAR MODAL DE DATOS
============================================================ */
function cerrarModal() {
    const modal = document.getElementById("checkoutModal");
    if (modal) {
        modal.style.display = "none";
    }
}

/* ============================================================
    MODAL DE INSTRUCCIONES DE PAGO
============================================================ */
function mostrarPaymentModal(metodo, total) {
    const modal   = document.getElementById("paymentModal");
    const totalEl = document.getElementById("paymentTotal");
    const cont    = document.getElementById("paymentInstructions");

    if (!modal || !totalEl || !cont) return;

    const totalFormato = "$" + total.toLocaleString();
    totalEl.textContent = totalFormato;

    let html = "";
    const m = metodo.toLowerCase();

    if (m === "nequi") {
        html = `
            <div class="pago-instruccion-box">
                <strong>Pago por Nequi</strong>
                <p>Envía el valor total a nuestro número Nequi:</p>
                <p><strong>300 123 45 67</strong></p>
                <p>En la referencia escribe tu nombre completo o cédula.</p>
                <p>Envía el comprobante a: <strong>pagos@mihieribal.com</strong>.</p>
            </div>`;
    } else if (m === "transferencia bancaria") {
        html = `
            <div class="pago-instruccion-box">
                <strong>Transferencia bancaria</strong>
                <p>Realiza una transferencia a:</p>
                <p><strong>Banco:</strong> Bancolombia | <strong>Ahorros</strong></p>
                <p><strong>Número:</strong> 1234 5678 9012</p>
                <p><strong>Titular:</strong> Mi Hieribal SAS</p>
            </div>`;
    } else if (m === "contra entrega") {
        html = `
            <div class="pago-instruccion-box">
                <strong>Pago contra entrega</strong>
                <p>Pagarás en efectivo al recibir tu pedido.</p>
                <p>Total a entregar: <strong>${totalFormato}</strong></p>
            </div>`;
    } else {
        html = `<div class="pago-instruccion-box"><p>Método: <strong>${metodo}</strong>. Nos contactaremos pronto.</p></div>`;
    }

    cont.innerHTML = html;
    modal.style.display = "flex";
}

function cerrarPaymentModal() {
    const modal = document.getElementById("paymentModal");
    if (modal) {
        modal.style.display = "none";
        // Recargar para actualizar vista si es necesario
        location.reload();
    }
}

/* ============================================================
    ENVÍO DEL PEDIDO Y DESCUENTO DE INVENTARIO
============================================================ */
const checkoutForm = document.getElementById("checkoutForm");
const telInput = document.getElementById("telefono");

if (telInput) {
    telInput.addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 10);
    });
}

if (checkoutForm) {
    checkoutForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const telefono  = document.getElementById("telefono").value.trim();
        const direccion = document.getElementById("direccion").value.trim();
        const pago      = document.getElementById("pago").value.trim();
        const notas     = document.getElementById("notas").value.trim();

        if (telefono.length !== 10) {
            alert("El teléfono debe tener 10 dígitos.");
            return;
        }
        if (!direccion || !pago) {
            alert("Completa todos los campos obligatorios.");
            return;
        }

        const payload = { items: cart, telefono, direccion, pago, notas };
        const totalCalculado = cart.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);

        try {
            const res = await fetch("?r=admin_carrito_guardar", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (data.success) {
                // ✔ ÉXITO: El inventario ya se descontó en el servidor
                cerrarModal();
                cart = []; // Limpiamos el array
                renderCart(); // Actualizamos la vista del carrito (quedará vacío)
                mostrarPaymentModal(pago, totalCalculado);
            } else {
                alert("Error: " + (data.msg || "No se pudo procesar el pedido"));
            }
        } catch (error) {
            console.error("Error:", error);
            alert("Hubo un problema con la conexión al servidor.");
        }
    });
}

/* ============================================================
    EVENTOS DE BUSCADOR Y CATEGORÍAS
============================================================ */
const searchInput = document.getElementById("searchInput");
if (searchInput) {
    searchInput.addEventListener("input", function () {
        let term = this.value.toLowerCase();
        let sections = document.querySelectorAll(".category-section");
        sections.forEach(sec => {
            let productos = sec.querySelectorAll(".product-card");
            let visible = false;
            productos.forEach(card => {
                let nombre = card.querySelector("h3").textContent.toLowerCase();
                if (nombre.includes(term)) {
                    card.style.display = "block";
                    visible = true;
                } else {
                    card.style.display = "none";
                }
            });
            sec.style.display = visible ? "block" : "none";
        });
    });
}

const catButtons = document.querySelectorAll(".cat-btn");
catButtons.forEach(btn => {
    btn.addEventListener("click", () => {
        let cat = btn.dataset.category;
        catButtons.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        if (cat === "all") {
            window.scrollTo({ top: 0, behavior: "smooth" });
        } else {
            let section = document.querySelector(`section[data-category="${cat}"]`);
            if (section) section.scrollIntoView({ behavior: "smooth" });
        }
    });
});

/* Cierre de modales al hacer clic fuera */
window.onclick = function(event) {
    const checkoutModal = document.getElementById("checkoutModal");
    const paymentModal = document.getElementById("paymentModal");
    if (event.target == checkoutModal) cerrarModal();
    if (event.target == paymentModal) cerrarPaymentModal();
}