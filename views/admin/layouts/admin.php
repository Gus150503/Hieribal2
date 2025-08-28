<?php
$base   = $this->config['app']['base_url'] ?? '';
$titulo = $titulo ?? 'Panel Admin';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($titulo) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS admin (mismos que cargas cuando $isAdmin=true) -->
  <link rel="stylesheet" href="<?= $base ?>/assets/vendor/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="<?= $base ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/dashboard.css">
</head>
<body class="admin-layout">

  <aside class="sidebar">
    <?php include __DIR__ . '/../admin/sidebar.php'; ?>
  </aside>

  <main class="content">
    <?= $contenido ?? '' ?>
  </main>

  <!-- JS admin -->
  <script src="<?= $base ?>/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
  <script src="<?= $base ?>/assets/vendor/chartjs/chart.umd.min.js"></script>
  <script src="<?= $base ?>/assets/js/admin-dashboard.js"></script>
</body>
</html>
