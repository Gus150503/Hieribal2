<?php $partial = !empty($_GET['partial']); ?>
<?php if (!$partial) include __DIR__ . '/../_shell_start.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

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
  </div>
</section>

<!-- Modal Producto -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nuevo Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          <form id="frmProducto" method="POST" action="/Hieribal2/public/?r=admin_producto/api">
              <input type="hidden" name="action" value="create" id="productoAction">
              <input type="hidden" name="id" id="idProducto">
    <!-- aquí van los demás campos -->
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input type="text" class="form-control" name="nombre" id="nombre" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Categoría</label>
              <input type="text" class="form-control" name="categoria" id="categoria" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Marca</label>
              <input type="text" class="form-control" name="marca" id="marca">
            </div>
            <div class="col-md-6">
              <label class="form-label">Presentación</label>
              <input type="text" class="form-control" name="presentacion" id="presentacion">
            </div>
            <div class="col-md-6">
              <label class="form-label">Unidad</label>
              <input type="text" class="form-control" name="unidad" id="unidad">
            </div>
            <div class="col-md-6">
              <label class="form-label">Descripción</label>
              <textarea class="form-control" name="descripcion" id="descripcion"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Lote</label>
              <input type="text" class="form-control" name="lote" id="lote">
            </div>
            <div class="col-md-6">
              <label class="form-label">F. Vencimiento</label>
              <input type="date" class="form-control" name="fvencimiento" id="fvencimiento">
            </div>
            <div class="col-md-6">
              <label class="form-label">Precio Compra</label>
              <input type="number" class="form-control" name="precio_compra" id="precio_compra" min="0">
            </div>
            <div class="col-md-6">
              <label class="form-label">Precio Venta</label>
              <input type="number" class="form-control" name="precio_venta" id="precio_venta" min="0">
            </div>
            <div class="col-md-6">
              <label class="form-label">IVA</label>
              <input type="number" class="form-control" name="iva" id="iva" min="0" max="100">
            </div>
            <div class="col-md-6">
              <label class="form-label">Código SKU</label>
              <input type="text" class="form-control" name="codigo_sku" id="codigo_sku">
            </div>
            <div class="col-md-6">
              <label class="form-label">Ubicación</label>
              <input type="text" class="form-control" name="ubicacion" id="ubicacion">
            </div>

            <div class="col-md-6">
                <label class="form-label">Imagen</label>
                <input type="file" name="imagen" class="form-control" accept="image/*">
            </div>


            <div class="col-md-6">
              <label class="form-label">Estado</label>

              <select class="form-select" name="estado" id="estado">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
              </select>
            </div>
          </div>

          <div class="modal-footer border-0 pt-3">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check2-circle me-1"></i> Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('btnNuevoProducto').addEventListener('click', () => {
    const modal = new bootstrap.Modal(document.getElementById('modalProducto'));
    modal.show();
  });
  window.PRODUCTO_API = '/controllers/AdminProducto.php';
</script>
<script src="/public/assets/js/admin_producto.js?v=1"></script>

<?php if (!$partial) include __DIR__ . '/../_shell_end.php'; ?>
