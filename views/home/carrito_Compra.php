<?php
$base = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');
?>

<!-- ========================================
    BUSCADOR Y FILTROS DE CATEGORÍAS
========================================= -->
<section class="search-section">
    <div class="search-container">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Buscar productos...">
            <button type="button">🔍</button>
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

<!-- ========================================
     CONTENEDOR PRINCIPAL (PRODUCTOS + CARRITO)
========================================= -->
<div class="main-container">

    <!-- COLUMNA DE PRODUCTOS -->
    <div class="products-container">

        <!-- PROTEÍNAS -->
        <section class="category-section" id="proteinas">
            <div class="category-header">
                <span class="emoji">🌱</span>
                <h2>Proteínas</h2>
            </div>
            <div class="products-grid">

                <div class="product-card">
                    <img src="<?= $base ?>/assets/img/gym1.png" alt="Proteína Whey Amarilla" class="product-image">
                    <h3>Proteína Whey Amarilla</h3>
                    <div class="product-price">$80.000</div>
                    <button class="add-to-cart-btn"
                        onclick="addToCart(1, 'Proteína Whey Amarilla', 80000, '<?= $base ?>/assets/img/gym1.png')">
                        🛒 Añadir al carrito
                    </button>
                </div>

                <!-- ... TODO el resto de tus product-card tal cual los tienes ... -->

            </div>
        </section>

        <!-- ... TODAS LAS DEMÁS SECCIONES (tés, vitaminas, orgánicos, etc.) IGUAL ... -->

    </div>

    <!-- ========================================
         CARRITO DE COMPRAS (SIDEBAR)
    ========================================= -->
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

<!-- Si tu layout NO inyecta JS extra y quieres cargarlo aquí: -->
<script src="<?= htmlspecialchars($base) ?>/assets/js/carrito.js"></script>
