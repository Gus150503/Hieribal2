<?php $partial = !empty($_GET['partial']); ?>
<?php if (!$partial) include __DIR__ . '/../_shell_start.php'; ?>




<section class="card shadow-sm ui-pro border-0 rounded-4">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-people-fill fs-4 text-success"></i>
        <h1 class="h4 m-0">Usuarios</h1>

      </div>
      <button id="btnNuevo" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i> Nuevo
      </button>
    </div>

    <?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
    <?php if (!empty($_SESSION['flash'])):
      $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
      <div class="alert alert-<?= htmlspecialchars($f['type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($f['msg'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <div class="input-group mb-3" style="max-width:520px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input id="q" type="search" class="form-control" placeholder="Buscar por nombre, usuario o correo…">
      <button id="btnBuscar" class="btn btn-outline-success">Buscar</button>
    </div>

    <div class="table-responsive">
      <table id="tblUsuarios" class="table table-sm align-middle table-hover">
        <thead class="table-light position-sticky" style="top:0; z-index:1;">
          <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Verif.</th>
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
          <span id="totalUsuarios" class="text-muted small ms-2"></span>
        </div>
        <nav aria-label="Paginación">
          <ul id="paginador" class="pagination pagination-sm mb-0"></ul>
        </nav>
      </div>
    </div>
  </div>
</section>

<!-- Modal Bootstrap -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="modalTitle">Nuevo usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="frmUsuario" class="needs-validation" novalidate>
        <div class="modal-body pt-3">
          <input type="hidden" name="id_usuario" id="id_usuario">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Usuario</label>
              <input
                class="form-control"
                name="usuario"
                id="usuario"
                required
                minlength="3"
                maxlength="30"
                pattern="[A-Za-z0-9._-]{3,30}"
                title="De 3 a 30 caracteres: letras, números, punto, guión y guión bajo."
                autocomplete="username"
              >
              <div class="invalid-feedback">Usuario inválido (3–30, letras/números . _ -).</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Rol</label>
              <select class="form-select" name="rol" id="rol" required>
                <option value="empleado">Empleado</option>
                <option value="admin">Admin</option>
              </select>
              <div class="invalid-feedback">Selecciona un rol.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Estado</label>
              <select class="form-select" name="estado" id="estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
              </select>
              <div class="invalid-feedback">Selecciona un estado.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Nombres</label>
              <input
                class="form-control"
                name="nombres"
                id="nombres"
                required
                pattern="[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,60}"
                title="Sólo letras y espacios (2–60)."
                autocomplete="given-name"
              >
              <div class="invalid-feedback">Sólo letras y espacios (2–60).</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Apellidos</label>
              <input
                class="form-control"
                name="apellidos"
                id="apellidos"
                required
                pattern="[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,60}"
                title="Sólo letras y espacios (2–60)."
                autocomplete="family-name"
              >
              <div class="invalid-feedback">Sólo letras y espacios (2–60).</div>
            </div>

            <div class="col-md-8">
              <label class="form-label">Correo</label>
              <input
                class="form-control"
                type="email"
                name="correo"
                id="correo"
                required
                inputmode="email"
                autocomplete="email"
              >
              <div class="invalid-feedback">Correo inválido.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">
                Password
                <small class="text-muted">(mín. 8; vacío no cambia)</small>
              </label>
              <input
                class="form-control"
                type="password"
                name="password"
                id="password"
                minlength="8"
                autocomplete="new-password"
              >
              <div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres.</div>
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

<?php if (!$partial) include __DIR__ . '/../_shell_end.php'; ?>
