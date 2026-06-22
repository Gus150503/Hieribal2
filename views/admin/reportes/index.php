<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Mi Hierbal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-4">
    <section class="card shadow-sm ui-pro border-0 rounded-4">
      <div class="card-body">

        <div class="d-flex align-items-center gap-2 mb-3">
          <i class="bi bi-bar-chart-line fs-4 text-success"></i>
          <div>
            <h1 class="h4 m-0">Reportes</h1>
            <small class="text-muted">Descarga la información del sistema en Excel</small>
          </div>
        </div>

        <div class="row g-3">

          <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-box-seam text-success"></i>
                  <h6 class="m-0 fw-semibold">Inventario</h6>
                </div>
                <p class="text-muted small flex-grow-1">Stock actual de productos.</p>
                <a href="?r=admin_reportes_inventario_excel" class="btn btn-outline-success btn-sm w-100">
                  <i class="bi bi-download me-1"></i> Descargar Excel
                </a>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-cart-check text-success"></i>
                  <h6 class="m-0 fw-semibold">Ventas</h6>
                </div>
                <p class="text-muted small flex-grow-1">Ventas registradas en el sistema.</p>
                <a href="?r=admin_reportes_ventas_excel" class="btn btn-outline-success btn-sm w-100">
                  <i class="bi bi-download me-1"></i> Descargar Excel
                </a>
              </div>
            </div>
          </div>

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

          <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-people text-success"></i>
                  <h6 class="m-0 fw-semibold">Clientes</h6>
                </div>
                <p class="text-muted small flex-grow-1">Listado de clientes.</p>
                <a href="?r=admin_reportes_clientes_excel" class="btn btn-outline-success btn-sm w-100">
                  <i class="bi bi-download me-1"></i> Descargar Excel
                </a>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-person text-success"></i>
                  <h6 class="m-0 fw-semibold">Usuarios</h6>
                </div>
                <p class="text-muted small flex-grow-1">Usuarios del sistema.</p>
                <a href="?r=admin_reportes_usuarios_excel" class="btn btn-outline-success btn-sm w-100">
                  <i class="bi bi-download me-1"></i> Descargar Excel
                </a>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-arrow-return-left text-success"></i>
                  <h6 class="m-0 fw-semibold">Devoluciones</h6>
                </div>
                <p class="text-muted small flex-grow-1">Historial de devoluciones.</p>
                <a href="?r=admin_reportes_devoluciones_excel" class="btn btn-outline-success btn-sm w-100">
                  <i class="bi bi-download me-1"></i> Descargar Excel
                </a>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>