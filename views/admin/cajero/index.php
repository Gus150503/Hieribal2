<?php
$base = $this->config['app']['base_url'] ?? '';
?>
<section class="card shadow-sm ui-pro border-0 rounded-4">
  <div class="card-body">

    <!-- TITULO + ICONO -->
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-cash-coin fs-4 text-primary"></i>
        <h1 class="h4 m-0">Cajero</h1>
      </div>
    </div>

    <!-- LAYOUT DE 2 COLUMNAS -->
    <div class="row g-3">

      <!-- ==============================
           COLUMNA IZQUIERDA (VENTA)
      =============================== -->
      <div class="col-lg-7">

        <div class="card border-0 shadow-sm rounded-4 mb-3">
          <div class="card-body">

            <h5 class="fw-semibold mb-3">
              <i class="bi bi-bag-plus me-2 text-primary"></i> Nueva venta
            </h5>

            <!-- SELECT PRODUCTO (DROP DOWN) -->
            <div class="mb-3">
              <label class="form-label">Producto</label>
              <select id="productoSelect" class="form-select">
                <option value="">Seleccione un producto…</option>
                <!-- Se llena desde admin_cajero_api&action=productos -->
              </select>
            </div>

            <!-- CANTIDAD -->
            <div class="mb-3">
              <label class="form-label">Cantidad</label>
              <input id="cantidadProducto" type="number" min="1" value="1"
                     class="form-control">
            </div>

            <!-- BOTÓN AGREGAR -->
            <button id="btnAgregarProducto" class="btn btn-primary w-100 mb-3">
              <i class="bi bi-cart-plus me-1"></i> Agregar al carrito
            </button>

            <!-- TABLA DEL CARRITO -->
            <div class="table-responsive">
              <table id="tablaCarrito" class="table table-sm table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                    <th class="text-end">Eliminar</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

            <!-- TOTAL -->
            <div class="d-flex justify-content-end mt-2">
              <h5>Total: <span id="totalVenta">$0</span></h5>
            </div>

            <!-- BOTÓN GUARDAR VENTA -->
            <button id="btnGuardarVenta" class="btn btn-success w-100 mt-3">
              <i class="bi bi-check2-circle me-1"></i> Guardar venta
            </button>

            <div id="cjMsg" class="mt-2 small text-center"></div>

          </div>
        </div>
      </div>


      <!-- ==============================
           COLUMNA DERECHA (HISTORIAL)
      =============================== -->
      <div class="col-lg-5">

        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body">

            <h5 class="fw-semibold mb-3">
              <i class="bi bi-clock-history me-2 text-primary"></i>
              Mis ventas
            </h5>

            <div class="table-responsive" style="max-height:420px; overflow-y:auto;">
              <table id="tablaHistorial" class="table table-sm table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- Toasts -->
<div id="toastHost" class="toast-host" aria-live="polite" aria-atomic="true"></div>

<!-- JS específico de cajero -->
<script src="<?= $base ?>/assets/js/admin_cajero.js?v=1" defer></script>
