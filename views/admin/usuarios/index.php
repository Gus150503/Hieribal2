<?php $partial = !empty($_GET['partial']); ?>
<?php if (!$partial) include __DIR__ . '/../_shell_start.php'; ?>

<section class="card">
  <div style="display:flex; align-items:center; justify-content:space-between;">
    <h1><?= htmlspecialchars($titulo ?? 'Usuarios') ?></h1>
    <button id="btnNuevo" class="btn btn-primary">Nuevo</button>
  </div>

  <div style="margin:12px 0; display:flex; gap:8px;">
    <input id="q" type="search" placeholder="Buscar por nombre, usuario o correo..." class="form-control" style="max-width:320px;">
    <button id="btnBuscar" class="btn btn-secondary">Buscar</button>
  </div>

  <table id="tblUsuarios" class="table table-striped">
    <thead>
      <tr>
        <th>ID</th><th>Usuario</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Verif</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</section>

<!-- Modal -->
<div id="modalUsuario" class="modal" style="display:none;">
  <div class="modal-dialog">
    <div class="modal-content" style="padding:16px;">
      <h3 id="modalTitle">Nuevo usuario</h3>
      <form id="frmUsuario">
        <input type="hidden" name="id_usuario" id="id_usuario">
        <div class="row">
          <div class="col">
            <label>Usuario</label>
            <input class="form-control" name="usuario" id="usuario" required>
          </div>
          <div class="col">
            <label>Rol</label>
            <select class="form-control" name="rol" id="rol">
              <option value="empleado">Empleado</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
        <div class="row" style="margin-top:8px;">
          <div class="col">
            <label>Nombres</label>
            <input class="form-control" name="nombres" id="nombres" required>
          </div>
          <div class="col">
            <label>Apellidos</label>
            <input class="form-control" name="apellidos" id="apellidos" required>
          </div>
        </div>
        <div class="row" style="margin-top:8px;">
          <div class="col">
            <label>Correo</label>
            <input class="form-control" type="email" name="correo" id="correo" required>
          </div>
          <div class="col">
            <label>Estado</label>
            <select class="form-control" name="estado" id="estado">
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
          </div>
        </div>
        <div class="row" style="margin-top:8px;">
          <div class="col">
            <label>Password <small>(min 6, dejar vacío para no cambiar)</small></label>
            <input class="form-control" type="password" name="password" id="password">
          </div>
        </div>

        <div style="margin-top:12px; display:flex; gap:8px; justify-content:flex-end;">
          <button type="button" id="btnCerrar" class="btn btn-light">Cancelar</button>
          <button type="submit" class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="<?= $this->config['app']['base_url'] ?>/assets/js/admin_usuarios.js" defer></script>

<?php if (!$partial) include __DIR__ . '/../_shell_end.php'; ?>
