// admin_ventas.js
(function () {
  if (window.__VENTAS_JS_BOUND__) return;
  window.__VENTAS_JS_BOUND__ = true;

  'use strict';

  // ===== Base y endpoints =====
  const base = location.pathname.replace(/\/public\/?$/, '') + '/public';
  const api  = (params = '') => `${base}/?r=admin_ventas_api&${params}`;

  // ===== Estado =====
  const state = { page: 1, per: 10, total: 0, q: '' };
  let __LIST_REQ_SEQ__ = 0;

  // ===== Helpers DOM =====
  const $  = (s) => document.querySelector(s);
  const $$ = (s) => Array.from(document.querySelectorAll(s));
  const tbl = $('#tblVentas tbody');

  // =========================
  // Toast + Confirm
  // =========================
  function ensureToastCSS() {
    if ($('#_ventas_toast_css')) return;
    const css = document.createElement('style');
    css.id = '_ventas_toast_css';
    css.textContent = `
    .toast-host{position:fixed;right:16px;bottom:16px;display:flex;flex-direction:column;gap:10px;z-index:1080;pointer-events:none}
    .toast{pointer-events:auto;min-width:280px;max-width:420px;padding:10px 12px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.18);background:#111;color:#fff;display:flex;align-items:center;gap:10px;opacity:.98;transform:translateY(20px);animation:_toastSlideIn .2s ease-out forwards;border:1px solid transparent}
    .toast .btn-close{margin-left:auto;filter:invert(1);opacity:.8}
    .toast-success{background:#0f5132;color:#d1f7e5;border-color:#0f5132}
    .toast-danger{background:#842029;color:#ffd7db;border-color:#842029}
    .toast-warning{background:#664d03;color:#fff3cd;border-color:#664d03}
    .toast-info{background:#0b5ed7;color:#dbe8ff;border-color:#0b5ed7}
    .toast .dot{width:8px;height:8px;border-radius:50%;background:currentColor;opacity:.8}
    @keyframes _toastSlideIn{to{transform:translateY(0)}}
    .flash-success{animation:_flashGreen .9s ease-in-out}
    .flash-danger{animation:_flashRed .9s ease-in-out}
    @keyframes _flashGreen{0%,100%{background-color:transparent}30%{background-color:#e8f8f0}}
    @keyframes _flashRed{0%,100%{background-color:transparent}30%{background-color:#fdecef}}
    `;
    document.head.appendChild(css);
  }
  function ensureToastHost() {
    let host = $('#toastHost');
    if (!host) {
      host = document.createElement('div');
      host.id = 'toastHost';
      host.className = 'toast-host';
      document.body.appendChild(host);
    }
    return host;
  }
  function uiToast(msg, variant = 'info', ms = 3500) {
    ensureToastCSS();
    const host = ensureToastHost();
    const t = document.createElement('div');
    t.className = `toast toast-${variant}`;
    t.innerHTML = `
      <div class="dot"></div>
      <div class="toast-msg">${escapeHtml(String(msg))}</div>
      <button class="btn-close" aria-label="Cerrar"></button>
    `;
    host.appendChild(t);
    const close = () => t.remove();
    t.querySelector('.btn-close')?.addEventListener('click', close);
    const timer = setTimeout(close, ms);
    t.addEventListener('mouseenter', () => clearTimeout(timer), { once:true });
  }

  function ensureConfirmModal(){
    if ($('#confirmModal')) return;
    const wrap = document.createElement('div');
    wrap.innerHTML = `
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmTitle">Confirmar</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body" id="confirmBody">¿Seguro?</div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success" id="btnOkConfirm">Sí, continuar</button>
          </div>
        </div>
      </div>
    </div>`;
    document.body.appendChild(wrap.firstElementChild);
  }
  function uiConfirm({title='Confirmar acción', body='¿Seguro?', confirmText='Sí, continuar', variant='success'} = {}) {
    const modalEl = $('#confirmModal');
    if (!modalEl || !window.bootstrap) return Promise.resolve(confirm(body));
    $('#confirmTitle').textContent = title;
    $('#confirmBody').innerHTML = escapeHtml(String(body)).replace(/\n/g,'<br>');
    const okBtn = $('#btnOkConfirm');
    okBtn.className = 'btn ' + (variant === 'danger' ? 'btn-outline-danger'
                          : variant === 'warning' ? 'btn-outline-secondary'
                          : 'btn-success');
    okBtn.textContent = confirmText;

    return new Promise(resolve => {
      const bs = new bootstrap.Modal(modalEl, { backdrop: 'static' });
      const onOk = () => { cleanup(); bs.hide(); resolve(true); };
      const onHide = () => { cleanup(); resolve(false); };
      const cleanup = () => {
        okBtn.removeEventListener('click', onOk);
        modalEl.removeEventListener('hidden.bs.modal', onHide);
      };
      okBtn.addEventListener('click', onOk);
      modalEl.addEventListener('hidden.bs.modal', onHide, { once:true });
      bs.show();
    });
  }

  // =========================
  // Utils
  // =========================
  function escapeHtml(s) {
    return (s ?? '').toString().replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[m]));
  }
  function formData(obj){
    const fd = new FormData();
    Object.entries(obj).forEach(([k,v]) => fd.append(k,v));
    return fd;
  }
  async function resToJsonSafe(res){
    try { return await res.json(); }
    catch { return { ok:false, msg:'Respuesta inválida del servidor' }; }
  }

  // =========================
  // Listar + paginación
  // =========================
  function setLoading(on) {
    if (!tbl) return;
    if (on) {
      tbl.innerHTML = `<tr><td colspan="10" class="py-4 text-center">
        <div class="spinner-border spinner-border-sm me-2"></div> Cargando…
      </td></tr>`;
    }
  }

  function renderPager() {
    const ul = $('#paginadorVentas'); if (!ul) return;
    const pages = Math.max(1, Math.ceil((state.total || 0) / (state.per || 10)));
    let html = '';

    const prevDis = state.page <= 1 ? ' disabled' : '';
    html += `<li class="page-item${prevDis}">
      <button class="page-link" data-page="${state.page - 1}" aria-label="Anterior">&laquo;</button>
    </li>`;

    const win = 2;
    let start = Math.max(1, state.page - win);
    let end   = Math.min(pages, state.page + win);

    if (start > 1) {
      html += `<li class="page-item"><button class="page-link" data-page="1">1</button></li>`;
      if (start > 2) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
    }
    for (let p = start; p <= end; p++) {
      html += `<li class="page-item ${p === state.page ? 'active' : ''}">
        <button class="page-link" data-page="${p}">${p}</button>
      </li>`;
    }
    if (end < pages) {
      if (end < pages - 1) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
      html += `<li class="page-item"><button class="page-link" data-page="${pages}">${pages}</button></li>`;
    }

    const nextDis = state.page >= pages ? ' disabled' : '';
    html += `<li class="page-item${nextDis}">
      <button class="page-link" data-page="${state.page + 1}" aria-label="Siguiente">&raquo;</button>
    </li>`;

    ul.innerHTML = html;
  }

  function updateTotal() {
    const el = $('#totalVentas'); if (el) el.textContent = `${state.total} registro(s)`;
  }

  async function listar(page = state.page) {
    state.page = Math.max(1, page|0);
    const q = encodeURIComponent(state.q || '');
    setLoading(true);

    const mySeq = ++__LIST_REQ_SEQ__;

    try {
      const res = await fetch(api(`action=list&q=${q}&page=${state.page}&per=${state.per}`));
      const j   = await resToJsonSafe(res);

      if (mySeq !== __LIST_REQ_SEQ__) return;

      const items = j.items || [];
      state.total = +j.total || items.length;
      state.page  = +j.page  || state.page;
      state.per   = +j.per   || state.per;

      const pages = Math.max(1, Math.ceil((state.total || 0) / (state.per || 10)));
      if (state.total > 0 && state.page > pages) return listar(pages);

      if (!items.length) {
        tbl.innerHTML = `<tr><td colspan="10" class="text-center text-muted py-3">Sin resultados</td></tr>`;
        renderPager(); updateTotal(); return;
      }

      renderTabla(items);
      renderPager();
      updateTotal();
    } catch (e) {
      if (mySeq !== __LIST_REQ_SEQ__) return;
      tbl.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-3">No se pudo cargar.</td></tr>`;
      uiToast('No se pudieron cargar las ventas.', 'danger');
    }
  }

