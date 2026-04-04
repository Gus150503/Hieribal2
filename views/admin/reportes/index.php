<section class="card shadow-sm ui-pro border-0 rounded-4">
  <div class="card-body">

    <!-- ENCABEZADO -->
    <div class="d-flex align-items-center gap-2 mb-3">
      <i class="bi bi-bar-chart-line fs-4 text"></i>
      <div>
        <h1 class="h4 m-0">Reportes</h1>
        <small class="text-muted">Descarga la información del sistema en Excel</small>
      </div>
    </div>

    <!-- GRID DE REPORTES -->
    <div class="row g-3">

      <!-- INVENTARIO -->
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-4">
          <div class="card-body d-flex flex-column">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-box-seam text-primary"></i>
              <h6 class="m-0 fw-semibold">Inventario</h6>
            </div>
            <p class="text-muted small flex-grow-1">Stock actual de productos.</p>
            <a href="?r=admin_reportes_inventario_excel" class="btn btn-outline-primary btn-sm w-100">
              <i class="bi bi-download me-1"></i> Descargar Excel
            </a>
          </div>
        </div>
      </div>

      <!-- VENTAS -->
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-4">
          <div class="card-body d-flex flex-column">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-cart-check text-warning"></i>
              <h6 class="m-0 fw-semibold">Ventas</h6>
            </div>
            <p class="text-muted small flex-grow-1">Ventas registradas en el sistema.</p>
            <a href="?r=admin_reportes_ventas_excel" class="btn btn-outline-warning btn-sm w-100">
              <i class="bi bi-download me-1"></i> Descargar Excel
            </a>
          </div>
        </div>
      </div>



      <!-- PROVEEDORES -->
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-4">
          <div class="card-body d-flex flex-column">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-truck text-success"></i>
              <h6 class="m-0 fw-semibold">Proveedores</h6>
            </div>
            <p class="text-muted small flex-grow-1">Datos de proveedores.</p>
            <a href="?r=admin_reportes_proveedores_excel" class="btn btn-outline-success btn-sm w-100">
              <i class="bi bi-download me-1"></i> Descargar Excel
            </a>
          </div>
        </div>
      </div>



      <!-- CLIENTES -->
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-4">
          <div class="card-body d-flex flex-column">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-people text-secondary"></i>
              <h6 class="m-0 fw-semibold">Clientes</h6>
            </div>
            <p class="text-muted small flex-grow-1">Listado de clientes.</p>
            <a href="?r=admin_reportes_clientes_excel" class="btn btn-outline-secondary btn-sm w-100">
              <i class="bi bi-download me-1"></i> Descargar Excel
            </a>
          </div>
        </div>
      </div>



      <!-- USUARIOS -->
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-4">
          <div class="card-body d-flex flex-column">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-person text-info"></i>
              <h6 class="m-0 fw-semibold">Usuarios</h6>
            </div>
            <p class="text-muted small flex-grow-1">Usuarios del sistema.</p>
            <a href="?r=admin_reportes_usuarios_excel" class="btn btn-outline-info btn-sm w-100">
              <i class="bi bi-download me-1"></i> Descargar Excel
            </a>
          </div>
        </div>
      </div>


      <!-- DEVOLUCIONES -->
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-4">
          <div class="card-body d-flex flex-column">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-arrow-return-left text-danger"></i>
              <h6 class="m-0 fw-semibold">Devoluciones</h6>
            </div>
            <p class="text-muted small flex-grow-1">Historial de devoluciones.</p>
            <a href="?r=admin_reportes_devoluciones_excel" class="btn btn-outline-danger btn-sm w-100">
              <i class="bi bi-download me-1"></i> Descargar Excel
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>