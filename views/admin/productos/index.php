<?php
// views/admin/productos/index.php
$base = $this->config['app']['base_url'] ?? '';
?>
<section class="card shadow-sm ui-pro border-0 rounded-4">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-basket fs-4 text-success"></i>
        <h1 class="h4 m-0">Productos</h1>
      </div>
      <button id="btnNuevoProd" type="button" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i> Nuevo
      </button>
    </div>

    <div class="input-group mb-3" style="max-width:520px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input id="qProd" type="search" class="form-control" placeholder="Buscar por nombre, SKU, marca o categoría…">
      <button id="btnBuscarProd" class="btn btn-outline-success">Buscar</button>
    </div>

    <div class="table-responsive">
      <table id="tblProductos" class="table table-sm align-middle table-hover">
        <thead class="table-light position-sticky" style="top:0; z-index:1;">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>SKU</th>
            <th>Marca</th>
            <th>Categoría</th>
            <th>Stock</th>
            <th>Stock Mín.</th>
            <th>P. Compra</th>
            <th>P. Venta</th>
            <th>IVA</th>
            <th>Estado</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>

      <div class="d-flex align-items-center justify-content-between mt-3">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small me-1">Mostrar</label>
          <select id="perPageProd" class="form-select form-select-sm" style="width:80px">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>
          <span id="totalProductos" class="text-muted small ms-2"></span>
        </div>
        <nav aria-label="Paginación">
          <ul id="paginadorProd" class="pagination pagination-sm mb-0"></ul>
        </nav>
      </div>
    </div>
  </div>
</section>

<!-- Modal Producto -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="modalProdTitle">Nuevo producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body pt-3">
        <form id="frmProducto" class="needs-validation" novalidate>
          <input type="hidden" name="id" id="idProducto">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input class="form-control" name="nombre" id="nombre" required maxlength="255">
              <div class="invalid-feedback">Nombre requerido.</div>
            </div>

            <div class="col-md-3">
              <label class="form-label">SKU</label>
              <input class="form-control" name="codigo_sku" id="codigo_sku" maxlength="64">
            </div>

            <div class="col-md-3">
              <label class="form-label">Estado</label>
              <select class="form-select" name="estado" id="estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Categoría</label>
              <input class="form-control" name="categoria" id="categoria" maxlength="100">
            </div>

            <div class="col-md-4">
              <label class="form-label">Marca</label>
              <input class="form-control" name="marca" id="marca" maxlength="100">
            </div>

            <div class="col-md-4">
              <label class="form-label">Presentación</label>
              <input class="form-control" name="presentacion" id="presentacion" maxlength="100">
            </div>

            <div class="col-md-3">
              <label class="form-label">Stock actual</label>
              <input type="number" class="form-control" name="stock_actual" id="stock_actual" min="0" value="0" required>
            </div>

            <div class="col-md-3">
              <label class="form-label">Stock mínimo</label>
              <input type="number" class="form-control" name="stock_minimo" id="stock_minimo" min="0" value="0" required>
            </div>

            <div class="col-md-3">
              <label class="form-label">P. Compra</label>
              <input type="number" step="0.01" class="form-control" name="precio_compra" id="precio_compra" min="0" value="0">
            </div>

            <div class="col-md-3">
              <label class="form-label">P. Venta</label>
              <input type="number" step="0.01" class="form-control" name="precio_venta" id="precio_venta" min="0" value="0">
            </div>

            <div class="col-md-3">
              <label class="form-label">IVA (%)</label>
              <input type="number" step="0.01" class="form-control" name="iva" id="iva" min="0" value="0">
            </div>

            <div class="col-md-3">
              <label class="form-label">Lote</label>
              <input class="form-control" name="lote" id="lote" maxlength="80">
            </div>

            <div class="col-md-3">
              <label class="form-label">F. Vencimiento</label>
              <input type="date" class="form-control" name="f_vencimiento" id="f_vencimiento">
            </div>

            <div class="col-md-3">
              <label class="form-label">Ubicación</label>
              <input class="form-control" name="ubicacion" id="ubicacion" maxlength="100">
            </div>

            <div class="col-12">
              <label class="form-label">Descripción</label>
              <textarea class="form-control" name="descripcion" id="descripcion" rows="2" maxlength="1000"></textarea>
            </div>

            <div class="col-12">
              <label class="form-label">URL Imagen</label>
              <input class="form-control" name="imagen" id="imagen" maxlength="255">
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

<style>
  #tblProductos thead.table-light{
    --bs-table-bg: var(--brand, #198754);
    --bs-table-color: #fff;
    --bs-table-border-color: rgba(255,255,255,.25);
    background: var(--brand, #198754) !important;
    color: #fff !important;
    background-image: none !important;
  }
  #tblProductos thead.table-light th{ color:#fff !important; }
</style>

<script>
  window.PRODUCTO_API = '<?= $base ?>/?r=admin_productos_api';
  document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btnNuevoProd');
    if (btn && window.bootstrap) {
      btn.addEventListener('click', () => {
        const el = document.getElementById('modalProducto');
        if (el) new bootstrap.Modal(el).show();
      });
    }
  });
</script>

<script src="<?= $base ?>/assets/js/admin_productos.js?v=2" defer></script>