function renderTabla(items) {
  const tbl = document.querySelector('#tblVentas tbody');
  if (!tbl) return;

  tbl.innerHTML = '';

  if (!items.length) {
    tbl.innerHTML = `
      <tr>
        <td colspan="11" class="text-center text-muted py-3">
          Sin resultados
        </td>
      </tr>`;
    return;
  }

  for (const v of items) {
    const tr = document.createElement('tr');
    tr.dataset.id = v.id;

    tr.innerHTML = `
      <td>${v.id}</td>
      <td>${escapeHtml(v.numero_factura ?? '')}</td>
      <td>${escapeHtml(v.producto_nombre ?? '')}</td>
      <td>${v.cantidad ?? 0}</td>
      <td>${Number(v.precio ?? 0).toFixed(2)}</td>
      <td>${Number(v.total ?? 0).toFixed(2)}</td>
      <td>${escapeHtml(v.cliente_nombre ?? '')}</td>
      <td>${escapeHtml(v.vendedor_nombre ?? '')}</td>
      <td>${escapeHtml(v.metodo_pago ?? '')}</td>
      <td>${escapeHtml(v.fecha ?? '')}</td>
      <td class="text-end">
        <div class="btn-group btn-group-sm" role="group">
          <button class="btn btn-outline-primary" title="Editar" data-editar="${v.id}">
            <i class="bi bi-pencil-square"></i>
          </button>
          <button class="btn btn-outline-danger" title="Eliminar" data-eliminar="${v.id}">
            <i class="bi bi-trash"></i>
          </button>
          <!-- Si luego quieres activar/anular, aquí iría el toggle -->
        </div>
      </td>
    `;
    tbl.appendChild(tr);
  }
}

