<?php /* views/admin/dashboard.php */ ?>
<?php
$base = htmlspecialchars($this->config['app']['base_url'] ?? '');

/** Normaliza imagen (products/personas) */
$normFoto = function(array $row): string {
  $f = $row['imagen'] ?? ($row['img'] ?? '');
  $f = ltrim((string)$f, '/');
  if (stripos($f, 'assets/img/') === 0) { $f = substr($f, strlen('assets/img/')); }
  if (stripos($f, 'img/') === 0)        { $f = substr($f, strlen('img/')); }
  return $f !== '' ? $f : 'placeholder.png';
};
?>

<div class="dashboard-layout">
  <!-- Sidebar -->
  <nav class="sidebar" id="adminSidebar">
    <?php include __DIR__ . '/sidebar.php'; ?>
  </nav>

  <!-- Contenido -->
  <main class="p-4">
    <div class="dash-container"><!-- ancho máx y centrado -->

      <!-- Cabecera -->
      <header class="page-head">
        <h1 class="dash-title">
          Bienvenido, <?= htmlspecialchars($admin['nombre'] ?? '') ?>
          (<?= htmlspecialchars($admin['rol'] ?? '') ?>)
        </h1>
        <p class="dash-sub">Accede a tus módulos desde el menú lateral.</p>
      </header>

      <!-- ===== GRID principal (KPIs izquierda + 4 héroes) ===== -->
      <section class="hero-grid">
        <!-- KPI stack -->
        <aside class="kpi-list">
          <article class="kpi-tile kpi-red">
            <div class="kpi-name">👥 Total Empleados</div>
            <div class="kpi-num"><?= (int)($totalEmpleados ?? 0) ?></div>
          </article>
          <article class="kpi-tile kpi-amber">
            <div class="kpi-name">🧑‍⚕️ Total Clientes</div>
            <div class="kpi-num"><?= (int)($totalClientes ?? 0) ?></div>
          </article>
          <article class="kpi-tile kpi-green">
            <div class="kpi-name">🌿 Total Productos</div>
            <div class="kpi-num"><?= (int)($totalProductos ?? 0) ?></div>
          </article>
          <article class="kpi-tile kpi-cyan">
            <div class="kpi-name">💳 Ventas (mes)</div>
            <div class="kpi-num"><?= (int)($totalVentasMes ?? 0) ?></div>
          </article>
        </aside>

        <!-- Héroe 1: Inventario destacado -->
        <article class="hero panel">
          <h5 class="hero-title">🌟 Inventario destacado</h5>
          <div class="slider slider--hero" data-slider data-autoplay="4000">
            <button class="slider-btn prev" data-prev>&lsaquo;</button>
            <div class="slider-track" data-track>
              <?php if (!empty($invDestacados)): foreach ($invDestacados as $p): $foto = $normFoto($p); ?>
                <div class="hero-card">
                  <img class="hero-avatar" src="<?= $base ?>/assets/img/<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($p['nombre'] ?? 'Producto') ?>">
                  <div class="hero-text">
                    <div class="hero-name"><?= htmlspecialchars($p['nombre'] ?? 'Producto') ?></div>
                    <div class="hero-sub">Stock: <?= (int)($p['stock'] ?? 0) ?></div>
                  </div>
                </div>
              <?php endforeach; else: ?>
                <div class="hero-card hero-empty">Sin datos</div>
              <?php endif; ?>
            </div>
            <button class="slider-btn next" data-next>&rsaquo;</button>
          </div>
        </article>

        <!-- Héroe 2: Más vendidos -->
        <article class="hero panel hero-red">
          <h5 class="hero-title">🏆 Más vendidos</h5>
          <div class="slider slider--hero" data-slider data-autoplay="4000">
            <button class="slider-btn prev" data-prev>&lsaquo;</button>
            <div class="slider-track" data-track>
              <?php if (!empty($topVendidos)): foreach ($topVendidos as $p): $foto = $normFoto($p); ?>
                <div class="hero-card">
                  <img class="hero-avatar" src="<?= $base ?>/assets/img/<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($p['nombre'] ?? 'Producto') ?>">
                  <div class="hero-text">
                    <div class="hero-name"><?= htmlspecialchars($p['nombre'] ?? 'Producto') ?></div>
                    <div class="hero-sub">Unidades: <?= (int)($p['unidades'] ?? 0) ?></div>
                  </div>
                </div>
              <?php endforeach; else: ?>
                <div class="hero-card hero-empty">Sin datos</div>
              <?php endif; ?>
            </div>
            <button class="slider-btn next" data-next>&rsaquo;</button>
          </div>
        </article>

        <!-- Héroe 3: Productos agotados -->
        <article class="hero panel">
          <h5 class="hero-title">🚨 Productos agotados</h5>
          <div class="slider slider--hero" data-slider data-autoplay="4000">
            <button class="slider-btn prev" data-prev>&lsaquo;</button>
            <div class="slider-track" data-track>
              <?php if (!empty($agotados)): foreach ($agotados as $p): $foto = $normFoto($p); ?>
                <div class="hero-card">
                  <img class="hero-avatar" src="<?= $base ?>/assets/img/<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($p['nombre'] ?? 'Producto') ?>">
                  <div class="hero-text">
                    <div class="hero-name"><?= htmlspecialchars($p['nombre'] ?? 'Producto') ?></div>
                    <div class="hero-sub" style="opacity:.9">Agotado</div>
                  </div>
                </div>
              <?php endforeach; else: ?>
                <div class="hero-card hero-empty">Sin datos</div>
              <?php endif; ?>
            </div>
            <button class="slider-btn next" data-next>&rsaquo;</button>
          </div>
        </article>

        <!-- Héroe 4: 1 año en la empresa -->
        <article class="hero panel hero-red">
          <h5 class="hero-title">🎉 1 año en la empresa</h5>
          <div class="slider slider--hero" data-slider data-autoplay="4000">
            <button class="slider-btn prev" data-prev>&lsaquo;</button>
            <div class="slider-track" data-track>
              <?php if (!empty($aniversario1Año)): foreach ($aniversario1Año as $e): $foto = $normFoto($e); ?>
                <div class="hero-card">
                  <img class="hero-avatar" src="<?= $base ?>/assets/img/avatars/<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($e['nombre'] ?? 'Empleado') ?>">
                  <div class="hero-text">
                    <div class="hero-name"><?= htmlspecialchars($e['nombre'] ?? 'Empleado') ?></div>
                    <div class="hero-sub">Desde: <?= htmlspecialchars($e['desde'] ?? '') ?></div>
                  </div>
                </div>
              <?php endforeach; else: ?>
                <div class="hero-card hero-empty">Sin datos</div>
              <?php endif; ?>
            </div>
            <button class="slider-btn next" data-next>&rsaquo;</button>
          </div>
        </article>
      </section><!-- /hero-grid -->

      <!-- ===== CHARTS ===== -->
      <section class="charts-grid">
        <div class="panel">
          <h5>🟡 Productos por acabarse</h5>
          <div class="chart-card"><canvas id="barLowStock"></canvas></div>
        </div>
        <div class="panel">
          <h5>🧾 Productos por pedir</h5>
          <div class="chart-card"><canvas id="barToOrder"></canvas></div>
        </div>
        <div class="panel">
  <h5>👑 Clientes que más compran</h5>
  <div class="chart-card"><canvas id="barTopClients"></canvas></div>
