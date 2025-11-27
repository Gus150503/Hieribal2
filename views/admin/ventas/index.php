<?php
$base       = $this->config['app']['base_url'] ?? '';
$productos  = $productos  ?? [];
$clientes   = $clientes   ?? [];
$vendedores = $vendedores ?? [];
?>
<section class="card shadow-sm ui-pro border-0 rounded-4">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-receipt fs-4 text-primary"></i>
        <h1 class="h4 m-0">Reporte de ventas</h1>
      </div>
      <button id="btnNuevaVenta" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nueva venta
      </button>
    </div>

    <!-- Buscador -->
    <div class="input-group mb-3" style="max-width:520px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input id="qVentas" type="search" class="form-control"
            placeholder="Buscar por factura, producto, cliente o vendedor…">
      <button id="btnBuscarVentas" class="btn btn-outline-primary">Buscar</button>
    </div>

    <div class="table-responsive">
      <table id="tblVentas" class="table table-sm table-hover align-middle">
        <thead class="table-light position-sticky" style="top:0; z-index:1;">
          <tr>
            <th>ID</th>
            <th>Factura</th>
            <th>Producto</th>
            <th>Cant.</th>
            <th>Precio</th>
            <th>Total</th>
            <th>Cliente</th>
            <th>Vendedor</th>
            <th>Método pago</th>
            <th>Fecha</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>

      <!-- Paginación -->
      <div class="d-flex align-items-center justify-content-between mt-3">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small me-1">Mostrar</label>
          <select id="perPageVentas" class="form-select form-select-sm" style="width:80px">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>
          <span id="totalVentas" class="text-muted small ms-2"></span>
        </div>
        <nav aria-label="Paginación">
          <ul id="paginadorVentas" class="pagination pagination-sm mb-0"></ul>
        </nav>
      </div>
    </div>
  </div>
</section>

<!-- Modal Venta -->
<div class="modal fade" id="modalVenta" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="modalTitleVenta">Nueva venta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="frmVenta" class="needs-validation" novalidate>
        <div class="modal-body pt-3">
          <input type="hidden" name="id" id="id" value="0">

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Número de factura</label>
              <input class="form-control" id="numero_factura" name="numero_factura" required>
              <div class="invalid-feedback">Requerido.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Producto</label>
              <select class="form-select" id="producto_id" name="producto_id" required>
                <option value="">-- Selecciona --</option>
                <?php foreach ($productos as $p): ?>
                  <option value="<?= (int)$p['id'] ?>">
                    <?= htmlspecialchars($p['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Selecciona un producto.</div>
            </div>

            <div class="col-md-2">
              <label class="form-label">Cantidad</label>
              <input class="form-control" id="cantidad" name="cantidad" type="number" min="1" required>
              <div class="invalid-feedback">Mayor a 0.</div>
            </div>

            <div class="col-md-2">
              <label class="form-label">Precio</label>
              <input class="form-control" id="precio" name="precio" type="number" min="0" step="0.01" required>
              <div class="invalid-feedback">Mayor a 0.</div>
            </div>

            <div class="col-md-3">
              <label class="form-label">Total</label>
              <input class="form-control" id="total" name="total" type="number" step="0.01" readonly>
            </div>

            <div class="col-md-4">
              <label class="form-label">Cliente</label>
              <select class="form-select" id="cliente_id" name="cliente_id" required>
                <option value="">-- Selecciona --</option>
                <?php foreach ($clientes as $c): ?>
                  <option value="<?= (int)$c['id_cliente'] ?>">
                    <?= htmlspecialchars($c['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Selecciona un cliente.</div>
            </div>

            <div class="col-md-5">
              <label class="form-label">Vendedor</label>
              <select class="form-select" id="vendedor_id" name="vendedor_id" required>
                <option value="">-- Selecciona --</option>
                <?php foreach ($vendedores as $v): ?>
                  <option value="<?= (int)$v['id_usuario'] ?>">
                    <?= htmlspecialchars($v['usuario']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Selecciona un vendedor.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Método de pago</label>
              <input class="form-control" id="metodo_pago" name="metodo_pago" required
                     placeholder="Efectivo, tarjeta, transferencia…">
              <div class="invalid-feedback">Requerido.</div>
            </div>

            <div class="col-md-3">
              <label class="form-label">Fecha</label>
              <input class="form-control" id="fecha" name="fecha" type="date" required>
              <div class="invalid-feedback">Requerida.</div>
            </div>

            <div class="col-12">
              <label class="form-label">Observaciones</label>
              <textarea class="form-control" id="observaciones" name="observaciones" rows="2"
                        placeholder="Notas adicionales"></textarea>
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

<!-- Contenedor para toasts -->
<div id="toastHost" class="toast-host" aria-live="polite" aria-atomic="true"></div>

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="confirmTitle">Confirmar acción</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body pt-3" id="confirmBody">¿Seguro?</div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnOkConfirm">Sí, continuar</button>
      </div>
    </div>
  </div>
</div>

<!-- JS específico -->
<script src="<?= $base ?>/assets/js/admin_ventas.js?v=1" defer></script>
