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
        checkoutBtn.disabled  = true;
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
    checkoutBtn.disabled  = false;
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
                <p>Puedes enviar el comprobante al correo <strong>pagos@mihieribal.com</strong>.</p>
            </div>
        `;
    } else if (m === "transferencia bancaria") {
        html = `
            <div class="pago-instruccion-box">
                <strong>Transferencia bancaria</strong>
                <p>Realiza una transferencia por el valor total a la siguiente cuenta:</p>
                <p><strong>Banco:</strong> Bancolombia</p>
                <p><strong>Tipo de cuenta:</strong> Ahorros</p>
                <p><strong>Número:</strong> 1234 5678 9012</p>
                <p><strong>Titular:</strong> Mi Hieribal SAS</p>
                <p>En el concepto indica tu nombre o documento y envía el comprobante a <strong>pagos@mihieribal.com</strong>.</p>
            </div>
        `;
    } else if (m === "contra entrega") {
        html = `
            <div class="pago-instruccion-box">
                <strong>Pago contra entrega</strong>
                <p>Has elegido pagar en efectivo al recibir tu pedido.</p>
                <p>Por favor asegúrate de tener el valor exacto en efectivo:</p>
                <p><strong>${totalFormato}</strong></p>
                <p>Uno de nuestros asesores se comunicará contigo para coordinar la entrega.</p>
            </div>
        `;
    } else {
        html = `
            <div class="pago-instruccion-box">
                <strong>Método de pago registrado</strong>
                <p>Has registrado el método de pago: <strong>${metodo}</strong>.</p>
                <p>Nos comunicaremos contigo para indicarte cómo completar el pago.</p>
            </div>
        `;
    }

    cont.innerHTML = html;
    modal.style.display = "flex";
}

function cerrarPaymentModal() {
    const modal = document.getElementById("paymentModal");
    if (modal) {
        modal.style.display = "none";
    }
}

/* ============================================================
   VALIDACIÓN TELÉFONO Y ENVÍO DEL PEDIDO (FORM DEL MODAL)
============================================================ */
const checkoutForm = document.getElementById("checkoutForm");

// limitar teléfono a solo números y 10 dígitos
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
            alert("El teléfono debe tener exactamente 10 dígitos.");
            return;
        }

        if (!direccion) {
            alert("Por favor ingresa la dirección de envío.");
            return;
        }

        if (!pago) {
            alert("Por favor selecciona un método de pago.");
            return;
        }

        const payload = {
            items: cart,
            telefono,
            direccion,
            pago,
            notas
        };

        const total = cart.reduce((acc, item) => acc + item.precio * item.cantidad, 0);

        try {
            const res  = await fetch("?r=admin_carrito_guardar", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!data.success) {
                alert("Error: " + (data.msg || "No se pudo registrar el pedido"));
                return;
            }

            // ✔ Pedido guardado en BD
            cerrarModal();
            cart = [];
            renderCart();

            // Mostrar modal de instrucciones de pago
            mostrarPaymentModal(pago, total);

        } catch (error) {
            console.error("Error en checkout:", error);
            alert("Error inesperado.");
        }
    });
}

/* ============================================================
   EVENTOS DE BUSCADOR Y CATEGORÍAS
============================================================ */
const searchInput = document.getElementById("searchInput");
if (searchInput) {
    searchInput.addEventListener("input", function () {
        let term     = this.value.toLowerCase();
        let sections = document.querySelectorAll(".category-section");
        let found    = false;

        sections.forEach(sec => {
            let productos = sec.querySelectorAll(".product-card");
            let visible   = false;

            productos.forEach(card => {
                let nombre = card.querySelector("h3").textContent.toLowerCase();
                if (nombre.includes(term)) {
                    card.style.display = "block";
                    visible = true;
                    found   = true;
                } else {
                    card.style.display = "none";
                }
            });

            sec.style.display = visible ? "block" : "none";
        });

        if (!found && term.trim() !== "") {
            alert("❌ No se encontró ningún producto con ese nombre");
        }
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
            return;
        }

        let section = document.querySelector(`section[data-category="${cat}"]`);
        if (section) section.scrollIntoView({ behavior: "smooth" });
    });
});

/* botón principal de checkout */
if (checkoutBtn) {
    checkoutBtn.addEventListener("click", checkout);
}

document.addEventListener("click", function(e){
  const checkoutModal = document.getElementById("checkoutModal");
  const paymentModal  = document.getElementById("paymentModal");

  if (checkoutModal && checkoutModal.style.display === "flex" && e.target === checkoutModal) {
    cerrarModal();
  }

  if (paymentModal && paymentModal.style.display === "flex" && e.target === paymentModal) {
    cerrarPaymentModal();
  }
});