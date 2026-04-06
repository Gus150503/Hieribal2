<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-cart-check-fill text-success me-2"></i>Pedidos desde la Web
        </h1>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white sticky-top">
                        <tr>
                            <th class="px-4">Fecha / Hora</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Estado</th>
                            <th>Monto</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $p): ?>
                        <tr id="fila-<?= $p['id_carrito'] ?>">
                            <td class="px-4 small">
                                <strong><?= date('d/m/Y', strtotime($p['fecha_agregado'])) ?></strong><br>
                                <span class="text-muted"><?= date('g:i a', strtotime($p['fecha_agregado'])) ?></span>
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($p['cliente_nombre']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($p['direccion_envio']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success border">
                                    <?= htmlspecialchars($p['nombre_producto']) ?>
                                </span>
                            </td>
                            <td id="estado-<?= $p['id_carrito'] ?>">
                                <?php if ($p['estado'] == 2): ?>
                                    <span class="badge rounded-pill bg-success">Enviado</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-warning text-dark">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold">$<?= number_format((float)$p['subtotal'], 0, ',', '.') ?></td>
                            <td class="text-center" id="btn-contenedor-<?= $p['id_carrito'] ?>">
                                <?php if ($p['estado'] != 2): ?>
                                    <button onclick="marcarDespacho(<?= $p['id_carrito'] ?>)" class="btn btn-sm btn-success">
                                        <i class="bi bi-send-check"></i> Despachar
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small"><i class="bi bi-check2-all"></i> Listo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>