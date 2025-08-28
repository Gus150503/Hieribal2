<?php
/* views/admin/usuarioadmin.php */
$title = 'Usuarios'; // <- el layout lo usará para <title>
?>
<div class="p-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Usuarios</h2>
    <button id="btnNuevo" class="btn btn-primary">
      <i class="bi bi-plus-lg"></i> Nuevo
    </button>
  </div>

  <div class="card mb-3">
    <div class="card-body d-flex gap-2 align-items-center flex-wrap">
      <input id="txtBuscar" class="form-control" placeholder="Buscar por usuario, correo, nombre..." style="max-width:320px" autocomplete="off">
      <button id="btnBuscar" class="btn btn-outline-secondary" type="button" aria-label="Buscar">
        <i class="bi bi-search"></i>
      </button>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Estado</th>
            <th>Creación</th>
            <th style="width:140px">Acciones</th>
          </tr>
        </thead>
        <tbody id="tblUsuarios"></tbody>
      </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
      <small id="lblTotal" class="text-muted"></small>
      <nav>
        <ul class="pagination pagination-sm mb-0" id="paginacion"></ul>
      </nav>
    </div>
  </div>
</div>
