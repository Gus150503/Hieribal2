<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
function resolverImagen(?string $img, string $base): string
{
    // Si viene null o vacío → usar una imagen por defecto
    if ($img === null || trim($img) === '') {
        return $base . "/assets/img/no-image.png";
    }

    $img = trim($img);

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
     <button class="btn-comprar-verde" onclick="addToCart(<?= $prod['id'] ?>, '<?= addslashes($prod['nombre']) ?>', <?= $prod['precio_venta'] ?>, '<?= $imgFinal ?>')">
    🛒 Añadir al carrito
</button>

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


<div id="checkoutModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <h2>Finalizar Pedido</h2>

        <form id="checkoutForm">

            <label>Nombre:</label>
            <input type="text"
                value="<?= $_SESSION['cliente']['nombres'] ?? '' ?>"
                readonly>

            <label>Correo:</label>
            <input type="email"
                value="<?= $_SESSION['cliente']['correo'] ?? '' ?>"
                readonly>

            <label>Teléfono:</label>
            <input type="tel"
                id="telefono"
                required
                maxlength="10"
                placeholder="Ingresa tu número (10 dígitos)">

            <label>Dirección de envío:</label>
            <input type="text"
                id="direccion"
                required
                placeholder="Ingresa tu dirección completa">

            <label>Método de pago:</label>
            <select id="pago" required>
                <option value="">Seleccione...</option>
                <option value="Contra entrega">Contra entrega</option>
                <option value="Nequi">Nequi</option>
                <option value="Transferencia bancaria">Transferencia bancaria</option>
            </select>

            <label>Notas adicionales:</label>
            <textarea id="notas" placeholder="Opcional"></textarea>

            <div class="modal-buttons">
                <button type="button" onclick="cerrarModal()">Cancelar</button>
                <button type="submit">Confirmar pedido</button>
            </div>
        </form>
    </div>
</div>

<!-- ================================
     MODAL DE INSTRUCCIONES DE PAGO
================================ -->
<div id="paymentModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <h2>✅ Pedido registrado</h2>

        <p>Gracias por tu compra. A continuación encontrarás las instrucciones para completar el pago.</p>

        <div class="pago-total">
            <span>Total a pagar:</span>
            <strong id="paymentTotal"></strong>
        </div>

        <div id="paymentInstructions"></div>

        <div class="modal-buttons">
            <button type="button" onclick="cerrarPaymentModal()">Cerrar</button>
        </div>
    </div>
</div>


<script src="<?= $base ?>/assets/js/carrito.js?v=7"></script>