const tblVentasBody = document.querySelector('#tblVentas tbody');
const apiVentas = (params = '') =>
  `${location.pathname.replace(/\/public\/?$/, '')}/public/?r=admin_ventas_api&${params}`;

function formData(obj) {
  const fd = new FormData();
  Object.entries(obj).forEach(([k, v]) => fd.append(k, v));
  return fd;
}

async function resToJsonSafe(res) {
  try { return await res.json(); }
  catch { return { ok: false, msg: 'Respuesta inválida del servidor' }; }
}

// TODO: ajusta estos helpers si ya los tienes en este mismo JS
function uiToast(msg, variant = 'info', ms = 3500) {
  alert(msg); // si ya tienes toasts bonitos, usa esos
}

// Si ya tienes un uiConfirm (como en clientes), usa ese en vez de confirm()
async function uiConfirmSimple(msg) {
  return confirm(msg);
}

// Rellenar el formulario del modal con los datos de una venta
function fillVentaForm(data = {}) {
  const frm = document.querySelector('#frmVenta');
  if (!frm) return;

  const d = {
    id: 0,
    numero_factura: '',
    producto_id: '',
    cantidad: '',
    precio: '',
    total: '',
    cliente_id: '',
    vendedor_id: '',
    metodo_pago: '',
    fecha: '',
    observaciones: '',
    ...data
  };

  const setVal = (id, val) => {
    const el = frm.querySelector('#' + id);
    if (el) el.value = val ?? '';
  };

  setVal('id', d.id);
  setVal('numero_factura', d.numero_factura);
  setVal('producto_id', d.producto_id);
  setVal('cantidad', d.cantidad);
  setVal('precio', d.precio);
  setVal('total', d.total);
  setVal('cliente_id', d.cliente_id);
  setVal('vendedor_id', d.vendedor_id);
  setVal('metodo_pago', d.metodo_pago);
  setVal('fecha', d.fecha);
  setVal('observaciones', d.observaciones);
}

function openVentaModal(data, title) {
  fillVentaForm(data);
  const modalEl = document.querySelector('#modalVenta');
  const titleEl = modalEl?.querySelector('#modalVentaTitle');
  if (titleEl) titleEl.textContent = title || 'Nueva venta';

  if (window.bootstrap && modalEl) {
    const bs = new bootstrap.Modal(modalEl, { backdrop: 'static' });
    bs.show();
  } else if (modalEl) {
    modalEl.style.display = 'block';
  }
}

