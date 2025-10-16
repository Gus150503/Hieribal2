<?php
// views/admin/proveedores/index.php
// Esta vista se renderiza dentro de plantilla.php (con sidebar y <main>)
// No incluir _shell_start/_shell_end ni Bootstrap aquí (la plantilla ya los carga).
$base = $this->config['app']['base_url'] ?? '';
?>

<style>
  /* Encabezado amarillo para la tabla de Proveedores */
  #tblProveedor thead.table-light {
    --bs-table-bg: #ffc107;           /* amarillo Bootstrap */
    --bs-table-color: #fff;
    --bs-table-border-color: rgba(255,255,255,.25);
    background: #ffc107 !important;
    color: #fff !important;
    background-image: none !important;
  }
  #tblProveedor thead.table-light th { color:#fff !important; }
</style>

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

    <!-- Buscador -->
    <div class="input-group mb-3" style="max-width:520px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input id="qProveedor" type="search" class="form-control" placeholder="Buscar por empresa o contacto…">
      <button id="btnBuscarProveedor" class="btn btn-outline-warning">Buscar</button>
    </div>

    <!-- Tabla -->
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

    <div class="d-flex align-items-center justify-content-between mt-3">
      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small me-1">Mostrar</label>
        <select id="perPage" class="form-select form-select-sm" style="width:80px">
          <option value="5">5</option>
          <option value="10" selected>10</option>
          <option value="20">20</option>
          <option value="50">50</option>
        </select>
        <span id="totalProveedores" class="text-muted small ms-2"></span>
      </div>
      <nav aria-label="Paginación">
        <ul id="paginador" class="pagination pagination-sm mb-0"></ul>
      </nav>
    </div>
  </div>
</section>

<!-- Modal Proveedores -->
<div class="modal fade" id="modalProveedor" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitleProveedor">Nuevo Proveedor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <form id="frmProveedor" class="needs-validation" method="POST" action="<?= $base ?>/controllers/AdminProveedores.php" novalidate>
          <input type="hidden" name="action" id="proveedorAction" value="create">
          <input type="hidden" name="id" id="idProveedor">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Empresa</label>
              <input type="text" class="form-control" name="empresa" id="empresa" required maxlength="100">
              <div class="invalid-feedback">Empresa inválida.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">NIT</label>
              <input type="text" class="form-control" name="nit" id="nit" required maxlength="20">
              <div class="invalid-feedback">NIT inválido.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Nombre Contacto</label>
              <input type="text" class="form-control" name="nombre_contacto" id="nombre_contacto" required maxlength="100">
              <div class="invalid-feedback">Nombre de contacto inválido.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input type="text" class="form-control" name="telefono" id="telefono" maxlength="20">
              <div class="invalid-feedback">Teléfono inválido.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" id="email" required maxlength="100">
              <div class="invalid-feedback">Email inválido.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Dirección</label>
              <input type="text" class="form-control" name="direccion" id="direccion" maxlength="150">
              <div class="invalid-feedback">Dirección inválida.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Ciudad</label>
              <input type="text" class="form-control" name="ciudad" id="ciudad" maxlength="100">
              <div class="invalid-feedback">Ciudad inválida.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Condiciones de Pago</label>
              <input type="text" class="form-control" name="condiciones_pago" id="condiciones_pago" maxlength="100">
              <div class="invalid-feedback">Condiciones de pago inválidas.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Estado</label>
              <select class="form-select" name="estado" id="estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Abrir modal "Nuevo Proveedor"
  const btnNuevoProveedor = document.getElementById('btnNuevoProveedor');
  if (btnNuevoProveedor) {
    btnNuevoProveedor.addEventListener('click', () => {
      const modal = new bootstrap.Modal(document.getElementById('modalProveedor'));
      modal.show();
    });
  }

  // Endpoint API para fetch/AJAX
  window.PROVEEDOR_API = '<?= $base ?>/controllers/AdminProveedores.php';
});
</script>

<!-- JS propio de la página (solo uno) -->
<script src="<?= $base ?>/assets/js/admin_proveedores.js?v=1"></script>