</div>

      </section>

      <!-- Datos para charts -->
      <script>
        window.__charts = {
          lowStock:   { labels: <?= json_encode($lowStockLabels ?? []) ?>,  values: <?= json_encode($lowStockValues ?? []) ?> },
          toOrder:    { labels: <?= json_encode($toOrderLabels ?? []) ?>,   values: <?= json_encode($toOrderValues ?? []) ?> },
          topClients: { labels: <?= json_encode($topClientsLabels ?? []) ?>, values: <?= json_encode($topClientsValues ?? []) ?> }
        };
      </script>

    </div><!-- /dash-container -->
  </main>

  <!-- Backdrop móvil (debe ser hermano de main/sidebar) -->
  <div id="sidebarBackdrop" class="sidebar-backdrop"></div>
</div><!-- /dashboard-layout -->

<!-- Toggle sidebar móvil -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn  = document.getElementById('sidebarToggle');
  const back = document.getElementById('sidebarBackdrop');
  const open  = () => { document.body.classList.add('sidebar-open');  btn?.setAttribute('aria-expanded','true');  };
  const close = () => { document.body.classList.remove('sidebar-open'); btn?.setAttribute('aria-expanded','false'); };
  btn?.addEventListener('click', () => document.body.classList.contains('sidebar-open') ? close() : open());
  back?.addEventListener('click', close);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
  document.querySelectorAll('#adminSidebar a').forEach(a => a.addEventListener('click', close));
});
</script>

<!-- Autoplay simple para todos los sliders -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-slider]').forEach(slider => {
    const track = slider.querySelector('[data-track]');
    if (!track) return;

    const delay = +slider.getAttribute('data-autoplay') || 4000;
    let i = 0, timer;

    const go = (idx) => {
      i = idx;
      const w = track.clientWidth; // 100% por vista
      track.scrollTo({ left: i * w, behavior: 'smooth' });
    };

    const step = () => go((i + 1) % track.children.length);
    const start = () => { stop(); timer = setInterval(step, delay); };
    const stop  = () => { if (timer) clearInterval(timer); };

    slider.querySelector('[data-prev]')?.addEventListener('click', e => { e.preventDefault(); stop(); go(Math.max(0, i - 1)); });
    slider.querySelector('[data-next]')?.addEventListener('click', e => { e.preventDefault(); stop(); go(Math.min(track.children.length - 1, i + 1)); });

    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);

    start();
  });
});
</script>
