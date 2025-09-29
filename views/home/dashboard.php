<?php $base = $this->config['app']['base_url']; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Compra/Cliente</title>

  <!-- Enlace a carrito.css -->
<link rel="stylesheet" href="<?= $base ?>/assets/css/carrito.css">

</head>
<body>

  <div class="profile-section">
    <img src="../public/assets/img/Avatar.jfif" alt="Perfil">
    <h3>Jose Perez</h3>
    <p>Correo: jose@gmail.com</p>
  </div>

  <!-- Productos -->
  <div class="catalogo">
    <div class="product">
      <img src="../public/assets/img/gym1.png" alt="Proteina">
      <h3>Proteina whey Amarilla</h3>
      <p>$80.000</p>
      <button onclick="addToCart(1, 'Proteina', 80000, '../public/assets/img/gym1.png')">Añadir al carrito</button>
    </div>

    <div class="product">
      <img src="../public/assets/img/gym2.jfif" alt="Proteina Premium">
      <h3>Proteina Whey Premium</h3>
      <p>$120.000</p>
      <button onclick="addToCart(2, 'Proteina Premium', 120000, '../public/assets/img/gym2.jfif')">Añadir al carrito</button>
    </div>

    <div class="product">
      <img src="../public/assets/img/gym3.jfif" alt="Proteina Clasica">
      <h3>Proteina Clasica</h3>
      <p>$90.000</p>
      <button onclick="addToCart(3, 'Proteina Clasica', 90000, '../public/assets/img/gym3.jfif')">Añadir al carrito</button>
    </div>

    <div class="product">
      <img src="../public/assets/img/gym4.jfif" alt="Combo de Proteinas">
      <h3>Combo de Proteinas</h3>
      <p>$200.000</p>
      <button onclick="addToCart(4, 'Combo de Proteinas', 200000, '../public/assets/img/gym4.jfif')">Añadir al carrito</button>
    </div>

    <div class="product">
      <img src="../public/assets/img/gym5.jfif" alt="Proteina de Fresa">
      <h3>Proteina de Fresa</h3>
      <p>$95.000</p>
      <button onclick="addToCart(5, 'Proteina de Fresa', 95000, '../public/assets/img/gym5.jfif')">Añadir al carrito</button>
    </div>

    <div class="product">
      <img src="../public/assets/img/gym6.jfif" alt="Proteina Whey iso">
      <h3>Proteina Whey iso</h3>
      <p>$110.000</p>
      <button onclick="addToCart(6, 'Proteina Whey iso', 110000, '../public/assets/img/gym6.jfif')">Añadir al carrito</button>
    </div>

    <div class="product">
      <img src="../public/assets/img/gym7.jfif" alt="Proteina sabor vainilla">
      <h3>Proteina sabor vainilla</h3>
      <p>$40.000</p>
      <button onclick="addToCart(7, 'Proteina sabor vainilla', 40000, '../public/assets/img/gym7.jfif')">Añadir al carrito</button>
    </div>

    <div class="product">
      <img src="../public/assets/img/gym8.jfif" alt="Proteina Total">
      <h3>Proteina Total</h3>
      <p>$80.000</p>
      <button onclick="addToCart(8, 'Proteina Total', 80000, '../public/assets/img/gym8.jfif')">Añadir al carrito</button>
    </div>
  </div>

  <!-- Carrito -->
  <div class="cart">
    <h2>Carrito</h2>
    <ul id="cart-list">
      <li>No hay productos en el carrito.</li>
    </ul>
    <p><strong>Total:</strong> $<span id="total-price">0</span></p>
    <button id="checkout-btn" onclick="checkout()">Comprar</button>
  </div>

  <div id="product-preview" style="display: none; margin: 20px auto; text-align: center;">
  <h2 id="preview-name"></h2>
  <img id="preview-img" src="" alt="Imagen producto" style="width: 200px; border-radius: 10px; margin-top: 10px;">
  <p id="preview-price" style="font-weight: bold; margin-top: 10px;"></p>
</div>


<script src="/hieribal2/public/assets/js/carrito.js"></script>



</body>
</html>
