<?php
/**
 * --- CONFIGURACIÓN DE ERRORES Y RUTAS ---
 * Se activan los errores para poder ver qué falla durante el desarrollo.
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definimos la URL base para cargar imágenes o scripts (ej: http://tusitio.com)
$base = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');

// Si la variable $productos no viene de la base de datos, la inicializamos vacía
$productos = $productos ?? [];

/**
 * --- LÓGICA DE CATEGORÍAS ---
 * Aquí organizamos los productos. En lugar de una lista plana, 
 * creamos un array donde cada "llave" es el nombre de una categoría.
 */
$categorias = [];
foreach ($productos as $p) {
    // Normalizamos el nombre de la categoría (minúsculas y sin espacios extra)
    $cat = strtolower(trim($p['categoria'] ?? 'otros'));
    
    // Si la categoría no existe en nuestro nuevo array, la creamos como una lista vacía
    if (!isset($categorias[$cat])) {
        $categorias[$cat] = [];
    }
    // Agregamos el producto a su categoría correspondiente
    $categorias[$cat][] = $p;
}

/**
 * --- FUNCIÓN AUXILIAR PARA IMÁGENES ---
 * Verifica si el producto tiene imagen. Si no, pone una por defecto.
 * Si es una URL externa la deja igual, si es un nombre de archivo busca en la carpeta local.
 */
function resolverImagen(?string $img, string $base): string
{
    // Caso 1: No hay imagen definida
    if ($img === null || trim($img) === '') {
        return $base . "/assets/img/no-image.png";
    }

    $img = trim($img);

    // Caso 2: Es una ruta local (no empieza con http)
    if (!filter_var($img, FILTER_VALIDATE_URL)) {
        return $base . "/assets/img/" . ltrim($img, '/');
    }

    // Caso 3: Es una URL completa de internet
    return $img;
}
?>

<section class="search-section">
    <div class="search-container">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Buscar productos...">
            <button type="button">🔍</button>
        </div>

        <div class="categories">
            <button class="cat-btn active" data-category="all">✨ Todos</button>
            <?php foreach ($categorias as $catNombre => $items): ?>
                <button class="cat-btn" data-category="<?= $catNombre ?>">
                    <?= ucfirst($catNombre) ?> </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

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
                        <?php $imgFinal = resolverImagen($prod['imagen'], $base); ?>

                        <div class="product-card">
                            <img src="<?= $imgFinal ?>" 
                                 alt="<?= htmlspecialchars($prod['nombre']) ?>" 
                                 class="product-image"
                                 onerror="this.src='<?= $base ?>/assets/img/no-image.png'">

                            <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
                            
                            <div class="product-price">
                                $<?= number_format($prod['precio_venta'], 0, ',', '.') ?>
                            </div>

                            <button class="btn-comprar-verde" onclick="addToCart(<?= $prod['id'] ?>, '<?= addslashes($prod['nombre']) ?>', <?= $prod['precio_venta'] ?>, '<?= $imgFinal ?>')">
                                🛒 Añadir al carrito
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <aside class="cart-sidebar">
        <div class="cart-header">
            <h2>🛒 Carrito</h2>
            <span id="cartCount">0</span> </div>

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

        <button class="btn-comprar-verde" id="checkoutBtn" onclick="checkout()" disabled>
            Finalizar Compra
        </button>
    </aside>
</div>

<div id="checkoutModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <h2>Finalizar Pedido</h2>

        <form id="checkoutForm">
            <label>Nombre:</label>
            <input type="text" value="<?= $_SESSION['cliente']['nombres'] ?? '' ?>" readonly>

            <label>Correo:</label>
            <input type="email" value="<?= $_SESSION['cliente']['correo'] ?? '' ?>" readonly>

            <label>Teléfono:</label>
            <input type="tel" id="telefono" required maxlength="10" placeholder="Ingresa tu número (10 dígitos)">

            <label>Dirección de envío:</label>
            <input type="text" id="direccion" required placeholder="Ingresa tu dirección completa">

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
                <button type="button" class="btn-cancelar-gris" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-comprar-verde">Confirmar pedido</button>
            </div>
        </form>
    </div>
</div>

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
            <button type="button" class="btn-comprar-verde" onclick="cerrarPaymentModal()">Cerrar</button>
        </div>
    </div>
</div>

<script src="<?= $base ?>/assets/js/carrito.js?v=7"></script>