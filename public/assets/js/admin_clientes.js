// admin_clientes.js
(function () {
  if (window.__CLIENTES_JS_BOUND__) return;
  window.__CLIENTES_JS_BOUND__ = true;

  'use strict';

  // ===== Base y endpoints =====
  const base = location.pathname.replace(/\/public\/?$/, '') + '/public';
  const api  = (params = '') => `${base}/?r=admin_clientes_api&${params}`;

  // ===== Estado =====
  const state = { page: 1, per: 10, total: 0, q: '' };
  let __LIST_REQ_SEQ__ = 0;

  // ===== Helpers DOM =====
  const $  = (s) => document.querySelector(s);
  const $$ = (s) => Array.from(document.querySelectorAll(s));
  const tbl = $('#tblClientes tbody');

  // =========================
  // Toast minimal + Confirm
  // =========================
  function ensureToastCSS() {
    if ($('#_clientes_toast_css')) return;
    const css = document.createElement('style');
    css.id = '_clientes_toast_css';
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
  function uiToast(msg, variant='info', ms=3500) {
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
    t.classList.add('show');
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
  function formData(obj){ const fd = new FormData(); Object.entries(obj).forEach(([k,v])=>fd.append(k,v)); return fd; }
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
      tbl.innerHTML = `<tr><td colspan="8" class="py-4 text-center">
        <div class="spinner-border spinner-border-sm me-2"></div> Cargando…
      </td></tr>`;
    }
  }
  function renderPager() {
    const ul = $('#paginadorClientes'); if (!ul) return;
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
    const el = $('#totalClientes'); if (el) el.textContent = `${state.total} registro(s)`;
  }

  async function listar(page = state.page) {
    state.page = Math.max(1, page|0);
    const q = encodeURIComponent(state.q || '');
    setLoading(true);

    const mySeq = ++__LIST_REQ_SEQ__;

    try {
      const res = await fetch(api(`action=list&q=${q}&page=${state.page}&per=${state.per}`));
      const j   = await resToJsonSafe(res);

      if (mySeq !== __LIST_REQ_SEQ__) return; // última respuesta gana

      const items = j.items || [];
      state.total = +j.total || items.length;
      state.page  = +j.page  || state.page;
      state.per   = +j.per   || state.per;

      const pages = Math.max(1, Math.ceil((state.total || 0) / (state.per || 10)));
      if (state.total > 0 && state.page > pages) return listar(pages);

      if (!items.length) {
        tbl.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-3">Sin resultados</td></tr>`;
        renderPager(); updateTotal(); return;
      }

      renderTabla(items);
      renderPager();
      updateTotal();
    } catch (e) {
      if (mySeq !== __LIST_REQ_SEQ__) return;
      tbl.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-3">No se pudo cargar.</td></tr>`;
      uiToast('No se pudieron cargar los clientes.', 'danger');
    }
  }

  function htmlBadgeEstado(estado) {
    const isActive = String(estado || '').toLowerCase().startsWith('activo');
    return isActive
      ? '<span class="badge bg-success-subtle text-success border">Activo</span>'
      : '<span class="badge bg-secondary-subtle text-secondary border">Inactivo</span>';
  }

  function renderTabla(items) {
    if (!tbl) return;
    tbl.innerHTML = '';
    for (const c of items) {
      const tr = document.createElement('tr');
      tr.dataset.id = c.id_cliente;

      const isActive = String(c.estado || '').toLowerCase().startsWith('activo');

      tr.innerHTML = `
        <td>${c.id_cliente}</td>
        <td>${escapeHtml(c.cedula ?? '')}</td>
        <td class="fw-semibold">${escapeHtml(c.nombres ?? '')} ${escapeHtml(c.apellidos ?? '')}</td>
        <td>${escapeHtml(c.telefono || '-')}</td>
        <td>${escapeHtml(c.correo ?? '')}</td>
        <td data-col="estado">${htmlBadgeEstado(c.estado)}</td>
        <td>${escapeHtml(c.fecha_registro ?? '')}</td>
        <td class="text-end">
          <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" title="Editar" data-editar="${c.id_cliente}">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-outline-danger" title="Eliminar" data-eliminar="${c.id_cliente}">
              <i class="bi bi-trash"></i>
            </button>
            <button class="btn btn-outline-secondary"
                    title="${isActive ? 'Inactivar' : 'Activar'}"
                    data-toggle="${c.id_cliente}"
                    data-active="${isActive ? 1 : 0}">
              <i class="bi ${isActive ? 'bi-toggle-on' : 'bi-toggle-off'}"></i>
            </button>
          </div>
        </td>
      `;
      tbl.appendChild(tr);
    }
  }

  // =========================
  // Paginador & filtros
  // =========================
  $('#paginadorClientes')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-page]'); if (!btn) return;
    const to = parseInt(btn.dataset.page, 10);
    const pages = Math.max(1, Math.ceil((state.total || 0) / (state.per || 10)));
    if (to >= 1 && to <= pages && to !== state.page) listar(to);
  });
  $('#perPageClientes')?.addEventListener('change', (e) => {
    state.per = parseInt(e.target.value, 10) || 10;
    listar(1);
  });
  $('#btnBuscarClientes')?.addEventListener('click', () => {
    state.q = $('#qClientes')?.value.trim() || '';
    listar(1);
  });
  $('#qClientes')?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); state.q = e.target.value.trim(); listar(1); }
  });

  // =========================
  // Modal Crear/Editar
  // =========================
  const modalEl   = $('#modalCliente');
  const frm       = $('#frmCliente');
  const btnNuevo  = $('#btnNuevoCliente');
  const modalTitle= $('#modalTitle');
  let bsModal     = null;

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
    modalEl.addEventListener('hidden.bs.modal', () => fillForm({})); // reset
  }

  function fillForm(data = {}) {
    const d = {
      id_cliente: 0, cedula:'', nombres:'', apellidos:'', telefono:'', correo:'',
      contrasena:'', estado:'activo', ...data
    };
    d.estado = String(d.estado||'').toLowerCase().startsWith('inac') ? 'inactivo' : 'activo';

    frm?.querySelector('#id_cliente')?.setAttribute('value', d.id_cliente || 0);
    const set = (id,val)=>{ const el=frm?.querySelector('#'+id); if(el) el.value = val ?? ''; };
    set('cedula', d.cedula);
    set('nombres', d.nombres);
    set('apellidos', d.apellidos);
    set('telefono', d.telefono);
    set('correo', d.correo);
    set('contrasena',''); // nunca prellenar
    const est = frm?.querySelector('#estado'); if (est) est.value = d.estado;
  }
  function openEditor(data, title) {
    fillForm(data);
    if (modalTitle) modalTitle.textContent = title || 'Nuevo cliente';
    ensureHidden();
    if (bsModal) {
      bsModal.show();
      setTimeout(() => frm?.querySelector('#cedula')?.focus(), 120);
    } else {
      modalEl.style.display = 'block';
    }
  }
  function closeEditor(){ if (bsModal) bsModal.hide(); ensureHidden(); }

  btnNuevo?.addEventListener('click', () => openEditor({}, 'Nuevo cliente'));

  // =========================
  // Acciones de tabla
  // =========================
  tbl?.addEventListener('click', async (e) => {
    const btn = e.target.closest('button'); if (!btn) return;
    const id  = +btn.dataset.editar || +btn.dataset.eliminar || +btn.dataset.toggle;

    // Editar
    if (btn.dataset.editar) {
      try {
        const r = await fetch(api(`action=get&id=${id}`));
        const j = await resToJsonSafe(r);
        if (!j || !j.data) throw new Error('No se pudo cargar el cliente.');
        openEditor(j.data, 'Editar cliente');
      } catch (err) { uiToast(err.message || 'Error al cargar cliente', 'danger'); }
      return;
    }

    // Eliminar
    if (btn.dataset.eliminar) {
      const ok = await uiConfirm({
        title:'Eliminar cliente',
        body:'¿Seguro que deseas eliminar este cliente?\nEsta acción no se puede deshacer.',
        confirmText:'Sí, eliminar',
        variant:'danger'
      });
      if (!ok) return;
      try {
        const r = await fetch(api('action=delete'), { method:'POST', body: formData({id_cliente:id}) });
        const j = await resToJsonSafe(r);
        if (!j.ok) throw new Error(j.msg || 'No se pudo eliminar');
        uiToast('Cliente eliminado.', 'success');
        listar(state.page);
      } catch (err) { uiToast(err.message || 'Error al eliminar', 'danger'); }
      return;
    }

    // Toggle
    if (btn.dataset.toggle) {
      const active = btn.dataset.active === '1';
      const verbo  = active ? 'inactivar' : 'activar';
      const ok = await uiConfirm({
        title: `${verbo[0].toUpperCase()+verbo.slice(1)} cliente`,
        body: `¿Seguro que deseas ${verbo} este cliente?`,
        confirmText: `Sí, ${verbo}`,
        variant: active ? 'warning' : 'success'
      });
      if (!ok) return;

      try {
        const r = await fetch(api('action=toggle'), { method:'POST', body: formData({id_cliente:id}) });
        const j = await resToJsonSafe(r);
        if (!j.ok) throw new Error(j.msg || 'No se pudo cambiar el estado');

        // Actualización optimista
        applyToggleToRow(id, j.estado);
        uiToast(j.msg || (String(j.estado).toLowerCase().startsWith('activo') ? 'Cliente activado.' : 'Cliente inactivado.'), 'success');

        // Refrescar listado para mantener sync (ajusta si cambió de página por filtros)
        listar(state.page);
      } catch (err) { uiToast(err.message || 'Error al cambiar estado', 'danger'); }
      return;
    }
  });

  function applyToggleToRow(id, nuevoEstado) {
    const tr = tbl?.querySelector(`tr[data-id="${id}"]`); if (!tr) return;
    const isActive = String(nuevoEstado || '').toLowerCase().startsWith('activo');

    const tdEstado = tr.querySelector('td[data-col="estado"]');
    if (tdEstado) tdEstado.innerHTML = htmlBadgeEstado(nuevoEstado);

    const btn = tr.querySelector(`button[data-toggle="${id}"]`);
    if (btn) {
      btn.dataset.active = isActive ? '1' : '0';
      btn.title = isActive ? 'Inactivar' : 'Activar';
      const icon = btn.querySelector('i');
      if (icon) icon.className = `bi ${isActive ? 'bi-toggle-on' : 'bi-toggle-off'}`;
    }

    tr.classList.remove('flash-success','flash-danger');
    void tr.offsetWidth;
    tr.classList.add(isActive ? 'flash-success' : 'flash-danger');
  }

  // =========================
  // Guardar (crear/editar)
  // =========================
  const nameRe = /^[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,60}$/u;
  function validate(plain, isUpdate) {
    if (!/^\d{6,15}$/.test(plain.cedula || '')) return 'Cédula inválida (6-15 dígitos).';
    if (!nameRe.test(plain.nombres || '')) return 'Nombres inválidos (sólo letras/espacios, 2-60).';
    if (!nameRe.test(plain.apellidos || '')) return 'Apellidos inválidos (sólo letras/espacios, 2-60).';
    if (!/.+@.+\..+/.test(plain.correo || '')) return 'Correo inválido.';
    if (!isUpdate && (!plain.contrasena || plain.contrasena.length < 8)) return 'Contraseña mínima 8 caracteres.';
    return '';
  }

  frm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd   = new FormData(frm);
    const id   = +fd.get('id_cliente');
    const isUpdate = id > 0;
    const plain = Object.fromEntries(fd.entries());

    const err = validate(plain, isUpdate);
    if (err) { uiToast(err, 'warning'); return; }

    const msg = isUpdate
      ? `¿Guardar cambios del cliente?\n\n${plain.nombres || ''} ${plain.apellidos || ''}\nCédula: ${plain.cedula || ''}`
      : `¿Crear nuevo cliente?\n\n${plain.nombres || ''} ${plain.apellidos || ''}\nCédula: ${plain.cedula || ''}`;

    const ok = await uiConfirm({
      title: isUpdate ? 'Confirmar guardar' : 'Confirmar creación',
      body: msg,
      confirmText: isUpdate ? 'Sí, guardar' : 'Sí, crear',
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
      uiToast(isUpdate ? 'Cliente actualizado.' : 'Cliente creado.', 'success');
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
