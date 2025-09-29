<?php
// views/admin/sidebar.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$base  = $base ?? ($this->config['app']['base_url'] ?? '');
$route = $_GET['r'] ?? 'admin/dashboard';

// Detectar sección activa
$sec = (function(string $r): string {
  if (strpos($r, 'admin/') === 0) {
    $parts = explode('/', $r, 3);
    return $parts[1] ?? 'dashboard';
  }
  if (strpos($r, 'admin_') === 0) {
    return substr($r, strlen('admin_'));
  }
  return 'dashboard';
})($route);

$active = fn(string $key) => $sec === $key ? 'active' : '';
$rol = strtolower($_SESSION['admin']['rol'] ?? 'empleado');

$ACL = [
  'admin'    => ['dashboard','ventas','inventario','productos','usuarios','Proveedores','configuracion','reportes'],
  'cajero'   => ['dashboard','ventas','inventario.view','productos.view'],
  'empleado' => ['dashboard','inventario','productos'],
];
$can = function(string $perm) use ($rol, $ACL): bool {
  $grants = $ACL[$rol] ?? [];
  if (in_array($perm, $grants, true)) return true;
  [$base] = explode('.', $perm, 2);
  return in_array("$base.view", $grants, true);
};
?>

<div class="sidebar-header">
  <div class="brand">
    <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Logo" />
    <b class="brand-text"></b>
  </div>
  <button type="button" class="pin-btn" data-pin title="Colapsar">
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

  <?php if ($can('Proveedores')): ?>
    <li class="<?= $active('Proveedores') ?>">
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
    <a href="?r=admin_logout" data-no-spa><i class="bi bi-box-arrow-right"></i><span class="label">Salir</span></a>
  </li>
</ul>
