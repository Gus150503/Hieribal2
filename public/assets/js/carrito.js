let cart = [];

    // Añadir al carrito
    function addToCart(id, name, price, image) {
      let product = cart.find(item => item.id === id);
      if (product) {
        product.quantity += 1;
      } else {
        cart.push({ id, name, price, image, quantity: 1 });
      }
      renderCart();
      
      // Animación visual
      event.target.innerHTML = '✓ Añadido';
      setTimeout(() => {
        event.target.innerHTML = '🛒 Añadir al carrito';
      }, 1000);
    }

    // Renderizar carrito
    function renderCart() {
      const cartItems = document.getElementById('cartItems');
      const cartCount = document.getElementById('cartCount');
      const cartTotal = document.getElementById('cartTotal');
      const checkoutBtn = document.getElementById('checkoutBtn');

      if (cart.length === 0) {
        cartItems.innerHTML = `
          <div class="cart-empty">
            <p>Tu carrito está vacío</p>
            <p style="font-size: 48px; margin-top: 20px;">🛍️</p>
          </div>
        `;
        cartCount.textContent = '0';
        cartTotal.textContent = '$0';
        checkoutBtn.disabled = true;
        return;
      }

      let totalItems = 0;
      let totalPrice = 0;

      cartItems.innerHTML = cart.map((product, index) => {
        totalItems += product.quantity;
        totalPrice += product.price * product.quantity;
        
        return `
          <div class="cart-item">
            <img src="${product.image}" alt="${product.name}">
            <div class="cart-item-info">
              <div class="cart-item-name">${product.name}</div>
              <div class="cart-item-price">$${(product.price * product.quantity).toLocaleString()} (x${product.quantity})</div>
            </div>
            <button class="remove-btn" onclick="removeFromCart(${index})">✕</button>
          </div>
        `;
      }).join('');

      cartCount.textContent = totalItems;
      cartTotal.textContent = `$${totalPrice.toLocaleString()}`;
      checkoutBtn.disabled = false;
    }

    // Eliminar del carrito
    function removeFromCart(index) {
      cart.splice(index, 1);
      renderCart();
    }

    // Finalizar compra
    function checkout() {
      if (cart.length === 0) {
        alert('Tu carrito está vacío');
        return;
      }

      let resumen = '🛒 RESUMEN DE TU COMPRA\n\n';
      let total = 0;
      
      cart.forEach(p => {
        const subtotal = p.price * p.quantity;
        total += subtotal;
        resumen += `${p.name}\n  Cantidad: ${p.quantity}\n  Subtotal: $${subtotal.toLocaleString()}\n\n`;
      });
      
      resumen += `━━━━━━━━━━━━━━━━\nTOTAL: $${total.toLocaleString()}`;
      
      alert(resumen);
      
      cart = [];
      renderCart();
    }

    // Scroll suave a categorías
    document.querySelectorAll('.cat-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const category = this.dataset.category;
        
        // Activar botón
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Scroll a la categoría
        if (category !== 'all') {
          const section = document.getElementById(category);
          if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        } else {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      });
    });

    // Buscador
    document.getElementById('searchInput').addEventListener('input', function(e) {
      const searchTerm = e.target.value.toLowerCase();
      const products = document.querySelectorAll('.product-card');
      
      products.forEach(product => {
        const name = product.querySelector('h3').textContent.toLowerCase();
        if (name.includes(searchTerm)) {
          product.style.display = 'flex';
        } else {
          product.style.display = 'none';
        }
      });
    });