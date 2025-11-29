<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$base  = $base ?? ($this->config['app']['base_url'] ?? '');
$route = $_GET['r'] ?? 'admin/dashboard';

/* Detectar sección activa */
$sec = (function (string $r): string {
  if (strpos($r, 'admin/') === 0) {
    $parts = explode('/', $r, 3);
    return strtolower($parts[1] ?? 'dashboard');
  }
  if (strpos($r, 'admin_') === 0) {
    return strtolower(substr($r, strlen('admin_')));
  }
  return 'dashboard';
})($route);

$active = fn (string $key) => $sec === strtolower($key) ? 'active' : '';

$rol = strtolower($_SESSION['admin']['rol'] ?? 'empleado');

/* ACL (Control de acceso por rol) */
$ACL = [
  'admin'    => ['dashboard','ventas','inventario','productos','clientes','usuarios','proveedores','configuracion','ventas','devoluciones'],
  'cajero'   => ['dashboard','ventas','inventario.view','productos.view','clientes.view'],
  'empleado' => ['dashboard','inventario','productos','clientes.view'],
];

$can = function (string $perm) use ($rol, $ACL): bool {
  $grants = $ACL[$rol] ?? [];
  if (in_array(strtolower($perm), $grants, true)) return true;
  [$basePerm] = explode('.', strtolower($perm), 2);
  return in_array("$basePerm.view", $grants, true);
};
?>

<!-- ⚠️ Este parcial NO crea <aside> ni importa CSS/JS -->
<header class="sidebar-header">
  <!-- Hamburguesa (móvil/off-canvas; en desktop actúa como colapsar) -->
  <button id="sidebarToggle"
          class="hamburger"
          aria-label="Abrir menú"
          aria-expanded="false"
          aria-controls="adminSidebar">
    <span></span><span></span><span></span>
  </button>

  <div class="brand">
    <img src="<?= htmlspecialchars($base, ENT_QUOTES) ?>/assets/img/logo.png" alt="Logo" />
  </div>

  <!-- Pin (desktop: colapsa/expande) -->
  <button id="pinBtn" class="pin-btn" type="button" title="Colapsar" aria-label="Colapsar menú">
    <i class="bi bi-chevron-double-left"></i>
  </button>

  <!-- Cerrar (solo móvil; cierra off-canvas) -->
  <button type="button" class="sidebar-close" aria-label="Cerrar menú">&times;</button>
</header>

<nav class="sidebar-nav" role="navigation" aria-label="Menú principal">
  <ul class="menu">
    <?php if ($can('dashboard')): ?>
      <li class="<?= $active('dashboard') ?>">
        <a href="?r=admin/dashboard" aria-current="<?= $sec==='dashboard'?'page':'false' ?>">
          <i class="bi bi-house-door"></i><span class="label">Inicio</span>
        </a>
      </li>
    <?php endif; ?>

    <?php if ($can('inventario')): ?>
      <li class="<?= $active('inventario') ?>">
        <a href="?r=admin/inventario" aria-current="<?= $sec==='inventario'?'page':'false' ?>">
          <i class="bi bi-box-seam"></i><span class="label">Inventario</span>
        </a>
      </li>
    <?php endif; ?>

    <?php if ($can('productos')): ?>
      <li class="<?= $active('productos') ?>">
        <a href="?r=admin/productos" aria-current="<?= $sec==='productos'?'page':'false' ?>">
          <i class="bi bi-bag"></i><span class="label">Productos</span>
        </a>
      </li>
    <?php endif; ?>


    <?php if ($can('ventas')): ?>
      <li class="<?= $active('ventas') ?>">
        <a href="?r=admin/ventas" aria-current="<?= $sec==='ventas'?'page':'false' ?>">
          <i class="bi bi-receipt-cutoff"></i><span class="label">Reporte Ventas</span>
        </a>
      </li>
    <?php endif; ?>



      <?php if ($can('proveedores')): ?>
      <li class="<?= $active('proveedores') ?>">
        <a href="?r=admin/proveedores" aria-current="<?= $sec==='proveedores'?'page':'false' ?>">
          <i class="bi bi-truck"></i><span class="label">Proveedores</span>
        </a>
      </li>
    <?php endif; ?>

    <?php if ($can('clientes')): ?>
      <li class="<?= $active('clientes') ?>">
        <a href="?r=admin/clientes" aria-current="<?= $sec==='clientes'?'page':'false' ?>">
          <i class="bi bi-person-badge"></i><span class="label">Clientes</span>
        </a>
      </li>
    <?php endif; ?>



    <?php if ($can('usuarios')): ?>
      <li class="<?= $active('usuarios') ?>">
        <a href="?r=admin/usuarios" aria-current="<?= $sec==='usuarios'?'page':'false' ?>">
          <i class="bi bi-people"></i><span class="label">Usuarios</span>
        </a>
      </li>
    <?php endif; ?>

    <?php if ($can('devoluciones')): ?>
  <li class="<?= $active('devoluciones') ?>">
    <a href="?r=admin/devoluciones" aria-current="<?= $sec==='devoluciones'?'page':'false' ?>">
      <i class="bi bi-arrow-counterclockwise"></i><span class="label">Devoluciones</span>
    </a>
  </li>
<?php endif; ?>


    <?php if ($can('configuracion')): ?>
      <li class="<?= $active('configuracion') ?>">
        <a href="?r=admin/configuracion" aria-current="<?= $sec==='configuracion'?'page':'false' ?>">
          <i class="bi bi-gear"></i><span class="label">Configuración</span>
        </a>
      </li>
    <?php endif; ?>

    <li>
      <a href="?r=admin_logout" data-no-spa rel="nofollow">
        <i class="bi bi-box-arrow-right"></i><span class="label">Salir</span>
      </a>
    </li>
  </ul>
</nav>
