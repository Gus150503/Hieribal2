<div class="perfil-dashboard">

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Mis pedidos</div>
            <div class="stat-num green"><?= $usuario['total_pedidos'] ?? 0 ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total gastado</div>
            <div class="stat-num gold">$<?= number_format($usuario['total_gastado'] ?? 0, 0, ',', '.') ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-label">En carrito</div>
            <div class="stat-num"><?= $totalCarrito ?? 0 ?> items</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Devoluciones</div>
            <div class="stat-num"><?= $usuario['devoluciones'] ?? 0 ?></div>
        </div>
    </div>

    <div class="mid-row">
        <div class="card">
            <div class="card-title">Mis compras por mes</div>
            <div class="chart-wrap">
                <canvas id="chartPedidos"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="profile-mini">
                <div class="profile-ava-big">
                    <?= strtoupper(substr($usuario['nombre'] ?? 'U', 0, 1)) ?>
                </div>
                <div>
                    <div class="profile-name">
                        <?= htmlspecialchars($usuario['nombre'] ?? 'Usuario') ?>
                    </div>
                    <div class="profile-role">
                        Cliente Hieribal <?= !empty($usuario['es_pro']) ? '<span class="pro-badge">PRO</span>' : '' ?>
                    </div>
                </div>
            </div>

            <div class="pf-row">
                <span class="pf-label">Email</span>
                <span class="pf-val"><?= htmlspecialchars($usuario['email'] ?? 'No disponible') ?></span>
            </div>

            <div class="pf-row">
                <span class="pf-label">Teléfono</span>
                <span class="pf-val"><?= htmlspecialchars($usuario['telefono'] ?? 'No disponible') ?></span>
            </div>

            <button class="btn-edit">
                <i class="fas fa-edit"></i> Editar perfil 
            </button>
        </div>
    </div>

</div>