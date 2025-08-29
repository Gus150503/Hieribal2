<?php
// sidebar.php – menú lateral admin con ACL por rol

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$base  = $base ?? ($this->config['app']['base_url'] ?? '');
$route = $_GET['r'] ?? 'admin/dashboard';

// === Detectar sección actual para la clase 'active'
$sec = (function(string $r): string {
  if (strpos($r, 'admin/') === 0) {
    $parts = explode('/', $r, 3);        // admin/usuarios -> [admin, usuarios]
    return $parts[1] ?? 'dashboard';
  }
  if (strpos($r, 'admin_') === 0) {
    return substr($r, strlen('admin_')); // admin_usuarios -> usuarios
  }
  return 'dashboard';
})($route);

$active = fn(string $key) => $sec === $key ? 'active' : '';

// === Rol actual
$rol = strtolower($_SESSION['admin']['rol'] ?? 'empleado'); // 'admin' | 'cajero' | 'empleado'

// === ACL por rol
// Usa ".view" para permisos solo-lectura (el item se muestra, pero internamente bloqueas mutaciones).
$ACL = [
  'admin'    => ['dashboard','ventas','inventario','productos','usuarios','configuracion','reportes'],
  'cajero'   => ['dashboard','ventas','inventario.view','productos.view'],
  'empleado' => ['dashboard','inventario','productos'],
];

// Helper: ¿tiene permiso para ver el ítem?
$can = function(string $perm) use ($rol, $ACL): bool {
  $grants = $ACL[$rol] ?? [];
  if (in_array($perm, $grants, true)) return true;
  [$base] = explode('.', $perm, 2);
  return in_array("$base.view", $grants, true);
};
?>

<div class="logo">
  <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Logo Hieribal">
</div>

<ul class="menu">
  <?php if ($can('dashboard')): ?>
    <li class="<?= $active('dashboard') ?>"><a href="?r=admin/dashboard">Inicio</a></li>
  <?php endif; ?>

  <?php if ($can('inventario')): ?>
    <li class="<?= $active('inventario') ?>"><a href="?r=admin/inventario">Inventario</a></li>
  <?php endif; ?>

  <?php if ($can('productos')): ?>
    <li class="<?= $active('productos') ?>"><a href="?r=admin/productos">Productos</a></li>
  <?php endif; ?>

  <?php if ($can('usuarios')): ?>
    <li class="<?= $active('usuarios') ?>"><a href="?r=admin/usuarios">Usuarios</a></li>
  <?php endif; ?>

  <?php if ($can('configuracion')): ?>
    <li class="<?= $active('configuracion') ?>"><a href="?r=admin/configuracion">Configuración</a></li>
  <?php endif; ?>

  <!-- Si añades ventas:
  <?php // if ($can('ventas')): ?>
    <li class="<?= $active('ventas') ?>"><a href="?r=admin/ventas">Ventas</a></li>
  <?php // endif; ?>
  -->

  <li><a href="?r=admin_logout" data-no-spa>Salir</a></li>
</ul>
