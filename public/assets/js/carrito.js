/* ============================================================
   VARIABLES DEL CARRITO
============================================================ */
let cart = [];
const cartItems = document.getElementById("cartItems");
const cartTotal = document.getElementById("cartTotal");
const cartCount = document.getElementById("cartCount");
const checkoutBtn = document.getElementById("checkoutBtn");

/* ============================================================
   AÑADIR PRODUCTO AL CARRITO
============================================================ */
function addToCart(id, nombre, precio, imagen) {
    let item = cart.find(p => p.id === id);

    if (item) {
        item.cantidad++;
    } else {
        cart.push({
            id,
            nombre,
            precio,
            imagen,
            cantidad: 1
        });
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
            </div>
        `;

        cartTotal.textContent = "$0";
        cartCount.textContent = "0";
        checkoutBtn.disabled = true;
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
            </div>
        `;
    });

    cartTotal.textContent = "$" + total.toLocaleString();
    cartCount.textContent = cart.length;
    checkoutBtn.disabled = false;
}

/* ============================================================
   GUARDAR COMPRA EN LA BD
============================================================ */
function checkout() {
    fetch("index.php?r=carrito_admin/guardar", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(cart)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Compra registrada correctamente ✔");
            cart = [];
            renderCart();
        } else {
            alert("Error: " + data.msg);
        }
    });
}

/* ============================================================
   BUSCADOR DE PRODUCTOS
============================================================ */
const searchInput = document.getElementById("searchInput");

searchInput.addEventListener("input", function () {
    let term = this.value.toLowerCase();
    let sections = document.querySelectorAll(".category-section");

    let found = false;

    sections.forEach(sec => {
        let productos = sec.querySelectorAll(".product-card");
        let visible = false;

        productos.forEach(card => {
            let nombre = card.querySelector("h3").textContent.toLowerCase();

            if (nombre.includes(term)) {
                card.style.display = "block";
                visible = true;
                found = true;
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

/* ============================================================
   BOTONES DE CATEGORÍAS CON SCROLL
============================================================ */
const catButtons = document.querySelectorAll(".cat-btn");

catButtons.forEach(btn => {
    btn.addEventListener("click", () => {
        let cat = btn.dataset.category;

        // Resaltar botón
        catButtons.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");

        if (cat === "all") {
            window.scrollTo({ top: 0, behavior: "smooth" });
            return;
        }

        let section = document.querySelector(`section[data-category="${cat}"]`);

        if (section) {
            section.scrollIntoView({ behavior: "smooth" });
        }
    });
});
