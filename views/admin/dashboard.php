<?php /* views/admin/dashboard.php */ ?>
<?php $base = htmlspecialchars($this->config['app']['base_url'] ?? ''); ?>

<div class="dashboard-layout">
  <!-- Sidebar -->
  <nav class="sidebar" id="adminSidebar">
    <div class="logo">
      <img src="<?= $base ?>/assets/img/logo.png" alt="Logo Hieribal">
    </div>
    <a href="<?= $base ?>/?r=admin_dashboard"><i class="bi bi-house-door"></i> Inicio</a>
    <a href="<?= $base ?>/?r=admin_inventario"><i class="bi bi-box-seam"></i> Inventario</a>
    <a href="<?= $base ?>/?r=admin_productos"><i class="bi bi-basket3"></i> Productos</a>
    <a href="<?= $base ?>/?r=admin_usuarios"><i class="bi bi-people"></i> Usuarios</a>
    <a href="<?= $base ?>/?r=admin_configuracion"><i class="bi bi-gear"></i> Configuración</a>
    
  </nav>

  <!-- Backdrop para cerrar tocando fuera (solo móvil) -->
  <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

  <!-- Contenido -->
  <main class="p-4">
    <!-- Barra móvil con botón -->
    <div class="mobile-bar">
      <button id="sidebarToggle"
              class="menu-toggle"
              aria-label="Abrir menú"
              aria-controls="adminSidebar"
              aria-expanded="false">
        <i class="bi bi-list"></i>
      </button>
      <strong>Menú</strong>
    </div>

    <h4>Bienvenido, <?= htmlspecialchars($admin['nombre'] ?? '') ?> (<?= htmlspecialchars($admin['rol'] ?? '') ?>)</h4>
    <p class="text-muted">Accede a tus módulos desde el menú lateral.</p>

    <!-- ====== GRID SUPERIOR (KPIs + carruseles) ====== -->
    <div class="dash-grid">
      <!-- KPIs apilados -->
      <aside class="kpi-stack">
        <div class="kpi-card kpi-red">
          <div class="kpi-title">👥 Total Empleados</div>
          <div class="kpi-value"><?= (int)($totalEmpleados ?? 0) ?></div>
        </div>
        <div class="kpi-card kpi-orange">
          <div class="kpi-title">🧑‍⚕️ Total Clientes</div>
          <div class="kpi-value"><?= (int)($totalClientes ?? 0) ?></div>
        </div>
        <div class="kpi-card kpi-green">
          <div class="kpi-title">🌿 Total Productos</div>
          <div class="kpi-value"><?= (int)($totalProductos ?? 0) ?></div>
        </div>
        <div class="kpi-card kpi-cyan">
          <div class="kpi-title">💳 Ventas (mes)</div>
          <div class="kpi-value"><?= (int)($totalVentasMes ?? 0) ?></div>
        </div>
      </aside>

      <!-- Carruseles (derecha) -->
      <section class="carousel-grid">
        <!-- Inventario destacados -->
        <article class="panel">
          <h5 class="mb-2">🌟 Inventario destacado</h5>
          <div class="slider" data-slider>
            <button class="slider-btn prev" data-prev>&lsaquo;</button>
            <div class="slider-track" data-track>
              <?php foreach (($invDestacados ?? []) as $p): ?>
                <div class="slide-card">
                  <img src="<?= $base ?>/assets/img/products/<?= htmlspecialchars($p['img'] ?? 'placeholder.png') ?>" alt="">
                  <div class="slide-title"><?= htmlspecialchars($p['nombre'] ?? 'Producto') ?></div>
                  <div class="slide-sub">Stock: <?= (int)($p['stock'] ?? 0) ?></div>
                </div>
              <?php endforeach; ?>
              <?php if (empty($invDestacados)): ?>
                <div class="slide-empty">Sin datos</div>
              <?php endif; ?>
            </div>
            <button class="slider-btn next" data-next>&rsaquo;</button>
          </div>
        </article>

        <!-- Más vendidos -->
        <article class="panel">
          <h5 class="mb-2">🏆 Más vendidos</h5>
          <div class="slider" data-slider>
            <button class="slider-btn prev" data-prev>&lsaquo;</button>
            <div class="slider-track" data-track>
              <?php foreach (($topVendidos ?? []) as $p): ?>
                <div class="slide-card">
                  <img src="<?= $base ?>/assets/img/products/<?= htmlspecialchars($p['img'] ?? 'placeholder.png') ?>" alt="">
                  <div class="slide-title"><?= htmlspecialchars($p['nombre'] ?? 'Producto') ?></div>
                  <div class="slide-sub">Unid: <?= (int)($p['unidades'] ?? 0) ?></div>
                </div>
              <?php endforeach; ?>
              <?php if (empty($topVendidos)): ?>
                <div class="slide-empty">Sin datos</div>
              <?php endif; ?>
            </div>
            <button class="slider-btn next" data-next>&rsaquo;</button>
          </div>
        </article>

        <!-- Agotados -->
        <article class="panel">
          <h5 class="mb-2">🚨 Productos agotados</h5>
          <div class="slider" data-slider>
            <button class="slider-btn prev" data-prev>&lsaquo;</button>
            <div class="slider-track" data-track>
              <?php foreach (($agotados ?? []) as $p): ?>
                <div class="slide-card">
                  <img src="<?= $base ?>/assets/img/products/<?= htmlspecialchars($p['img'] ?? 'placeholder.png') ?>" alt="">
                  <div class="slide-title"><?= htmlspecialchars($p['nombre'] ?? 'Producto') ?></div>
                  <div class="slide-sub text-danger">Agotado</div>
                </div>
              <?php endforeach; ?>
              <?php if (empty($agotados)): ?>
                <div class="slide-empty">Sin datos</div>
              <?php endif; ?>
            </div>
            <button class="slider-btn next" data-next>&rsaquo;</button>
          </div>
        </article>

        <!-- Empleados con 1 año -->
        <article class="panel">
          <h5 class="mb-2">🎉 1 año en la empresa</h5>
          <div class="slider" data-slider>
            <button class="slider-btn prev" data-prev>&lsaquo;</button>
            <div class="slider-track" data-track>
              <?php foreach (($aniversario1Año ?? []) as $e): ?>
                <div class="slide-card">
                  <img src="<?= $base ?>/assets/img/avatars/<?= htmlspecialchars($e['img'] ?? 'avatar.png') ?>" alt="">
                  <div class="slide-title"><?= htmlspecialchars($e['nombre'] ?? 'Empleado') ?></div>
                  <div class="slide-sub">Desde: <?= htmlspecialchars($e['desde'] ?? '') ?></div>
                </div>
              <?php endforeach; ?>
              <?php if (empty($aniversario1Año)): ?>
                <div class="slide-empty">Sin datos</div>
              <?php endif; ?>
            </div>
            <button class="slider-btn next" data-next>&rsaquo;</button>
          </div>
        </article>
      </section>
    </div><!-- /dash-grid -->

    <!-- ====== GRÁFICOS ====== -->
    <section class="charts-grid">
      <div class="panel">
        <h5 class="mb-2">🟡 Productos por acabarse</h5>
        <div class="chart-card chart-lg"><canvas id="barLowStock"></canvas></div>
      </div>

      <div class="panel">
        <h5 class="mb-2">🧾 Productos por pedir</h5>
        <div class="chart-card chart-lg"><canvas id="barToOrder"></canvas></div>
      </div>

      <div class="panel">
        <h5 class="mb-2">👑 Clientes que más compran</h5>
        <div class="chart-card chart-lg"><canvas id="barTopClients"></canvas></div>
      </div>
    </section>

    <!-- Inyección de datos para JS (charts) -->
    <script>
      window.__charts = {
        lowStock:   { labels: <?= json_encode($lowStockLabels ?? []) ?>,  values: <?= json_encode($lowStockValues ?? []) ?> },
        toOrder:    { labels: <?= json_encode($toOrderLabels ?? []) ?>,   values: <?= json_encode($toOrderValues ?? []) ?> },
        topClients: { labels: <?= json_encode($topClientsLabels ?? []) ?>, values: <?= json_encode($topClientsValues ?? []) ?> }
      };
    </script>

    <!-- Toggle sidebar móvil (si no lo tienes en un .js aparte) -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const btn  = document.getElementById('sidebarToggle');
        const back = document.getElementById('sidebarBackdrop');

        const open  = () => { document.body.classList.add('sidebar-open');  btn?.setAttribute('aria-expanded','true');  };
        const close = () => { document.body.classList.remove('sidebar-open'); btn?.setAttribute('aria-expanded','false'); };

        btn?.addEventListener('click', () =>
          document.body.classList.contains('sidebar-open') ? close() : open()
        );
        back?.addEventListener('click', close);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
        document.querySelectorAll('#adminSidebar a').forEach(a => a.addEventListener('click', close));
      });
    </script>
  </main>
</div><!-- /dashboard-layout -->
