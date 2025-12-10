<?php
/** Layout principal */
$full    = $full  ?? false;
$isAdmin = !empty($esAdmin);

/* 1) Resolver $base */
$cfgBase = $this->config['app']['base_url'] ?? '';
$base = rtrim($cfgBase, '/');
if ($base === '') {
  $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
  if ($base === '' || $base === '.') $base = '/';
}

/* 2) Helper assets */
$asset = function (string $path) use ($base) {
  return rtrim($base, '/') . '/' . ltrim($path, '/');
};

$bodyClasses   = [];
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

  <!-- Base HREF para que TODO relativo apunte bien -->
  <base href="<?= htmlspecialchars(rtrim($base, '/')) ?>/">

  <!-- Tema (antes del CSS) -->
  <script>
  (function(){
    try{
      var LS=localStorage;
      var pref=(LS&&LS.getItem('ui_tema'))||<?= json_encode($ui_tema_fallback) ?>||'light';
      var brand=(LS&&LS.getItem('ui_color_principal'))||'#198754';
      function resolveTheme(x){if(x==='auto')return matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';return (x==='dark'||x==='light')?x:'light';}
      function apply(){
        var t=resolveTheme(pref);
        document.documentElement.setAttribute('data-theme',t);
        document.documentElement.style.setProperty('--brand',brand);
        try{
          var v=brand.replace('#','');
          var r=parseInt(v.substr(0,2),16)||25,g=parseInt(v.substr(2,2),16)||135,b=parseInt(v.substr(4,2),16)||84;
          document.documentElement.style.setProperty('--ring','rgba('+r+','+g+','+b+',.18)');
        }catch(_){}
      }
      apply();
      if(pref==='auto'&&window.matchMedia){
        var mq=matchMedia('(prefers-color-scheme: dark)');
        (mq.addEventListener||mq.addListener).call(mq,'change',apply);
      }
    }catch(e){}
  })();
  </script>

  <!-- CSS global -->
  <link rel="stylesheet" href="<?= $asset('assets/css/app.css') ?>">

  <?php if ($isAdmin): ?>
    <!-- CSS del panel -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $asset('assets/css/sidebar.css?v=12') ?>">
    <link rel="stylesheet" href="<?= $asset('assets/css/theme.css?v=3') ?>">
    <link rel="stylesheet" href="<?= $asset('assets/css/dashboard.css?v=16') ?>">
  <?php endif; ?>

  <!-- CSS extra por página -->
  <?php if (!empty($extra_css) && is_array($extra_css)): ?>
    <?php foreach ($extra_css as $href): ?>
      <link rel="stylesheet" href="<?= htmlspecialchars($href) ?>">
    <?php endforeach; ?>
  <?php endif; ?>
</head>

<body class="<?= htmlspecialchars($bodyClassAttr) ?>">

<?php if ($isAdmin): ?>
  <!-- ===== ADMIN LAYOUT ===== -->
  <div class="admin-wrap">
    <aside class="sidebar" id="adminSidebar">
      <?php
        // En el parcial debe estar el #sidebarToggle y opcionalmente #pinBtn dentro de .sidebar-header
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

  <!-- Botón hamburguesa flotante (móvil) -->
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
        <a class="logo" href="<?= htmlspecialchars($base) ?>/?r=home" aria-label="Ir al inicio">
          <img src="<?= $asset('assets/img/logo1.png') ?>" alt="Logo MI HIERBAL" style="height:50px;">
        </a>
        <nav aria-label="Navegación principal">
          <ul class="menu-publico">
            <li><a href="<?= htmlspecialchars($base) ?>/?r=home#top">Inicio</a></li>
            <li><a href="<?= htmlspecialchars($base) ?>/?r=home#quienes-somos">Quiénes Somos</a></li>
            <?php if (!empty($_SESSION['cliente'])): ?>
              <li><a class="nav-link" href="<?= htmlspecialchars($base) ?>/?r=dashboard">Panel</a></li>
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

  <?php if (!$full): ?>
    <footer class="site-footer">
      <div class="container"></div>
    </footer>
  <?php endif; ?>

<?php endif; ?>
<!-- /$isAdmin -->

<!-- ===== Scripts ===== -->
<?php if ($isAdmin): ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const root        = document.body;
    const sidebarEl   = document.getElementById('adminSidebar');
    const backdropEl  = document.getElementById('sidebarBackdrop');
    const toggleBtn   = document.getElementById('sidebarToggle');       // en sidebar.php
    const pinBtn      = document.getElementById('pinBtn');              // opcional, en sidebar.php
    const closeBtn    = sidebarEl?.querySelector('.sidebar-close');     // opcional, en sidebar.php
    const mobileBtn   = document.getElementById('mobileSidebarToggle');

    const isDesktop    = () => window.matchMedia('(min-width: 992px)').matches;
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

    toggleBtn?.addEventListener('click', (e) => {
      e.preventDefault();
      if (isDesktop()) toggleCollapsed();
      else root.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
    });

    pinBtn?.addEventListener('click', (e) => {
      e.preventDefault();
      if (isDesktop()) toggleCollapsed();
      else root.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
    });

    mobileBtn?.addEventListener('click', (e) => {
      e.preventDefault();
      if (!isDesktop()) openSidebar();
    });

    backdropEl?.addEventListener('click', closeSidebar);
    closeBtn?.addEventListener('click', closeSidebar);

    sidebarEl?.addEventListener('click', (e) => {
      if (!isDesktop() && e.target.closest('a')) closeSidebar();
    });

    window.addEventListener('resize', () => { if (isDesktop()) closeSidebar(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && root.classList.contains('sidebar-open')) closeSidebar(); });
  });
  </script>
