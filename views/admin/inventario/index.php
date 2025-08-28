<?php $partial = !empty($_GET['partial']); ?>
<?php if (!$partial) include __DIR__ . '/../_shell_start.php'; ?>

<section class="card">
  <h1><?= htmlspecialchars($titulo ?? 'Inventario') ?></h1>
  <p>En construcción.</p>
</section>

<?php if (!$partial) include __DIR__ . '/../_shell_end.php'; ?>
