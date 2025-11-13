<?php
$base = $this->config['app']['base_url'] ?? '';
?>

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
      <input id="qProveedor" type="search" class="form-control" placeholder="Buscar por empresa, contacto, NIT o ciudad…">
      <button id="btnBuscarProveedor" class="btn btn-outline-warning">Buscar</button>
    </div>

    <div class="table-responsive">
      <table id="tblProveedor" class="table table-sm align-middle table-hover">
          <thead class="table-light position-sticky" 
              style="top:0; z-index:1; background-color:#ffc107!important; color:#ffffff!important;">

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
            <th>Estado</th>
            <th>Creado</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
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
          <span id="totalProveedores" class="text-muted small ms-2"></span>
        </div>
        <nav aria-label="Paginación">
          <ul id="paginador" class="pagination pagination-sm mb-0"></ul>
        </nav>
      </div>
    </div>
  </div>
</section>

<!-- Modal Proveedor -->
<div class="modal fade" id="modalProveedor" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="modalTitleProveedor">Nuevo Proveedor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="frmProveedor" class="needs-validation" novalidate>
        <div class="modal-body pt-3">
          <input type="hidden" name="id" id="idProveedor" value="0">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Empresa</label>
              <input type="text" class="form-control" name="empresa" id="empresa" required maxlength="100">
              <div class="invalid-feedback">Empresa inválida.</div>
              <div class="valid-feedback">Correcto.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">NIT</label>
              <input type="text" class="form-control" name="nit" id="nit" required maxlength="20">
              <div class="invalid-feedback">NIT inválido.</div>
              <div class="valid-feedback">Correcto.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Nombre Contacto</label>
              <input type="text" class="form-control" name="nombre_contacto" id="nombre_contacto" required maxlength="100">
              <div class="invalid-feedback">Nombre de contacto inválido.</div>
              <div class="valid-feedback">Correcto.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input type="text" class="form-control" name="telefono" id="telefono" maxlength="20">
              <div class="invalid-feedback">Teléfono inválido.</div>
              <div class="valid-feedback">Opcional.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" id="email" required maxlength="100">
              <div class="invalid-feedback">Email inválido.</div>
              <div class="valid-feedback">Correcto.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Dirección</label>
              <input type="text" class="form-control" name="direccion" id="direccion" maxlength="150">
              <div class="invalid-feedback">Dirección inválida.</div>
              <div class="valid-feedback">Opcional.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Ciudad</label>
              <input type="text" class="form-control" name="ciudad" id="ciudad" maxlength="100">
              <div class="invalid-feedback">Ciudad inválida.</div>
              <div class="valid-feedback">Opcional.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Condiciones de Pago</label>
              <input type="text" class="form-control" name="condiciones_pago" id="condiciones_pago" maxlength="100">
              <div class="invalid-feedback">Condiciones de pago inválidas.</div>
              <div class="valid-feedback">Opcional.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Estado</label>
              <select class="form-select" name="estado" id="estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
              </select>
              <div class="invalid-feedback">Selecciona un estado.</div>
              <div class="valid-feedback">Correcto.</div>
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

<!-- Contenedor para toasts -->
<div id="toastHost" class="toast-host" aria-live="polite" aria-atomic="true"></div>

<!-- Modal de confirmación (el JS lo rellena) -->
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
    #tblProveedor thead th {
    background-color:#ffc107 !important;
    color:#ffffff !important;
    border-color: rgba(255,255,255,.25) !important;
  }
  /* PROVEEDORES – encabezado amarillo forzado */
  #tblProveedor thead.table-light {
    background-color: #ffc107 !important;
  }
  #tblProveedor thead.table-light th {
    background-color: #ffc107 !important;
    color: #ffffff !important;
    border-color: rgba(255,255,255,.25) !important;
  }



  /* Scroll horizontal consistente */
  #tblProveedor { min-width: 1200px; }
  .table-responsive { overflow-x:auto; }

  /* Bordes de validación (coherentes con otros módulos) */
  .form-control.is-valid, .form-select.is-valid{
    border-color:#198754 !important;
    box-shadow:0 0 0 .15rem rgba(25,135,84,.15) !important;
  }
  .form-control.is-invalid, .form-select.is-invalid{
    border-color:#dc3545 !important;
    box-shadow:0 0 0 .15rem rgba(220,53,69,.15) !important;
  }
</style>


<!-- JS del módulo -->
<script src="<?= $base ?>/assets/js/admin_proveedores.js?v=5" defer></script>

