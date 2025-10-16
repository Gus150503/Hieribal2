<?php
// views/admin/configuracion/index.php
// Se inyecta dentro de plantilla.php (Bootstrap y sidebar ya cargan allí)
$base = $this->config['app']['base_url'] ?? '';
?>

<section class="card shadow-sm border-0 rounded-4">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h4 m-0">Configuración</h1>
      <button id="btnGuardarCfg" class="btn btn-success">
        <i class="bi bi-check2-circle me-1"></i> Guardar cambios
      </button>
    </div>

    <!-- Fallback por si Bootstrap JS no activa las tabs -->
    <style>
      .tab-pane{display:none}
      .tab-pane.active{display:block}
    </style>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
      <li class="nav-item" role="presentation">
        <a class="nav-link active" id="tabEmpresa-tab" data-bs-toggle="tab" href="#tabEmpresa"
           role="tab" aria-controls="tabEmpresa" aria-selected="true">Empresa</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="tabCorreo-tab" data-bs-toggle="tab" href="#tabCorreo"
           role="tab" aria-controls="tabCorreo" aria-selected="false">Correo</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="tabUI-tab" data-bs-toggle="tab" href="#tabUI"
           role="tab" aria-controls="tabUI" aria-selected="false">Apariencia</a>
      </li>
    </ul>

    <div class="tab-content">
      <!-- Empresa -->
      <div class="tab-pane active" id="tabEmpresa" role="tabpanel" aria-labelledby="tabEmpresa-tab">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nombre comercial</label>
            <input id="empresa_nombre" class="form-control" placeholder="Mi Hieribal">
          </div>
          <div class="col-md-6">
            <label class="form-label">RUC / NIT</label>
            <input id="empresa_ruc" class="form-control">
          </div>
          <div class="col-12">
            <label class="form-label">Dirección</label>
            <input id="empresa_direccion" class="form-control">
          </div>
        </div>
      </div>

      <!-- Correo -->
      <div class="tab-pane" id="tabCorreo" role="tabpanel" aria-labelledby="tabCorreo-tab">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Servidor SMTP</label>
            <input id="correo_host" class="form-control" placeholder="smtp.tu-dominio.com">
          </div>
          <div class="col-md-3">
            <label class="form-label">Puerto</label>
            <input id="correo_puerto" type="number" class="form-control" value="587" min="1">
          </div>
          <div class="col-md-3">
            <label class="form-label">Seguridad</label>
            <select id="correo_seguridad" class="form-select">
              <option value="tls">TLS</option>
              <option value="ssl">SSL</option>
              <option value="none">Sin cifrado</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Usuario</label>
            <input id="correo_usuario" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Remitente (From)</label>
            <input id="correo_from" class="form-control" placeholder="no-reply@tu-dominio.com">
          </div>
          <div class="col-12">
            <div class="form-check mt-2">
              <input id="correo_activo" class="form-check-input" type="checkbox">
              <label class="form-check-label" for="correo_activo">Habilitar envío de correos</label>
            </div>
          </div>
        </div>
      </div>

      <!-- Apariencia -->
      <div class="tab-pane" id="tabUI" role="tabpanel" aria-labelledby="tabUI-tab">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Tema</label>
            <select id="ui_tema" class="form-select">
              <option value="light">Claro</option>
              <option value="dark">Oscuro</option>
              <option value="auto">Automático</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Color principal</label>
            <input id="ui_color_principal" class="form-control" type="color" value="#198754" title="Color de marca">
          </div>
          <div class="col-md-4">
            <label class="form-label">Previsualización</label>
            <div class="form-control d-flex align-items-center" style="height: 38px;">
              <span class="badge text-bg-success" id="ui_preview">Botón</span>
            </div>
          </div>
        </div>
      </div>
    </div> <!-- /tab-content -->
  </div>
</section>

<!-- JS específico de Configuración -->
<script src="<?= $base ?>/assets/js/admin_config.js?v=4"></script>
