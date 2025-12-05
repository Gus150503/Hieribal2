// assets/js/admin_productos.js
(function () {
    if (window.__PRODUCTOS_JS_BOUND__) return;
    window.__PRODUCTOS_JS_BOUND__ = true;

    'use strict';

        // Permisos basados en lo que pintó PHP
        const PUEDE_GESTIONAR_PRODUCTOS =
      !!document.querySelector('#tblProductos thead th.text-end');

    // =====================================
    // Endpoints
    // =====================================
    const API_BASE =
        window.PRODUCTO_API ||
        (location.pathname.replace(/\/public\/?$/, '') +
            '/public/?r=admin_productos_api');

    const api = (params = '') => `${API_BASE}&${params}`;

    // =====================================
    // Estado
    // =====================================
    const state = { page: 1, per: 10, total: 0, q: '' };
    let __SEQ__ = 0;
    let imagenBase64 = null; // si suben archivo (por si luego lo usas)

    // =====================================
    // Selectores
    // =====================================
    const $ = (s) => document.querySelector(s);
    const $$ = (s) => Array.from(document.querySelectorAll(s));

    const tblBody = $('#tblProductos tbody');
    const pager = $('#paginadorProd');
    const perSel = $('#perPageProd');
    const totalEl = $('#totalProductos');
    const qInput = $('#qProd');
    const btnBuscar = $('#btnBuscarProd');
    const btnNuevo = $('#btnNuevoProd');

    const modalEl = $('#modalProducto');
    const frm = $('#frmProducto');
    const modalTit = $('#modalProdTitle');
    let bsModal = null;

    // Imagen
    const tipoURL = $('#tipoURL');
    const tipoArchivo = $('#tipoArchivo');
    const seccionURL = $('#seccionURL');
    const seccionArchivo = $('#seccionArchivo');
    const inputImagenURL = $('#imagen');
    const archivoImagen = $('#archivoImagen');
    const previewWrap = $('#previewImagen');
    const imgPreview = $('#imgPreview');
    const btnLimpiarImg = $('#btnLimpiarImagen');

    // =====================================
    // TOASTS
    // =====================================
    function ensureToastCSS() {
        if ($('#_prod_toast_css')) return;
        const css = document.createElement('style');
        css.id = '_prod_toast_css';
        css.textContent = `
.nvtoast-host{position:fixed;right:16px;bottom:16px;display:flex;flex-direction:column;gap:10px;z-index:1090;pointer-events:none}
.nvtoast{pointer-events:auto;min-width:280px;max-width:420px;padding:10px 12px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.18);display:flex;align-items:center;gap:10px;color:#fff;opacity:.98;transform:translateY(20px);animation:_tSlide .2s ease-out forwards;border:1px solid transparent}
.nvtoast .close{margin-left:auto;background:none;border:0;color:inherit;opacity:.9;cursor:pointer;font-size:16px;line-height:1}
.nvtoast .dot{width:8px;height:8px;border-radius:50%;background:currentColor;opacity:.9}
@keyframes _tSlide{to{transform:translateY(0)}}
.nv-success{background:#198754;border-color:#198754;color:#EAF6EF}
.nv-danger{background:#dc3545;border-color:#dc3545;color:#FFE5E8}
.nv-warning{background:#ffc107;border-color:#ffc107;color:#1f1f1f}
.nv-info{background:#0d6efd;border-color:#0d6efd;color:#E9F1FF}
`;
        document.head.appendChild(css);
    }

    function ensureToastHost() {
        let host = $('#nvtoastHost');
        if (!host) {
            host = document.createElement('div');
            host.id = 'nvtoastHost';
            host.className = 'nvtoast-host';
            document.body.appendChild(host);
        }
        return host;
    }

    function uiToast(msg, variant = 'info', ms = 3200) {
        ensureToastCSS();
        const host = ensureToastHost();

        const el = document.createElement('div');
        el.className = `nvtoast nv-${variant}`;
        el.innerHTML = `
            <div class="dot"></div>
            <div class="msg">${(msg ?? '')
                .toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')}</div>
            <button type="button" class="close" aria-label="Cerrar">✕</button>
        `;
        host.appendChild(el);

        const close = () => el.remove();
        el.querySelector('.close')?.addEventListener('click', close);

        const timer = setTimeout(close, ms);
        el.addEventListener(
            'mouseenter',
            () => {
                clearTimeout(timer);
            },
            { once: true }
        );
    }

    // =====================================
    // Confirm modal
    // =====================================
    function ensureConfirmModal() {
        if ($('#confirmModal')) return;
        const wrap = document.createElement('div');
        wrap.innerHTML = `
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" id="confirmTitle">Confirmar acción</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body pt-3" id="confirmBody">¿Seguro?</div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnOkConfirm">Sí, continuar</button>
      </div>
    </div>
  </div>
</div>`;
        document.body.appendChild(wrap.firstElementChild);
    }

    function uiConfirm({
        title = 'Confirmar',
        body = '¿Seguro?',
        confirmText = 'Sí, continuar',
        variant = 'success',
    } = {}) {
        const modal = $('#confirmModal');
        if (!modal || !window.bootstrap) return Promise.resolve(confirm(body));

        $('#confirmTitle').textContent = title;
        $('#confirmBody').innerHTML = (body ?? '')
            .toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/\n/g, '<br>');

        const okBtn = $('#btnOkConfirm');
        okBtn.className =
            'btn ' +
            (variant === 'danger'
                ? 'btn-outline-danger'
                : variant === 'warning'
                ? 'btn-outline-secondary'
                : 'btn-success');
        okBtn.textContent = confirmText;

        return new Promise((resolve) => {
            const bs = new bootstrap.Modal(modal, { backdrop: 'static' });

            const onOk = () => {
                cleanup();
                bs.hide();
                resolve(true);
            };
            const onHide = () => {
                cleanup();
                resolve(false);
            };
            const cleanup = () => {
                okBtn.removeEventListener('click', onOk);
                modal.removeEventListener('hidden.bs.modal', onHide);
            };

            okBtn.addEventListener('click', onOk);
            modal.addEventListener('hidden.bs.modal', onHide, { once: true });

            bs.show();
        });
    }

    // =====================================
    // Validación visual
    // =====================================
    function ensureFieldStyles() {
        if ($('#_prod_field_css')) return;
        const css = document.createElement('style');
        css.id = '_prod_field_css';
        css.textContent = `
.form-control.is-valid,
.form-select.is-valid{
  border-color:#198754!important;
  box-shadow:0 0 0 .2rem rgba(25,135,84,.15)!important;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23198754' class='bi bi-check' viewBox='0 0 16 16'%3E%3Cpath d='M10.97 4.97a.75.75 0 0 1 1.07 1.05l-4.0 4.99a.75.75 0 0 1-1.08.02L3.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 4.473-4.448z'/%3E%3C/svg%3E");
  background-repeat:no-repeat;
  background-position:right .75rem center;
  background-size:1rem;
}
.form-control.is-invalid,
.form-select.is-invalid{
  border-color:#dc3545!important;
  box-shadow:0 0 0 .2rem rgba(220,53,69,.15)!important;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23dc3545' class='bi bi-exclamation-circle' viewBox='0 0 16 16'%3E%3Cpath d='M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0-12a.905.905 0 0 0-.9.995l.35 4.507a.55.55 0 0 0 1.1 0l.35-4.507A.905.905 0 0 0 8 3zm.002 8a1 1 0 1 0 0 2 1 1 0 0 0 0-2z'/%3E%3C/svg%3E");
  background-repeat:no-repeat;
  background-position:right .75rem center;
  background-size:1rem;
}
.valid-optional{
  color:#198754;
  font-size:.875rem;
  margin-top:.25rem;
}
`;
        document.head.appendChild(css);
    }

    function setValid(el, ok, msgIfOptional = '') {
        if (!el) return;

        el.classList.remove('is-valid', 'is-invalid');

        const next = el.nextElementSibling;
        if (
            next &&
            (next.classList?.contains('invalid-feedback') ||
                next.classList?.contains('valid-optional'))
        ) {
            if (next.classList.contains('valid-optional')) {
                next.remove();
            }
        }

        if (ok === true) {
            el.classList.add('is-valid');
            if (msgIfOptional) {
                const small = document.createElement('div');
                small.className = 'valid-optional';
                small.textContent = msgIfOptional;
                el.parentNode.insertBefore(small, el.nextSibling);
            }
        } else if (ok === false) {
            el.classList.add('is-invalid');
            if (!(next && next.classList?.contains('invalid-feedback'))) {
                const fb = document.createElement('div');
                fb.className = 'invalid-feedback';
                fb.textContent = 'Campo inválido.';
                el.parentNode.insertBefore(fb, el.nextSibling);
            }
        }
    }

    // =====================================
    // Utils
    // =====================================
    function escapeHtml(s) {
        return (s ?? '').toString().replace(/[&<>"']/g, (m) => {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            }[m];
        });
    }

    function fmtMoney(n) {
        const v = Number(n || 0);
        return isNaN(v) ? '0.00' : v.toFixed(2);
    }

    function fmtNumber(n) {
        const v = Number(n || 0);
        return isNaN(v) ? '0' : v.toString();
    }

    // =====================================
    // Helpers de validación NUEVOS
    // =====================================
    function soloLetrasEspacios(str) {
        if (!str) return true;
        return /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]+$/.test(str.trim());
    }

    function soloNumeros(str) {
        if (!str) return true;
        return /^[0-9]+$/.test(str.trim());
    }

    // obtener código de barras desde plain (soporta ambos nombres)
    function getCodigoBarrasFromPlain(plain) {
        return (plain.codigo_barras ?? plain.codigo_sku ?? '').toString();
    }

    // Referencias a campos para validar EN TIEMPO REAL
    const inputDescripcion = $('#descripcion');
    const inputCategoria = $('#categoria');
    const inputMarca = $('#marca');
    const inputCodigoBarras =
        document.getElementById('codigo_barras') ||
        document.getElementById('codigo_sku');

    // Listeners para limpiar entrada mientras escribe
    if (inputCategoria) {
        inputCategoria.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(
                /[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]/g,
                ''
            );
        });
    }

    if (inputMarca) {
        inputMarca.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(
                /[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]/g,
                ''
            );
        });
    }

    if (inputDescripcion) {
        inputDescripcion.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(
                /[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]/g,
                ''
            );
        });
    }

    if (inputCodigoBarras) {
        inputCodigoBarras.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    }

    // =====================================
    // Listar + paginación + búsqueda
    // =====================================
    function setLoading(on) {
        if (!tblBody) return;
        if (on) {
            const colspan = PUEDE_GESTIONAR_PRODUCTOS ? 18 : 17;
            tblBody.innerHTML = `
<tr>
  <td colspan="${colspan}" class="text-center py-3">
    <div class="spinner-border spinner-border-sm me-2"></div>
    Cargando…
  </td>
</tr>`;
        }
    }


    function renderPager() {
        if (!pager) return;
        const pages = Math.max(
            1,
            Math.ceil((state.total || 0) / (state.per || 10))
        );
        let html = '';

        const prevDis = state.page <= 1 ? ' disabled' : '';
        html += `
<li class="page-item${prevDis}">
  <button class="page-link" data-page="${
      state.page - 1
  }" aria-label="Anterior">&laquo;</button>
</li>`;

        const win = 2;
        let start = Math.max(1, state.page - win);
        let end = Math.min(pages, state.page + win);

        if (start > 1) {
            html += `
<li class="page-item">
  <button class="page-link" data-page="1">1</button>
</li>`;
            if (start > 2) {
                html += `
<li class="page-item disabled">
  <span class="page-link">…</span>
</li>`;
            }
        }

        for (let p = start; p <= end; p++) {
            html += `
<li class="page-item ${p === state.page ? 'active' : ''}">
  <button class="page-link" data-page="${p}">${p}</button>
</li>`;
        }

        if (end < pages) {
            if (end < pages - 1) {
                html += `
<li class="page-item disabled">
  <span class="page-link">…</span>
</li>`;
            }
            html += `
<li class="page-item">
  <button class="page-link" data-page="${pages}">${pages}</button>
</li>`;
        }

        const nextDis = state.page >= pages ? ' disabled' : '';
        html += `
<li class="page-item${nextDis}">
  <button class="page-link" data-page="${
      state.page + 1
  }" aria-label="Siguiente">&raquo;</button>
</li>`;

        pager.innerHTML = html;
    }

    function updateTotal() {
        if (totalEl) totalEl.textContent = `${state.total} registro(s)`;
    }

    async function listar(page = 1) {
        state.page = page;
        const q = encodeURIComponent(state.q || '');
        setLoading(true);
        const seq = ++__SEQ__;

        try {
            const res = await fetch(
                api(`action=list&q=${q}&page=${state.page}&per=${state.per}`)
            );
            const j = await res.json();
            if (seq !== __SEQ__) return;

            const items = j.items || j.data || [];
            state.total = j.total || items.length;
            state.page = +j.page || state.page;
            state.per = +j.per || state.per;

            if (!items.length) {
                const colspan = PUEDE_GESTIONAR_PRODUCTOS ? 18 : 17;
                tblBody.innerHTML = `
<tr>
  <td colspan="${colspan}" class="text-center text-muted py-3">
    Sin resultados
  </td>
</tr>`;

                renderPager();
                updateTotal();
                return;
            }

            renderTabla(items);
            renderPager();
            updateTotal();
        } catch (err) {
            if (seq !== __SEQ__) return;
            const colspan = PUEDE_GESTIONAR_PRODUCTOS ? 18 : 17;
            tblBody.innerHTML = `
<tr>
  <td colspan="${colspan}" class="text-center text-danger py-3">
    No se pudo cargar.
  </td>
</tr>`;

            uiToast('No se pudo cargar productos.', 'danger');
        }
    }

    function renderTabla(items) {
        if (!tblBody) return;
        tblBody.innerHTML = '';

        for (const p of items) {
            const tr = document.createElement('tr');
            tr.dataset.id = p.id;
            const activo = String(p.estado || '').toLowerCase() === 'activo';

            let imagenHtml = '';
            if (p.imagen && p.imagen.trim()) {
                const src = escapeHtml(p.imagen);
                const alt = escapeHtml(p.nombre);
                imagenHtml = `<img src="${src}" alt="${alt}" class="producto-img" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'producto-img-placeholder\\'><i class=\\'bi bi-image\\'></i></div>';">`;
            } else {
                imagenHtml =
                    '<div class="producto-img-placeholder"><i class="bi bi-box-seam"></i></div>';
            }

                        let accionesHtml = '';
            if (PUEDE_GESTIONAR_PRODUCTOS) {
                accionesHtml = `
<td class="text-end">
  <div class="btn-group btn-group-sm">
    <button class="btn btn-outline-primary" data-edit="${p.id}" title="Editar">
      <i class="bi bi-pencil-square"></i>
    </button>
    <button class="btn btn-outline-danger" data-del="${p.id}" title="Eliminar">
      <i class="bi bi-trash"></i>
    </button>
    <button class="btn btn-outline-secondary" data-toggle="${p.id}" title="${activo ? 'Inactivar' : 'Activar'}">
      <i class="bi ${activo ? 'bi-toggle-on' : 'bi-toggle-off'}"></i>
    </button>
  </div>
</td>`;
            }


           tr.innerHTML = `
<td>${p.id}</td>
<td class="fw-semibold">${escapeHtml(p.nombre)}</td>
<td>${escapeHtml(p.categoria ?? '')}</td>
<td>${escapeHtml(p.marca ?? '')}</td>
<td>${escapeHtml(p.presentacion ?? '')}</td>
<td class="descripcion">${escapeHtml(p.descripcion ?? '')}</td>
<td>${fmtNumber(p.stock_actual)}</td>
<td>${fmtNumber(p.stock_minimo)}</td>
<td>${escapeHtml(p.lote ?? '')}</td>
<td>${escapeHtml(p.f_vencimiento ?? p.fecha_vencimiento ?? '')}</td>
<td>$${fmtMoney(p.precio_compra)}</td>
<td>$${fmtMoney(p.precio_venta)}</td>
<td>${fmtNumber(p.iva)}%</td>
<td>${escapeHtml(p.codigo_sku ?? p.codigo_barras ?? '')}</td>
<td>${escapeHtml(p.ubicacion ?? '')}</td>
<td>${
        activo
            ? '<span class="badge bg-success-subtle text-success border">Activo</span>'
            : '<span class="badge bg-secondary-subtle text-secondary border">Inactivo</span>'
    }</td>
<td class="text-center">${imagenHtml}</td>
${accionesHtml}
`;

            tblBody.appendChild(tr);
        }
    }

    // =====================================
    // Eventos de lista / filtros
    // =====================================
    pager?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-page]');
        if (!btn) return;
        const to = parseInt(btn.dataset.page, 10);
        const pages = Math.max(
            1,
            Math.ceil((state.total || 0) / (state.per || 10))
        );
        if (to >= 1 && to <= pages && to !== state.page) listar(to);
    });

    perSel?.addEventListener('change', (e) => {
        state.per = parseInt(e.target.value, 10) || 10;
        listar(1);
    });

    btnBuscar?.addEventListener('click', () => {
        state.q = qInput?.value.trim() || '';
        listar(1);
    });

    qInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            state.q = e.target.value.trim();
            listar(1);
        }
    });

    // =====================================
    // Modal crear/editar
    // =====================================
    function ensureHidden() {
        if (!modalEl) return;
        modalEl.classList.remove('show');
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.style.display = 'none';
        document.body.classList.remove('modal-open');
        $$('.modal-backdrop').forEach((b) => b.remove());
    }

    if (modalEl && window.bootstrap) {
        ensureHidden();
        bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static' });

        modalEl.addEventListener('hidden.bs.modal', () => {
            if (!frm) return;

            imagenBase64 = null;
            frm.reset();

            const idInput = frm.querySelector('[name="id"]');
            if (idInput) idInput.value = '';

            $$('.is-valid, .is-invalid').forEach((el) =>
                el.classList.remove('is-valid', 'is-invalid')
            );
            $$('.valid-optional').forEach((el) => el.remove());

            if (previewWrap) previewWrap.style.display = 'none';
            if (imgPreview) imgPreview.src = '';
        });
    }

    function openEditor(title) {
        if (modalTit) modalTit.textContent = title || 'Nuevo producto';
        ensureHidden();
        bsModal?.show();
    }

    function closeEditor() {
        bsModal?.hide();
        ensureHidden();
    }

    // Botón "Nuevo" – modo creación
