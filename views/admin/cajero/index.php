<?php
$base = $this->config['app']['base_url'] ?? '';
?>
<section class="card shadow-sm ui-pro border-0 rounded-4">
  <div class="card-body">

    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-cash-coin fs-4 text-primary"></i>
        <h1 class="h4 m-0">Cajero</h1>
      </div>
    </div>

    <div class="row g-3">

      <div class="col-lg-7">

        <div class="card border-0 shadow-sm rounded-4 mb-3">
          <div class="card-body">

            <h5 class="fw-semibold mb-3">
              <i class="bi bi-bag-plus me-2 text-primary"></i> Nueva venta
            </h5>

            <div class="row g-2 mb-3">
              <div class="col-md-4">
                <label class="form-label small">Nombre cliente <span class="text-danger">*</span></label>
                <input type="text"
                       id="cliNombre"
                       class="form-control form-control-sm"
                       placeholder="Ej: Juan">
              </div>
              <div class="col-md-4">
                <label class="form-label small">Apellido cliente <span class="text-danger">*</span></label>
                <input type="text"
                       id="cliApellido"
                       class="form-control form-control-sm"
                       placeholder="Ej: Pérez"
                       required> 
              </div>
              <div class="col-md-4">
                <label class="form-label small">Cédula <span class="text-danger">*</span></label>
                <input type="text"
                       id="cliCedula"
                       class="form-control form-control-sm"
                       placeholder="Ej: 1012345678">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small">Método de pago</label>
              <select id="metodoPago" class="form-select form-select-sm">
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
              </select>
              <div class="form-text small">
                Este valor aparecerá en la factura.
              </div>
            </div>

            <hr>

            <div class="mb-3">
              <label class="form-label">Producto</label>
              <input class="form-control" 
                     list="listaProductos" 
                     id="productoSelect" 
                     placeholder="Escriba para buscar un producto...">
              <datalist id="listaProductos">
                </datalist>
              <div class="form-text small">Escriba el nombre o código para filtrar.</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Cantidad</label>
              <input id="cantidadProducto" type="number" min="1" value="1"
                     class="form-control">
            </div>

            <button id="btnAgregarProducto" class="btn btn-primary w-100 mb-3">
              <i class="bi bi-cart-plus me-1"></i> Agregar al carrito
            </button>

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

            <div class="d-flex justify-content-end mt-2">
              <h5>Total: <span id="totalVenta">$0</span></h5>
            </div>

            <button id="btnGuardarVenta" class="btn btn-success w-100 mt-3">
              <i class="bi bi-check2-circle me-1"></i> Guardar venta
            </button>

            <div id="cjMsg" class="mt-2 small text-center"></div>

          </div>
        </div>
      </div>

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

<div class="modal fade" id="ventaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title">
          <i class="bi bi-cash-coin me-1 text-success"></i> Confirmar venta
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body pt-0">
        <p class="mb-2">
          <strong>Total a pagar:</strong>
          <span id="vmTotal" class="fw-bold text-primary"></span>
        </p>

        <div class="mb-3">
          <label for="vmPagaCon" class="form-label mb-1">¿Con cuánto paga el cliente?</label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number"
                   class="form-control"
                   id="vmPagaCon"
                   min="1"
                   step="1"
                   autocomplete="off"
                   placeholder="Ej: 5000">
          </div>
          <div class="form-text">Solo números, sin puntos ni comas.</div>
          <div class="invalid-feedback">
            El valor debe ser igual o mayor al total de la venta.
          </div>
        </div>

        <p class="mb-2">
          <strong>Cambio:</strong>
          <span id="vmCambio" class="fw-bold text-success">$0</span>
        </p>

        <hr>

        <div class="small text-muted" id="vmResumen"></div>
      </div>

      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          Cancelar
        </button>
        <button type="button" class="btn btn-success" id="vmBtnConfirmar">
          <i class="bi bi-check2-circle me-1"></i> Registrar venta
        </button>
      </div>
    </div>
  </div>
</div>

<div id="toastHost" class="toast-host" aria-live="polite" aria-atomic="true"></div>