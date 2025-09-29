<?php
// views/admin/sidebar.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$base  = $base ?? ($this->config['app']['base_url'] ?? '');
$route = $_GET['r'] ?? 'admin/dashboard';

/* Detectar sección activa a partir de la ruta */
$sec = (function (string $r): string {
  if (strpos($r, 'admin/') === 0) {
    $parts = explode('/', $r, 3);
    return strtolower($parts[1] ?? 'dashboard'); // 🔽 forzamos minúsculas
  }
  if (strpos($r, 'admin_') === 0) {
    return strtolower(substr($r, strlen('admin_'))); // 🔽 minúsculas
  }
  return 'dashboard';
})($route);

$active = fn (string $key) => $sec === strtolower($key) ? 'active' : '';

$rol = strtolower($_SESSION['admin']['rol'] ?? 'empleado');

/* ACL: usa claves en minúscula para que coincidan con $sec */
$ACL = [
  'admin'    => ['dashboard','ventas','inventario','productos','usuarios','proveedores','configuracion','reportes'],
  'cajero'   => ['dashboard','ventas','inventario.view','productos.view'],
  'empleado' => ['dashboard','inventario','productos'],
];

$can = function (string $perm) use ($rol, $ACL): bool {
  $grants = $ACL[$rol] ?? [];
  if (in_array(strtolower($perm), $grants, true)) return true;
  [$basePerm] = explode('.', strtolower($perm), 2);
  return in_array("$basePerm.view", $grants, true);
};
?>

<div class="sidebar-header">
  <div class="brand">
    <img src="<?= htmlspecialchars($base, ENT_QUOTES) ?>/assets/img/logo.png" alt="Logo" />
    <b class="brand-text"></b>
  </div>

  <!-- Botón interno (colapsar). También tendrás el flotante arriba con JS -->
  <button type="button" id="pinBtn" class="pin-btn" data-pin aria-label="Colapsar/Expandir menú" title="Colapsar">
    <i class="bi bi-chevron-double-left"></i>
  </button>
</div>

<ul class="menu">
  <?php if ($can('dashboard')): ?>
    <li class="<?= $active('dashboard') ?>">
      <a href="?r=admin/dashboard"><i class="bi bi-house-door"></i><span class="label">Inicio</span></a>
    </li>
  <?php endif; ?>

  <?php if ($can('inventario')): ?>
    <li class="<?= $active('inventario') ?>">
      <a href="?r=admin/inventario"><i class="bi bi-box-seam"></i><span class="label">Inventario</span></a>
    </li>
  <?php endif; ?>

  <?php if ($can('productos')): ?>
    <li class="<?= $active('productos') ?>">
      <a href="?r=admin/productos"><i class="bi bi-bag"></i><span class="label">Productos</span></a>
    </li>
  <?php endif; ?>

  <?php if ($can('proveedores')): ?> <!-- 🔽 minúscula -->
    <li class="<?= $active('proveedores') ?>">
      <a href="?r=admin/proveedores"><i class="bi bi-truck"></i><span class="label">Proveedores</span></a>
    </li>
  <?php endif; ?>

  <?php if ($can('usuarios')): ?>
    <li class="<?= $active('usuarios') ?>">
      <a href="?r=admin/usuarios"><i class="bi bi-people"></i><span class="label">Usuarios</span></a>
    </li>
  <?php endif; ?>

  <?php if ($can('configuracion')): ?>
    <li class="<?= $active('configuracion') ?>">
      <a href="?r=admin/configuracion"><i class="bi bi-gear"></i><span class="label">Configuración</span></a>
    </li>
  <?php endif; ?>

  <li>
    <a href="?r=admin_logout" data-no-spa rel="nofollow">
      <i class="bi bi-box-arrow-right"></i><span class="label">Salir</span>
    </a>
  </li>
</ul>
