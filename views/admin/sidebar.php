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

<ul class="menu">
  <li><a href="?r=admin/dashboard">Inicio</a></li>
  <li><a href="?r=admin/inventario">Inventario</a></li>
  <li><a href="?r=admin/productos">Productos</a></li>
  <li><a href="?r=admin/usuarios">Usuarios</a></li>
  <li><a href="?r=admin/configuracion">Configuración</a></li>
  <li><a href="?r=auth/logout">Salir</a></li>
</ul>

