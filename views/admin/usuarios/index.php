<?php $partial = !empty($_GET['partial']); ?>
<?php if (!$partial) include __DIR__ . '/../_shell_start.php'; ?>

<section class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-people-fill fs-4 text-success"></i>
        <h1 class="h4 m-0"><?= htmlspecialchars($titulo ?? 'Usuarios') ?></h1>
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
      <table id="tblUsuarios" class="table align-middle table-hover">
        <thead class="table-light">
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

<!-- Modal Bootstrap (único formulario) -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-3">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Nuevo usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="frmUsuario">
        <div class="modal-body">
          <input type="hidden" name="id_usuario" id="id_usuario">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Usuario</label>
              <input class="form-control" name="usuario" id="usuario" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Rol</label>
              <select class="form-select" name="rol" id="rol">
                <option value="empleado">Empleado</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Estado</label>
              <select class="form-select" name="estado" id="estado">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Nombres</label>
              <input class="form-control" name="nombres" id="nombres" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Apellidos</label>
              <input class="form-control" name="apellidos" id="apellidos" required>
            </div>

            <div class="col-md-8">
              <label class="form-label">Correo</label>
              <input class="form-control" type="email" name="correo" id="correo" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Password
                <small class="text-muted">(min 6; vacío no cambia)</small>
              </label>
              <input class="form-control" type="password" name="password" id="password">
            </div>
          </div>
        </div>
        <div class="modal-footer">
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
