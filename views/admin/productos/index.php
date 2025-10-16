<?php
// views/admin/productos/index.php
// Esta vista se inyecta dentro de plantilla.php (que ya carga Bootstrap, Icons y sidebar)
$base = $this->config['app']['base_url'] ?? '';
?>

<style>
/* Encabezado azul para la tabla de Productos */
#tblProducto thead.table-light{
  --bs-table-bg: #0d6efd;
  --bs-table-color: #fff;
  --bs-table-border-color: rgba(255,255,255,.25);
  background: #0d6efd !important;
  color: #fff !important;
  background-image: none !important;
}
#tblProducto thead.table-light th{ color:#fff !important; }
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

    <!-- Buscador -->
    <div class="input-group mb-3" style="max-width:520px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input id="qProducto" type="search" class="form-control" placeholder="Buscar por nombre, marca o categoría…">
      <button id="btnBuscarProducto" class="btn btn-outline-primary">Buscar</button>
    </div>

    <!-- Tabla -->
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
        <select id="perPageProducto" class="form-select form-select-sm" style="width:80px">
          <option value="5">5</option>
          <option value="10" selected>10</option>
          <option value="20">20</option>
          <option value="50">50</option>
        </select>
        <span id="totalProducto" class="text-muted small ms-2"></span>
      </div>
      <nav aria-label="Paginación">
        <ul id="paginadorProducto" class="pagination pagination-sm mb-0"></ul>
      </nav>
    </div>
  </div>
</section>

<!-- Modal Producto -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nuevo Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <form id="frmProducto"
              method="POST"
              action="<?= $base ?>/?r=admin_producto/api"
              enctype="multipart/form-data"
              class="needs-validation"
              novalidate>
          <input type="hidden" name="action" value="create" id="productoAction">
          <input type="hidden" name="id" id="idProducto">

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
              <input type="number" class="form-control" name="precio_compra" id="precio_compra" min="0" step="0.01">
            </div>
            <div class="col-md-6">
              <label class="form-label">Precio Venta</label>
              <input type="number" class="form-control" name="precio_venta" id="precio_venta" min="0" step="0.01">
            </div>
            <div class="col-md-6">
              <label class="form-label">IVA (%)</label>
              <input type="number" class="form-control" name="iva" id="iva" min="0" max="100" step="0.01">
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
              <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*">
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
document.addEventListener('DOMContentLoaded', () => {
  // Abrir modal
  const btn = document.getElementById('btnNuevoProducto');
  if (btn){
    btn.addEventListener('click', () => {
      const modal = new bootstrap.Modal(document.getElementById('modalProducto'));
      modal.show();
    });
  }

  // API base (ajusta si tu ruta difiere)
  window.PRODUCTO_API = '<?= $base ?>/controllers/AdminProducto.php';
});
</script>

<!-- JS propio de la página -->
<script src="<?= $base ?>/assets/js/admin_producto.js?v=2"></script>
