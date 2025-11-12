<?php
// views/admin/inventario/index.php
$base = $this->config['app']['base_url'] ?? '';
?>
<section class="card shadow-sm ui-pro border-0 rounded-4">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-box-seam fs-4 text-success"></i>
        <h1 class="h4 m-0">Inventario</h1>
      </div>
      <button id="btnNuevoInventario" type="button" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i> Nuevo
      </button>
    </div>

    <!-- Buscador -->
    <div class="input-group mb-3" style="max-width:520px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input id="qInventario" type="search" class="form-control" placeholder="Buscar por código interno, producto o ubicación…">
      <button id="btnBuscarInventario" class="btn btn-outline-success">Buscar</button>
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
      <table id="tblInventario" class="table table-sm table-hover align-middle">
        <thead class="table-light position-sticky" style="top:0; z-index:1;">
          <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Código Interno</th>
            <th>Stock</th>
            <th>Stock Mín.</th>
            <th>Stock Máx.</th>
            <th>Punto Reorden</th>
            <th>Ubicación</th>
            <th>Estado</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>

      <div class="d-flex align-items-center justify-content-between mt-3">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small me-1">Mostrar</label>
          <select id="perPageInventario" class="form-select form-select-sm" style="width:80px">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>
          <span id="totalInventario" class="text-muted small ms-2"></span>
        </div>
        <nav aria-label="Paginación">
          <ul id="paginadorInventario" class="pagination pagination-sm mb-0"></ul>
        </nav>
      </div>
    </div>
  </div>
</section>

<!-- Modal Inventario -->
<div class="modal fade" id="modalInventario" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="modalTitleInventario">Nuevo inventario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="frmInventario" class="needs-validation" novalidate>
        <div class="modal-body pt-3">
          <input type="hidden" name="id" id="idInventario" value="">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Producto</label>
              <select class="form-select" name="producto_id" id="producto_id" required>
                <option value="">Seleccione un producto…</option>
              </select>
              <div class="invalid-feedback">Selecciona un producto válido.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Código Interno</label>
              <input type="text" class="form-control" name="codigo_interno" id="codigo_interno" required minlength="3" maxlength="50">
              <div class="invalid-feedback">Código mínimo 3 caracteres.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Stock</label>
              <input type="number" class="form-control" name="stock" id="stock" required min="0" step="1">
              <div class="invalid-feedback">Stock inválido.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Stock Mínimo</label>
              <input type="number" class="form-control" name="stock_minimo" id="stock_minimo" required min="0" step="1">
              <div class="invalid-feedback">Stock mínimo inválido.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Stock Máximo</label>
              <input type="number" class="form-control" name="stock_maximo" id="stock_maximo" required min="0" step="1">
              <div class="invalid-feedback">Stock máximo inválido.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Punto de Reorden</label>
              <input type="number" class="form-control" name="punto_reorden" id="punto_reorden" required min="0" step="1">
              <div class="invalid-feedback">Punto de reorden inválido.</div>
            </div>

              <div class="col-md-6">
                <label class="form-label">Ubicación</label>
                <input type="text" class="form-control" name="ubicacion" id="ubicacion" maxlength="100">
                <div class="valid-feedback">Opcional.</div>          <!-- aparece en verde cuando es válido -->
                <div class="invalid-feedback">Ubicación inválida.</div> <!-- rojo solo si es inválido -->
              </div>


            <div class="col-md-6">
              <label class="form-label">Estado</label>
              <select class="form-select" name="estado" id="estado" required>
                <option value="disponible">Disponible</option>
                <option value="agotado">Agotado</option>
                <option value="pendiente">Pendiente</option>
              </select>
              <div class="invalid-feedback">Selecciona un estado.</div>
            </div>
          </div>
        </div>

        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check2-circle me-1"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Contenedores para toasts y confirm usados por el JS -->
<div id="toastHost" class="toast-host" aria-live="polite" aria-atomic="true"></div>
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

<style>
  /* Encabezado verde para la tabla de Inventario */
  #tblInventario thead.table-light{
    --bs-table-bg: var(--brand, #198754);
    --bs-table-color: #fff;
    --bs-table-border-color: rgba(255,255,255,.25);
    background: var(--brand, #198754) !important;
    color: #fff !important;
    background-image: none !important;
  }
  #tblInventario thead.table-light th{ color:#fff !important; }
</style>

<!-- Endpoints para que el JS pegue al controlador correcto -->
<script>
  window.INVENTARIO_API = '<?= $base ?>/?r=admin_inventario_api';
  window.PRODUCTO_API   = '<?= $base ?>/?r=admin_productos_api';
</script>


<!-- JS específico -->
<script src="<?= $base ?>/assets/js/admin_inventario.js?v=3" defer></script>
