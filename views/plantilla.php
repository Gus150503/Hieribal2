<?php

/** Layout principal corregido */
$full    = $full  ?? false;
$isAdmin = !empty($esAdmin);

// 1) Resolver $base de forma robusta (desde config o calculado)
$cfgBase = $this->config['app']['base_url'] ?? '';
$base = rtrim($cfgBase, '/');
if ($base === '') {
  // Deriva la carpeta del front controller (public/index.php)
  $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
  if ($base === '' || $base === '.') $base = '/';
}

// 2) Helper para construir URLs de assets de forma segura
$asset = function (string $path) use ($base) {
  // Quita / duplicados
  $url = rtrim($base, '/') . '/' . ltrim($path, '/');
  return $url;
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

    <!-- 3) Base HREF para que TODO relativo apunte a /Hieribal2/public/ -->
    <base href="<?= htmlspecialchars(rtrim($base, '/')) ?>/">

    <!-- Tema antes del CSS -->
    <script>
    (function() {
        try {
            var LS = window.localStorage;
            var pref = (LS && LS.getItem('ui_tema')) || <?= json_encode($ui_tema_fallback) ?> || 'light';
            var brand = (LS && LS.getItem('ui_color_principal')) || '#198754';

            function resolveTheme(x) {
                if (x === 'auto') return matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                return (x === 'dark' || x === 'light') ? x : 'light';
            }

            function apply() {
                var t = resolveTheme(pref);
                document.documentElement.setAttribute('data-theme', t);
                document.documentElement.style.setProperty('--brand', brand);
                try {
                    var v = brand.replace('#', '');
                    var r = parseInt(v.substr(0, 2), 16) || 25,
                        g = parseInt(v.substr(2, 2), 16) || 135,
                        b = parseInt(v.substr(4, 2), 16) || 84;
                    document.documentElement.style.setProperty('--ring', 'rgba(' + r + ',' + g + ',' + b + ',.18)');
                } catch (_) {}
            }
            apply();
            if (pref === 'auto' && window.matchMedia) {
                var mq = matchMedia('(prefers-color-scheme: dark)');
                (mq.addEventListener || mq.addListener).call(mq, 'change', apply);
            }
        } catch (e) {}
    })();
    </script>

    <!-- CSS global público -->
    <link rel="stylesheet" href="<?= $asset('assets/css/app.css') ?>">

    <?php if ($isAdmin): ?>
    <!-- CSS del panel -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $asset('assets/css/sidebar.css?v=5') ?>">
    <link rel="stylesheet" href="<?= $asset('assets/css/theme.css?v=3') ?>">
    <link rel="stylesheet" href="<?= $asset('assets/css/dashboard.css?v=16') ?>"><!-- ÚLTIMO -->
    <?php endif; ?>

    <!-- CSS extra por página -->
    <?php if (!empty($extra_css) && is_array($extra_css)): foreach ($extra_css as $href): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($href) ?>">
    <?php endforeach;
  endif; ?>
</head>

<body class="<?= htmlspecialchars($bodyClassAttr) ?>">

    <?php if ($isAdmin): ?>
    <!-- ========= SHELL ADMIN (grid: sidebar + contenido) ========= -->
    <div class="admin-wrap">
        <aside class="sidebar" id="adminSidebar">
            <?php
        // ✅ Ruta correcta a views/admin/sidebar.php
        $sidebarPath = __DIR__ . '/admin/sidebar.php';
        if (is_file($sidebarPath)) {
          include $sidebarPath;
        } else {
          echo '<!-- Sidebar no encontrada en ' . htmlspecialchars($sidebarPath) . ' -->';
        }
        ?>
        </aside>

        <main class="content p-3">
            <!-- Botón toggle (puedes moverlo a tu header del panel si quieres) -->
            <button id="sidebarToggle" class="btn btn-light btn-sm mb-3" type="button" aria-expanded="false"
                aria-label="Alternar menú">
                <i class="bi bi-list"></i>
            </button>

            <?= $contenido ?? '' ?>
            <!-- aquí se inyecta dashboard.php -->
        </main>
    </div>

    <!-- Backdrop para móvil -->
    <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

    <?php else: ?>
    <!-- ========= Sitio público ========= -->
    <?php if (!$full): ?>
    <header class="site-header header">
        <div class="container header-wrap" style="display:flex;align-items:center;justify-content:space-between;">
            <a class="logo" href="<?= htmlspecialchars($base) ?>/?r=home" aria-label="Ir al inicio">
                <img src="<?= $asset('assets/img/logo1.png') ?>" alt="Logo MI HIERBAL" style="height:50px;">
            </a>
            <nav aria-label="Navegación principal">
                <ul style="list-style:none;display:flex;gap:25px;margin:0;padding:0;">
                    <li><a href="<?= htmlspecialchars($base) ?>/?r=home#top">Inicio</a></li>
                    <li><a href="<?= htmlspecialchars($base) ?>/?r=home#quienes-somos">Quiénes Somos</a></li>
                    <?php if (!empty($_SESSION['cliente'])): ?>
                    <li><a class="nav-link" href="<?= htmlspecialchars($base) ?>/?r=dashboard">Panel</a></li>
                    <li><a class="btn btn-sm btn-ghost" href="<?= htmlspecialchars($base) ?>/?r=logout">Salir</a></li>
                    <?php else: ?>
                    <li><a class="btn btn-sm" href="<?= htmlspecialchars($base) ?>/?r=login">Ingresar</a></li>
                    <li><a class="btn btn-sm btn-ghost" href="<?= htmlspecialchars($base) ?>/?r=register">Registro</a>
                    </li>
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
        <div class="container">
            <?= $contenido ?? '' ?>
        </div>
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
    // Toggle sidebar: desktop = colapsa; móvil = off-canvas
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('sidebarToggle');
        const back = document.getElementById('sidebarBackdrop');
        const isDesktop = () => matchMedia('(min-width: 992px)').matches;

        const openM = () => document.body.classList.add('sidebar-open');
        const closeM = () => document.body.classList.remove('sidebar-open');

        btn?.addEventListener('click', (e) => {
            e.preventDefault();
            if (isDesktop()) {
                document.body.classList.toggle('sidebar-collapsed');
            } else {
                document.body.classList.contains('sidebar-open') ? closeM() : openM();
            }
        });

        back?.addEventListener('click', closeM);
        addEventListener('resize', () => {
            if (isDesktop()) closeM();
            else document.body.classList.remove('sidebar-collapsed');
        });
    });
    </script>
    <?php endif; ?>

    <script src="<?= $asset('assets/js/app.js') ?>"></script>

    <?php if (!empty($extra_js) && is_array($extra_js)): foreach ($extra_js as $src): ?>
    <script src="<?= htmlspecialchars($src) ?>"></script>
    <?php endforeach;
  endif; ?>

    <?php if (!empty($carga_chartjs)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="<?= $asset('assets/js/admin-dashboard.js') ?>"></script>
    <?php endif; ?>
</body>

</html>