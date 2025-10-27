<?php $base = $this->config['app']['base_url']; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Hieribal - Tienda</title>
  
  <!-- CSS -->
  <link rel="stylesheet" href="<?= $base ?>/assets/css/carrito.css">
</head>
<body>

  <!-- Header -->
  <header class="header">
    <div class="header-content">
      <div class="logo">
        🌿 MI<span>HIERIBAL</span>
      </div>
      <div class="profile-mini">
        <img src="<?= $base ?>/assets/img/Avatar.jfif" alt="Usuario">
        <div>
          <div style="font-weight: 600;">Jose Perez</div>
          <div style="font-size: 13px; opacity: 0.9;">jose@gmail.com</div>
        </div>
      </div>
    </div>
  </header>

  <!-- Buscador y categorías -->
  <section class="search-section">
    <div class="search-container">
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Buscar productos...">
        <button>🔍</button>
      </div>
      
      <div class="categories">
        <button class="cat-btn active" data-category="all">✨ Todos</button>
        <button class="cat-btn" data-category="proteinas">🌱 Proteínas</button>
        <button class="cat-btn" data-category="tes">🍵 Tés y Aromáticas</button>
        <button class="cat-btn" data-category="vitaminas">💊 Vitaminas</button>
        <button class="cat-btn" data-category="organicos">🥗 Orgánicos</button>
        <button class="cat-btn" data-category="mieles">🍯 Mieles</button>
        <button class="cat-btn" data-category="cereales">🌾 Cereales</button>
      </div>
    </div>
  </section>

  <!-- Contenedor principal -->
  <div class="main-container">
    <!-- Productos por categoría -->
    <div class="products-container">
      
      <!-- ================================
           PROTEÍNAS
      ================================== -->
      <section class="category-section" id="proteinas">
        <div class="category-header">
          <span class="emoji">🌱</span>
          <h2>Proteínas</h2>
        </div>
        <div class="products-grid">
          
          <div class="product-card" data-category="proteinas">
            <img src="<?= $base ?>/assets/img/gym1.png" alt="Proteina Whey Amarilla" class="product-image">
            <h3>Proteína Whey Amarilla</h3>
            <div class="product-price">$80.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(1, 'Proteína Whey Amarilla', 80000, '<?= $base ?>/assets/img/gym1.png')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="proteinas">
            <img src="<?= $base ?>/assets/img/gym2.jfif" alt="Proteina Whey Premium" class="product-image">
            <h3>Proteína Whey Premium</h3>
            <div class="product-price">$120.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(2, 'Proteína Whey Premium', 120000, '<?= $base ?>/assets/img/gym2.jfif')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="proteinas">
            <img src="<?= $base ?>/assets/img/gym3.jfif" alt="Proteina Clasica" class="product-image">
            <h3>Proteína Clásica</h3>
            <div class="product-price">$90.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(3, 'Proteína Clásica', 90000, '<?= $base ?>/assets/img/gym3.jfif')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="proteinas">
            <img src="<?= $base ?>/assets/img/gym4.jfif" alt="Combo de Proteinas" class="product-image">
            <h3>Combo de Proteínas</h3>
            <div class="product-price">$200.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(4, 'Combo de Proteínas', 200000, '<?= $base ?>/assets/img/gym4.jfif')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="proteinas">
            <img src="<?= $base ?>/assets/img/gym5.jfif" alt="Proteina de Fresa" class="product-image">
            <h3>Proteína de Fresa</h3>
            <div class="product-price">$95.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(5, 'Proteína de Fresa', 95000, '<?= $base ?>/assets/img/gym5.jfif')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="proteinas">
            <img src="<?= $base ?>/assets/img/gym6.jfif" alt="Proteina Whey Iso" class="product-image">
            <h3>Proteína Whey Iso</h3>
            <div class="product-price">$110.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(6, 'Proteína Whey Iso', 110000, '<?= $base ?>/assets/img/gym6.jfif')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="proteinas">
            <img src="<?= $base ?>/assets/img/gym7.jfif" alt="Proteina sabor vainilla" class="product-image">
            <h3>Proteína Sabor Vainilla</h3>
            <div class="product-price">$40.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(7, 'Proteína Sabor Vainilla', 40000, '<?= $base ?>/assets/img/gym7.jfif')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="proteinas">
            <img src="<?= $base ?>/assets/img/gym8.jfif" alt="Proteina Total" class="product-image">
            <h3>Proteína Total</h3>
            <div class="product-price">$80.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(8, 'Proteína Total', 80000, '<?= $base ?>/assets/img/gym8.jfif')">
              🛒 Añadir al carrito
            </button>
          </div>

        </div>
      </section>

      <!-- ================================
           TÉS Y AROMÁTICAS
      ================================== -->
      <section class="category-section" id="tes">
        <div class="category-header">
          <span class="emoji">🍵</span>
          <h2>Tés y Aromáticas</h2>
        </div>
        <div class="products-grid">
          
          <div class="product-card" data-category="tes">
            <img src="https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Té+Verde" alt="Té Verde" class="product-image">
            <h3>Té Verde Orgánico</h3>
            <div class="product-price">$25.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(101, 'Té Verde Orgánico', 25000, 'https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Té+Verde')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="tes">
            <img src="https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Té+Negro" alt="Té Negro" class="product-image">
            <h3>Té Negro Premium</h3>
            <div class="product-price">$28.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(102, 'Té Negro Premium', 28000, 'https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Té+Negro')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="tes">
            <img src="https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Manzanilla" alt="Manzanilla" class="product-image">
            <h3>Manzanilla Natural</h3>
            <div class="product-price">$18.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(103, 'Manzanilla Natural', 18000, 'https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Manzanilla')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="tes">
            <img src="https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Té+Rojo" alt="Té Rojo" class="product-image">
            <h3>Té Rojo Pu-erh</h3>
            <div class="product-price">$32.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(104, 'Té Rojo Pu-erh', 32000, 'https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Té+Rojo')">
              🛒 Añadir al carrito
            </button>
          </div>

        </div>
      </section>

      <!-- ================================
           VITAMINAS
      ================================== -->
      <section class="category-section" id="vitaminas">
        <div class="category-header">
          <span class="emoji">💊</span>
          <h2>Vitaminas</h2>
        </div>
        <div class="products-grid">
          
          <div class="product-card" data-category="vitaminas">
            <img src="https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Vitamina+C" alt="Vitamina C" class="product-image">
            <h3>Vitamina C 1000mg</h3>
            <div class="product-price">$35.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(201, 'Vitamina C 1000mg', 35000, 'https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Vitamina+C')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="vitaminas">
            <img src="https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Complejo+B" alt="Complejo B" class="product-image">
            <h3>Complejo B12</h3>
            <div class="product-price">$42.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(202, 'Complejo B12', 42000, 'https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Complejo+B')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="vitaminas">
            <img src="https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Vitamina+D3" alt="Vitamina D3" class="product-image">
            <h3>Vitamina D3 5000 IU</h3>
            <div class="product-price">$38.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(203, 'Vitamina D3 5000 IU', 38000, 'https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Vitamina+D3')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="vitaminas">
            <img src="https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Multivitamínico" alt="Multivitamínico" class="product-image">
            <h3>Multivitamínico Completo</h3>
            <div class="product-price">$55.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(204, 'Multivitamínico Completo', 55000, 'https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Multivitamínico')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="vitaminas">
            <img src="https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Omega+3" alt="Omega 3" class="product-image">
            <h3>Omega 3 Fish Oil</h3>
            <div class="product-price">$48.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(205, 'Omega 3 Fish Oil', 48000, 'https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Omega+3')">
              🛒 Añadir al carrito
            </button>
          </div>

        </div>
      </section>

      <!-- ================================
           ORGÁNICOS
      ================================== -->
      <section class="category-section" id="organicos">
        <div class="category-header">
          <span class="emoji">🥗</span>
          <h2>Orgánicos</h2>
        </div>
        <div class="products-grid">
          
          <div class="product-card" data-category="organicos">
            <img src="https://via.placeholder.com/300x220/6FBC43/FFFFFF?text=Quinoa" alt="Quinoa" class="product-image">
            <h3>Quinoa Orgánica 500g</h3>
            <div class="product-price">$22.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(301, 'Quinoa Orgánica 500g', 22000, 'https://via.placeholder.com/300x220/6FBC43/FFFFFF?text=Quinoa')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="organicos">
            <img src="https://via.placeholder.com/300x220/6FBC43/FFFFFF?text=Chía" alt="Semillas de Chía" class="product-image">
            <h3>Semillas de Chía Orgánicas</h3>
            <div class="product-price">$18.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(302, 'Semillas de Chía Orgánicas', 18000, 'https://via.placeholder.com/300x220/6FBC43/FFFFFF?text=Chía')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="organicos">
            <img src="https://via.placeholder.com/300x220/6FBC43/FFFFFF?text=Linaza" alt="Linaza" class="product-image">
            <h3>Linaza Dorada Orgánica</h3>
            <div class="product-price">$15.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(303, 'Linaza Dorada Orgánica', 15000, 'https://via.placeholder.com/300x220/6FBC43/FFFFFF?text=Linaza')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="organicos">
            <img src="https://via.placeholder.com/300x220/6FBC43/FFFFFF?text=Spirulina" alt="Spirulina" class="product-image">
            <h3>Spirulina en Polvo 250g</h3>
            <div class="product-price">$45.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(304, 'Spirulina en Polvo 250g', 45000, 'https://via.placeholder.com/300x220/6FBC43/FFFFFF?text=Spirulina')">
              🛒 Añadir al carrito
            </button>
          </div>

        </div>
      </section>

      <!-- ================================
           MIELES
      ================================== -->
      <section class="category-section" id="mieles">
        <div class="category-header">
          <span class="emoji">🍯</span>
          <h2>Mieles</h2>
        </div>
        <div class="products-grid">
          
          <div class="product-card" data-category="mieles">
            <img src="https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Miel+Pura" alt="Miel Pura" class="product-image">
            <h3>Miel de Abeja Pura 500g</h3>
            <div class="product-price">$28.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(401, 'Miel de Abeja Pura 500g', 28000, 'https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Miel+Pura')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="mieles">
            <img src="https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Miel+Orgánica" alt="Miel Orgánica" class="product-image">
            <h3>Miel Orgánica Premium</h3>
            <div class="product-price">$35.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(402, 'Miel Orgánica Premium', 35000, 'https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Miel+Orgánica')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="mieles">
            <img src="https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Miel+Manuka" alt="Miel Manuka" class="product-image">
            <h3>Miel de Manuka UMF 15+</h3>
            <div class="product-price">$85.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(403, 'Miel de Manuka UMF 15+', 85000, 'https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Miel+Manuka')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="mieles">
            <img src="https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Propóleo" alt="Propóleo" class="product-image">
            <h3>Propóleo Natural 30ml</h3>
            <div class="product-price">$32.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(404, 'Propóleo Natural 30ml', 32000, 'https://via.placeholder.com/300x220/F6A30D/FFFFFF?text=Propóleo')">
              🛒 Añadir al carrito
            </button>
          </div>

        </div>
      </section>

      <!-- ================================
           CEREALES
      ================================== -->
      <section class="category-section" id="cereales">
        <div class="category-header">
          <span class="emoji">🌾</span>
          <h2>Cereales</h2>
        </div>
        <div class="products-grid">
          
          <div class="product-card" data-category="cereales">
            <img src="https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Avena" alt="Avena" class="product-image">
            <h3>Avena en Hojuelas 1kg</h3>
            <div class="product-price">$12.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(501, 'Avena en Hojuelas 1kg', 12000, 'https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Avena')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="cereales">
            <img src="https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Granola" alt="Granola" class="product-image">
            <h3>Granola Artesanal 500g</h3>
            <div class="product-price">$20.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(502, 'Granola Artesanal 500g', 20000, 'https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Granola')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="cereales">
            <img src="https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Amaranto" alt="Amaranto" class="product-image">
            <h3>Amaranto Inflado 300g</h3>
            <div class="product-price">$16.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(503, 'Amaranto Inflado 300g', 16000, 'https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Amaranto')">
              🛒 Añadir al carrito
            </button>
          </div>

          <div class="product-card" data-category="cereales">
            <img src="https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Mix+Cereales" alt="Mix Cereales" class="product-image">
            <h3>Mix de Cereales Integrales</h3>
            <div class="product-price">$25.000</div>
            <button class="add-to-cart-btn" onclick="addToCart(504, 'Mix de Cereales Integrales', 25000, 'https://via.placeholder.com/300x220/8B572A/FFFFFF?text=Mix+Cereales')">
              🛒 Añadir al carrito
            </button>
          </div>

        </div>
      </section>

    </div>

    <!-- ================================
      CARRITO LATERAL
    ================================== -->
    <aside class="cart-sidebar">
      <div class="cart-header">
        <h2>🛒 Carrito</h2>
        <span class="cart-count" id="cartCount">0</span>
      </div>

      <div class="cart-items" id="cartItems">
        <div class="cart-empty">
          <p>Tu carrito está vacío</p>
          <p style="font-size: 48px; margin-top: 20px;">🛍️</p>
        </div>
      </div>

      <div class="cart-total">
        <div class="cart-total-label">Total:</div>
        <div class="cart-total-amount" id="cartTotal">$0</div>
      </div>

      <button class="checkout-btn" id="checkoutBtn" onclick="checkout()" disabled>
        Finalizar Compra
      </button>
    </aside>
  </div>

  <!-- JavaScript -->
  <script src="<?= $base ?>/assets/js/carrito.js"></script>
</body>
</html>