<?php $partial = !empty($_GET['partial']); ?>
<?php if (!$partial) include __DIR__ . '/../_shell_start.php'; ?>



<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<section class="card shadow-sm ui-pro border-0 rounded-4">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-box-seam fs-4 text-success"></i>
        <h1 class="h4 m-0">Inventario</h1>
      </div>

      <!-- Botón Nuevo -->
      <button id="btnNuevo" type="button" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i> Nuevo
      </button>
    </div>

    <!-- Buscador -->
    <div class="input-group mb-3" style="max-width:520px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input id="q" type="search" class="form-control" placeholder="Buscar por código interno o ubicación…">
      <button id="btnBuscar" class="btn btn-outline-success">Buscar</button>
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
      <table id="tblInventario" class="table table-sm align-middle table-hover">
        <thead class="table-light position-sticky" style="top:0; z-index:1;">
          <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Código Interno</th>
            <th>Stock</th>
            <th>Stock Mínimo</th>
            <th>Stock Máximo</th>
            <th>Punto Reorden</th>
            <th>Ubicación</th>
            <th>Estado</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <!-- Aquí se cargarán los datos -->
        </tbody>
      </table>

      <div class="d-flex align-items-center justify-content-between mt-3">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small me-1">Mostrar</label>
          <select id="perPage" class="form-select form-select-sm" style="width:80px">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>
          <span id="totalInventario" class="text-muted small ms-2"></span>
        </div>
        <nav aria-label="Paginación">
          <ul id="paginador" class="pagination pagination-sm mb-0"></ul>
        </nav>
      </div>
    </div>
  </div>
</section>

<!-- Modal Inventario -->
<div class="modal fade" id="modalInventario" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Nuevo Inventario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <form id="frmInventario" class="needs-validation" method="POST" action="/Hieribal2/controllers/AdminInventario.php" novalidate>
            <input type="hidden" name="action" id="inventarioAction" value="create">
            <input type="hidden" name="id" id="idInventario">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Producto ID</label>
              <input type="number" class="form-control" name="producto_id" id="producto_id" required min="1">
              <div class="invalid-feedback">Ingresa un producto válido.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Código Interno</label>
              <input type="text" class="form-control" name="codigo_interno" id="codigo_interno" required maxlength="50">
              <div class="invalid-feedback">Código inválido.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Stock</label>
              <input type="number" class="form-control" name="stock" id="stock" required min="0">
              <div class="invalid-feedback">Stock inválido.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Stock Mínimo</label>
              <input type="number" class="form-control" name="stock_minimo" id="stock_minimo" required min="0">
              <div class="invalid-feedback">Stock mínimo inválido.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Stock Máximo</label>
              <input type="number" class="form-control" name="stock_maximo" id="stock_maximo" required min="0">
              <div class="invalid-feedback">Stock máximo inválido.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Punto de Reorden</label>
              <input type="number" class="form-control" name="punto_reorden" id="punto_reorden" required min="0">
              <div class="invalid-feedback">Punto de reorden inválido.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Ubicación</label>
              <input type="text" class="form-control" name="ubicacion" id="ubicacion" maxlength="100">
              <div class="invalid-feedback">Ubicación inválida.</div>
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

          <div class="modal-footer border-0 pt-3">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check2-circle me-1"></i> Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
  /* Encabezado verde para la tabla de Inventario */
  #tblInventario thead.table-light{
    /* pisa a Bootstrap */
    --bs-table-bg: var(--brand, #198754);
    --bs-table-color: #fff;
    --bs-table-border-color: rgba(255,255,255,.25);

    background: var(--brand, #198754) !important;
    color: #fff !important;
    background-image: none !important;
  }
  #tblInventario thead.table-light th{
    color: #fff !important;
  }
</style>


<script>
  // Conectar botón con modal
  document.getElementById('btnNuevo').addEventListener('click', function () {
    const modal = new bootstrap.Modal(document.getElementById('modalInventario'));
    modal.show();
  });

  // Definir API
  window.INVENTARIO_API = '/controllers/AdminInventario.php';
</script>

<script src="/Hieribal2/public/assets/js/admin_inventario.js?v=1" defer></script>

<?php if (!$partial) include __DIR__ . '/../_shell_end.php'; ?>