btnNuevo?.addEventListener('click', () => {
    if (!PUEDE_GESTIONAR_PRODUCTOS) return;  // ← corregir
    if (!frm) return;

    frm.reset();

        const idInput = frm.querySelector('[name="id"]');
        if (idInput) idInput.value = '';

        imagenBase64 = null;

        if (tipoURL) tipoURL.checked = true;
        if (seccionURL) seccionURL.style.display = 'block';
        if (seccionArchivo) seccionArchivo.style.display = 'none';
        if (previewWrap) previewWrap.style.display = 'none';
        if (imgPreview) imgPreview.src = '';

        uiToast('Modo creación', 'info');
        openEditor('Nuevo producto');
    });

    // =====================================
    // Imagen: URL/Archivo + preview
    // =====================================
    tipoURL?.addEventListener('change', () => {
        if (!tipoURL.checked) return;
        seccionURL.style.display = 'block';
        seccionArchivo.style.display = 'none';
        archivoImagen.value = '';
        imagenBase64 = null;

        if (inputImagenURL.value.trim()) {
            imgPreview.src = inputImagenURL.value.trim();
            previewWrap.style.display = 'block';
        } else {
            previewWrap.style.display = 'none';
        }
    });

    tipoArchivo?.addEventListener('change', () => {
        if (!tipoArchivo.checked) return;
        seccionURL.style.display = 'none';
        seccionArchivo.style.display = 'block';
        inputImagenURL.value = '';
        previewWrap.style.display = 'none';
        imagenBase64 = null;
    });

    inputImagenURL?.addEventListener('input', () => {
        const url = inputImagenURL.value.trim();
        if (url && /^(https?:)?\/\//i.test(url)) {
            imgPreview.src = url;
            previewWrap.style.display = 'block';
            imgPreview.onerror = () => {
                previewWrap.style.display = 'none';
                uiToast('No se pudo cargar la imagen (URL).', 'warning');
            };
        } else {
            previewWrap.style.display = 'none';
        }
    });

    archivoImagen?.addEventListener('change', (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            uiToast('La imagen es muy grande (máx 2MB).', 'warning');
            archivoImagen.value = '';
            return;
        }

        if (!file.type.startsWith('image/')) {
            uiToast('Selecciona una imagen válida.', 'warning');
            archivoImagen.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (ev) => {
            imagenBase64 = ev.target.result;
            imgPreview.src = imagenBase64;
            previewWrap.style.display = 'block';
            uiToast('Imagen cargada.', 'success');
        };
        reader.onerror = () => uiToast('Error al leer la imagen.', 'danger');
        reader.readAsDataURL(file);
    });

    btnLimpiarImg?.addEventListener('click', () => {
        inputImagenURL.value = '';
        archivoImagen.value = '';
        imagenBase64 = null;
        previewWrap.style.display = 'none';
        imgPreview.src = '';
    });

    // =====================================
    // Acciones de fila (editar / eliminar / toggle)
    // =====================================
