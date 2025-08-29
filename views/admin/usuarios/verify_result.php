<?php $partial = !empty($_GET['partial']); ?>
<?php if (!$partial) include __DIR__ . '/../_shell_start.php'; ?>

<section class="card">
  <h1><?= htmlspecialchars($titulo ?? 'Verificación de correo') ?></h1>
  <p><?= htmlspecialchars($msg ?? 'Resultado no disponible') ?></p>

  <div style="margin-top:12px;">
    <a class="btn btn-primary" href="<?= $this->config['app']['base_url'] ?>/?r=admin_usuarios">Volver a Usuarios</a>
  </div>
</section>

<?php if (!$partial) include __DIR__ . '/../_shell_end.php'; ?>