<?php endif; ?>

<!-- Tu JS base -->
<script src="<?= $asset('assets/js/app.js') ?>"></script>

<?php
// Necesito SweetAlert si la vista lo pide O si falta la cédula del cliente
$needSwal = !empty($carga_swal)
    || (!empty($_SESSION['cliente']['falta_cedula']) && $_SESSION['cliente']['falta_cedula']);
?>

<!-- SweetAlert2 -->
<?php if ($needSwal): ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php endif; ?>

<?php if (!empty($_SESSION['cliente']['falta_cedula']) && $_SESSION['cliente']['falta_cedula']): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (typeof Swal === 'undefined') return;
  pedirCedulaObligatoria();
});

async function pedirCedulaObligatoria() {
  while (true) {
    const { value: cedula, isConfirmed } = await Swal.fire({
      title: 'Completa tu cédula',
      html: '<p style="font-size:14px;">Para continuar usando MI HIERIBAL necesitamos tu número de identificación.</p>',
      input: 'text',
      inputLabel: 'Cédula (8 o 10 dígitos)',
      inputAttributes: {
        autocapitalize: 'off',
        inputmode: 'numeric',
        maxlength: '10'
      },
      allowOutsideClick: false,
      allowEscapeKey: false,
      showCancelButton: false,
      confirmButtonText: 'Guardar',
      confirmButtonColor: '#22c55e'
    });

    if (!isConfirmed) {
      continue; // no lo dejamos irse
    }

    const limpia = (cedula || '').replace(/\D/g, '');

    if (!/^(\d{8}|\d{10})$/.test(limpia)) {
      await Swal.fire({
        icon: 'error',
        title: 'Cédula inválida',
        text: 'La cédula debe tener 8 o 10 dígitos numéricos.',
        confirmButtonColor: '#ef4444'
      });
      continue;
    }

    try {
      const fd = new FormData();
      fd.append('cedula', limpia);

      const res = await fetch('?r=completar_cedula', {
        method: 'POST',
        body: fd,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok || !data.ok) {
        await Swal.fire({
          icon: 'error',
          title: 'No se pudo guardar',
          text: (data && data.msg) ? data.msg : 'Ocurrió un error al guardar la cédula.',
          confirmButtonColor: '#ef4444'
        });
        continue;
      }

      await Swal.fire({
        icon: 'success',
        title: '¡Cédula guardada!',
        text: 'Tu número de identificación se guardó correctamente.',
        confirmButtonColor: '#22c55e'
      });

      window.location.reload();
      break;

    } catch (err) {
      await Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No pudimos comunicarnos con el servidor. Intenta de nuevo.',
        confirmButtonColor: '#ef4444'
      });
    }
  }
}
</script>
<?php endif; ?>

<!-- Scripts extra por página (después de SweetAlert2 para que Swal esté disponible) -->
<?php if (!empty($extra_js) && is_array($extra_js)): ?>
  <?php foreach ($extra_js as $src): ?>
    <script src="<?= htmlspecialchars($src) ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>


</body>
</html>
