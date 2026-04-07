<?php
// --- LÓGICA DE SESIÓN (SEGURIDAD Y DATOS) ---
// Verifica si existe una sesión activa para evitar errores al intentar acceder a $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validamos si el cliente está logueado. 
// Si existe la sesión 'cliente', guardamos sus datos en variables seguras.
$logueado = isset($_SESSION['cliente']) && !empty($_SESSION['cliente']['nombres']);

// Extraemos los datos de la sesión (Basado en tu estructura de phpMyAdmin)
// Usamos htmlspecialchars para evitar ataques XSS al mostrar texto en el navegador.
$nombreUsuario = $logueado ? htmlspecialchars($_SESSION['cliente']['nombres'] . " " . $_SESSION['cliente']['apellidos']) : 'Usuario';
$emailUsuario  = $logueado ? htmlspecialchars($_SESSION['cliente']['correo']) : 'No disponible';
$telUsuario    = $logueado ? htmlspecialchars($_SESSION['cliente']['telefono']) : '3132254044'; // Valor por defecto de tus capturas
$idUsuario     = $logueado ? $_SESSION['cliente']['id_cliente'] : null;

// --- CONFIGURACIÓN DE RUTAS ---
$ocultar_navbar = true;
if (!isset($asset)) { 
    $asset = fn($path) => "/Hieribal2/public/" . $path; 
}
$base = "/Hieribal2/public";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil · <?= $nombreUsuario ?></title>
    <link rel="stylesheet" href="<?= $asset('assets/css/perfil.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="hp-layout">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <a href="<?= $base ?>/?r=home">
                <img src="<?= $asset('assets/img/logo1.png') ?>" alt="Mi Hieribal">
            </a>
        </div>

        <div class="profile-mini-side">
            <div class="profile-ava-big" id="preview" onclick="document.getElementById('inputFoto').click();">
                <?= strtoupper(substr($nombreUsuario, 0, 1)) ?>
            </div>
            <p class="user-name-side"><?= $nombreUsuario ?></p>
            <span class="pro-badge">CLIENTE PRO</span>
        </div>

        <nav class="nav-group">
            <a href="<?= $base ?>/?r=perfil" class="nav-link-custom active">
                <span>🏠</span> Inicio
            </a>
            <a href="<?= $base ?>/?r=carrito" class="nav-link-custom">
                <span>🛒</span> Mi carrito
                <?php if (($totalCarrito ?? 0) > 0): ?>
                    <span class="nav-badge"><?= $totalCarrito ?></span>
                <?php endif; ?>
            </a>
        </nav>

        <div style="margin-top: auto;">
            <a href="<?= $base ?>/?r=logout" class="nav-link-custom logout-style">
                <span>🚪</span> Cerrar sesión
            </a>
        </div>
    </aside>

    <main class="perfil-dashboard">
        <h2 class="page-title">Mi perfil <span>· <?= $nombreUsuario ?></span></h2>

        <div class="stats-row">
            <div class="stat-card">
                <span class="stat-label">Mis pedidos</span>
                <div class="stat-num"><?= $usuario['total_pedidos'] ?? 12 ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Gastado</span>
                <div class="stat-num gold">$<?= number_format($usuario['total_gastado'] ?? 3556800, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label">En Carrito</span>
                <div class="stat-num"><?= $totalCarrito ?? 0 ?> items</div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Devoluciones</span>
                <div class="stat-num"><?= $usuario['devoluciones'] ?? 0 ?></div>
            </div>
        </div>

        <div class="mid-row">
            <div class="card">
                <h4 class="card-title">📈 Compras por mes</h4>
                <div class="chart-wrap">
                    <canvas id="chartPedidos"></canvas>
                </div>
            </div>
            <div class="card">
                <h4 class="card-title">🔥 Productos más comprados</h4>
                <div class="chart-wrap">
                    <canvas id="chartTop"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <h4 class="card-title">👤 Datos Personales</h4>
            <div class="info-box">
                <div class="pf-row">
                    <span class="pf-label">Email</span>
                    <strong><?= $emailUsuario ?></strong>
                </div>
                <div class="pf-row">
                    <span class="pf-label">Teléfono</span>
                    <strong><?= $telUsuario ?></strong>
                </div>
                <div class="pf-row">
                    <span class="pf-label">Ciudad</span>
                    <strong><?= htmlspecialchars($usuario['ciudad'] ?? 'Bogotá') ?></strong>
                </div>
            </div>
            <input type="file" id="inputFoto" accept="image/*" onchange="previewImage(event)" style="display:none;">
            <button class="btn-edit">✏️ Editar perfil</button>
        </div>
    </main>
</div>

<script>
    // Estos datos deben venir de tu controlador para alimentar las gráficas
    window.statsGrafica = <?= json_encode($statsGrafica ?? []) ?>;
    window.productosTop = <?= json_encode($productosTop ?? []) ?>;
</script>

<script src="<?= $asset('assets/js/perfil.js') ?>"></script>

</body>
</html>