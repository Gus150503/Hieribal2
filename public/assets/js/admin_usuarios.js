// admin_usuarios.js
// =====================================================
// Evitar doble carga/binding del script
// =====================================================
(function () {
  if (window.__USUARIOS_JS_BOUND__) {
    console.warn('admin_usuarios.js ya estaba cargado; ignoro segunda carga');
    return;
  }
  window.__USUARIOS_JS_BOUND__ = true;

  'use strict';

  // ===== Base y endpoints =====
  const base = location.pathname.replace(/\/public\/?$/, '') + '/public';
  const api  = (params) => `${base}/?r=admin_usuarios_api&${params}`;

  // ===== Estado =====
  const state = { page: 1, per: 10, total: 0, q: '' };
  // Secuencia para evitar condiciones de carrera (última respuesta gana)
  let __LIST_REQ_SEQ__ = 0;

  // ===== Selectores =====
  const $  = (s) => document.querySelector(s);
  const tbl = $('#tblUsuarios tbody');

  // ===== Toast CSS + Host (inyección one-time) =====
  function ensureToastCSS() {
    if (document.getElementById('_usuarios_toast_css')) return;
    const css = document.createElement('style');
    css.id = '_usuarios_toast_css';
    css.textContent = `
    .toast-host{
      position: fixed;
      right: 16px;
      bottom: 16px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      z-index: 1080; /* > 1050 (backdrop bootstrap) */
      pointer-events: none;
    }
    .toast{
      pointer-events: auto;
      min-width: 280px;
      max-width: 420px;
      padding: 10px 12px;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,.18);
      background: #111;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 10px;
      opacity: .98;
      transform: translateY(20px);
      animation: _toastSlideIn .20s ease-out forwards;
      border: 1px solid transparent;
    }
    .toast .btn-close{
      margin-left: auto;
      filter: invert(1);
      opacity: .8;
    }
    .toast-success{ background:#0f5132; color:#d1f7e5; border-color:#0f5132; }
    .toast-danger { background:#842029; color:#ffd7db; border-color:#842029; }
    .toast-warning{ background:#664d03; color:#fff3cd; border-color:#664d03; }
    .toast-info   { background:#0b5ed7; color:#dbe8ff; border-color:#0b5ed7; }
    .toast .dot{ width:8px; height:8px; border-radius:50%; background: currentColor; opacity:.8; }

    @keyframes _toastSlideIn { to { transform: translateY(0); } }

    .flash-success{ animation: _flashGreen .9s ease-in-out; }
    .flash-danger { animation: _flashRed   .9s ease-in-out; }
    @keyframes _flashGreen{ 0%,100%{ background-color: transparent;} 30%{ background-color: #e8f8f0; } }
    @keyframes _flashRed  { 0%,100%{ background-color: transparent;} 30%{ background-color: #fdecef; } }
    `;
    document.head.appendChild(css);
  }

  function ensureToastHost(){
    let host = document.getElementById('toastHost');
    if(!host){
      host = document.createElement('div');
      host.id = 'toastHost';
      host.className = 'toast-host';
      document.body.appendChild(host);
    }
    return host;
  }

  /** uiToast('mensaje', 'success'|'danger'|'warning'|'info', ms=3500) */
  /** uiToast('mensaje', 'success'|'danger'|'warning'|'info', ms=3500) */
function uiToast(msg, variant='info', ms=3500){
  const host = ensureToastHost();
  const t = document.createElement('div');
  t.className = `toast toast-${variant}`;
  t.innerHTML = `
    <div class="dot"></div>
    <div class="toast-msg">${escapeHtml(String(msg))}</div>
    <button class="btn-close" aria-label="Cerrar"></button>
  `;
  host.appendChild(t);

  // 👇 Necesario cuando hay Bootstrap: sin .show el toast queda con opacity:0
  t.classList.add('show');

  const close = () => t.remove();
  t.querySelector('.btn-close')?.addEventListener('click', close);
  const timer = setTimeout(close, ms);
  // si pasas el mouse, se queda
  t.addEventListener('mouseenter', () => clearTimeout(timer), { once:true });
}

  // ===== Confirm genérico (Bootstrap) + fallback =====
  function ensureConfirmModal(){
    if (document.getElementById('confirmModal')) return;
    const wrap = document.createElement('div');
    wrap.innerHTML = `
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmTitle">Confirmar acción</h5>
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

  /** uiConfirm({title, body, confirmText, variant}) -> Promise<boolean> (Bootstrap modal) */
  function uiConfirm(opts = {}){
    const modalEl = document.getElementById('confirmModal');
    if(!modalEl || !window.bootstrap){
      return Promise.resolve(confirm(opts.body || '¿Seguro?')); // fallback
    }
    const title = modalEl.querySelector('#confirmTitle');
    const body  = modalEl.querySelector('#confirmBody');
    const btnOk = modalEl.querySelector('#btnOkConfirm');

    title.textContent = opts.title || 'Confirmar acción';
    body.innerHTML = escapeHtml(String(opts.body || '¿Seguro?')).replace(/\n/g,'<br>');
    btnOk.textContent = opts.confirmText || 'Sí, continuar';

    // color del botón ok según variante
    btnOk.className = 'btn ' + (
      opts.variant === 'danger'  ? 'btn-outline-danger' :
      opts.variant === 'warning' ? 'btn-outline-secondary' :
      opts.variant === 'success' ? 'btn-success' :
                                   'btn-success'
    );

    return new Promise(resolve => {
      const bs = new bootstrap.Modal(modalEl, { backdrop: 'static' });

      const onOk = () => { cleanup(); bs.hide(); resolve(true); };
      const onHide = () => { cleanup(); resolve(false); };
      const cleanup = () => {
        btnOk.removeEventListener('click', onOk);
        modalEl.removeEventListener('hidden.bs.modal', onHide);
      };

      btnOk.addEventListener('click', onOk);
      modalEl.addEventListener('hidden.bs.modal', onHide, { once:true });
      bs.show();
    });
  }

  // ===== Util UI existentes =====
  function setLoading(on) {
    if (on) {
      tbl.innerHTML = `<tr><td colspan="8" class="py-4 text-center">
        <div class="spinner-border spinner-border-sm me-2"></div> Cargando…
      </td></tr>`;
    }
  }
  function emptyState() {
    tbl.innerHTML = `<tr><td colspan="8" class="py-4 text-center text-muted">Sin resultados</td></tr>`;
  }
  function updateTotal() {
    const el = $('#totalUsuarios');
    if (el) el.textContent = `${state.total} registro(s)`;
  }

  // ===== Listado con paginación (auto-ajuste + última respuesta gana) =====
  async function listar(page = state.page) {
    state.page = page < 1 ? 1 : page;
    const q = encodeURIComponent(state.q || '');
    setLoading(true);

    const mySeq = ++__LIST_REQ_SEQ__; // id de esta petición

    try {
      const res = await fetch(api(`action=list&q=${q}&page=${state.page}&per=${state.per}`));
      const j = await resToJsonSafe(res);

      // si llegó otra petición más nueva, aborta pintar
      if (mySeq !== __LIST_REQ_SEQ__) return;

      const items = j.items || [];
      state.total = parseInt(j.total ?? items.length, 10);
      state.page  = parseInt(j.page  ?? state.page, 10);
      state.per   = parseInt(j.per   ?? state.per, 10);

      // ajustar página si quedó fuera de rango
      const pages = Math.max(1, Math.ceil((state.total || 0) / (state.per || 10)));
      if (state.total > 0 && state.page > pages) {
        return listar(pages); // reintenta con la última página existente
      }

      // backend inconsistente: total>0 pero items vacíos en page 1 -> reintenta
      if (state.total > 0 && items.length === 0 && state.page === 1) {
        return listar(1);
      }

      if (!items.length) {
        emptyState();
        renderPager();
        updateTotal();
        return;
      }

      renderTabla(items);
      renderPager();
      updateTotal();
    } catch (err) {
      // si llegó otra petición más nueva, no pintes error
      if (mySeq !== __LIST_REQ_SEQ__) return;
      console.error('Error listar:', err);
      emptyState();
      uiToast('No se pudieron cargar los usuarios.', 'danger');
    }
  }

  // ===== Render de tabla =====
  function renderTabla(items) {
    if (!items.length) return emptyState();
    tbl.innerHTML = '';

    for (const u of items) {
      const isActive = String(u.estado || '').toLowerCase().startsWith('activo');

      const estadoBadge = htmlBadgeEstado(u.estado);
      const verifBadge = u.correo_verificado
        ? '<span class="badge bg-success-subtle text-success border">Verificado</span>'
        : '<span class="badge bg-danger-subtle text-danger border">Pendiente</span>';

      const tr = document.createElement('tr');
      tr.dataset.id = u.id_usuario; // clave para actualizar luego

      tr.innerHTML = `
        <td>${u.id_usuario}</td>
        <td class="fw-semibold">${escapeHtml(u.usuario)}</td>
        <td>${escapeHtml(u.nombres)} ${escapeHtml(u.apellidos)}</td>
        <td>${escapeHtml(u.correo)}</td>
        <td><span class="badge bg-light text-dark border">${escapeHtml(u.rol)}</span></td>
        <td data-col="estado">${estadoBadge}</td>
        <td>${verifBadge}</td>
        <td class="text-end">
          <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" title="Editar" data-editar="${u.id_usuario}">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-outline-danger" title="Eliminar" data-eliminar="${u.id_usuario}">
              <i class="bi bi-trash"></i>
            </button>
            <button class="btn btn-outline-secondary"
                    title="${isActive ? 'Desactivar' : 'Activar'}"
                    data-toggle="${u.id_usuario}"
                    data-active="${isActive ? 1 : 0}">
              <i class="bi ${isActive ? 'bi-toggle-on' : 'bi-toggle-off'}"></i>
            </button>
            ${u.correo_verificado ? '' : `
              <button class="btn btn-outline-success" title="Reenviar verificación" data-reenviar="${u.id_usuario}">
                <i class="bi bi-envelope-arrow-up"></i>
              </button>`}
          </div>
        </td>
      `;
      tbl.appendChild(tr);
    }
  }

  function htmlBadgeEstado(estado) {
    const isActive = String(estado || '').toLowerCase().startsWith('activo');
    return isActive
      ? '<span class="badge bg-success-subtle text-success border">Activo</span>'
      : '<span class="badge bg-secondary-subtle text-secondary border">Inactivo</span>';
  }

  function applyToggleToRow(id, nuevoEstado) {
    const tr = tbl.querySelector(`tr[data-id="${id}"]`);
    if (!tr) return;

    const isActive = String(nuevoEstado || '').toLowerCase().startsWith('activo');

    // Actualiza celda de estado
    const tdEstado = tr.querySelector('td[data-col="estado"]');
    if (tdEstado) tdEstado.innerHTML = htmlBadgeEstado(nuevoEstado);

    // Actualiza botón toggle (icono + title + data-active)
    const btn = tr.querySelector(`button[data-toggle="${id}"]`);
    if (btn) {
      btn.dataset.active = isActive ? '1' : '0';
      btn.title = isActive ? 'Desactivar' : 'Activar';
      const icon = btn.querySelector('i');
      if (icon) icon.className = `bi ${isActive ? 'bi-toggle-on' : 'bi-toggle-off'}`;
    }

    // flash visual
    tr.classList.remove('flash-success','flash-danger');
    void tr.offsetWidth; // reflow para reiniciar la animación
    tr.classList.add(isActive ? 'flash-success' : 'flash-danger');
  }

  function escapeHtml(s) {
    return (s ?? '').toString().replace(/[&<>"']/g, m => ({
      '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'
    }[m]));
  }

  // ===== Paginación =====
  function renderPager() {
    const ul = $('#paginador'); if (!ul) return;
    const pages = Math.max(1, Math.ceil(state.total / state.per));
    let html = '';

    const prevDisabled = state.page <= 1 ? ' disabled' : '';
    html += `<li class="page-item${prevDisabled}">
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

    const nextDisabled = state.page >= pages ? ' disabled' : '';
    html += `<li class="page-item${nextDisabled}">
      <button class="page-link" data-page="${state.page + 1}" aria-label="Siguiente">&raquo;</button>
    </li>`;

    ul.innerHTML = html;
  }

  $('#paginador')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-page]'); if (!btn) return;
    const to = parseInt(btn.dataset.page, 10);
    const pages = Math.max(1, Math.ceil(state.total / state.per));
    if (to >= 1 && to <= pages && to !== state.page) listar(to);
  });

  $('#perPage')?.addEventListener('change', (e) => {
    state.per = parseInt(e.target.value, 10) || 10;
    listar(1);
  });

  // ===== Buscar =====
  $('#btnBuscar')?.addEventListener('click', () => {
    state.q = $('#q').value.trim();
    listar(1);
  });
  $('#q')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); state.q = e.target.value.trim(); listar(1); }
  });

  // ===== Modal =====
  const modalEl   = document.getElementById('modalUsuario');
  const frm       = document.getElementById('frmUsuario');
  const btnNuevo  = document.getElementById('btnNuevo');
  const modalTitle= document.getElementById('modalTitle');
  let bsModal     = null;

  function ensureHidden() {
    if (!modalEl) return;
    modalEl.classList.remove('show');
    modalEl.setAttribute('aria-hidden', 'true');
    modalEl.style.display = 'none';
    document.body.classList.remove('modal-open');
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
  }

  if (modalEl && window.bootstrap) {
    ensureHidden();
    bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static' });
  }

  function fillForm(data = {}) {
    const d = {
      id_usuario: 0, usuario: '', rol: 'empleado', estado: 'activo',
      nombres: '', apellidos: '', correo: '', ...data
    };
    d.estado = String(d.estado || '').toLowerCase().startsWith('inac') ? 'inactivo' : 'activo';
    frm.querySelector('#id_usuario').value = d.id_usuario || 0;
    frm.querySelector('#usuario').value    = d.usuario || '';
    frm.querySelector('#rol').value        = (d.rol || 'empleado').toLowerCase();
    frm.querySelector('#estado').value     = d.estado;
    frm.querySelector('#nombres').value    = d.nombres || '';
    frm.querySelector('#apellidos').value  = d.apellidos || '';
    frm.querySelector('#correo').value     = d.correo || '';
    frm.querySelector('#password').value   = '';
  }

  function openEditor(data, title) {
    fillForm(data);
    if (modalTitle) modalTitle.textContent = title || 'Nuevo usuario';
    ensureHidden();
    if (bsModal) {
      bsModal.show();
      setTimeout(() => document.getElementById('usuario')?.focus(), 120);
    } else {
      modalEl.style.display = 'block';
    }
  }
  function closeEditor() { if (bsModal) bsModal.hide(); ensureHidden(); }

  btnNuevo?.addEventListener('click', () => openEditor({}, 'Nuevo usuario'));

  // ===== Acciones tabla (un solo listener) =====
  async function onTableClick(e) {
    const btn = e.target.closest('button'); if (!btn) return;
    const id =
      +btn.dataset.editar  ||
      +btn.dataset.eliminar||
      +btn.dataset.toggle  ||
      +btn.dataset.reenviar;

    if (btn.dataset.editar) {
      const r = await fetch(api(`action=get&id=${id}`));
      const j = await r.json();
      if (!j || !j.data) { uiToast('No se pudo cargar el usuario.', 'danger'); return; }
      openEditor(j.data, 'Editar usuario');
      return;
    }

    if (btn.dataset.eliminar) {
      const ok = await uiConfirm({
        title: 'Eliminar usuario',
        body: '¿Seguro que deseas eliminar este usuario?\nEsta acción no se puede deshacer.',
        confirmText: 'Sí, eliminar',
        variant: 'danger'
      });
      if (!ok) return;
      await fetch(api('action=delete'), { method:'POST', body: formData({id_usuario:id}) });
      uiToast('Usuario eliminado.', 'success');
      listar(state.page);
      return;
    }

    if (btn.dataset.toggle) {
      const active = btn.dataset.active === '1';
      const verbo  = active ? 'desactivar' : 'activar';
      const aviso  = active
        ? '\n\nNota: al desactivar se rotará la contraseña automáticamente.'
        : '';

      const ok = await uiConfirm({
        title: `${verbo[0].toUpperCase()+verbo.slice(1)} usuario`,
        body: `¿Seguro que deseas ${verbo} este usuario?${aviso}`,
        confirmText: `Sí, ${verbo}`,
        variant: active ? 'warning' : 'success'
      });
      if (!ok) return;

      try {
        const r = await fetch(api('action=toggle'), {
          method:'POST',
          body: formData({ id_usuario: id })
        });
        const j = await resToJsonSafe(r);
        if (!j.ok) throw new Error(j.msg || 'No se pudo cambiar el estado');

        // Actualización optimista + flash
        applyToggleToRow(id, j.estado);
        uiToast(
          j.msg || (j.estado && String(j.estado).toLowerCase().startsWith('activo')
            ? 'Usuario activado.' : 'Usuario desactivado.'),
          'success'
        );

        // Refrescar para quedar en sync con BD (manteniendo página actual con auto-ajuste)
        listar(state.page);
      } catch (err) {
        uiToast(err.message || 'Error al cambiar estado', 'danger');
      }
      return;
    }

    if (btn.dataset.reenviar) {
      location.href = `${base}/?r=admin_usuarios_resend_verif&id=${id}`;
    }
  }

  // Quita cualquier listener previo y agrega uno solo
  tbl.removeEventListener('click', onTableClick);
  tbl.addEventListener('click', onTableClick);

  // ===== Validación y submit =====
  const nameRe = /^[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,60}$/u;
  const userRe = /^[A-Za-z0-9._-]{3,30}$/;

  function validate(data, isUpdate) {
    if (!userRe.test(data.usuario || '')) return 'Usuario inválido (3-30, letras/números . _ -)';
    if (!nameRe.test(data.nombres || '')) return 'Nombres inválidos (sólo letras/espacios, 2-60).';
    if (!nameRe.test(data.apellidos || '')) return 'Apellidos inválidos (sólo letras/espacios, 2-60).';
    if (!/.+@.+\..+/.test(data.correo || '')) return 'Correo inválido.';
    if (!isUpdate && (!data.password || data.password.length < 6)) return 'Password mínimo 6 caracteres.';
    return '';
  }

  frm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(frm);
    const id = +fd.get('id_usuario');
    const isUpdate = id > 0;

    const plain = Object.fromEntries(fd.entries());
    const err = validate(plain, isUpdate);
    if (err) { uiToast(err, 'warning'); return; }

    const btnSubmit = frm.querySelector('button[type="submit"]');
    const prevHtml = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando…';

    try {
      const action = isUpdate ? 'update' : 'create';
      const res = await fetch(api(`action=${action}`), { method:'POST', body: fd });
      const j = await resToJsonSafe(res);
      if (!j.ok) throw new Error(j.msg || 'Error al guardar');
      closeEditor();
      uiToast(isUpdate ? 'Usuario actualizado.' : 'Usuario creado.', 'success');
      listar(state.page);
    } catch (er) {
      uiToast(er.message || 'Error al guardar', 'danger');
    } finally {
      btnSubmit.disabled = false;
      btnSubmit.innerHTML = prevHtml;
    }
  });

  // ===== Init =====
  function boot(){
    ensureToastCSS();
    ensureConfirmModal();
    ensureHidden();
    listar(1);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
  window.addEventListener('pageshow', (e) => { if (e.persisted) boot(); });

  function formData(obj){ const fd=new FormData(); Object.entries(obj).forEach(([k,v])=>fd.append(k,v)); return fd; }

  // helper robusto para JSON (por si el backend devuelve HTML de error)
  async function resToJsonSafe(res){
    try { return await res.json(); }
    catch { return { ok:false, msg:'Respuesta inválida del servidor' }; }
  }
})();
