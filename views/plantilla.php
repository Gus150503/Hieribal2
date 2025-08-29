<?php
/** Layout principal */
$full  = $full  ?? false;                   // Vistas full-bleed (login/registro)
$base  = $this->config['app']['base_url'];  // Atajo para rutas absolutas
$isAdmin = !empty($esAdmin);                // Flag para panel admin

// Clases para <body>
$bodyClasses = [];
$bodyClasses[] = $full ? 'admin-login' : 'app';
if ($isAdmin) $bodyClasses[] = 'admin-layout';
$bodyClassAttr = implode(' ', $bodyClasses);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($titulo ?? 'App') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS global público -->
  <link rel="stylesheet" href="<?= $base ?>/assets/css/app.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <?php if ($isAdmin): ?>
    <!-- CSS del panel admin -->
    <link rel="stylesheet" href="<?= $base ?>/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/dashboard.css">
  <?php endif; ?>

  <!-- CSS extra por página -->
  <?php if (!empty($extra_css) && is_array($extra_css)): ?>
    <?php foreach ($extra_css as $href): ?>
      <link rel="stylesheet" href="<?= htmlspecialchars($href) ?>">
    <?php endforeach; ?>
  <?php endif; ?>
</head>

<body class="<?= htmlspecialchars($bodyClassAttr) ?>">

  <?php if (!$full && !$isAdmin): ?>
    <!-- ===== Header público (NO en admin ni en vistas full) ===== -->
    <header class="site-header header">
      <div class="container header-wrap" style="display:flex;align-items:center;justify-content:space-between;">
        <a class="logo" href="<?= $base ?>/?r=home" aria-label="Ir al inicio">
          <img src="<?= $base ?>/assets/img/logo1.png" alt="Logo MI HIERBAL" style="height:50px;">
        </a>

        <nav aria-label="Navegación principal">
          <ul style="list-style:none;display:flex;gap:25px;margin:0;padding:0;">
            <li><a href="<?= $base ?>/?r=home#top">Inicio</a></li>
            <li><a href="<?= $base ?>/?r=home#quienes-somos">Quiénes Somos</a></li>

            <?php if (!empty($_SESSION['cliente'])): ?>
              <li><a class="nav-link" href="<?= $base ?>/?r=dashboard">Panel</a></li>
              <li><a class="btn btn-sm btn-ghost" href="<?= $base ?>/?r=logout">Salir</a></li>
            <?php else: ?>
              <li><a class="btn btn-sm" href="<?= $base ?>/?r=login">Ingresar</a></li>
              <li><a class="btn btn-sm btn-ghost" href="<?= $base ?>/?r=register">Registro</a></li>
              <li><a class="btn btn-sm" href="<?= $base ?>/?r=login">Comprar Ahora</a></li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </header>
  <?php endif; ?>

  <!-- ===== Contenido ===== -->
  <main class="site-main <?= $full ? 'site-main--full' : '' ?>">
    <?php if ($full || $isAdmin): ?>
      <!-- Sin .container en login ni en admin (las vistas de admin traen su propio layout interno) -->
      <?= $contenido ?? '' ?>
    <?php else: ?>
      <div class="container">
        <?= $contenido ?? '' ?>
      </div>
    <?php endif; ?>
  </main>

  <?php if (!$full && !$isAdmin): ?>
    <!-- ===== Footer público (no en admin ni en full) ===== -->
    <footer class="site-footer">
      <div class="container">
        <p style="margin:0;">© <?= date('Y') ?> MI HIERBAL • Bienestar natural</p>
      </div>
    </footer>
  <?php endif; ?>

  <!-- ===== Scripts ===== -->

  <?php if ($isAdmin): ?>
    <!-- Vendor admin primero (Bootstrap para modales, tooltips, etc.) -->
    <script src="<?= $base ?>/assets/vendor/bootstrap/bootstrap.bundle.min.js" defer></script>
  <?php endif; ?>

  <!-- JS global de la app -->
  <script src="<?= $base ?>/assets/js/app.js" defer></script>

  <!-- JS extra por página (después de Bootstrap) -->
  <?php if (!empty($extra_js) && is_array($extra_js)): ?>
    <?php foreach ($extra_js as $src): ?>
      <script src="<?= htmlspecialchars($src) ?>" defer></script>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- Scripts opcionales sólo si la página lo pide (p.ej. Dashboard) -->
  <?php if (!empty($carga_chartjs)): ?>
    <script src="<?= $base ?>/assets/vendor/chartjs/chart.umd.min.js" defer></script>
    <script src="<?= $base ?>/assets/js/admin-dashboard.js" defer></script>
  <?php endif; ?>

    <?php if ($isAdmin): ?>
    <!-- Bootstrap primero: necesario para modales/tooltip -->
    <script src="<?= $base ?>/assets/vendor/bootstrap/bootstrap.bundle.min.js" defer></script>
  <?php endif; ?>

  <!-- JS global de la app -->
  <script src="<?= $base ?>/assets/js/app.js" defer></script>

  <!-- JS extra por página (inyectado por el controlador) -->
  <?php if (!empty($extra_js) && is_array($extra_js)): ?>
    <?php foreach ($extra_js as $src): ?>
      <script src="<?= htmlspecialchars($src) ?>" defer></script>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- SOLO si la página lo pide -->
  <?php if (!empty($carga_chartjs)): ?>
    <script src="<?= $base ?>/assets/vendor/chartjs/chart.umd.min.js" defer></script>
    <script src="<?= $base ?>/assets/js/admin-dashboard.js" defer></script>
  <?php endif; ?>


</body>
</html>
