<?php
// views/admin/productos/index.php
// MODULO PRODUCTOS / Juliana Lugo / Vista de index de productos 

// === Rol / permisos (igual que clientes) ===
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$rol = strtolower($_SESSION['admin']['rol'] ?? 'empleado');
$puedeGestionarProductos = ($rol === 'admin');
?>

<section class="card shadow-sm ui-pro border-0 rounded-4">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-basket fs-4 text-success"></i>
                <h1 class="h4 m-0">Productos</h1>
            </div>
                <?php if ($puedeGestionarProductos): ?>
            <button id="btnNuevoProd" type="button" class="btn btn-success">
                <i class="bi bi-plus-lg me-1"></i> Nuevo
            </button>
            <?php endif; ?>

        </div>

        <!-- Buscador -->
        <div class="input-group input-group-sm mb-3" style="max-width:400px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input id="qProd" type="search" class="form-control"
                placeholder="Buscar por Nombre, Categoria o Codigo">
            <button id="btnBuscarProd" class="btn btn-outline-success">Buscar</button>
        </div>
    <!-- ===================================== -->
    <!-- CONTENEDOR SCROLL PARA TABLA PRODUCTOS -->
    <!-- ===================================== -->
    <div class="contenedor-tabla-productos">
            <table id="tblProductos" class="table table-sm align-middle table-hover">
                <thead class="table-light position-sticky" style="top:0; z-index:1;">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th>Presentación</th>
                    <th>Descripción</th>
                    <th>Stock Mínimo</th>
                    <th>Lote</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Precio Compra</th>
                    <th>Precio Venta</th>
                    <th>IVA</th>
                    <th>Cod. Barras</th>
                    <th>Ubicación</th>
                    <th>Estado</th>
                    <th>Imagen</th>
                    <?php if ($puedeGestionarProductos): ?>
                    <th class="text-end">Acciones</th>
                <?php endif; ?>

                </tr>
                </thead>
                <tbody></tbody>
            </table>
    </div>                   
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
                    <!-- Este ID se usa solo para editar; en "Nuevo" lo limpia el JS -->
                    <input type="hidden" name="id" id="idProducto" value="">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input class="form-control" name="nombre" id="nombre" required maxlength="255">
                            <div class="invalid-feedback">Nombre requerido.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoría</label>
                            <input list="listaCategorias" name="categoria" class="form-control" placeholder="Seleccione o escriba una categoría">
                            <datalist id="listaCategorias">
                                <option value="Aceites y Grasas Saludables">
                                <option value="Aminoácidos">
                                <option value="Antioxidantes">
                                <option value="Bebidas Vegetales">
                                <option value="Cápsulas de Plantas">
                                <option value="Creatinas">
                                <option value="Energizantes">
                                <option value="Endulzantes Naturales">
                                <option value="Extractos y Tinturas Madre">
                                <option value="Fibras">
                                <option value="Frutos Secos y Deshidratados">
                                <option value="Gotas y Esencias">
                                <option value="Granos, Semillas y Cereales">
                                <option value="Harinas Especiales">
                                <option value="Jarabes Naturales">
                                <option value="Laxantes">
                                <option value="Minerales">
                                <option value="Multivitamínicos">
                                <option value="Plantas Medicinales y Hierbas">
                                <option value="Probióticos">
                                <option value="Snacks Saludables">
                                <option value="Tés e Infusiones">
                                <option value="Vitaminas">
                            </datalist>
                            <div class="invalid-feedback">Categoría requerida.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marca</label>
                            <input class="form-control" name="marca" id="marca" required maxlength="100">
                            <div class="invalid-feedback">Marca requerida.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Presentación</label>
                            <input list="listaPresentacion" name="presentacion" class="form-control" placeholder="Seleccione o escriba una presentación">
                            <datalist id="listaPresentacion">
                                <option value="Atado">
                                <option value="Bolsa">
                                <option value="Caja">
                                <option value="Capsulas">
                                <option value="Frasco">
                                <option value="Frasco vidrio">
                                <option value="Gotero">
                                <option value="Liquido">
                                <option value="Sachet">
                            </datalist>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" id="descripcion" rows="2"
                            maxlength="100"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Stock mínimo</label>
                            <input type="number" class="form-control" name="stock_minimo" id="stock_minimo"
                                   min="0" value="0" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Lote</label>
                            <input class="form-control" name="lote" id="lote" maxlength="80">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Fecha Vencimiento</label>
                            <input type="date" class="form-control" name="f_vencimiento" id="f_vencimiento">
                            <div class="invalid-feedback">Fecha requerida.</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Precio Compra</label>
                            <input type="number" step="0.01" class="form-control" name="precio_compra"
                                   id="precio_compra" min="0.01" value="" required>
                            <div class="invalid-feedback">Precio Compra requerido.</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">IVA</label><br>

                            <input type="radio" name="iva" value="1" id="iva_si" checked>
                            <label for="iva_si">Con IVA</label>

                            <input type="radio" name="iva" value="0" id="iva_no" style="margin-left:10px;">
                            <label for="iva_no">Sin IVA</label>

                            <small id="info_iva" style="display:block; margin-top:5px; color:#6c757d;">
                                IVA (19%): 0 <br> Neto: 0
                            </small>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Precio Venta</label>
                            <input type="number" step="0.01" class="form-control" name="precio_venta"
                                   id="precio_venta" min="0.01" value="" required>
                            <div class="invalid-feedback">Precio Venta requerido.</div>
                            <div id="mensajeGanancia" class="mensaje-ganancia"></div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Cod. Barras</label>
                            <input class="form-control" name="codigo_sku" id="codigo_sku" maxlength="64">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Ubicación</label>
                            <input class="form-control" name="ubicacion" id="ubicacion" maxlength="100">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="estado" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>

                        <!-- Imagen -->
                        <div class="col-12">
                            <label class="form-label d-block">Imagen del Producto (Opcional)</label>

                            <div class="btn-group mb-3" role="group">
                                <input type="radio" class="btn-check" name="tipoImagen" id="tipoURL" value="url" checked>
                                <label class="btn btn-outline-success" for="tipoURL">
                                    <i class="bi bi-link-45deg me-1"></i> URL
                                </label>

                                <input type="radio" class="btn-check" name="tipoImagen" id="tipoArchivo" value="archivo">
                                <label class="btn btn-outline-success" for="tipoArchivo">
                                    <i class="bi bi-upload me-1"></i> Subir Archivo
                                </label>
                            </div>

                            <div id="seccionURL">
                                <input type="text" class="form-control" name="imagen" id="imagen"
                                       placeholder="https://ejemplo.com/imagen.jpg" maxlength="500">
                                <small class="text-muted">Pega la URL de una imagen de internet</small>
                            </div>

                            <div id="seccionArchivo" style="display:none;">
                                <input type="file" class="form-control" id="archivoImagen" name="imagen_archivo"
                                       accept="image/*">
                                <small class="text-muted">Formatos: JPG, PNG, GIF (Máx. 2MB)</small>
                            </div>

                            <div id="previewImagen" class="mt-3" style="display:none;">
                                <label class="form-label text-muted small">Vista previa:</label>
                                <div class="border rounded p-2 bg-light d-flex align-items-center justify-content-center"
                                     style="height:150px;">
                                    <img id="imgPreview" src="" alt="Preview"
                                         style="max-height:140px; max-width:100%; object-fit:contain; border-radius:8px;">
                                </div>
                                <button type="button" id="btnLimpiarImagen" class="btn btn-sm btn-outline-danger mt-2">
                                    <i class="bi bi-x-circle me-1"></i> Quitar imagen
                                </button>
                            </div>
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

<script>window.PRODUCTO_API = '<?= htmlspecialchars($this->config['app']['base_url']) ?>/public/?r=admin_productos_api';</script> 
    <?php
    $titulo = "Productos";
    $esAdmin = true;

    /* AQUÍ AGREGAMOS LOS CSS Y JS CORRECTAMENTE PARA EL USO DE LA PLANTILLA*/
    $extra_css = [
        'assets/css/AdminProducto.css'
    ];

    $extra_js = [
        'assets/js/admin_productos.js?v=6'
    ];
    ?>