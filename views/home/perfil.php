
<?php
$ocultar_navbar = true;
?>

<?php
// views/home/perfil.php
// Variables esperadas del controlador:
//   $usuario, $pedidos, $carrito, $statsGrafica, $productosTop
//   $totalCarrito, $totalValorCarrito, $seccion

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
<title>Mi Perfil · <?= htmlspecialchars($usuario['nombre']) ?></title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* ── Reset ── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
a{text-decoration:none;color:inherit;}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0f4f0;color:#1a1a1a;min-height:100vh;}

/* ── Variables ── */
:root{
  --verde:#1D9E75;--verde-l:#E1F5EE;--verde-m:#9FE1CB;--verde-d:#0F6E56;
  --ambar:#BA7517;--ambar-l:#FAEEDA;
  --rojo:#E24B4A;--rojo-l:#FCEBEB;
  --border:rgba(0,0,0,0.08);
  --radius:12px;--radius-sm:8px;
}

/* ══ LAYOUT ══ */
.hp-wrap{display:grid;grid-template-columns:230px 1fr;min-height:100vh;}

/* ══ SIDEBAR ══ */
.hp-sidebar{
  background:#f7f9f5;
  border-right:1px solid var(--border);
  padding:1.5rem 1rem;
  display:flex;flex-direction:column;gap:1.5rem;
  position:sticky;top:0;height:100vh;overflow-y:auto;
}
.sidebar-logo{display:flex;justify-content:center;padding-bottom:1.25rem;border-bottom:1px solid var(--border);}
.sidebar-logo img{height:42px;object-fit:contain;}

