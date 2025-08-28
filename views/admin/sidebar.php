<?php
// sidebar.php – menú lateral admin
$base  = $base ?? ''; // base pública, p.ej. /Hieribal2/public
$route = $_GET['r'] ?? 'admin_dashboard';

// función helper para clase activa
$act = fn($r) => $route === $r ? 'active' : '';
?>
<div class="logo">
  <img src="<?= $base ?>/assets/img/logo.png" alt="Logo Hieribal">
</div>

<nav class="sidebar-nav">
  <a class="<?= $act('admin_dashboard') ?>" href="?r=admin_dashboard">Inicio</a>
  <a class="<?= $act('inventario') ?>"      href="?r=inventario">Inventario</a>
  <a class="<?= $act('productos') ?>"       href="?r=productos">Productos</a>
  <a class="<?= $act('usuarios') ?>"        href="?r=usuarios">Usuarios</a>
  <a class="<?= $act('configuracion') ?>"   href="?r=configuracion">Configuración</a>
  <a class="<?= $act('logout') ?>"          href="?r=logout">Salir</a>
</nav>
