<?php
$base = $this->config['app']['base_url'] ?? '';

// 👇 leer rol de la sesión
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$rol  = strtolower($_SESSION['admin']['rol'] ?? 'empleado');

// 👇 sólo admin puede crear/editar/borrar clientes
$puedeGestionarClientes = ($rol === 'admin');
?>



<section class="card shadow-sm ui-pro border-0 rounded-4">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-person-badge fs-4 text-success"></i>
        <h1 class="h4 m-0">Clientes</h1>
      </div>

      <?php if ($puedeGestionarClientes): ?>
        <button id="btnNuevoCliente" class="btn btn-success">
          <i class="bi bi-plus-lg me-1"></i> Nuevo
        </button>
      <?php endif; ?>
    </div>


    <!-- Buscador: IDs que usa el JS -->
    <div class="input-group mb-3" style="max-width:520px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input id="qClientes" type="search" class="form-control" placeholder="Buscar por nombre, correo o cédula…">
      <button id="btnBuscarClientes" class="btn btn-outline-success">Buscar</button>
    </div>

    <div class="table-responsive">
      <table id="tblClientes" class="table table-sm table-hover align-middle">
        <thead class="table-light position-sticky" style="top:0; z-index:1;">
          <tr>
            <th>ID</th>
            <th>Cédula</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Estado</th>
            <th>Registro</th>

            <?php if ($puedeGestionarClientes): ?>
              <th class="text-end">Acciones</th>
            <?php endif; ?>

          </tr>
        </thead>
        <tbody></tbody>
      </table>

      <!-- Paginación + perPage: IDs que usa el JS -->
      <div class="d-flex align-items-center justify-content-between mt-3">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small me-1">Mostrar</label>
          <select id="perPageClientes" class="form-select form-select-sm" style="width:80px">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>
          <span id="totalClientes" class="text-muted small ms-2"></span>
        </div>
        <nav aria-label="Paginación">
          <ul id="paginadorClientes" class="pagination pagination-sm mb-0"></ul>
        </nav>
      </div>
    </div>
  </div>
</section>

<!-- Modal Cliente: IDs que usa el JS (modalCliente, frmCliente, modalTitle, etc.) -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="modalTitle">Nuevo cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="frmCliente" class="needs-validation" novalidate>
        <div class="modal-body pt-3">
          <input type="hidden" name="id_cliente" id="id_cliente" value="0">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Cédula</label>
              <input class="form-control" id="cedula" name="cedula" required pattern="\d{6,15}">
              <div class="invalid-feedback">6 a 15 dígitos.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Nombres</label>
              <input class="form-control" id="nombres" name="nombres" required
                     pattern="[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,60}">
              <div class="invalid-feedback">Sólo letras y espacios (2–60).</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Apellidos</label>
              <input class="form-control" id="apellidos" name="apellidos" required
                     pattern="[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,60}">
              <div class="invalid-feedback">Sólo letras y espacios (2–60).</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Teléfono</label>
              <input class="form-control" id="telefono" name="telefono" inputmode="tel">
            </div>

            <div class="col-md-5">
              <label class="form-label">Correo</label>
              <input class="form-control" id="correo" name="correo" type="email" required>
              <div class="invalid-feedback">Correo inválido.</div>
            </div>

            <div class="col-md-3">
              <label class="form-label">Estado</label>
              <select class="form-select" id="estado" name="estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label">
                Contraseña <small class="text-muted">(mín. 8; vacío no cambia)</small>
              </label>
              <input class="form-control" id="contraseña" name="contraseña" type="password" minlength="8" autocomplete="new-password">
              <div class="invalid-feedback">Mínimo 8 caracteres.</div>
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

<!-- Contenedor para toasts (el JS lo usa si existe) -->
<div id="toastHost" class="toast-host" aria-live="polite" aria-atomic="true"></div>

<!-- Modal de confirmación (coincide con util de JS) -->
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

<!-- JS específico de la página -->
<script src="<?= $base ?>/assets/js/admin_clientes.js?v=2" defer></script>