tblBody?.addEventListener('click', async (e) => {
    if (!PUEDE_GESTIONAR_PRODUCTOS) return;  // ← corregir
    const btn = e.target.closest('button');

        const id = btn.dataset.edit || btn.dataset.del || btn.dataset.toggle;
        if (!id) return;

        // Editar
        if (btn.dataset.edit) {
            try {
                const r = await fetch(api(`action=get&id=${id}`));
                const j = await r.json();
                const d = j.data;
                if (!d) return uiToast('Producto no encontrado', 'warning');

                if (frm) {
                    for (const [k, v] of Object.entries(d)) {
                        if (frm[k]) frm[k].value = v ?? '';
                    }
                }

                if (d.imagen && d.imagen.trim()) {
                    if (tipoURL) tipoURL.checked = true;
                    seccionURL.style.display = 'block';
                    seccionArchivo.style.display = 'none';
                    inputImagenURL.value = d.imagen;
                    imgPreview.src = d.imagen;
                    previewWrap.style.display = 'block';
                } else {
                    previewWrap.style.display = 'none';
                    imgPreview.src = '';
                }

                uiToast('Modo edición', 'info');
                openEditor('Editar producto');
            } catch {
                uiToast('Error al cargar el producto', 'danger');
            }
            return;
        }

        // Eliminar
        if (btn.dataset.del) {
            const ok = await uiConfirm({
                title: 'Eliminar producto',
                body: '¿Seguro que deseas eliminar este producto?\nEsta acción no se puede deshacer.',
                confirmText: 'Sí, eliminar',
                variant: 'danger',
            });
            if (!ok) return;

            try {
                const r = await fetch(api('action=delete'), {
                    method: 'POST',
                    body: (() => {
                        const fd = new FormData();
                        fd.append('id', id);
                        return fd;
                    })(),
                });
                const j = await r.json();
                if (!j.ok) throw new Error(j.msg || 'No se pudo eliminar');

                uiToast('Eliminado exitosamente', 'danger');
                listar(state.page);
            } catch (err) {
                uiToast(err.message || 'Error al eliminar', 'danger');
            }
            return;
        }

        // Toggle activar/inactivar
        if (btn.dataset.toggle) {
            // Miramos el ícono dentro del botón para saber el estado actual
            const icon = btn.querySelector('i');
            const isActive = icon && icon.classList.contains('bi-toggle-on');

            const titleMsg =
                isActive ? 'Inactivar producto' : 'Activar producto';
            const bodyMsg = isActive
                ? '¿Seguro que deseas inactivar este producto?'
                : '¿Seguro que deseas activar este producto?';
            const confirmText = isActive ? 'Sí, inactivar' : 'Sí, activar';

            const ok = await uiConfirm({
                title: titleMsg,
                body: bodyMsg,
                confirmText,
                variant: 'success',
            });
            if (!ok) return;

            try {
                const r = await fetch(api('action=toggle'), {
                    method: 'POST',
                    body: (() => {
                        const fd = new FormData();
                        fd.append('id', id);
                        return fd;
                    })(),
                });
                const j = await r.json();
                if (!j.ok) throw new Error(j.msg || 'No se pudo cambiar el estado');

                uiToast(j.msg || 'Estado actualizado', 'info');
                listar(state.page);
            } catch (err) {
                uiToast(err.message || 'Error al cambiar estado', 'danger');
            }
            return;
        }

    });

    // =====================================
    // Fecha de vencimiento: hoy o futura (OBLIGATORIA)
    // =====================================
    function esFechaFuturaOHoy(valor) {
        // obligatorio: si no hay valor, es inválido
        if (!valor) return false;

        let d, m, y;

        // Soporta dd/mm/aaaa y yyyy-mm-dd
        if (valor.includes('/')) {
            const partes = valor.split('/');
            if (partes.length !== 3) return false;
            [d, m, y] = partes.map((p) => parseInt(p, 10));
        } else {
            const partes = valor.split('-');
            if (partes.length !== 3) return false;
            [y, m, d] = partes.map((p) => parseInt(p, 10));
        }

        const fecha = new Date(y, m - 1, d);
        if (isNaN(fecha.getTime())) return false;

        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        fecha.setHours(0, 0, 0, 0);

        return fecha >= hoy;
    }

    function limitFechaVencimiento() {
        const inp = document.getElementById('f_vencimiento');
        if (!inp) return;

        const hoy = new Date();
        const y = hoy.getFullYear();
        const m = String(hoy.getMonth() + 1).padStart(2, '0');
        const d = String(hoy.getDate()).padStart(2, '0');
        const iso = `${y}-${m}-${d}`;

        if (inp.type === 'date') {
            inp.setAttribute('min', iso);
        }

        inp.addEventListener('change', () => {
            const v = inp.value.trim();

            // obligatorio
            if (!v) {
                setValid(inp, false);
                uiToast('La fecha de vencimiento es obligatoria.', 'warning');
                return;
            }

            const ok = esFechaFuturaOHoy(v);
            setValid(inp, ok);

            if (!ok) {
                uiToast(
                    'La fecha de vencimiento debe ser hoy o una fecha futura.',
                    'warning'
                );
                inp.value = '';
            }
        });
    }

    // =====================================
    // Validación lógica antes de enviar
    // =====================================
    function validatePlain(plain) {
        if (!plain.nombre || !plain.nombre.trim())
            return 'Nombre es requerido.';
        if (!plain.marca || !plain.marca.trim())
            return 'Marca es requerida.';
        if (!plain.categoria || !plain.categoria.trim())
            return 'Categoría es requerida.';

        // reglas nuevas
        if (!soloLetrasEspacios(plain.marca))
            return 'La marca solo debe contener letras y espacios.';
        if (!soloLetrasEspacios(plain.categoria))
            return 'La categoría solo debe contener letras y espacios.';
        if (plain.descripcion && plain.descripcion.trim() && !soloLetrasEspacios(plain.descripcion))
            return 'La descripción solo debe contener letras y espacios.';

        const codBar = getCodigoBarrasFromPlain(plain);
        if (codBar && !soloNumeros(codBar))
            return 'El código de barras solo debe contener números.';

        if (
            plain.precio_compra !== undefined &&
            +plain.precio_compra <= 0
        )
            return 'Precio Compra debe ser mayor a 0';

        if (
            plain.precio_venta !== undefined &&
            +plain.precio_venta <= 0
        )
            return 'Precio Venta debe ser mayor a 0';

        if (
            plain.stock_actual !== undefined &&
            +plain.stock_actual <= 0
        )
            return 'Se requiere un Stock actual';

        // Fecha de vencimiento obligatoria
        if (!plain.f_vencimiento || !plain.f_vencimiento.trim())
            return 'Fecha de vencimiento es requerida.';

        if (!esFechaFuturaOHoy(plain.f_vencimiento))
            return 'La fecha de vencimiento debe ser hoy o una fecha futura.';

        return '';
    }

    // =====================================
    // Guardar (crear / actualizar)
    // =====================================
    frm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        ensureFieldStyles();
        if (!frm) return;

        const fd = new FormData(frm);

        // ID: decidir si es create o update
        let id = fd.get('id');
        id = (id ?? '').toString().trim();
        const isUpdate = id !== '' && id !== '0';

        if (!isUpdate) {
            fd.delete('id');
        }

        // Imagen archivo: priorizar file sobre URL
        const file = archivoImagen?.files?.[0];
        if (file) {
            fd.set('imagen_archivo', file);
            fd.delete('imagen');
        }

        const plain = Object.fromEntries(fd.entries());

        // Validación básica
        const err = validatePlain(plain);

        // Marcar campos clave (con nuevas reglas)
        setValid(
            $('#nombre'),
            !!(plain.nombre && plain.nombre.trim())
        );

        const catOK =
            !!(plain.categoria && plain.categoria.trim()) &&
            soloLetrasEspacios(plain.categoria);
        setValid($('#categoria'), catOK);

        const marcaOK =
            !!(plain.marca && plain.marca.trim()) &&
            soloLetrasEspacios(plain.marca);
        setValid($('#marca'), marcaOK);

        setValid(
            $('#precio_compra'),
            !(
                plain.precio_compra === '' ||
                +plain.precio_compra <= 0
            )
        );

        setValid(
            $('#precio_venta'),
            !(
                plain.precio_venta === '' ||
                +plain.precio_venta <= 0
            )
        );

        setValid(
            $('#stock_actual'),
            !(
                plain.stock_actual === '' ||
                +plain.stock_actual <= 0
            )
        );

        // Descripción: opcional pero solo letras/espacios si se llena
        const descOK =
            !plain.descripcion ||
            soloLetrasEspacios(plain.descripcion);
        setValid(
            $('#descripcion'),
            descOK,
            plain.descripcion ? '' : 'Opcional'
        );

        // Código de barras: opcional pero solo números
        const codDom =
            document.getElementById('codigo_barras') ||
            document.getElementById('codigo_sku');
        const codBar = getCodigoBarrasFromPlain(plain);
        const codOK = !codBar || soloNumeros(codBar);
        setValid(
            codDom,
            codOK,
            codBar ? '' : 'Opcional'
        );

        // Fecha vencimiento (OBLIGATORIA y hoy/futuro)
        const fvInput = document.getElementById('f_vencimiento');
        const fvOk =
            !!plain.f_vencimiento &&
            esFechaFuturaOHoy(plain.f_vencimiento);
        setValid(fvInput, fvOk);

        // Campos opcionales restantes
        setValid($('#stock_minimo'), true, 'Opcional');
        setValid($('#presentacion'), true, 'Opcional');
        setValid($('#lote'), true, 'Opcional');
        setValid($('#ubicacion'), true, 'Opcional');
        setValid($('#iva'), true, 'Opcional');

        if (err) {
            uiToast(`⚠ ${err}`, 'warning');
            return;
        }

        const ok = await uiConfirm({
            title: isUpdate
                ? 'Confirmar actualización'
                : 'Confirmar creación',
            body: isUpdate
                ? '¿Guardar cambios del producto?'
                : '¿Crear nuevo producto?',
            confirmText: isUpdate ? 'Sí, actualizar' : 'Sí, crear',
            variant: 'success',
        });
        if (!ok) return;

        const btnSubmit = frm.querySelector('button[type="submit"]');
        const prevHtml = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Guardando…';

        try {
            const action = isUpdate ? 'update' : 'create';
            const r = await fetch(api(`action=${action}`), {
                method: 'POST',
                body: fd,
            });
            const j = await r.json();
            if (!j.ok) throw new Error(j.msg || 'Error al guardar');

            closeEditor();

            if (isUpdate) {
                uiToast('Actualizado exitosamente', 'warning');
                listar(state.page);
            } else {
                uiToast('Creado exitosamente', 'success');
                listar(1);
            }
        } catch (err2) {
            uiToast(err2.message || 'Error al guardar', 'danger');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = prevHtml;
            imagenBase64 = null;
        }
    });

    // =====================================
    // Init
    // =====================================
    function boot() {
        ensureToastCSS();
        ensureConfirmModal();
        ensureFieldStyles();
        ensureHidden();
        limitFechaVencimiento();
        listar(1);
    }

    if (document.readyState === 'loading')
        document.addEventListener('DOMContentLoaded', boot);
    else boot();

    // Safari bfcache
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) boot();
    });
})();
