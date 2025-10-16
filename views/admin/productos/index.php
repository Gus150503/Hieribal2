<?php $partial = !empty($_GET['partial']); ?>
<?php if (!$partial) include __DIR__ . '/../_shell_start.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>#tblProducto thead.table-light {
  --bs-table-bg: #0d6efd; /* azul Bootstrap por defecto */
  --bs-table-color: #fff;
  --bs-table-border-color: rgba(255,255,255,.25);
  background: #0d6efd !important;
  color: #fff !important;
  background-image: none !important;
}
#tblProducto thead.table-light th { color: #fff !important; }
</style>

<section class="card shadow-sm ui-pro border-0 rounded-4">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-basket fs-4 text-primary"></i>
        <h1 class="h4 m-0">Productos</h1>
      </div>
      <button id="btnNuevoProducto" type="button" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nuevo
      </button>
    </div>

    <div class="input-group mb-3" style="max-width:520px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input id="qProducto" type="search" class="form-control" placeholder="Buscar por nombre, marca o categoría…">
      <button id="btnBuscarProducto" class="btn btn-outline-primary">Buscar</button>
    </div>

    <div class="table-responsive">
      <table id="tblProducto" class="table table-sm align-middle table-hover">
        <thead class="table-light position-sticky" style="top:0; z-index:1;">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Marca</th>
            <th>Presentación</th>
            <th>Unidad</th>
            <th>Descripción</th>
            <th>Lote</th>
            <th>F. Vencimiento</th>
            <th>Precio Compra</th>
            <th>Precio Venta</th>
            <th>IVA</th>
            <th>SKU</th>
            <th>Ubicación</th>
            <th>Estado</th>
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
          <span id="totalProducto" class="text-muted small ms-2"></span>
        </div>
        <nav aria-label="Paginación">
          <ul id="paginador" class="pagination pagination-sm mb-0"></ul>
        </nav>
      </div>
    </div>
  </div>
  </div>
      
</section>

<!-- Modal Productos jj-->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitleProducto">Nuevo Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <form id="frmProducto" class="needs-validation" method="POST" action="/Hieribal2/controllers/AdminProducto.php" novalidate>
          <input type="hidden" name="action" id="productoAction" value="create">
          <input type="hidden" name="id" id="idProducto">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input type="text" class="form-control" name="nombre" id="nombre" required maxlength="100">
              <div class="invalid-feedback">Nombre inválido.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Categoría</label>
              <input type="text" class="form-control" name="categoria" id="categoria" required maxlength="100">
              <div class="invalid-feedback">Categoría inválida.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Marca</label>
              <input type="text" class="form-control" name="marca" id="marca" maxlength="100">
              <div class="invalid-feedback">Marca inválida.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Presentación</label>
              <input type="text" class="form-control" name="presentacion" id="presentacion" maxlength="100">
              <div class="invalid-feedback">Presentación inválida.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Unidad</label>
              <input type="text" class="form-control" name="unidad" id="unidad" maxlength="50">
              <div class="invalid-feedback">Unidad inválida.</div>
            </div>

            <div class="col-md-12">
              <label class="form-label">Descripción</label>
              <textarea class="form-control" name="descripcion" id="descripcion" maxlength="255"></textarea>
              <div class="invalid-feedback">Descripción inválida.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Lote</label>
              <input type="text" class="form-control" name="lote" id="lote" maxlength="50">
              <div class="invalid-feedback">Lote inválido.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">F. Vencimiento</label>
             <input type="date" class="form-control" name="f_vencimiento" id="fvencimiento">
              <div class="invalid-feedback">Fecha inválida.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Precio Compra</label>
              <input type="number" class="form-control" name="precio_compra" id="precio_compra" required step="0.01" min="0">
              <div class="invalid-feedback">Precio de compra inválido.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Precio Venta</label>
              <input type="number" class="form-control" name="precio_venta" id="precio_venta" required step="0.01" min="0">
              <div class="invalid-feedback">Precio de venta inválido.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">IVA (%)</label>
              <input type="number" class="form-control" name="iva" id="iva" step="0.01" min="0">
              <div class="invalid-feedback">IVA inválido.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">SKU</label>
              <input type="text" class="form-control" name="codigo_sku" id="sku" maxlength="50">
              <div class="invalid-feedback">SKU inválido.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Ubicación</label>
              <input type="text" class="form-control" name="ubicacion" id="ubicacion" maxlength="100">
              <div class="invalid-feedback">Ubicación inválida.</div>
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
  document.getElementById('btnNuevoProducto').addEventListener('click', function () {
    const modal = new bootstrap.Modal(document.getElementById('modalProducto'));
    modal.show();
  });

  window.PRODUCTO_API = '/controllers/AdminProducto.php';
</script>
<script src="/Hieribal2/public/assets/js/admin_productos.js?v=1" defer></script>