<?php
$base      = $this->config['app']['base_url'] ?? '';
$titulo    = $titulo ?? 'App';
$extra_css = $extra_css ?? [];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titulo) ?></title>

  <!-- Bootstrap + Icons (CDN) -->
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <!-- CSS global de tu app (opcional) -->
  <link href="<?= $base ?>/assets/css/app.css?v=1" rel="stylesheet">

  <!-- Hojas extra que pasas desde el controlador -->
  <?php foreach ($extra_css as $href): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($href) ?>">
  <?php endforeach; ?>
</head>
<body class="bg-body-tertiary">

<div class="admin-wrap d-flex">
  <aside class="sidebar">
    <?php include __DIR__ . '/sidebar.php'; ?>
  </aside>

  <main class="content flex-fill p-3">
