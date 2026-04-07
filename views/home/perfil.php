<?php
/**
 * --- LÓGICA DE SESIÓN Y SEGURIDAD ---
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$logueado = isset($_SESSION['cliente']) && !empty($_SESSION['cliente']['nombres']);

/**
 * Datos del Usuario
 */
$nombreUsuario = $logueado ? htmlspecialchars($_SESSION['cliente']['nombres'] . " " . $_SESSION['cliente']['apellidos']) : 'Usuario';
$emailUsuario  = $logueado ? htmlspecialchars($_SESSION['cliente']['correo']) : 'No disponible';
$telUsuario    = $logueado ? htmlspecialchars($_SESSION['cliente']['telefono']) : '3132254044';
$idUsuario     = $logueado ? $_SESSION['cliente']['id_cliente'] : null;

/**
 * --- LÓGICA DE PROMOCIONES ---
 * Usamos el primer producto de la lista de más comprados para la oferta.
 */
$productoParaPromo = "Té de Manzanilla"; 
if (!empty($productosTop) && isset($productosTop[0]['nombre_producto'])) {
    $productoParaPromo = $productosTop[0]['nombre_producto'];
}
$tipoOferta = "¡2x1 SOLO HOY!";

/**
 * Configuración de Rutas y Tema
 */
$temaActual = $_COOKIE['theme'] ?? 'light';
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
    
    <style>
        header, .navbar, nav:not(.nav-group) { display: none !important; }
        
        /* Estilo para la tarjeta de promoción 2x1 */
        .promo-highlight {
            border: 2px solid #BA7517 !important;
            background: linear-gradient(145deg, rgba(186, 117, 23, 0.1), transparent) !important;
            position: relative;
            overflow: hidden;
        }
        .promo-tag {
            position: absolute;
            top: 10px;
            right: -20px;
            background: #BA7517;
            color: white;
            padding: 5px 25px;
            transform: rotate(45deg);
            font-size: 0.7rem;
            font-weight: bold;
        }
    </style>
</head>

<body class="<?= $temaActual === 'dark' ? 'dark-mode' : '' ?>">

<input type="hidden" id="js_statsGrafica" value='<?= json_encode($statsGrafica ?? []) ?>'>
<input type="hidden" id="js_productosTop" value='<?= json_encode($productosTop ?? []) ?>'>

<div class="hp-layout">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <a href="<?= $base ?>/?r=home">
                <img src="<?= $asset('assets/img/logo1.png') ?>" alt="Mi Hieribal">
            </a>
        </div>

        <div class="profile-mini-side">
            <div class="profile-ava-big" id="avaPreview" style="cursor: pointer;">
                <span><?= strtoupper(substr($nombreUsuario, 0, 1)) ?></span>
            </div>
            <p class="user-name-side"><?= $nombreUsuario ?></p>
            <span class="pro-badge">CLIENTE PRO</span>
        </div>

        <nav class="nav-group">
            <a href="<?= $base ?>/?r=perfil" class="nav-link-custom active">
                <span>🏠</span> <span class="nav-text">Inicio</span>
            </a>
            <a href="<?= $base ?>/?r=carrito_Compra" class="nav-link-custom">
                <span>🛒</span> <span class="nav-text">Mi carrito</span>
            </a>
        </nav>

        <div class="logout-style">
            <a href="<?= $base ?>/?r=logout" class="nav-link-custom" style="color: inherit; padding: 0;">
                <span>🚪</span> <span class="nav-text">Cerrar sesión</span>
            </a>
        </div>
    </aside>

    <main class="perfil-dashboard">
        <h2 class="page-title">Mi perfil <span>· <?= $nombreUsuario ?></span></h2>

        <div class="stats-row">
            <div class="stat-card">
                <span class="stat-label">Mis pedidos</span>
                <div class="stat-num"><?= $usuario['total_pedidos'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Gastado</span>
                <div class="stat-num gold">$<?= number_format($usuario['total_gastado'] ?? 0, 0, ',', '.') ?></div>
            </div>
            
            <div class="stat-card promo-highlight">
                <div class="promo-tag">OFERTA</div>
                <span class="stat-label" style="color: #BA7517;"><b><?= $tipoOferta ?></b></span>
                <div class="stat-num" style="font-size: 1.1rem; color: var(--text-color);">
                    <?= $productoParaPromo ?>
                </div>
                <a href="<?= $base ?>/?r=home" style="color: #1D9E75; font-size: 0.8rem; text-decoration: none; font-weight: bold;">
                    Ver productos →
                </a>
            </div>
        </div>

        <div class="mid-row">
            <div class="card">
                <h4 class="card-title">📈 Compras por mes</h4>
                <div class="chart-wrap"><canvas id="chartPedidos"></canvas></div>
            </div>
            <div class="card">
                <h4 class="card-title">🔥 Productos más comprados</h4>
                <div class="chart-wrap"><canvas id="chartTop"></canvas></div>
            </div>
        </div>

        <div class="card">
            <h4 class="card-title">👤 Datos Personales</h4>
            <div class="info-box">
                <div class="pf-row"><span class="pf-label">Email</span><strong><?= $emailUsuario ?></strong></div>
                <div class="pf-row"><span class="pf-label">Teléfono</span><strong><?= $telUsuario ?></strong></div>
                <div class="pf-row"><span class="pf-label">Ciudad</span><strong><?= htmlspecialchars($usuario['ciudad'] ?? 'Bogotá') ?></strong></div>
            </div>
            <input type="file" id="inputFoto" accept="image/*" style="display:none;">
            <button class="btn-dark-mode" id="btnThemeToggle">🌓 Cambiar Modo Oscuro / Claro</button>
        </div>
    </main>
</div>

<script src="<?= $asset('assets/js/perfil.js') ?>"></script>

</body>
</html>