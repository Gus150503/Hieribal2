const cartList = document.getElementById("cart-list");
const totalPriceEl = document.getElementById("total-price");
let cart = [];

// Agregar producto al carrito
function addToCart(id, name, price, image) {
  // Si ya existe, aumenta cantidad
let product = cart.find(item => item.id === id);
if (product) {
    product.quantity += 1;
} else {
    cart.push({ id, name, price, image, quantity: 1 });
}
renderCart();
}

// Mostrar carrito
function renderCart() {
cartList.innerHTML = "";
let total = 0;

if (cart.length === 0) {
    cartList.innerHTML = "<li>No hay productos en el carrito.</li>";
    totalPriceEl.textContent = "0";
    return;
}

cart.forEach((product, index) => {
    const li = document.createElement("li");
    li.innerHTML = `
    <img src="${product.image}" alt="${product.name}" style="width:40px; height:40px; border-radius:5px; margin-right:10px;">
      ${product.name} (x${product.quantity}) - $${(product.price * product.quantity).toLocaleString()}
    <button onclick="removeFromCart(${index})">✕</button>
    `;
    cartList.appendChild(li);
    total += product.price * product.quantity;
});

totalPriceEl.textContent = total.toLocaleString();
}

// Eliminar producto
function removeFromCart(index) {
cart.splice(index, 1);
renderCart();
}

// Comprar
function checkout() {
if (cart.length === 0) {
    alert("Tu carrito está vacío.");
    return;
}

let resumen = "Resumen de tu compra:\n\n";
cart.forEach(p => {
    resumen += `${p.name} (x${p.quantity}) - $${(p.price * p.quantity).toLocaleString()}\n`;
});
resumen += `\nTotal: $${totalPriceEl.textContent}`;
alert(resumen);

  // Vaciar carrito
cart = [];
renderCart();
}

