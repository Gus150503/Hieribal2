<?php
// views/admin/usuarios/verificacion.php (o el nombre que uses)
// Se renderiza dentro de plantilla.php (que ya carga Bootstrap y sidebar)
$base = $this->config['app']['base_url'] ?? '';
$t = $titulo ?? 'Verificación de correo';
$m = $msg ?? 'Resultado no disponible';
?>

<section class="card shadow-sm ui-pro border-0 rounded-4">
  <div class="card-body">
    <h1 class="h4 mb-2"><?= htmlspecialchars($t) ?></h1>
    <p class="mb-3"><?= htmlspecialchars($m) ?></p>

    <a class="btn btn-primary" href="<?= $base ?>/?r=admin_usuarios">
      Volver a Usuarios
    </a>
  </div>
</section>
