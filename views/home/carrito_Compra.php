<?php
$base = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');
$productos = $productos ?? [];

/* =======================================
   Construir categorías dinámicas
======================================= */
$categorias = [];

foreach ($productos as $p) {
    $cat = strtolower(trim($p['categoria'] ?? 'otros'));
    if (!isset($categorias[$cat])) {
        $categorias[$cat] = [];
    }
    $categorias[$cat][] = $p;
}

/* =======================================
   Función para resolver imágenes
======================================= */
function resolverImagen(string $img, string $base): string {
    // Si NO es URL → generar ruta normal
    if (!filter_var($img, FILTER_VALIDATE_URL)) {
        return $base . "/assets/img/" . ltrim($img, '/');
    }
    // Si ya es URL → devolverla
    return $img;
}
?>
<!-- ============================
          BUSCADOR + CATEGORÍAS
============================= -->
<section class="search-section">
    <div class="search-container">

        <!-- Buscador -->
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Buscar productos...">
            <button type="button">🔍</button>
        </div>

        <!-- Categorías dinámicas -->
        <div class="categories">
            <button class="cat-btn active" data-category="all">✨ Todos</button>

            <?php foreach ($categorias as $catNombre => $items): ?>
                <button class="cat-btn" data-category="<?= $catNombre ?>">
                    <?= ucfirst($catNombre) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================
        CONTENEDOR PRINCIPAL
============================= -->
<div class="main-container">

    <div class="products-container">

        <?php foreach ($categorias as $catNombre => $items): ?>
        <section class="category-section" data-category="<?= $catNombre ?>">

            <div class="category-header">
                <span class="emoji">📦</span>
                <h2><?= ucfirst($catNombre) ?></h2>
            </div>

            <div class="products-grid">

                <?php foreach ($items as $prod): ?>

                    <?php
                    // Obtener la imagen final lista para mostrar
                    $imgFinal = resolverImagen($prod['imagen'], $base);
                    ?>

                    <div class="product-card">

                        <!-- Imagen -->
                        <img src="<?= $imgFinal ?>"
                             alt="<?= htmlspecialchars($prod['nombre']) ?>"
                             class="product-image"
                             onerror="this.src='<?= $base ?>/assets/img/no-image.png'">

                        <!-- Nombre -->
                        <h3><?= htmlspecialchars($prod['nombre']) ?></h3>

                        <!-- Precio -->
                        <div class="product-price">
                            $<?= number_format($prod['precio_venta'], 0, ',', '.') ?>
                        </div>

                        <!-- Botón Carrito -->
                        <button onclick="addToCart(
                            <?= $prod['id'] ?>,
                            '<?= addslashes($prod['nombre']) ?>',
                            <?= $prod['precio_venta'] ?>,
                            '<?= $imgFinal ?>'
                        )">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                <?php endforeach; ?>

            </div>
        </section>
        <?php endforeach; ?>

    </div>

    <!-- ============================
           SIDEBAR DEL CARRITO
     =============================== -->
    <aside class="cart-sidebar">

        <div class="cart-header">
            <h2>🛒 Carrito</h2>
            <span id="cartCount">0</span>
        </div>

        <div class="cart-items" id="cartItems">
            <div class="cart-empty">
                <p>Tu carrito está vacío</p>
                <p style="font-size:48px;margin-top:20px;">🛍️</p>
            </div>
        </div>

        <div class="cart-total">
            <strong>Total:</strong>
            <span id="cartTotal">$0</span>
        </div>

        <button class="checkout-btn" id="checkoutBtn" onclick="checkout()" disabled>
            Finalizar Compra
        </button>

    </aside>

</div>

<script src="<?= $base ?>/assets/js/carrito.js"></script>
