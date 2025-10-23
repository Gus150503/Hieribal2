<?php
/** Layout principal corregido con hamburguesa (en parcial) y pin (en desktop) */
$full    = $full  ?? false;
$isAdmin = !empty($esAdmin);

// 1) Resolver $base
$cfgBase = $this->config['app']['base_url'] ?? '';
$base = rtrim($cfgBase, '/');
if ($base === '') {
  $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
  if ($base === '' || $base === '.') $base = '/';
}

// 2) Helper para assets
$asset = function (string $path) use ($base) {
  return rtrim($base, '/') . '/' . ltrim($path, '/');
};

$bodyClasses = [];
$bodyClasses[] = $full ? 'admin-login' : 'app';
if ($isAdmin) $bodyClasses[] = 'admin-layout';
$bodyClassAttr = implode(' ', $bodyClasses);

$ui_tema_fallback = $ui_tema ?? 'light';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($titulo ?? 'App') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <base href="<?= htmlspecialchars(rtrim($base, '/')) ?>/">

  <!-- Tema -->
  <script>
  (function(){
    try {
      var LS = localStorage;
      var pref=(LS&&LS.getItem('ui_tema'))||<?=json_encode($ui_tema_fallback)?>||'light';
      var brand=(LS&&LS.getItem('ui_color_principal'))||'#198754';
      function resolveTheme(x){if(x==='auto')return matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';return(x==='dark'||x==='light')?x:'light';}
      function apply(){var t=resolveTheme(pref);document.documentElement.setAttribute('data-theme',t);
        document.documentElement.style.setProperty('--brand',brand);
        try{var v=brand.replace('#','');var r=parseInt(v.substr(0,2),16)||25,g=parseInt(v.substr(2,2),16)||135,b=parseInt(v.substr(4,2),16)||84;
        document.documentElement.style.setProperty('--ring','rgba('+r+','+g+','+b+',.18)');}catch(_){}
      }
      apply();if(pref==='auto'&&matchMedia){var mq=matchMedia('(prefers-color-scheme: dark)');(mq.addEventListener||mq.addListener).call(mq,'change',apply);}
    }catch(e){}
  })();
  </script>

  <!-- CSS -->
  <link rel="stylesheet" href="<?= $asset('assets/css/app.css') ?>">
<?php if ($isAdmin): ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= $asset('assets/css/sidebar.css?v=12') ?>"><!-- sube versión para cache -->
  <link rel="stylesheet" href="<?= $asset('assets/css/theme.css?v=3') ?>">
  <link rel="stylesheet" href="<?= $asset('assets/css/dashboard.css?v=16') ?>">
<?php endif; ?>

  <?php if (!empty($extra_css) && is_array($extra_css)): foreach ($extra_css as $href): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($href) ?>">
  <?php endforeach; endif; ?>
</head>

<body class="<?= htmlspecialchars($bodyClassAttr) ?>">

<?php if ($isAdmin): ?>
  <!-- ===== ADMIN LAYOUT ===== -->
  <div class="admin-wrap">
    <aside class="sidebar" id="adminSidebar">
      <?php
        // En el parcial debe estar el #sidebarToggle y el #pinBtn dentro de .sidebar-header
        $sidebarPath = __DIR__ . '/admin/sidebar.php';
        if (is_file($sidebarPath)) include $sidebarPath;
        else echo '<!-- Sidebar no encontrada -->';
      ?>
    </aside>

    <main class="content p-3">
      <?= $contenido ?? '' ?>
    </main>
  </div>

  <!-- Backdrop móvil -->
  <div id="sidebarBackdrop" class="sidebar-backdrop" hidden></div>

  <!-- Botón hamburguesa flotante (solo móvil, solo cuando el menú está cerrado) -->
  <button id="mobileSidebarToggle"
          class="hamburger mobile-hamburger"
          aria-label="Abrir menú"
          aria-expanded="false"
          aria-controls="adminSidebar">
    <span></span><span></span><span></span>
  </button>

<?php else: ?>
  <!-- ===== SITIO PÚBLICO ===== -->
  <?php if (!$full): ?>
    <header class="site-header header">
      <div class="container header-wrap" style="display:flex;align-items:center;justify-content:space-between;">
        <a class="logo" href="<?= htmlspecialchars($base) ?>/?r=home">
          <img src="<?= $asset('assets/img/logo1.png') ?>" alt="Logo MI HIERBAL" style="height:50px;">
        </a>
        <nav>
          <ul style="list-style:none;display:flex;gap:25px;margin:0;padding:0;">
            <li><a href="<?= htmlspecialchars($base) ?>/?r=home#top">Inicio</a></li>
            <li><a href="<?= htmlspecialchars($base) ?>/?r=home#quienes-somos">Quiénes Somos</a></li>
            <?php if (!empty($_SESSION['cliente'])): ?>
              <li><a href="<?= htmlspecialchars($base) ?>/?r=dashboard">Panel</a></li>
              <li><a class="btn btn-sm btn-ghost" href="<?= htmlspecialchars($base) ?>/?r=logout">Salir</a></li>
            <?php else: ?>
              <li><a class="btn btn-sm" href="<?= htmlspecialchars($base) ?>/?r=login">Ingresar</a></li>
              <li><a class="btn btn-sm btn-ghost" href="<?= htmlspecialchars($base) ?>/?r=register">Registro</a></li>
              <li><a class="btn btn-sm" href="<?= htmlspecialchars($base) ?>/?r=login">Comprar Ahora</a></li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </header>
  <?php endif; ?>

  <main class="site-main <?= $full ? 'site-main--full' : '' ?>">
    <?php if ($full): ?>
      <?= $contenido ?? '' ?>
    <?php else: ?>
      <div class="container"><?= $contenido ?? '' ?></div>
    <?php endif; ?>
  </main>

  <?php if (!$full): ?><footer class="site-footer"><div class="container"></div></footer><?php endif; ?>

<?php endif; ?>

<!-- ===== JS ===== -->
<?php if ($isAdmin): ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const root        = document.body;
    const sidebarEl   = document.getElementById('adminSidebar');
    const backdropEl  = document.getElementById('sidebarBackdrop');

    const toggleBtn   = document.getElementById('sidebarToggle');         // dentro del sidebar.php
    const pinBtn      = document.getElementById('pinBtn');                 // dentro del sidebar.php
    const closeBtn    = sidebarEl?.querySelector('.sidebar-close');        // dentro del sidebar.php
    const mobileBtn   = document.getElementById('mobileSidebarToggle');    // flotante móvil

    const isDesktop   = () => window.matchMedia('(min-width: 992px)').matches;

    const lockScroll   = () => { document.documentElement.style.overflow='hidden'; document.body.style.overflow='hidden'; };
    const unlockScroll = () => { document.documentElement.style.overflow='';       document.body.style.overflow='';       };

    const openSidebar  = () => { root.classList.add('sidebar-open');  backdropEl.hidden=false;  lockScroll();  };
    const closeSidebar = () => { root.classList.remove('sidebar-open');backdropEl.hidden=true;  unlockScroll(); };

    const toggleCollapsed = () => {
      root.classList.toggle('sidebar-collapsed');
      const icon = pinBtn?.querySelector('i');
      if (icon) {
        const c = root.classList.contains('sidebar-collapsed');
        icon.classList.toggle('bi-chevron-double-right', c);
        icon.classList.toggle('bi-chevron-double-left', !c);
        pinBtn.title = c ? 'Expandir' : 'Colapsar';
      }
    };

    // Hamburguesa del header del sidebar
    toggleBtn?.addEventListener('click', (e) => {
      e.preventDefault();
      if (isDesktop()) toggleCollapsed();
      else root.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
    });

    // Pin (desktop) — colapsa/expande
    pinBtn?.addEventListener('click', (e) => {
      e.preventDefault();
      if (isDesktop()) toggleCollapsed();
      else root.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
    });

    // Hamburguesa flotante (móvil) — abre
    mobileBtn?.addEventListener('click', (e) => {
      e.preventDefault();
      if (!isDesktop()) openSidebar();
    });

    // Cerrar con backdrop o con “×”
    backdropEl?.addEventListener('click', closeSidebar);
    closeBtn?.addEventListener('click', closeSidebar);

    // Cierra al navegar en móvil
    sidebarEl?.addEventListener('click', (e) => {
      if (!isDesktop() && e.target.closest('a')) closeSidebar();
    });

    // En desktop, limpia estado móvil al redimensionar
    window.addEventListener('resize', () => { if (isDesktop()) closeSidebar(); });

    // Esc para cerrar en móvil
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && root.classList.contains('sidebar-open')) closeSidebar();
    });
  });
  </script>
<?php endif; ?>

<script src="<?= $asset('assets/js/app.js') ?>"></script>

<?php if (!empty($extra_js) && is_array($extra_js)): foreach ($extra_js as $src): ?>
  <script src="<?= htmlspecialchars($src) ?>"></script>
<?php endforeach; endif; ?>

<?php if (!empty($carga_chartjs)): ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <script src="<?= $asset('assets/js/admin-dashboard.js') ?>"></script>
<?php endif; ?>
</body>
</html>
