// assets/js/admin_devoluciones.js
(function () {
    if (window.__DEVOL_JS_BOUND__) return;
    window.__DEVOL_JS_BOUND__ = true;

    'use strict';

    // ==============================
    // Base y endpoint
    // ==============================
    const base = location.pathname.replace(/\/public\/?$/, '') + '/public';
    const api = (params = '') => `${base}/?r=admin_devoluciones_api&${params}`;

    // ==============================
    // Estado
    // ==============================
    const state = { page: 1, per: 10, total: 0, q: '' };
    let __SEQ__ = 0;
    let currentRow = null; // datos originales al editar

    // ==============================
    // Helpers DOM
    // ==============================
    const $ = (s) => document.querySelector(s);
    const $$ = (s) => Array.from(document.querySelectorAll(s));

    const tblBody = $('#tblDevoluciones tbody');
    const pager = $('#paginadorDevoluciones');
    const perSel = $('#perPageDevoluciones');
    const totalEl = $('#totalDevoluciones');
    const qInput = $('#qDevoluciones');
    const btnBuscar = $('#btnBuscarDevoluciones');
    const btnNuevo = $('#btnNuevaDevolucion');

    const modalEl = $('#modalDevolucion');
    const frm = $('#frmDevolucion');
    const modalTit = $('#modalTitleDevolucion');
    let bsModal = null;

    // Campos formulario
    const idInput           = $('#id');
    const selProveedor      = $('#proveedor_id');
    const selProducto       = $('#producto_id');
    const inpCantidad       = $('#cantidad');
    const inpNumeroOrden    = $('#numero_orden');
    const txtMotivo         = $('#motivo_devolucion');
    const inpFechaCompra    = $('#fecha_compra');
    const inpFechaDev       = $('#fecha_devolucion');
    const selEstado         = $('#estado');
    const txtObs            = $('#observaciones');
    const inpOrigenHidden   = $('#origen');
    const inpOrigenLabel    = $('#origen_label');

    const grpCliente        = $('#grpCliente');
    const grpProveedor      = $('#grpProveedor');
    const inpClienteNombre  = $('#cliente_nombre');

    // ==============================
    // Toasts bonitos
    // ==============================
    function ensureToastCSS() {
        if ($('#_devol_toast_css')) return;
        const css = document.createElement('style');
        css.id = '_devol_toast_css';
        css.textContent = `
.nvtoast-host{position:fixed;right:16px;bottom:16px;display:flex;flex-direction:column;gap:10px;z-index:1090;pointer-events:none}
.nvtoast{pointer-events:auto;min-width:260px;max-width:420px;padding:10px 12px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.18);display:flex;align-items:center;gap:10px;color:#fff;opacity:.98;transform:translateY(20px);animation:_tSlide .2s ease-out forwards;border:1px solid transparent;font-size:.9rem}
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

    // ==============================
    // Confirm modal
    // ==============================
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

    // ==============================
    // Utils
    // ==============================
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

    function origenLabel(origen) {
        const o = (origen || '').toLowerCase();
        if (o === 'cliente') return 'Cliente';
        // cualquier cosa que NO sea cliente la tratamos como interno
        return 'Interno';
    }


    function setLoading(on) {
        if (!tblBody) return;
        if (on) {
            tblBody.innerHTML = `
<tr>
  <td colspan="12" class="text-center py-3">
    <div class="spinner-border spinner-border-sm me-2"></div>
    Cargando…
  </td>
</tr>`;
        }
    }

    function updateTotal() {
        if (totalEl) totalEl.textContent = `${state.total} registro(s)`;
    }

    // ==============================
    // Paginación
    // ==============================
    function renderPager() {
        if (!pager) return;
        const pages = Math.max(1, Math.ceil((state.total || 0) / (state.per || 10)));
        let html = '';

        const prevDis = state.page <= 1 ? ' disabled' : '';
        html += `
<li class="page-item${prevDis}">
  <button class="page-link" data-page="${state.page - 1}" aria-label="Anterior">&laquo;</button>
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
  <button class="page-link" data-page="${state.page + 1}" aria-label="Siguiente">&raquo;</button>
</li>`;

        pager.innerHTML = html;
    }

    pager?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-page]');
        if (!btn) return;
        const to = parseInt(btn.dataset.page, 10);
        const pages = Math.max(1, Math.ceil((state.total || 0) / (state.per || 10)));
        if (to >= 1 && to <= pages && to !== state.page) listar(to);
    });

    perSel?.addEventListener('change', (e) => {
        state.per = parseInt(e.target.value, 10) || 10;
        listar(1);
    });

    // ==============================
    // Listar
    // ==============================
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

            const items = j.data || j.items || [];
            state.total = j.total || items.length;
            state.page = +j.page || state.page;
            state.per = +j.per || state.per;

            if (!items.length) {
                tblBody.innerHTML = `
<tr>
  <td colspan="12" class="text-center text-muted py-3">
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
            tblBody.innerHTML = `
<tr>
  <td colspan="12" class="text-center text-danger py-3">
    No se pudo cargar.
  </td>
</tr>`;
            uiToast('No se pudo cargar devoluciones.', 'danger');
        }
    }

    function renderTabla(items) {
    const tbody = document.querySelector('#tblDevoluciones tbody');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!items || !items.length) {
        tbody.innerHTML = `
        <tr>
          <td colspan="12" class="text-center text-muted py-3">
            Sin resultados
          </td>
        </tr>`;
        return;
    }

    for (const d of items) {
        const tr = document.createElement('tr');
        tr.dataset.id = d.id;

        const nombrePC = (d.origen === 'cliente')
            ? `${(d.cliente_nombres || '').trim()} ${(d.cliente_apellidos || '').trim()}`.trim()
            : (d.proveedor_empresa || '');

        const correoTel = (d.origen === 'cliente')
            ? (d.cliente_correo || d.cliente_telefono || '')
            : (d.proveedor_correo || d.proveedor_telefono || '');

        const estadoBadge = (d.estado || '').toLowerCase();
        const estadoHtml =
            estadoBadge === 'aprobada'
                ? '<span class="badge bg-success-subtle text-success border">Aprobada</span>'
                : estadoBadge === 'rechazada'
                ? '<span class="badge bg-danger-subtle text-danger border">Rechazada</span>'
                : '<span class="badge bg-secondary-subtle text-secondary border">Pendiente</span>';

        tr.innerHTML = `
            <td>${d.id}</td>
            <td>${escapeHtml(nombrePC || '')}</td>
            <td>${escapeHtml(correoTel || '')}</td>
            <td>${escapeHtml(d.producto_nombre || '')}</td>
            <td>${d.cantidad ?? ''}</td>
            <td>${escapeHtml(d.numero_orden || '')}</td>
            <td>${escapeHtml(d.motivo_devolucion || '')}</td>
            <td>${escapeHtml(d.fecha_compra || '')}</td>
            <td>${escapeHtml(d.fecha_devolucion || '')}</td>
            <td>${estadoHtml}</td>
            <td>${escapeHtml(origenLabel(d.origen))}</td>
            <td class="text-end">
              <div class="btn-group btn-group-sm">
                <!-- 👇 OJO: ahora data-editar y data-eliminar -->
                <button class="btn btn-outline-primary" data-editar="${d.id}" title="Editar">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-outline-danger" data-eliminar="${d.id}" title="Eliminar">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
        `;
        tbody.appendChild(tr);
    }
}



    // ==============================
    // Buscar
    // ==============================
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

    // ==============================
    // Modal: init
    // ==============================
    function ensureHiddenModal() {
        if (!modalEl) return;
        modalEl.classList.remove('show');
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.style.display = 'none';
        document.body.classList.remove('modal-open');
        $$('.modal-backdrop').forEach((b) => b.remove());
    }

    if (modalEl && window.bootstrap) {
        ensureHiddenModal();
        bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static' });

        modalEl.addEventListener('hidden.bs.modal', () => {
            if (!frm) return;
            frm.reset();
            if (idInput) idInput.value = '0';
            currentRow = null;
            if (grpCliente) grpCliente.classList.add('d-none');
            if (grpProveedor) grpProveedor.classList.remove('d-none');
            if (inpClienteNombre) inpClienteNombre.value = '';
            if (inpOrigenHidden) inpOrigenHidden.value = 'proveedor';
            if (inpOrigenLabel) inpOrigenLabel.value = 'Interno';
        });
    }

    function openModal(title) {
        if (modalTit) modalTit.textContent = title || 'Nueva devolución';
        ensureHiddenModal();
        bsModal?.show();
    }

    function closeModal() {
        bsModal?.hide();
        ensureHiddenModal();
    }

    // ==============================
    // Modo crear / editar
    // ==============================
    function setFormMode(mode, origenRaw) {
    const isEdit  = mode === 'edit';
    const origen  = (origenRaw || (currentRow && currentRow.origen) || 'proveedor').toLowerCase();

    const lock = (el, lockIt) => {
        if (!el) return;

        if (lockIt) {
            el.setAttribute('data-locked', '1');
            el.classList.add('bg-body-secondary', 'border-0');

            // 🔒 realmente bloquear
            el.disabled = true;
            if ('readOnly' in el) el.readOnly = true;
        } else {
            el.removeAttribute('data-locked');
            el.classList.remove('bg-body-secondary', 'border-0');

            // 🔓 volver a habilitar (solo se usa en modo crear)
            el.disabled = false;
            if ('readOnly' in el) el.readOnly = false;
        }
    };

    // En editar, se bloquean estos campos:
    lock(selProveedor,   isEdit);
    lock(selProducto,    isEdit);   // 👈 producto queda deshabilitado
    lock(inpCantidad,    isEdit);
    lock(inpNumeroOrden, isEdit);
    lock(txtMotivo,      isEdit);
    lock(inpFechaCompra, isEdit);

    // Origen solo lectura en edición
    if (inpOrigenLabel) {
        if (isEdit) {
            inpOrigenLabel.classList.add('bg-body-secondary', 'border-0');
            inpOrigenLabel.disabled = true;
        } else {
            inpOrigenLabel.classList.remove('bg-body-secondary', 'border-0');
            inpOrigenLabel.disabled = false;
        }
    }

    // Campos siempre editables
    if (inpFechaDev) { inpFechaDev.disabled = false; inpFechaDev.readOnly = false; }
    if (selEstado)   { selEstado.disabled   = false; selEstado.readOnly   = false; }
    if (txtObs)      { txtObs.disabled      = false; txtObs.readOnly      = false; }

    // Mostrar/ocultar Cliente vs Proveedor según origen
    if (origen === 'cliente') {
        grpCliente?.classList.remove('d-none');
        grpProveedor?.classList.add('d-none');
        if (inpOrigenHidden) inpOrigenHidden.value = 'cliente';
        if (inpOrigenLabel)  inpOrigenLabel.value  = 'Cliente';
    } else {
        grpProveedor?.classList.remove('d-none');
        grpCliente?.classList.add('d-none');
        if (inpOrigenHidden) inpOrigenHidden.value = 'interno';
        if (inpOrigenLabel)  inpOrigenLabel.value  = 'Interno';
    }
}


    // ==============================
    // Botón "Nueva devolución"
    // ==============================