.avatar-wrap{display:flex;flex-direction:column;align-items:center;gap:6px;}
.avatar-circle{
  width:64px;height:64px;border-radius:50%;
  background:var(--verde);color:#fff;
  font-size:22px;font-weight:600;
  display:flex;align-items:center;justify-content:center;
}
.user-name{font-size:14px;font-weight:600;text-align:center;}
.user-email{font-size:11px;color:#6b7280;text-align:center;}
.pro-badge{background:var(--verde-d);color:var(--verde-m);font-size:10px;font-weight:600;padding:2px 10px;border-radius:20px;letter-spacing:.06em;}

.nav-section{display:flex;flex-direction:column;gap:2px;}
.nav-label{font-size:10px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;padding:0 8px;margin-bottom:4px;}
.nav-item{
  display:flex;align-items:center;gap:10px;
  padding:8px 10px;border-radius:var(--radius-sm);
  font-size:13px;color:#6b7280;
  transition:background .15s,color .15s;
}
.nav-item:hover{background:#edf3ec;color:#1a1a1a;}
.nav-item.active{background:var(--verde-l);color:var(--verde-d);font-weight:600;}
.nav-icon{font-size:14px;flex-shrink:0;}
.nav-badge{margin-left:auto;font-size:10px;font-weight:600;padding:1px 7px;border-radius:20px;}
.nb-green{background:var(--verde-l);color:var(--verde-d);}
.nb-red{background:var(--rojo-l);color:var(--rojo);}

.sidebar-bottom{margin-top:auto;padding-top:1rem;border-top:1px solid var(--border);}
.logout-btn{display:flex;align-items:center;gap:8px;font-size:12px;color:#6b7280;padding:6px 10px;border-radius:var(--radius-sm);transition:color .15s,background .15s;}
.logout-btn:hover{color:var(--rojo);background:var(--rojo-l);}

/* ══ MAIN ══ */
.hp-main{padding:1.75rem;display:flex;flex-direction:column;gap:1.25rem;}
.page-title{font-size:20px;font-weight:600;}
.page-title span{color:#6b7280;font-weight:400;}

/* Stats */
.stats-row{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;}
.stat-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:14px 16px;display:flex;flex-direction:column;gap:5px;}
.stat-label{font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;}
.stat-value{font-size:22px;font-weight:600;line-height:1.2;}
.sv-green{color:var(--verde);}
.sv-amber{color:var(--ambar);}
.stat-sub{font-size:11px;color:#9ca3af;}
.tag{display:inline-block;font-size:11px;font-weight:600;padding:2px 9px;border-radius:20px;width:fit-content;}
.tag-green{background:var(--verde-l);color:var(--verde-d);}
.tag-red{background:var(--rojo-l);color:var(--rojo);}
.tag-amber{background:var(--ambar-l);color:var(--ambar);}

/* Grid */
.grid-2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1.25rem;}

/* Cards */
.card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;}
.card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;}
.card-title{font-size:13px;font-weight:600;color:#6b7280;}
.card-link{font-size:11px;color:var(--verde);}
.card-link:hover{text-decoration:underline;}

/* Chart header */
.chart-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;}
.chart-legend{display:flex;gap:12px;}
.legend-item{display:flex;align-items:center;gap:5px;font-size:11px;color:#6b7280;}
.dot{width:7px;height:7px;border-radius:50%;display:inline-block;}
.dot-green{background:var(--verde);}
.dot-amber{background:var(--ambar);}

/* Perfil box */
.perfil-box .profile-header{display:flex;align-items:center;gap:12px;margin-bottom:1rem;}
.avatar-sm{width:44px;height:44px;border-radius:50%;background:var(--verde);color:#fff;font-size:14px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.perfil-box .profile-header h3{font-size:15px;font-weight:600;margin-bottom:2px;}
.perfil-box .profile-header p{font-size:12px;color:#6b7280;}
.info-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);font-size:12px;}
.info-row:last-child{border-bottom:none;}
.info-key{color:#6b7280;}
.info-val{font-weight:600;}
.info-val.link{color:var(--verde);}
.edit-btn{margin-top:1rem;width:100%;padding:8px 12px;border-radius:var(--radius-sm);border:1px solid var(--border);background:transparent;color:#1a1a1a;font-size:13px;display:flex;align-items:center;justify-content:center;gap:6px;cursor:pointer;transition:background .15s;}
.edit-btn:hover{background:#f9fafb;}

/* Tabla pedidos */
.orders-table{width:100%;border-collapse:collapse;font-size:12px;}
.orders-table th{text-align:left;font-size:10px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;padding:0 0 10px;border-bottom:1px solid var(--border);}
.orders-table td{padding:9px 0;border-bottom:1px solid var(--border);}
.orders-table tr:last-child td{border-bottom:none;}
.pedido-id{color:var(--verde)!important;font-weight:600;}
.status-pill{display:inline-block;font-size:10px;font-weight:600;padding:3px 9px;border-radius:20px;}
.status-entregado{background:var(--verde-l);color:var(--verde-d);}
.status-en-camino{background:var(--ambar-l);color:var(--ambar);}
.status-cancelado{background:var(--rojo-l);color:var(--rojo);}
.status-pendiente{background:#f3f4f6;color:#6b7280;}

/* Carrito */
.cart-item{display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--border);}
.cart-item:last-of-type{border-bottom:none;}
.cart-thumb{width:38px;height:38px;border-radius:var(--radius-sm);background:var(--verde-l);display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;overflow:hidden;}
.cart-thumb img{width:100%;height:100%;object-fit:cover;}
.cart-info{flex:1;min-width:0;}
.cart-name{font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.cart-sub{font-size:11px;color:#6b7280;margin-top:2px;}
.cart-price{font-size:13px;font-weight:600;color:var(--verde);flex-shrink:0;}

/* Producto favorito */
.favorito-card{display:flex;align-items:center;gap:1rem;}
.favorito-icon{font-size:40px;}
.favorito-card h2{font-size:18px;font-weight:600;}
.favorito-card p{font-size:13px;color:#6b7280;margin-top:4px;}

/* Empty */
.empty-state{color:#6b7280;font-size:13px;padding:1.5rem 0;text-align:center;}

/* Responsive */
@media(max-width:1024px){.stats-row{grid-template-columns:repeat(2,1fr);}}
@media(max-width:768px){
  .hp-wrap{grid-template-columns:1fr;}
  .hp-sidebar{position:static;height:auto;flex-direction:row;flex-wrap:wrap;padding:1rem;gap:1rem;}
  .sidebar-logo{padding-bottom:0;border-bottom:none;}
  .hp-main{padding:1rem;}
  .grid-2{grid-template-columns:1fr;}
  .stats-row{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:480px){.stats-row{grid-template-columns:1fr;}}
</style>
</head>
<body>

<div class="hp-wrap">

  <!-- ═══ SIDEBAR ═══ -->
  <aside class="hp-sidebar">

    <div class="sidebar-logo">
      <a href="<?= $base ?>/?r=home">
        <img src="<?= $asset('assets/img/logo1.png') ?>" alt="Mi Hieribal">
      </a>
    </div>

    <div class="avatar-wrap">
      <div class="avatar-circle">
        <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
      </div>
      <p class="user-name"><?= htmlspecialchars($usuario['nombre']) ?></p>
      <p class="user-email"><?= htmlspecialchars($usuario['email']) ?></p>
      <?php if (!empty($usuario['es_pro'])): ?>
        <span class="pro-badge">PRO</span>
      <?php endif; ?>
    </div>

    <nav class="nav-section">
      <span class="nav-label">Mis compras</span>
      <a href="<?= $base ?>/?r=pedidos" class="nav-item <?= ($seccion ?? '') === 'pedidos' ? 'active' : '' ?>">
        <span class="nav-icon">📦</span> Mis pedidos
      </a>
      <a href="<?= $base ?>/?r=carrito" class="nav-item <?= ($seccion ?? '') === 'carrito' ? 'active' : '' ?>">
        <span class="nav-icon">🛒</span> Mi carrito
        <?php if (($totalCarrito ?? 0) > 0): ?>
          <span class="nav-badge nb-green"><?= $totalCarrito ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= $base ?>/?r=devoluciones" class="nav-item <?= ($seccion ?? '') === 'devoluciones' ? 'active' : '' ?>">
        <span class="nav-icon">↩</span> Devoluciones
        <?php if (!empty($usuario['devoluciones']) && $usuario['devoluciones'] > 0): ?>
          <span class="nav-badge nb-red"><?= $usuario['devoluciones'] ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= $base ?>/?r=resenas" class="nav-item <?= ($seccion ?? '') === 'resenas' ? 'active' : '' ?>">
        <span class="nav-icon">⭐</span> Mis reseñas
      </a>
    </nav>

    <nav class="nav-section">
      <span class="nav-label">Mi cuenta</span>
      <a href="<?= $base ?>/?r=perfil" class="nav-item active">
        <span class="nav-icon">👤</span> Mi perfil
      </a>
      <a href="<?= $base ?>/?r=notificaciones" class="nav-item">
        <span class="nav-icon">🔔</span> Notificaciones
      </a>
      <a href="<?= $base ?>/?r=pagos" class="nav-item">
        <span class="nav-icon">💳</span> Métodos de pago
      </a>
    </nav>

    <div class="sidebar-bottom">
      <a href="<?= $base ?>/?r=logout" class="logout-btn">
        <span>⏻</span> Cerrar sesión
      </a>
    </div>

  </aside>

  <!-- ═══ MAIN ═══ -->
  <main class="hp-main">

    <p class="page-title">Mi perfil <span>· <?= htmlspecialchars($usuario['nombre']) ?></span></p>

    <!-- STATS -->
    <div class="stats-row">
      <div class="stat-card">
        <span class="stat-label">Mis pedidos</span>
        <span class="stat-value sv-green"><?= $usuario['total_pedidos'] ?? 0 ?></span>
        <?php if (!empty($usuario['pedidos_este_mes'])): ?>
          <span class="tag tag-green">+<?= $usuario['pedidos_este_mes'] ?> este mes</span>
        <?php endif; ?>
      </div>
      <div class="stat-card">
        <span class="stat-label">Gastado</span>
        <span class="stat-value sv-amber">$<?= number_format($usuario['total_gastado'] ?? 0, 0, ',', '.') ?></span>
        <span class="stat-sub">desde <?= date('M Y', strtotime($usuario['fecha_registro'] ?? 'now')) ?></span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Carrito</span>
        <span class="stat-value"><?= $totalCarrito ?? 0 ?> items</span>
        <?php if (!empty($totalValorCarrito)): ?>
          <span class="stat-sub">$<?= number_format($totalValorCarrito, 0, ',', '.') ?></span>
        <?php endif; ?>
      </div>
      <div class="stat-card">
        <span class="stat-label">Devoluciones</span>
        <span class="stat-value"><?= $usuario['devoluciones'] ?? 0 ?></span>
        <?php if (!empty($usuario['devoluciones'])): ?>
          <a href="<?= $base ?>/?r=devoluciones" class="tag tag-red">ver historial</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- GRÁFICAS -->
    <div class="grid-2">

      <div class="card">
        <div class="chart-header">
          <h4 class="card-title">Compras por mes</h4>
          <div class="chart-legend">
            <span class="legend-item"><i class="dot dot-green"></i> pedidos</span>
            <span class="legend-item"><i class="dot dot-amber"></i> gastado</span>
          </div>
        </div>
        <?php if (!empty($statsGrafica)): ?>
          <canvas id="chartPedidos" height="130"></canvas>
        <?php else: ?>
          <p class="empty-state">Sin datos de compras aún 🌿</p>
        <?php endif; ?>
      </div>

      <div class="card">
        <div class="chart-header">
          <h4 class="card-title">🔥 Productos más comprados</h4>
        </div>
        <?php if (!empty($productosTop)): ?>
          <canvas id="chartTop" height="130"></canvas>
        <?php else: ?>
          <p class="empty-state">Sin datos de productos aún 🌿</p>
        <?php endif; ?>
      </div>

    </div>

    <!-- FAVORITO + PERFIL -->
    <div class="grid-2">

      <div class="card">
        <div class="card-header">
          <h4 class="card-title">🥇 Tu producto favorito</h4>
        </div>
        <?php if (!empty($productosTop)): ?>
          <div class="favorito-card">
            <span class="favorito-icon">🌿</span>
            <div>
              <h2><?= htmlspecialchars($productosTop[0]['nombre_producto']) ?></h2>
              <p><?= $productosTop[0]['porcentaje'] ?>% de tus compras</p>
            </div>
          </div>
        <?php else: ?>
          <p class="empty-state">No hay datos aún 🌿</p>
        <?php endif; ?>
      </div>

      <div class="card perfil-box">
        <div class="profile-header">
          <div class="avatar-sm">
            <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
          </div>
          <div>
            <h3><?= htmlspecialchars($usuario['nombre']) ?></h3>
            <p>Cliente <?= !empty($usuario['es_pro']) ? 'PRO' : 'Regular' ?> · <?= htmlspecialchars($usuario['ciudad'] ?? '') ?></p>
          </div>
        </div>
        <div>
          <div class="info-row">
            <span class="info-key">Email</span>
            <span class="info-val link"><?= htmlspecialchars($usuario['email']) ?></span>
          </div>
          <div class="info-row">
            <span class="info-key">Teléfono</span>
            <span class="info-val"><?= htmlspecialchars($usuario['telefono'] ?? '—') ?></span>
          </div>
          <div class="info-row">
            <span class="info-key">Método pago</span>
            <span class="info-val"><?= htmlspecialchars($usuario['metodo_pago'] ?? '—') ?></span>
          </div>
          <div class="info-row">
            <span class="info-key">Cliente desde</span>
            <span class="info-val"><?= date('M Y', strtotime($usuario['fecha_registro'] ?? 'now')) ?></span>
          </div>
        </div>
        <a href="<?= $base ?>/?r=perfil&accion=editar" class="edit-btn">✏ Editar perfil</a>
      </div>

    </div>

    <!-- ÚLTIMOS PEDIDOS -->
    <!-- ═══ PEDIDOS AGRUPADOS (NUEVO) ═══ -->
<?php if (!empty($perfil["pedidosAgrupados"])): ?>

<div class="card">
  <div class="card-header">
    <h4 class="card-title">📦 Historial de compras</h4>
  </div>

  <?php foreach ($perfil["pedidosAgrupados"] as $pedido): ?>

    <?php 
    $key = $pedido["fecha"] . "_" . $pedido["metodo_pago"];
    ?>

    <div style="border:1px solid var(--border); padding:12px; margin-bottom:12px; border-radius:10px; background:#fafafa;">

      <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
        <strong>📅 <?= $pedido["fecha"] ?></strong>
        <strong style="color:var(--verde);">
          $<?= number_format($pedido["total"], 0, ',', '.') ?>
        </strong>
      </div>

      <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">
        Método: <?= $pedido["metodo_pago"] ?> · 
        Dirección: <?= $pedido["direccion_envio"] ?>
      </div>

      <div style="font-size:12px;">
        <strong>Productos:</strong><br>

        <?php foreach ($perfil["detallePedidos"][$key] as $prod): ?>
          • <?= $prod["nombre_producto"] ?> 
          (<?= $prod["cantidad"] ?>) 
          → $<?= number_format($prod["subtotal"], 0, ',', '.') ?><br>
        <?php endforeach; ?>
      </div>

    </div>

  <?php endforeach; ?>

</div>

<?php endif; ?>

<!-- ═══ SCRIPTS CHARTS ═══ -->
<?php if (!empty($statsGrafica)): ?>
<script>
(function(){
  const labels  = <?= json_encode(array_column($statsGrafica, 'mes')) ?>;
  const pedidos = <?= json_encode(array_map('intval',   array_column($statsGrafica, 'total_pedidos'))) ?>;
  const gastado = <?= json_encode(array_map('floatval', array_column($statsGrafica, 'total_gastado'))) ?>;

  new Chart(document.getElementById('chartPedidos'), {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Pedidos',
          data: pedidos,
          backgroundColor: '#9FE1CB',
          borderRadius: 4,
          yAxisID: 'y'
        },
        {
          label: 'Gastado',
          data: gastado,
          type: 'line',
          borderColor: '#BA7517',
          backgroundColor: 'transparent',
          tension: 0.4,
          pointBackgroundColor: '#BA7517',
          pointRadius: 4,
          borderWidth: 2,
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { display: false } },
      scales: {
        y:  { type:'linear', position:'left',  grid:{ color:'rgba(0,0,0,0.04)' }, ticks:{ font:{size:10}, color:'#9ca3af', precision:0 } },
        y1: { type:'linear', position:'right', grid:{ drawOnChartArea:false }, ticks:{ font:{size:10}, color:'#BA7517', callback: v => '$'+Math.round(v/1000)+'K' } },
        x:  { grid:{ display:false }, ticks:{ font:{size:10}, color:'#9ca3af' } }
      }
    }
  });
})();
</script>
<?php endif; ?>

<?php if (!empty($productosTop)): ?>
<script>
(function(){
  const productos = <?= json_encode($productosTop) ?>;
  const labels = productos.map(p => p.nombre_producto);
  const data   = productos.map(p => parseFloat(p.total));
  const colors = ['#1D9E75','#9FE1CB','#0F6E56','#5DCAA5','#E1F5EE'];

  new Chart(document.getElementById('chartTop'), {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{ data, backgroundColor: colors.slice(0, data.length), borderWidth: 0 }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'right', labels:{ font:{size:11}, color:'#6b7280', padding:12, boxWidth:10 } }
      },
      cutout: '65%'
    }
  });
})();
</script>
<?php endif; ?>

</body>
</html>
