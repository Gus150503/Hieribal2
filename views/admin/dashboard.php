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

<div class="dash-container"><!-- ancho máx y márgenes del contenido -->

  <!-- Cabecera -->
  <header class="page-head mb-4">
    <h1 class="dash-title h2 mb-1">
      Bienvenido, <?= htmlspecialchars($admin['nombre'] ?? '') ?>
      (<?= htmlspecialchars($admin['rol'] ?? '') ?>)
    </h1>
    <p class="dash-sub text-muted">Accede a tus módulos desde el menú lateral.</p>
  </header>

  <!-- ===== GRID principal (KPIs izquierda + 4 héroes) ===== -->
  <section class="hero-grid d-grid gap-4 mb-5">
    <!-- KPI stack -->
    <aside class="kpi-list d-grid gap-2">
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
  <section class="charts-grid d-grid gap-4">
    <div class="panel">
      <h5>🟡 Productos por acabarse</h5>
      <div class="chart-card" style="height:260px"><canvas id="barLowStock"></canvas></div>
    </div>
    <div class="panel">
      <h5>🧾 Productos por pedir</h5>
      <div class="chart-card" style="height:260px"><canvas id="barToOrder"></canvas></div>
    </div>
    <div class="panel">
      <h5>👑 Clientes que más compran</h5>
      <div class="chart-card" style="height:260px"><canvas id="barTopClients"></canvas></div>
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

<!-- JS local del dashboard: sliders + charts -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  /* ===== Sliders simples ===== */
  document.querySelectorAll('[data-slider]').forEach(slider => {
    const track = slider.querySelector('[data-track]');
    const prev  = slider.querySelector('[data-prev]');
    const next  = slider.querySelector('[data-next]');
    if (!track) return;

    const delay = +slider.getAttribute('data-autoplay') || 4000;
    let idx = 0, timer;

    const go = (i) => {
      idx = Math.max(0, Math.min(i, track.children.length - 1));
      const w = track.clientWidth; // 100% por vista
      track.scrollTo({ left: idx * w, behavior: 'smooth' });
    };
    const step = () => go((idx + 1) % track.children.length);
    const start = () => { stop(); timer = setInterval(step, delay); };
    const stop  = () => { if (timer) clearInterval(timer); };

    prev?.addEventListener('click', e => { e.preventDefault(); stop(); go(idx - 1); });
    next?.addEventListener('click', e => { e.preventDefault(); stop(); go(idx + 1); });
    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);

    start();
  });

  /* ===== Charts (Chart.js) ===== */
  if (typeof Chart !== 'undefined') {
    const gridColor = 'rgba(17,24,39,.06)';
    const mkBar = (id, labels=[], values=[], horizontal=false) => {
      const el = document.getElementById(id);
      if (!el) return;
      const allZero = !values.length || values.every(v => Number(v) === 0);
      if (allZero) { el.closest('.chart-card')?.insertAdjacentHTML('beforeend','<div class="chart-empty">Sin datos</div>'); return; }
      new Chart(el.getContext('2d'), {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Cantidad', data: values, borderWidth: 1 }] },
        options: {
          responsive: true, maintainAspectRatio: false,
          indexAxis: horizontal ? 'y' : 'x',
          scales: { x:{ grid:{ color:gridColor } }, y:{ beginAtZero:true, ticks:{ precision:0 }, grid:{ color:gridColor } } },
          plugins: { legend: { display:false } }
        }
      });
    };
    const D = window.__charts || {lowStock:{labels:[],values:[]}, toOrder:{labels:[],values:[]}, topClients:{labels:[],values:[]}};
    mkBar('barLowStock',   D.lowStock.labels,  D.lowStock.values);
    mkBar('barToOrder',    D.toOrder.labels,   D.toOrder.values);
    mkBar('barTopClients', D.topClients.labels, D.topClients.values, true);
  }
});
</script>
