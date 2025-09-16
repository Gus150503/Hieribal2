<?php $base = $this->config['app']['base_url']; ?>
<section class="productos">
  <h1>🌿 Nuestros Productos</h1>

  <!-- Carrusel de destacados -->
  <h2>Más Destacados</h2>
  <div class="carrusel">
    <?php foreach ($productos as $p): ?>
      <div class="card">
        <img src="<?= $base ?>/uploads/<?= htmlspecialchars($p['img']) ?>" alt="<?= $p['nombre'] ?>">
        <h3><?= $p['nombre'] ?></h3>
        <p>Stock: <?= $p['stock'] ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Agotados -->
  <h2>Agotados</h2>
  <ul class="agotados">
    <?php foreach ($agotados as $p): ?>
      <li><?= $p['nombre'] ?> ❌</li>
    <?php endforeach; ?>
  </ul>

  <!-- Charts -->
  <div class="charts">
    <canvas id="chartPorAcabarse"></canvas>
    <canvas id="chartPorPedir"></canvas>
  </div>
</section>

<script>
  // Ejemplo Chart.js
  const ctx1 = document.getElementById('chartPorAcabarse');
  new Chart(ctx1, {
    type: 'bar',
    data: {
      labels: <?= json_encode($porAcabarse[0]) ?>,
      datasets: [{
        label: 'Stock',
        data: <?= json_encode($porAcabarse[1]) ?>
      }]
    }
  });

  const ctx2 = document.getElementById('chartPorPedir');
  new Chart(ctx2, {
    type: 'bar',
    data: {
      labels: <?= json_encode($porPedir[0]) ?>,
      datasets: [{
        label: 'Faltante',
        data: <?= json_encode($porPedir[1]) ?>
      }]
    }
  });
</script>
