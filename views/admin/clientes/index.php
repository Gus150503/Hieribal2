<?php
$base = $this->config['app']['base_url'] ?? '';
?>

<section class="card shadow-sm ui-pro border-0 rounded-4">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h4 m-0"><i class="bi bi-person-badge text-success me-1"></i> Clientes</h1>
      <button id="btnNuevoCliente" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i> Nuevo
      </button>
    </div>

    <div class="input-group mb-3" style="max-width:520px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input id="qCliente" type="search" class="form-control" placeholder="Buscar por nombre, correo o cédula…">
      <button id="btnBuscarCliente" class="btn btn-outline-success">Buscar</button>
    </div>

    <div class="table-responsive">
      <table id="tblClientes" class="table table-sm table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Cédula</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Estado</th>
            <th>Registro</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</section>

<script src="<?= $base ?>/assets/js/admin_clientes.js?v=1" defer></script>
