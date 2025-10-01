<?php
$base      = $this->config['app']['base_url'] ?? '';
$titulo    = $titulo ?? 'Panel';
$extra_css = $extra_css ?? [];
$extra_js  = $extra_js  ?? [];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titulo) ?></title>

  <!-- Bootstrap + Icons -->
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <!-- CSS global + sidebar/dashboard (mantén este orden para que sidebar.css gane sobre dashboard.css si hay solapes) -->
  <link href="<?= $base ?>/assets/css/app.css?v=1" rel="stylesheet">
  <link href="<?= $base ?>/assets/css/sidebar.css?v=2" rel="stylesheet">
  <link href="<?= $base ?>/assets/css/dashboard.css?v=2" rel="stylesheet">

  <?php foreach ($extra_css as $href): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($href) ?>">
  <?php endforeach; ?>
</head>
<body class="admin-layout bg-body-tertiary">

  <!-- Botón hamburguesa solo en móvil (fuera del <aside>) -->
  <header class="main-header d-md-none p-2">
    <button id="menuToggle" class="menu-toggle btn btn-success" aria-label="Abrir menú">
      <i class="bi bi-list"></i>
    </button>
  </header>

  <div class="admin-shell">
    <!-- SIDEBAR -->
    <nav class="sidebar" id="adminSidebar">
      <div class="sidebar__brand d-none d-md-flex align-items-center gap-2 px-3 py-2">
        <img src="<?= $base ?>/assets/img/logo.png" alt="MI HIERIBAL" style="height:28px">
        <span>MI HIERIBAL</span>
      </div>

      <!-- Botón “pin” (colapsar en desktop). Tu CSS/JS ya lo manejan con body.sidebar-collapsed -->
      <button type="button" class="sidebar__collapse d-none d-md-inline ms-auto me-2 my-2"
              data-toggle-sidebar aria-label="Colapsar">
        ≪
      </button>

      <?php include __DIR__ . '/sidebar.php'; ?>
    </nav>

    <!-- Overlay móvil -->
    <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

    <!-- CONTENIDO -->
    <main class="content">
      <?= $contenido ?? '' ?>
    </main>
  </div>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="<?= $base ?>/assets/js/app.js" defer></script>
  <script src="<?= $base ?>/assets/js/sidebar.js"></script>

  <?php foreach ($extra_js as $src): ?>
    <script src="<?= htmlspecialchars($src) ?>" defer></script>
  <?php endforeach; ?>

  <script>
  // Toggle sidebar (desktop: pin con [data-toggle-sidebar], móvil: #menuToggle)
  document.addEventListener('DOMContentLoaded', () => {
    const btn      = document.querySelector('[data-toggle-sidebar]'); // pin (desktop)
    const back     = document.getElementById('sidebarBackdrop');      // backdrop móvil
    const root     = document.body;
    const menuT    = document.getElementById('menuToggle');           // hamburguesa móvil
    const sidebar  = document.getElementById('adminSidebar');

    const open  = () => root.classList.add('sidebar-open');
    const close = () => root.classList.remove('sidebar-open');
    const toggle = () => root.classList.toggle('sidebar-open');

    // Desktop: pin dentro del sidebar
    btn?.addEventListener('click', toggle);

    // Móvil: hamburguesa en el header
    menuT?.addEventListener('click', toggle);

    // Cerrar al tocar el backdrop (móvil)
    back?.addEventListener('click', close);

    // Cerrar al navegar (móvil)
    sidebar?.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 1100px)').matches) close();
      });
    });

    // Cerrar con ESC (móvil)
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') close();
    });
  });
  </script>
</body>
</html>
