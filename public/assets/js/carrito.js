const cartList = document.getElementById('cart-list');
const totalPriceEl = document.getElementById('total-price');
const avatar = document.getElementById('avatar');
const upload = document.getElementById('upload');

// Elementos para vista previa
const previewBox = document.getElementById('product-preview');
const previewName = document.getElementById('preview-name');
const previewImg = document.getElementById('preview-img');
const previewPrice = document.getElementById('preview-price');

let cart = [];

// Agregar producto al carrito y mostrar preview
function addToCart(id, name, price, image) {
cart.push({
    id,
    name,
    price,
    image
});
renderCart();
showPreview(name, price, image);
}

// Mostrar los productos en el carrito
function renderCart() {
cartList.innerHTML = '';
let total = 0;

if (cart.length === 0) {
    cartList.innerHTML = '<li>No hay productos en el carrito.</li>';
    totalPriceEl.textContent = '0';
    return;
}

cart.forEach((product, index) => {
    const li = document.createElement('li');
    li.style.display = 'flex';
    li.style.alignItems = 'center';
    li.style.marginBottom = '10px';
    li.style.gap = '10px';

    // Imagen
    const img = document.createElement('img');
    img.src = product.image;
    img.alt = product.name;
    img.style.width = '50px';
    img.style.height = '50px';
    img.style.objectFit = 'cover';
    img.style.borderRadius = '6px';

    // Texto
    const text = document.createElement('span');
    text.textContent = `${product.name} - $${product.price.toLocaleString()}`;

    // Botón eliminar
    const deleteBtn = document.createElement('button');
    deleteBtn.textContent = '✕';
    deleteBtn.style.background = '#e74c3c';
    deleteBtn.style.border = 'none';
    deleteBtn.style.color = 'white';
    deleteBtn.style.padding = '5px 10px';
    deleteBtn.style.borderRadius = '5px';
    deleteBtn.style.cursor = 'pointer';
    deleteBtn.onclick = () => removeFromCart(index);

    li.appendChild(img);
    li.appendChild(text);
    li.appendChild(deleteBtn);
    cartList.appendChild(li);
    total += product.price;
});

totalPriceEl.textContent = total.toLocaleString();
}

function removeFromCart(index) {
cart.splice(index, 1);
renderCart();
}

function checkout() {
if (cart.length === 0) {
    alert("Tu carrito está vacío.");
    return;
}

let resumen = "Resumen de tu compra:\n\n";
let total = 0;

cart.forEach((item, index) => {
    resumen += `${index + 1}. ${item.name} - $${item.price.toLocaleString()}\n`;
    total += item.price;
});

resumen += `\nTotal a pagar: $${total.toLocaleString()}`;

  // Confirmación
const confirmar = confirm(resumen + "\n\n¿Deseas finalizar la compra?");

if (confirmar) {
    alert("¡Gracias por tu compra!");
    cart = []; // Vaciar el carrito
    renderCart(); // Actualizar la vista del carrito
    previewBox.style.display = 'none'; // Ocultar vista previa
}
}