btnNuevo?.addEventListener('click', () => {
    if (!frm) return;
    frm.reset();
    if (idInput) idInput.value = '0';
    if (inpClienteNombre) inpClienteNombre.value = '';
    currentRow = null;

    // 🔹 Siempre devoluciones internas al crear
    if (inpOrigenHidden) inpOrigenHidden.value = 'interno';
    if (inpOrigenLabel)  inpOrigenLabel.value  = 'Interno';

    // 🔹 Dejar productos vacíos hasta que elijan proveedor
    if (selProducto) {
        selProducto.innerHTML = '<option value="">-- Selecciona producto --</option>';
    }

    setFormMode('create', 'interno');
    uiToast('Modo creación de devolución', 'info');
    openModal('Nueva devolución');
});


    // ==============================
    // Click en tabla (editar / eliminar)
    // ==============================
    tblBody?.addEventListener('click', async (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;
        const id = +btn.dataset.editar || +btn.dataset.eliminar;
        if (!id) return;

        // EDITAR
        if (btn.dataset.editar) {
            try {
                const res = await fetch(api(`action=get&id=${id}`));
                const j = await res.json();
                if (!j || !j.data) throw new Error('No se encontró la devolución');

                currentRow = j.data;

                const origen = (currentRow.origen || 'proveedor').toLowerCase();

                if (idInput) idInput.value = currentRow.id || id;

                if (selProveedor) {
                    selProveedor.value = currentRow.proveedor_id
                        ? String(currentRow.proveedor_id)
                        : '';
                }

                if (inpClienteNombre) {
                    const nombreCli = `${currentRow.cliente_nombres || ''} ${currentRow.cliente_apellidos || ''}`.trim();
                    inpClienteNombre.value = nombreCli || '';
                }

               // Productos según proveedor (solo los del proveedor de la devolución)
                if (selProveedor && currentRow.proveedor_id) {
                    await cargarProductosPorProveedor(currentRow.proveedor_id, currentRow.producto_id);
                } else if (selProducto && currentRow.producto_id) {
                    // Por si algún registro viejo no tiene proveedor_id
                    selProducto.value = String(currentRow.producto_id);
                }


                if (inpCantidad) inpCantidad.value = currentRow.cantidad || 1;
                if (inpNumeroOrden) inpNumeroOrden.value = currentRow.numero_orden || '';
                if (txtMotivo) txtMotivo.value = currentRow.motivo_devolucion || '';
                if (inpFechaCompra) inpFechaCompra.value = currentRow.fecha_compra || '';
                if (inpFechaDev) inpFechaDev.value = currentRow.fecha_devolucion || '';
                if (selEstado) selEstado.value = currentRow.estado || 'pendiente';
                if (txtObs) txtObs.value = currentRow.observaciones || '';

                if (inpOrigenHidden) inpOrigenHidden.value = origen;
                if (inpOrigenLabel) inpOrigenLabel.value = origenLabel(origen);

                setFormMode('edit', origen);
                openModal('Editar devolución');
            } catch (err) {
                uiToast(err.message || 'Error al cargar la devolución', 'danger');
            }
            return;
        }

        // ELIMINAR
        if (btn.dataset.eliminar) {
            const ok = await uiConfirm({
                title: 'Eliminar devolución',
                body: '¿Seguro que deseas eliminar esta devolución?',
                confirmText: 'Sí, eliminar',
                variant: 'danger',
            });
            if (!ok) return;

            try {
                const fd = new FormData();
                fd.append('id', String(id));
                const res = await fetch(api('action=delete'), {
                    method: 'POST',
                    body: fd,
                });
                const j = await res.json();
                if (!j.ok) throw new Error(j.msg || 'No se pudo eliminar');

                uiToast('Devolución eliminada', 'success');
                listar(state.page);
            } catch (err) {
                uiToast(err.message || 'Error al eliminar', 'danger');
            }
        }
    });

    // ==============================
    // Fechas: ahora MIN = hoy (no se puede ir hacia atrás)
    // y la fecha de devolución no puede ser menor que la de compra
    // ==============================
    function setupFechas() {
        const hoy = new Date();
        const y = hoy.getFullYear();
        const m = String(hoy.getMonth() + 1).padStart(2, '0');
        const d = String(hoy.getDate()).padStart(2, '0');
        const isoHoy = `${y}-${m}-${d}`;

        if (inpFechaCompra) inpFechaCompra.setAttribute('min', isoHoy);
        if (inpFechaDev) inpFechaDev.setAttribute('min', isoHoy);

        if (inpFechaCompra && inpFechaDev) {
            inpFechaCompra.addEventListener('change', () => {
                const v = inpFechaCompra.value;
                if (v) {
                    // la devolución no puede ser antes de la compra
                    inpFechaDev.setAttribute('min', v);
                    if (inpFechaDev.value && inpFechaDev.value < v) {
                        inpFechaDev.value = v;
                    }
                } else {
                    inpFechaDev.setAttribute('min', isoHoy);
                }
            });
        }
    }

    async function cargarProductosPorProveedor(proveedorId, selectedId = '') {
    if (!selProducto) return;

    // Reiniciar el select
    selProducto.innerHTML = '<option value="">-- Selecciona producto --</option>';

    if (!proveedorId) return;

    try {
        const res = await fetch(
            api(`action=productos_proveedor&proveedor_id=${encodeURIComponent(proveedorId)}`)
        );
        const j = await res.json();
        const items = j.data || [];

        for (const p of items) {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.nombre;
            if (selectedId && String(selectedId) === String(p.id)) {
                opt.selected = true;
            }
            selProducto.appendChild(opt);
        }
    } catch (err) {
        uiToast('No se pudieron cargar los productos del proveedor.', 'danger');
    }
}


    // ==============================
    // Guardar (crear / actualizar)
    // ==============================
    frm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!frm) return;

        const idRaw = (idInput?.value || '').trim();
        const isUpdate = idRaw !== '' && idRaw !== '0';

      const buildFdCreate = () => {
        const fd = new FormData(frm);

        // Siempre devoluciones internas cuando se crean
        fd.set('origen', 'interno');

        // cliente_id vacío porque es contra proveedor
        if (!fd.get('cliente_id')) fd.set('cliente_id', '');

        return fd;
    };


        const buildFdUpdate = () => {
            const fd = new FormData();
            const row = currentRow || {};

            fd.append('id', idRaw || String(row.id || ''));

            fd.append(
                'cliente_id',
                row.cliente_id != null ? String(row.cliente_id) : ''
            );
            fd.append(
                'proveedor_id',
                selProveedor?.value || row.proveedor_id || ''
            );
            fd.append(
                'producto_id',
                selProducto?.value || row.producto_id || ''
            );
            fd.append(
                'cantidad',
                inpCantidad?.value || row.cantidad || '1'
            );
            fd.append(
                'numero_orden',
                inpNumeroOrden?.value || row.numero_orden || ''
            );
            fd.append(
                'motivo_devolucion',
                txtMotivo?.value || row.motivo_devolucion || ''
            );
            fd.append(
                'fecha_compra',
                inpFechaCompra?.value || row.fecha_compra || ''
            );
            fd.append(
                'fecha_devolucion',
                inpFechaDev?.value || row.fecha_devolucion || ''
            );
            fd.append(
                'estado',
                selEstado?.value || row.estado || 'pendiente'
            );
            fd.append(
                'observaciones',
                txtObs?.value || row.observaciones || ''
            );
            fd.append(
                'origen',
                inpOrigenHidden?.value || row.origen || 'proveedor'
            );

            return fd;
        };

        const fd = isUpdate ? buildFdUpdate() : buildFdCreate();

        const ok = await uiConfirm({
            title: isUpdate ? 'Confirmar actualización' : 'Confirmar creación',
            body: isUpdate
                ? '¿Guardar cambios de la devolución?'
                : '¿Registrar nueva devolución?',
            confirmText: isUpdate ? 'Sí, actualizar' : 'Sí, crear',
            variant: 'success',
        });
        if (!ok) return;

        const btnSubmit = frm.querySelector('button[type="submit"]');
        const prevHtml = btnSubmit ? btnSubmit.innerHTML : '';
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>Guardando…';
        }

        try {
            const action = isUpdate ? 'update' : 'create';
            const res = await fetch(api(`action=${action}`), {
                method: 'POST',
                body: fd,
            });
            const j = await res.json();
            if (!j.ok) throw new Error(j.msg || 'Error al guardar');

            closeModal();

            if (isUpdate) {
                uiToast('Devolución actualizada', 'success');
                listar(state.page);
            } else {
                uiToast('Devolución registrada', 'success');
                listar(1);
            }
        } catch (err) {
            uiToast(err.message || 'Error al guardar', 'danger');
        } finally {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = prevHtml;
            }
        }
    });

    // ==============================
    // Init
    // ==============================
    function boot() {
        ensureToastCSS();
        ensureConfirmModal();
        setupFechas();
        listar(1);
    }

    if (document.readyState === 'loading')
        document.addEventListener('DOMContentLoaded', boot);
    else boot();
const inputNumOrden = document.getElementById('numero_orden');

if (inputNumOrden) {
    inputNumOrden.addEventListener('input', (e) => {

        // Elimina todo lo que NO sea un dígito
        const limpio = e.target.value.replace(/[^0-9]/g, '');

        // Si hubo cambios, restaura el caret correctamente
        if (limpio !== e.target.value) {
            const pos = e.target.selectionStart - (e.target.value.length - limpio.length);
            e.target.value = limpio;
            e.target.setSelectionRange(pos, pos);
        }
    });
}

    window.addEventListener('pageshow', (e) => {
        if (e.persisted) boot();
    });

    selProveedor?.addEventListener('change', (e) => {
    const provId = e.target.value;
    cargarProductosPorProveedor(provId);
});


    
})();
