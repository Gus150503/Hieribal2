<?php $partial = !empty($_GET['partial']); ?>
<?php if (!$partial) include __DIR__ . '/../_shell_start.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<section class="card shadow-sm ui-pro border-0 rounded-4">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-truck fs-4 text-warning"></i>
        <h1 class="h4 m-0">Proveedores</h1>
      </div>
      <button id="btnNuevoProveedor" type="button" class="btn btn-warning">
        <i class="bi bi-plus-lg me-1"></i> Nuevo
      </button>
    </div>

    <div class="input-group mb-3" style="max-width:520px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input id="qProveedor" type="search" class="form-control" placeholder="Buscar por empresa o contacto…">
      <button id="btnBuscarProveedor" class="btn btn-outline-warning">Buscar</button>
    </div>

    <div class="table-responsive">
      <table id="tblProveedor" class="table table-sm align-middle table-hover">
        <thead class="table-light position-sticky" style="top:0; z-index:1;">
          <tr>
            <th>ID</th>
            <th>Empresa</th>
            <th>NIT</th>
            <th>Contacto</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Dirección</th>
            <th>Ciudad</th>
            <th>Condiciones de Pago</th>
            <th>Creado</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal Proveedor -->
<div class="modal fade" id="modalProveedor" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nuevo Proveedor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="frmProveedor" class="needs-validation" novalidate>
          <input type="hidden" name="id" id="idProveedor">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Empresa</label>
              <input type="text" class="form-control" name="empresa" id="empresa" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">NIT</label>
              <input type="text" class="form-control" name="nit" id="nit" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Contacto</label>
              <input type="text" class="form-control" name="contacto" id="contacto">
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input type="text" class="form-control" name="telefono" id="telefono">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" id="email">
            </div>
            <div class="col-md-6">
              <label class="form-label">Dirección</label>
              <input type="text" class="form-control" name="direccion" id="direccion">
            </div>
            <div class="col-md-6">
              <label class="form-label">Ciudad</label>
              <input type="text" class="form-control" name="ciudad" id="ciudad">
            </div>
            <div class="col-md-6">
              <label class="form-label">Condiciones de Pago</label>
              <input type="text" class="form-control" name="condiciones_pago" id="condiciones_pago">
            </div>
            <div class="col-md-6">
              <label class="form-label">Creado</label>
              <input type="date" class="form-control" name="creado" id="creado">
            </div>
          </div>

          <div class="modal-footer border-0 pt-3">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning">
              <i class="bi bi-check2-circle me-1"></i> Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('btnNuevoProveedor').addEventListener('click', () => {
    const modal = new bootstrap.Modal(document.getElementById('modalProveedor'));
    modal.show();
  });
  window.PROVEEDOR_API = '/controllers/AdminProveedor.php';
</script>
<script src="/public/assets/js/admin_proveedor.js?v=1"></script>

<?php if (!$partial) include __DIR__ . '/../_shell_end.php'; ?>
