<div class="perfil-dashboard">

<!-- STATS -->
<div class="stats-row">

<div class="stat-card">
<div class="stat-label">Mis pedidos</div>
<div class="stat-num green">148</div>
</div>

<div class="stat-card">
<div class="stat-label">Total gastado</div>
<div class="stat-num gold">$2.847.500</div>
</div>

<div class="stat-card">
<div class="stat-label">En carrito</div>
<div class="stat-num">3 items</div>
</div>

<div class="stat-card">
<div class="stat-label">Devoluciones</div>
<div class="stat-num">2</div>
</div>

</div>


<!-- FILA CENTRAL -->
<div class="mid-row">

<div class="card">

<div class="card-title">
Mis compras por mes
</div>

<div class="chart-wrap">
<canvas id="chartPedidos"></canvas>
</div>

</div>


<div class="card">

<div class="profile-mini">

<div class="profile-ava-big">
<?= strtoupper(substr($cliente['usuario'] ?? 'U',0,1)) ?>
</div>

<div>

<div class="profile-name">
<?= htmlspecialchars($cliente['nombre'] ?? $cliente['usuario'] ?? 'Usuario') ?>
</div>

<div class="profile-role">
Cliente Hieribal
</div>

</div>

</div>


<div class="pf-row">
<span class="pf-label">Email</span>
<span class="pf-val">
<?= htmlspecialchars($cliente['email'] ?? 'No disponible') ?>
</span>
</div>

<div class="pf-row">
<span class="pf-label">Usuario</span>
<span class="pf-val">
<?= htmlspecialchars($cliente['usuario'] ?? 'No disponible') ?>
</span>
</div>

<button class="btn-edit">
Editar perfil
</button>

</div>

</div>

</div>