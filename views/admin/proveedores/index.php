<?php
// views/admin/proveedores/index.php
// MODULO PROVEDORES / Juliana Lugo / vista de index de proveedores
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

        <nav>
          <ul id="paginador" class="pagination pagination-sm mb-0"></ul>
        </nav>
      </div>
    </div>
  </div>
</section>

<!-- =========================================================
     MODAL PRINCIPAL: CREAR / EDITAR PROVEEDOR
========================================================= -->
<div class="modal fade" id="modalProveedor" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitleProveedor">Nuevo Proveedor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <form id="frmProveedor" class="needs-validation" novalidate>
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

          <div class="modal-footer border-0 pt-3 d-flex justify-content-between">
            <!-- 🔹 Botón para abrir gestión de productos de este proveedor -->
            <button type="button"
                    class="btn btn-outline-warning"
                    id="btnProductosProveedor">
              <i class="bi bi-box-seam me-1"></i>
              Productos que maneja
            </button>

            <div>
              <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-success">
                <i class="bi bi-check2-circle me-1"></i> Guardar
              </button>
            </div>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>

<!-- =========================================================
     MODAL SECUNDARIO: PRODUCTOS DEL PROVEEDOR
========================================================= -->
<div class="modal fade" id="modalProductosProveedor" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          Productos que maneja el proveedor
          <span class="text-muted small" id="mpProvNombre"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <!-- selector para agregar productos -->
        <div class="row g-2 align-items-end mb-3">
          <div class="col-md-8">
            <label class="form-label">Agregar producto</label>
            <select id="mpProductoCatalogo" class="form-select form-select-sm">
              <option value="">-- Selecciona producto --</option>
              <!-- opciones se llenan por JS -->
            </select>
          </div>
          <div class="col-md-4 d-grid">
            <button type="button" class="btn btn-warning btn-sm" id="mpBtnAgregarProducto">
              <i class="bi bi-plus-lg me-1"></i> Agregar
            </button>
          </div>
        </div>

        <div class="table-responsive border rounded-3">
          <table class="table table-sm align-middle mb-0" id="mpTablaProductos">
            <thead class="table-light">
              <tr>
                <th style="width: 40%;">Producto</th>
                <th style="width: 20%;">Precio base</th>
                <th style="width: 20%;">Precio compra prov.</th>
                <th style="width: 10%;">Activo</th>
                <th style="width: 10%;" class="text-end">Quitar</th>
              </tr>
            </thead>
            <tbody>
              <!-- filas dinámicas -->
            </tbody>
          </table>
        </div>

        <small class="text-muted d-block mt-2">
          * El <strong>precio base</strong> es el precio_compra registrado en la tabla de productos.
          Puedes ajustar el <strong>precio compra prov.</strong> si este proveedor maneja un costo distinto.
        </small>
      </div>

      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" id="mpBtnGuardar">
          <i class="bi bi-save me-1"></i> Guardar productos
        </button>
      </div>
    </div>
  </div>
</div>
<script>window.PROVEEDOR_API = '<?= htmlspecialchars($this->config['app']['base_url']) ?>/public/?r=admin_proveedores_api';</script>    
<?php
    $titulo = "Proveedores";
    $esAdmin = true;

    /* AQUÍ AGREGAMOS LOS CSS Y JS CORRECTAMENTE PARA EL USO DE LA PLANTILLA*/
    $extra_css = [
        'assets\css\AdminProveedores.css'
    ];

    $extra_js = [
        'assets\js\admin_proveedores.js?v=6'
    ];
?>