// Listener de acciones en la tabla
tblVentasBody?.addEventListener('click', async (e) => {
  const btn = e.target.closest('button');
  if (!btn) return;

  const id = +btn.dataset.editar || +btn.dataset.eliminar;
  if (!id) return;

  // ========= EDITAR =========
  if (btn.dataset.editar) {
    try {
      const res = await fetch(apiVentas(`action=get&id=${id}`));
      const j = await resToJsonSafe(res);
      if (!j || !j.data) throw new Error('No se pudo cargar la venta');

      openVentaModal(j.data, 'Editar venta');
    } catch (err) {
      uiToast(err.message || 'Error al cargar la venta', 'danger');
    }
    return;
  }

  // ========= ELIMINAR =========
  if (btn.dataset.eliminar) {
    const ok = await uiConfirmSimple('¿Seguro que deseas eliminar esta venta?\nEsta acción no se puede deshacer.');
    if (!ok) return;

    try {
      const res = await fetch(apiVentas('action=delete'), {
        method: 'POST',
        body: formData({ id })
      });
      const j = await resToJsonSafe(res);
      if (!j.ok) throw new Error(j.msg || 'No se pudo eliminar');

      uiToast('Venta eliminada correctamente.', 'success');
      // vuelve a listar (ajusta si tienes función listar(page))
      if (typeof listarVentas === 'function') {
        listarVentas(); // o listarVentas(state.page)
      }
    } catch (err) {
      uiToast(err.message || 'Error al eliminar venta', 'danger');
    }
    return;
  }
});


  // =========================
  // Paginador & filtros
  // =========================
  $('#paginadorVentas')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-page]'); if (!btn) return;
    const to = parseInt(btn.dataset.page, 10);
    const pages = Math.max(1, Math.ceil((state.total || 0) / (state.per || 10)));
    if (to >= 1 && to <= pages && to !== state.page) listar(to);
  });

  $('#perPageVentas')?.addEventListener('change', (e) => {
    state.per = parseInt(e.target.value, 10) || 10;
    listar(1);
  });

  $('#btnBuscarVentas')?.addEventListener('click', () => {
    state.q = $('#qVentas')?.value.trim() || '';
    listar(1);
  });

  $('#qVentas')?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      state.q = e.target.value.trim();
      listar(1);
    }
  });

  // =========================
  // Modal Crear/Editar
  // =========================
  const modalEl    = $('#modalVenta');
  const frm        = $('#frmVenta');
  const btnNuevo   = $('#btnNuevaVenta');
  const modalTitle = $('#modalTitleVenta');
  let bsModal      = null;

  function ensureHidden() {
    if (!modalEl) return;
    modalEl.classList.remove('show');
    modalEl.setAttribute('aria-hidden', 'true');
    modalEl.style.display = 'none';
    document.body.classList.remove('modal-open');
    $$('.modal-backdrop').forEach(b => b.remove());
  }
  if (modalEl && window.bootstrap) {
    ensureHidden();
    bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static' });
    modalEl.addEventListener('hidden.bs.modal', () => fillForm({}));
  }

  function fillForm(data = {}) {
    const d = {
      id: 0,
      numero_factura: '',
      producto_id: '',
      cantidad: '',
      precio: '',
      total: '',
      cliente_id: '',
      vendedor_id: '',
      metodo_pago: '',
      fecha: '',
      observaciones: '',
      ...data
    };

    frm?.querySelector('#id')?.setAttribute('value', d.id || 0);

    const set = (id, val) => {
      const el = frm?.querySelector('#' + id);
      if (el) el.value = val ?? '';
    };

    set('numero_factura', d.numero_factura);
    set('producto_id', d.producto_id);
    set('cantidad', d.cantidad);
    set('precio', d.precio);
    set('total', d.total);
    set('cliente_id', d.cliente_id);
    set('vendedor_id', d.vendedor_id);
    set('metodo_pago', d.metodo_pago);
    set('fecha', d.fecha);
    set('observaciones', d.observaciones || '');
  }

  function openEditor(data, title) {
    fillForm(data);
    if (modalTitle) modalTitle.textContent = title || 'Nueva venta';
    ensureHidden();
    if (bsModal) {
      bsModal.show();
      setTimeout(() => frm?.querySelector('#numero_factura')?.focus(), 120);
    } else {
      modalEl.style.display = 'block';
    }
  }
  function closeEditor(){ if (bsModal) bsModal.hide(); ensureHidden(); }

  btnNuevo?.addEventListener('click', () => openEditor({}, 'Nueva venta'));

  // =========================
  // Acciones tabla
  // =========================
  tbl?.addEventListener('click', async (e) => {
    const btn = e.target.closest('button'); if (!btn) return;
    const id  = +btn.dataset.editar || +btn.dataset.eliminar;

    // Editar
    if (btn.dataset.editar) {
      try {
        const r = await fetch(api(`action=get&id=${id}`));
        const j = await resToJsonSafe(r);
        if (!j || !j.data) throw new Error('No se pudo cargar la venta.');
        openEditor(j.data, 'Editar venta');
      } catch (err) {
        uiToast(err.message || 'Error al cargar venta', 'danger');
      }
      return;
    }

    // Eliminar
    if (btn.dataset.eliminar) {
      const ok = await uiConfirm({
        title:'Eliminar venta',
        body:'¿Seguro que deseas eliminar este registro de venta?\nEsta acción no se puede deshacer.',
        confirmText:'Sí, eliminar',
        variant:'danger'
      });
      if (!ok) return;

      try {
        const r = await fetch(api('action=delete'), {
          method:'POST',
          body: formData({ id })
        });
        const j = await resToJsonSafe(r);
        if (!j.ok) throw new Error(j.msg || 'No se pudo eliminar');
        uiToast('Venta eliminada.', 'success');
        listar(state.page);
      } catch (err) {
        uiToast(err.message || 'Error al eliminar', 'danger');
      }
      return;
    }
  });

  // =========================
  // Cálculo total en vivo
  // =========================
  function recalcTotal() {
    const cantEl = frm?.querySelector('#cantidad');
    const precEl = frm?.querySelector('#precio');
    const totEl  = frm?.querySelector('#total');
    if (!cantEl || !precEl || !totEl) return;

    const c = parseFloat(cantEl.value || '0');
    const p = parseFloat(precEl.value || '0');
    const t = (isNaN(c) ? 0 : c) * (isNaN(p) ? 0 : p);
    totEl.value = t > 0 ? t.toFixed(2) : '';
  }

  $('#cantidad')?.addEventListener('input', recalcTotal);
  $('#precio')?.addEventListener('input', recalcTotal);

  // =========================
  // Guardar (crear/editar)
  // =========================
  function validate(plain, isUpdate) {
    if (!plain.numero_factura) return 'Número de factura requerido.';
    if (!plain.producto_id || +plain.producto_id <= 0) return 'Selecciona un producto.';
    if (!plain.cliente_id || +plain.cliente_id <= 0) return 'Selecciona un cliente.';
    if (!plain.vendedor_id || +plain.vendedor_id <= 0) return 'Selecciona un vendedor.';
    if (!(+plain.cantidad > 0)) return 'Cantidad debe ser mayor a 0.';
    if (!(+plain.precio > 0)) return 'Precio debe ser mayor a 0.';
    if (!(+plain.total > 0)) return 'Total debe ser mayor a 0.';
    if (!plain.metodo_pago) return 'Método de pago requerido.';
    if (!plain.fecha) return 'Fecha requerida.';
    return '';
  }

  frm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd   = new FormData(frm);
    const id   = +fd.get('id');
    const isUpdate = id > 0;
    const plain = Object.fromEntries(fd.entries());

    const err = validate(plain, isUpdate);
    if (err) { uiToast(err, 'warning'); return; }

    const msg = isUpdate
      ? `¿Guardar cambios de la venta?\n\nFactura: ${plain.numero_factura || ''}`
      : `¿Registrar nueva venta?\n\nFactura: ${plain.numero_factura || ''}`;

    const ok = await uiConfirm({
      title: isUpdate ? 'Confirmar guardar' : 'Confirmar registro',
      body: msg,
      confirmText: isUpdate ? 'Sí, guardar' : 'Sí, registrar',
      variant: 'success'
    });
    if (!ok) return;

    const btnSubmit = frm.querySelector('button[type="submit"]');
    const prevHtml  = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando…';

    try {
      const action = isUpdate ? 'update' : 'create';
      const r = await fetch(api(`action=${action}`), { method:'POST', body: fd });
      const j = await resToJsonSafe(r);
      if (!j.ok) throw new Error(j.msg || 'Error al guardar');

      closeEditor();
      uiToast(isUpdate ? 'Venta actualizada.' : 'Venta registrada.', 'success');
      listar(state.page);
    } catch (err) {
      uiToast(err.message || 'Error al guardar', 'danger');
    } finally {
      btnSubmit.disabled = false;
      btnSubmit.innerHTML = prevHtml;
    }
  });

  // =========================
  // Init
  // =========================
  function boot(){
    ensureToastCSS();
    ensureConfirmModal();
    ensureHidden();
    listar(1);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
  window.addEventListener('pageshow', (e) => { if (e.persisted) boot(); });

})();
