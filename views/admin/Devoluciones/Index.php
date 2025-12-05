<?php
$base        = $this->config['app']['base_url'] ?? '';
$productos   = $productos   ?? [];
$clientes    = $clientes    ?? []; // ya no se usan en el form, pero por si acaso
$proveedores = $proveedores ?? [];
?>

<section class="card shadow-sm ui-pro border-0 rounded-4">
    <div class="card-body">

        <!-- Encabezado -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-arrow-counterclockwise fs-4 text-primary"></i>
                <h1 class="h4 m-0">Gestión de Devoluciones</h1>
            </div>
            <button id="btnNuevaDevolucion" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Nueva devolución
            </button>
        </div>

        <!-- Buscador -->
        <div class="input-group mb-3" style="max-width:520px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input id="qDevoluciones" type="search" class="form-control"
                   placeholder="Buscar por proveedor, producto, orden…">
            <button id="btnBuscarDevoluciones" class="btn btn-outline-primary">Buscar</button>
        </div>

        <!-- Tabla -->
        <div class="table-responsive">
            <table id="tblDevoluciones" class="table table-sm table-hover align-middle">
                <thead class="table-light position-sticky" style="top:0; z-index:1;">
                <tr>
                    <th>ID</th>
                    <th>Proveedor / Cliente</th>
                    <th>Correo / Teléfono</th>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>Orden</th>
                    <th>Motivo</th>
                    <th>F. Compra</th>
                    <th>F. Devolución</th>
                    <th>Estado</th>
                    <th>Origen</th>
                    <th class="text-end">Acciones</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>

            <!-- Paginación -->
            <div class="d-flex align-items-center justify-content-between mt-3">
                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted small me-1">Mostrar</label>
                    <select id="perPageDevoluciones" class="form-select form-select-sm" style="width:80px">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    <span id="totalDevoluciones" class="text-muted small ms-2"></span>
                </div>

                <nav aria-label="Paginación">
                    <ul id="paginadorDevoluciones" class="pagination pagination-sm mb-0"></ul>
                </nav>
            </div>

        </div>
    </div>
</section>

<!-- Modal Devolución -->
<div class="modal fade" id="modalDevolucion" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="modalTitleDevolucion">Nueva devolución</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="frmDevolucion" class="needs-validation" novalidate>
                <div class="modal-body pt-3">

                    <input type="hidden" name="id" id="id" value="0">

                    <div class="row g-3">

                        <!-- GRUPO CLIENTE (solo lectura, solo si origen = cliente) -->
                        <div class="col-md-6 d-none" id="grpCliente">
                            <label class="form-label">Cliente</label>
                            <input type="text" class="form-control" id="cliente_nombre" readonly>
                        </div>

                        <!-- GRUPO PROVEEDOR (para devoluciones internas / proveedor) -->
                        <div class="col-md-6" id="grpProveedor">
                            <label class="form-label">Proveedor</label>
                            <select id="proveedor_id" name="proveedor_id" class="form-select">
                                <option value="">-- Selecciona proveedor --</option>
                                <?php foreach ($proveedores as $pr): ?>
                                    <option value="<?= $pr['id'] ?>">
                                        <?= htmlspecialchars($pr['empresa']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Proveedor requerido.</div>
                        </div>

                        <!-- PRODUCTO -->
                        <div class="col-md-6">
                            <label class="form-label">Producto</label>
                            <select class="form-select" id="producto_id" name="producto_id" required>
                                <option value="">-- Selecciona producto --</option>
                                <?php foreach ($productos as $p): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= htmlspecialchars($p['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Producto requerido.</div>
                        </div>

                        <!-- CANTIDAD -->
                        <div class="col-md-4">
                            <label class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="cantidad"
                                   name="cantidad" min="1" value="1" required>
                            <div class="invalid-feedback">Cantidad inválida.</div>
                        </div>

                        <!-- ORIGEN (solo lectura visual, pero se manda hidden al backend) -->
                        <div class="col-md-4">
                        <label class="form-label">Origen</label>
                        <input type="hidden" id="origen" name="origen" value="interno">
                        <input type="text" class="form-control" id="origen_label" value="Interno" readonly>
                        </div>


                        <!-- NÚMERO DE ORDEN -->
                        <div class="col-md-4">
                            <label class="form-label">Número de orden</label>
                            <input type="text" id="numero_orden" name="numero_orden" class="form-control">
                            <div class="invalid-feedback">Número de orden requerido.</div>
                        </div>

                        <!-- MOTIVO -->
                        <div class="col-md-12">
                            <label class="form-label">Motivo</label>
                            <textarea class="form-control" id="motivo_devolucion"
                                      name="motivo_devolucion" rows="2" required></textarea>
                            <div class="invalid-feedback">Describe el motivo.</div>
                        </div>

                        <!-- FECHAS -->
                        <div class="col-md-6">
                            <label class="form-label">Fecha de compra</label>
                            <input type="date" class="form-control" id="fecha_compra"
                                   name="fecha_compra" required>
                            <div class="invalid-feedback">Fecha de compra requerida.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha de devolución</label>
                            <input type="date" class="form-control" id="fecha_devolucion"
                                   name="fecha_devolucion" required>
                            <div class="invalid-feedback">Fecha de devolución requerida.</div>
                        </div>

                        <!-- ESTADO -->
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="aprobada">Aprobada</option>
                                <option value="rechazada">Rechazada</option>
                            </select>
                            <div class="invalid-feedback">Selecciona un estado.</div>
                        </div>

                        <!-- OBSERVACIONES -->
                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones"
                                      name="observaciones" rows="2"></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i> Guardar
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="<?= $base ?>/assets/js/admin_devoluciones.js?v=3" defer></script>
