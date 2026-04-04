// assets/js/admin_proveedores.js
// MODULO INVENTARIOS / Juliana Lugo /
(function () {
  if (window.__PROVEEDORES_JS_BOUND__) return;
  window.__PROVEEDORES_JS_BOUND__ = true;

  'use strict';

  const API_BASE = window.PROVEEDOR_API;
    (location.pathname.replace(/\/public\/?$/, '') + '/public/?r=admin_proveedores_api');
  const api = (params = '') => `${API_BASE}&${params}`;

  const state = { page: 1, per: 10, total: 0, q: '' };
  let __SEQ__ = 0;

  const $  = (s) => document.querySelector(s);
  const $$ = (s) => Array.from(document.querySelectorAll(s));

  const tblBody   = $('#tblProveedor tbody');
  const pager     = $('#paginador');
  const perSel    = $('#perPage');
  const totalEl   = $('#totalProveedores');
  const qInput    = $('#qProveedor');
  const btnBuscar = $('#btnBuscarProveedor');
  const btnNuevo  = $('#btnNuevoProveedor');

  const modalEl   = $('#modalProveedor');
  const frm       = $('#frmProveedor');
  const modalTit  = $('#modalTitleProveedor');
  let   bsModal   = null;

  function cleanupBackdrops() {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  }

  // ========= TOASTS =========
  function ensureToastCSS() {
    if ($('#_prov_toast_css')) return;
    const css = document.createElement('style');
    css.id = '_prov_toast_css';
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
      <div class="msg">${(msg ?? '').toString()
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;')}</div>
      <button type="button" class="close" aria-label="Cerrar">✕</button>
    `;
    host.appendChild(el);
    const close = () => el.remove();
    el.querySelector('.close')?.addEventListener('click', close);
    const timer = setTimeout(close, ms);
    el.addEventListener('mouseenter', () => clearTimeout(timer), { once:true });
  }

  // ========= CONFIRM MODAL =========
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

  function uiConfirm({title='Confirmar', body='¿Seguro?', confirmText='Sí, continuar', variant='success'} = {}) {
    ensureConfirmModal(); // nos aseguramos de crearlo
    const modal = $('#confirmModal');
    if (!modal || !window.bootstrap || !bootstrap.Modal) {
      // fallback solo si de verdad no hay Bootstrap
      return Promise.resolve(confirm(body));
    }

    $('#confirmTitle').textContent = title;
    $('#confirmBody').innerHTML = (body ?? '').toString()
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/\n/g,'<br>');

    const okBtn = $('#btnOkConfirm');
    okBtn.className = 'btn ' + (variant === 'danger' ? 'btn-outline-danger'
                            : variant === 'warning' ? 'btn-outline-secondary'
                            : 'btn-success');
    okBtn.textContent = confirmText;

    return new Promise(resolve => {
      const bs = new bootstrap.Modal(modal, { backdrop: 'static' });
      const onOk   = () => { cleanup(); bs.hide(); resolve(true); };
      const onHide = () => { cleanup(); resolve(false); cleanupBackdrops(); };
      const cleanup = () => {
        okBtn.removeEventListener('click', onOk);
        modal.removeEventListener('hidden.bs.modal', onHide);
      };
      okBtn.addEventListener('click', onOk);
      modal.addEventListener('hidden.bs.modal', onHide, { once:true });
      bs.show();
    });
  }

  // ========= HEADER AMARILLO =========
  function colorizeHeaderProveedores() {
    const thead = document.querySelector('#tblProveedor thead');
    if (!thead) return;
    thead.classList.remove('table-primary', 'bg-primary');
    thead.style.setProperty('background-color', '#ffc107', 'important');
    thead.style.setProperty('color', '#ffffff', 'important');
    thead.querySelectorAll('th').forEach(th => {
      th.style.setProperty('background-color', '#ffc107', 'important');
      th.style.setProperty('color', '#ffffff', 'important');
      th.style.setProperty('border-color', 'rgba(255,255,255,.25)', 'important');
    });
  }

  // ========= VALIDACIÓN VISUAL =========
  function ensureFieldStyles() {
    if ($('#_prov_field_css')) return;
    const css = document.createElement('style');
    css.id = '_prov_field_css';
    css.textContent = `
    .form-control.is-valid,
    .form-select.is-valid{
      border-color:#198754!important;
      box-shadow:0 0 0 .2rem rgba(25,135,84,.15)!important;
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23198754' class='bi bi-check' viewBox='0 0 16 16'%3E%3Cpath d='M10.97 4.97a.75.75 0 0 1 1.07 1.05l-4.0 4.99a.75.75 0 0 1-1.08.02L3.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 4.473-4.448z'/%3E%3C/svg%3E");
      background-repeat:no-repeat;background-position:right .75rem center;background-size:1rem;
    }
    .form-control.is-invalid,
    .form-select.is-invalid{
      border-color:#dc3545!important;
      box-shadow:0 0 0 .2rem rgba(220,53,69,.15)!important;
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23dc3545' class='bi bi-exclamation-circle' viewBox='0 0 16 16'%3E%3Cpath d='M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0-12a.905.905 0 0 0-.9.995l.35 4.507a.55.55 0 0 0 1.1 0l.35-4.507A.905.905 0 0 0 8 3zm.002 8a1 1 0 1 0 0 2 1 1 0 0 0 0-2z'/%3E%3C/svg%3E");
      background-repeat:no-repeat;background-position:right .75rem center;background-size:1rem;
    }`;
    document.head.appendChild(css);
  }
  function setValid(el, ok) {
    if (!el) return;
    el.classList.remove('is-valid','is-invalid');
    if (ok === true) el.classList.add('is-valid');
    else if (ok === false) el.classList.add('is-invalid');
  }

  // ========= UTILS =========
  function escapeHtml(s) {
    return (s ?? '').toString().replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[m]));
  }
  function badgeEstado(estado){
    const on = String(estado||'').toLowerCase()==='activo';
    return on
      ? '<span class="badge bg-success-subtle text-success border">Activo</span>'
      : '<span class="badge bg-secondary-subtle text-secondary border">Inactivo</span>';
  }

  // ========= LISTA + PAGINACIÓN =========
  function setLoading(on) {
    if (!tblBody) return;
    if (on) {
      tblBody.innerHTML = `<tr><td colspan="12" class="py-4 text-center">
        <div class="spinner-border spinner-border-sm me-2"></div> Cargando… 
      </td></tr>`;
    }
  }
  function renderPager() {
    if (!pager) return;
    const pages = Math.max(1, Math.ceil((state.total || 0) / (state.per || 10)));
    let html = '';

    const prevDis = state.page <= 1 ? ' disabled' : '';
    html += `<li class="page-item${prevDis}">
      <button class="page-link" data-page="${state.page - 1}">&laquo;</button>
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
      <button class="page-link" data-page="${state.page + 1}">&raquo;</button>
    </li>`;

    pager.innerHTML = html;
  }
  function updateTotal(){ if (totalEl) totalEl.textContent = `${state.total} registro(s)`; }

  async function listar(page = 1) {
    state.page = page;
    const q = encodeURIComponent(state.q || '');
    setLoading(true);
    const seq = ++__SEQ__;

    try {
      const res = await fetch(api(`action=list&q=${q}&page=${state.page}&per=${state.per}`));
      const j   = await res.json();
      if (seq !== __SEQ__) return;

      const items = j.items || j.data || [];
      state.total = j.total || items.length;
      state.page  = +j.page  || state.page;
      state.per   = +j.per   || state.per;

      if (!items.length) {
        tblBody.innerHTML = `<tr><td colspan="12" class="text-center text-muted py-3">Sin resultados</td></tr>`;
        renderPager(); updateTotal(); return;
      }
      renderTabla(items);
      renderPager();
      updateTotal();
    } catch (err) {
      if (seq !== __SEQ__) return;
      tblBody.innerHTML = `<tr><td colspan="12" class="text-center text-danger py-3">No se pudo cargar.</td></tr>`;
      uiToast('No se pudieron cargar proveedores.', 'danger');
    }
  }

  function renderTabla(items) {
    if (!tblBody) return;
    tblBody.innerHTML = '';
    for (const p of items) {
      const tr = document.createElement('tr');
      tr.dataset.id = p.id;
      const activo = String(p.estado || '').toLowerCase() === 'activo';

      tr.innerHTML = `
        <td>${p.id}</td>
        <td class="fw-semibold">${escapeHtml(p.empresa)}</td>
        <td>${escapeHtml(p.nit)}</td>
        <td>${escapeHtml(p.nombre_contacto)}</td>
        <td>${escapeHtml(p.telefono)}</td>
        <td>${escapeHtml(p.email)}</td>
        <td>${escapeHtml(p.direccion)}</td>
        <td>${escapeHtml(p.ciudad)}</td>
        <td>${escapeHtml(p.condiciones_pago)}</td>
        <td data-col="estado">${badgeEstado(p.estado)}</td>
        <td>${escapeHtml(p.creado ?? '')}</td>
        <td class="text-end">
          <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" title="Editar" data-edit="${p.id}">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-outline-danger" title="Eliminar" data-del="${p.id}">
              <i class="bi bi-trash"></i>
            </button>
            <button class="btn btn-outline-secondary"
                    title="${activo ? 'Inactivar' : 'Activar'}"
                    data-toggle="${p.id}" data-active="${activo?1:0}">
              <i class="bi ${activo ? 'bi-toggle-on' : 'bi-toggle-off'}"></i>
            </button>
          </div>
        </td>
      `;
      tblBody.appendChild(tr);
    }
  }

  // ========= FILTROS =========
  pager?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-page]'); if (!btn) return;
    const to = parseInt(btn.dataset.page, 10);
    const pages = Math.max(1, Math.ceil((state.total || 0) / (state.per || 10)));
    if (to >= 1 && to <= pages && to !== state.page) listar(to);
  });
  perSel?.addEventListener('change', (e) => { state.per = parseInt(e.target.value, 10) || 10; listar(1); });
  btnBuscar?.addEventListener('click', () => { state.q = qInput?.value.trim() || ''; listar(1); });
  qInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); state.q = e.target.value.trim(); listar(1); }
  });

  // ========= MODAL CREAR / EDITAR =========
  function ensureHidden() {
    if (!modalEl) return;
    modalEl.classList.remove('show');
    modalEl.setAttribute('aria-hidden', 'true');
    modalEl.style.display = 'none';
    cleanupBackdrops();
  }

  if (modalEl && window.bootstrap && bootstrap.Modal) {
    ensureHidden();
    bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static' });
    modalEl.addEventListener('hidden.bs.modal', () => {
      frm?.reset();
      $$('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid','is-invalid'));
      cleanupBackdrops();
    });
  }

  function openEditor(title) {
    if (modalTit) modalTit.textContent = title || 'Nuevo Proveedor';
    ensureHidden();
    bsModal?.show();
  }
  function closeEditor(){ bsModal?.hide(); }

  btnNuevo?.addEventListener('click', () => {
    frm?.reset();
    if (frm && frm.idProveedor) frm.idProveedor.value = '';
    uiToast('Modo creación de proveedor', 'info');
    openEditor('Nuevo Proveedor');
  });

  // ========= ACCIONES TABLA =========
  tblBody?.addEventListener('click', async (e) => {
    const btn = e.target.closest('button'); if (!btn) return;
    const id  = btn.dataset.edit || btn.dataset.del || btn.dataset.toggle;
    if (!id) return;

    // Editar
    if (btn.dataset.edit) {
      try {
        const r = await fetch(api(`action=get&id=${id}`));
        const j = await r.json();
        const d = j.data;
        if (!d) return uiToast('Proveedor no encontrado','warning');

        frm.idProveedor.value       = d.id;
        frm.empresa.value           = d.empresa || '';
        frm.nit.value               = d.nit || '';
        frm.nombre_contacto.value   = d.nombre_contacto || '';
        frm.telefono.value          = d.telefono || '';
        frm.email.value             = d.email || '';
        frm.direccion.value         = d.direccion || '';
        frm.ciudad.value            = d.ciudad || '';
        frm.condiciones_pago.value  = d.condiciones_pago || '';
        frm.estado.value            = (d.estado || 'activo').toLowerCase()==='inactivo'?'inactivo':'activo';

        uiToast('Modo edición de proveedor', 'info');
        openEditor('Editar Proveedor');
      } catch {
        uiToast('Error al cargar proveedor', 'danger');
      }
      return;
    }

    // Eliminar
    if (btn.dataset.del) {
      const ok = await uiConfirm({
        title:'Eliminar proveedor',
        body:'¿Seguro que deseas eliminar este proveedor?\nEsta acción no se puede deshacer.',
        confirmText:'Sí, eliminar',
        variant:'danger'
      });
      if (!ok) return;

      try {
        const fd = new FormData(); fd.append('id', id);
        const r  = await fetch(api('action=delete'), { method:'POST', body: fd });
        const j  = await r.json();
        if (!j.ok) throw new Error(j.msg || 'No se pudo eliminar');
        uiToast('Proveedor eliminado exitosamente.', 'danger');
        listar(state.page);
      } catch (err) {
        uiToast(err.message || 'Error al eliminar','danger');
      }
      return;
    }

    // Toggle activar/inactivar
    if (btn.dataset.toggle) {
      const activo = btn.dataset.active === '1';
      const verbo  = activo ? 'inactivar' : 'activar';

      const ok = await uiConfirm({
        title: 'Cambiar estado',
        body: `¿Seguro que deseas ${verbo} este proveedor?`,
        confirmText: `Sí, ${verbo}`,
        variant: 'success'
      });
      if (!ok) return;

      try {
        const fd = new FormData(); fd.append('id', id);
        const r  = await fetch(api('action=toggle'), { method:'POST', body: fd });
        const j  = await r.json();
        if (!j.ok) throw new Error(j.msg || 'No se pudo cambiar el estado');

        const nuevoEstado = j.estado;
        const tr  = tblBody.querySelector(`tr[data-id="${id}"]`);
        const tdE = tr?.querySelector('td[data-col="estado"]');
        if (tdE) tdE.innerHTML = badgeEstado(nuevoEstado);

        const activoNow = String(nuevoEstado || '').toLowerCase() === 'activo';
        btn.dataset.active = activoNow ? '1':'0';
        btn.title = activoNow ? 'Inactivar' : 'Activar';
        const icon = btn.querySelector('i');
        if (icon) icon.className = `bi ${activoNow ? 'bi-toggle-on' : 'bi-toggle-off'}`;

        uiToast(
          activoNow ? 'Proveedor activado exitosamente.' : 'Proveedor inactivado exitosamente.',
          'info'
        );
        listar(state.page);
      } catch (err) {
        uiToast(err.message || 'Error al cambiar estado','danger');
      }
      return;
    }
  });

  // ========= VALIDACIÓN + GUARDAR =========
  function validatePlain(p) {
    if (!p.empresa || !p.empresa.trim())          return 'La empresa es obligatoria.';
    if (!p.nit || !p.nit.trim())                  return 'El NIT es obligatorio.';
    if (!p.nombre_contacto || !p.nombre_contacto.trim()) return 'El nombre de contacto es obligatorio.';
    if (!p.email || !/.+@.+\..+/.test(p.email))   return 'El email es inválido.';

    // Reglas adicionales solicitadas:
    // empresa y nombre_contacto no deben tener números
    if (p.empresa && /[0-9]/.test(p.empresa)) return 'La empresa no debe contener números.';
    if (p.nombre_contacto && /[0-9]/.test(p.nombre_contacto)) return 'El nombre de contacto no debe contener números.';

    // nit y telefono solo números
    if (p.nit && /[^0-9]/.test(p.nit)) return 'El NIT solo debe contener números.';
    if (p.telefono && /[^0-9]/.test(p.telefono)) return 'El teléfono solo debe contener números.';

    // ciudad y condiciones_pago no deben tener números
    if (p.ciudad && /[0-9]/.test(p.ciudad)) return 'La ciudad no debe contener números.';
    if (p.condiciones_pago && /[0-9]/.test(p.condiciones_pago)) return 'El método de pago no debe contener números.';

    return '';
  }

  // ---------- realtime input cleaners + setValid using existing setValid ----------
  function onlyLettersClean(v) {
    // permite letras acentuadas, Ñ, espacios
    return v.replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ\s]/g, '');
  }
  function onlyDigitsClean(v) {
    return v.replace(/[^0-9]/g, '');
  }

  // attach listeners but only if form and fields exist
  if (frm) {
    // empresa (no números)
    if (frm.empresa) {
      frm.empresa.addEventListener('input', (e) => {
        const cleaned = onlyLettersClean(e.target.value);
        if (cleaned !== e.target.value) {
          const pos = e.target.selectionStart - (e.target.value.length - cleaned.length);
          e.target.value = cleaned;
          e.target.setSelectionRange(pos, pos);
        }
        setValid(e.target, cleaned.trim() !== '');
      });
    }

    // nombre_contacto (no números)
    if (frm.nombre_contacto) {
      frm.nombre_contacto.addEventListener('input', (e) => {
        const cleaned = onlyLettersClean(e.target.value);
        if (cleaned !== e.target.value) {
          const pos = e.target.selectionStart - (e.target.value.length - cleaned.length);
          e.target.value = cleaned;
          e.target.setSelectionRange(pos, pos);
        }
        setValid(e.target, cleaned.trim() !== '');
      });
    }

    // ciudad (no números)
    if (frm.ciudad) {
      frm.ciudad.addEventListener('input', (e) => {
        const cleaned = onlyLettersClean(e.target.value);
        if (cleaned !== e.target.value) {
          const pos = e.target.selectionStart - (e.target.value.length - cleaned.length);
          e.target.value = cleaned;
          e.target.setSelectionRange(pos, pos);
        }
        setValid(e.target, cleaned.trim() !== '');
      });
    }

    // condiciones_pago (métodos de pago) (no números)
    if (frm.condiciones_pago) {
      frm.condiciones_pago.addEventListener('input', (e) => {
        const cleaned = onlyLettersClean(e.target.value);
        if (cleaned !== e.target.value) {
          const pos = e.target.selectionStart - (e.target.value.length - cleaned.length);
          e.target.value = cleaned;
          e.target.setSelectionRange(pos, pos);
        }
        setValid(e.target, cleaned.trim() !== '');
      });
    }

    // telefono (solo dígitos)
    if (frm.telefono) {
      frm.telefono.addEventListener('input', (e) => {
        const cleaned = onlyDigitsClean(e.target.value);
        if (cleaned !== e.target.value) {
          const pos = e.target.selectionStart - (e.target.value.length - cleaned.length);
          e.target.value = cleaned;
          e.target.setSelectionRange(pos, pos);
        }
        // si está vacío, no marcar inválido todavía; se maneja en submit
        setValid(e.target, cleaned.trim() !== '');
      });
    }

    // nit (solo dígitos)
    if (frm.nit) {
      frm.nit.addEventListener('input', (e) => {
        const cleaned = onlyDigitsClean(e.target.value);
        if (cleaned !== e.target.value) {
          const pos = e.target.selectionStart - (e.target.value.length - cleaned.length);
          e.target.value = cleaned;
          e.target.setSelectionRange(pos, pos);
        }
        setValid(e.target, cleaned.trim() !== '');
      });
    }
  }

  frm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    ensureFieldStyles();

    const fd    = new FormData(frm);
    const id    = fd.get('id');
    const plain = Object.fromEntries(fd.entries());
    const isUpdate = !!id;

    // reemplazé los setValid anteriores para que reflejen validaciones reales
    setValid($('#empresa'),          !!plain.empresa && plain.empresa.trim() !== '' && !(/[0-9]/.test(plain.empresa)));
    setValid($('#nit'),              !!plain.nit && plain.nit.trim() !== '' && !(/[^0-9]/.test(plain.nit)));
    setValid($('#nombre_contacto'),  !!plain.nombre_contacto && plain.nombre_contacto.trim() !== '' && !(/[0-9]/.test(plain.nombre_contacto)));
    setValid($('#email'),            !!plain.email && /.+@.+\..+/.test(plain.email));
    // telefono ahora validado: solo dígitos y no vacío
    setValid($('#telefono'),true,'OPCIONAL');
    setValid($('#direccion'),        !!plain.direccion && plain.direccion.trim() !== '');
    setValid($('#ciudad'),true,'OPCIONAL');
    setValid($('#condiciones_pago'), true,'OPCIONAL');

    const err = validatePlain(plain);
    if (err) { uiToast(`⚠ ${err}`, 'warning'); return; }

    const ok = await uiConfirm({
      title: isUpdate ? 'Confirmar actualización' : 'Confirmar creación',
      body:  isUpdate ? '¿Guardar cambios del proveedor?' : '¿Crear nuevo proveedor?',
      confirmText: isUpdate ? 'Sí, actualizar' : 'Sí, crear',
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
      const j = await r.json();
      if (!j.ok) throw new Error(j.msg || 'Error al guardar');

      closeEditor();
      cleanupBackdrops();

      if (isUpdate) {
        uiToast('Proveedor actualizado exitosamente.', 'warning');
        listar(state.page);
      } else {
        uiToast('Proveedor creado exitosamente.', 'success');
        listar(1);
      }
    } catch (err2) {
      uiToast(err2.message || 'Error al guardar','danger');
    } finally {
      btnSubmit.disabled = false;
      btnSubmit.innerHTML = prevHtml;
    }
  });

  // ========= INIT =========
  function boot(){
    ensureToastCSS();
    ensureConfirmModal();
    ensureFieldStyles();
    colorizeHeaderProveedores();
    listar(1);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  window.addEventListener('pageshow', (e) => { if (e.persisted) boot(); });

  // ======================================================================
//  MÓDULO: PRODUCTOS QUE MANEJA EL PROVEEDOR
// ======================================================================

// Elementos del modal secundario
const mpModalEl   = document.getElementById('modalProductosProveedor');
const mpProvNombre= document.getElementById('mpProvNombre');
const mpCatalogo  = document.getElementById('mpProductoCatalogo');
const mpTablaBody = document.querySelector('#mpTablaProductos tbody');
const mpBtnAgregar= document.getElementById('mpBtnAgregarProducto');
const mpBtnGuardar= document.getElementById('mpBtnGuardar');

let mpModal = null;
let currentProveedorId = 0;

// Inicializar modal BS
if (mpModalEl && bootstrap.Modal) {
  mpModal = new bootstrap.Modal(mpModalEl, { backdrop: 'static' });
}

// ----------------------------------------------------
// 1. Abrir modal de productos del proveedor
// ----------------------------------------------------
document.getElementById('btnProductosProveedor')?.addEventListener('click', async () => {
  const id = frm.idProveedor.value;

  if (!id) {
    uiToast("Primero guarda el proveedor antes de asignar productos.", "warning");
    return;
  }

  currentProveedorId = id;
  mpProvNombre.textContent = frm.empresa.value;

  await cargarCatalogo();
  await cargarProductosProveedor(id);

  mpModal.show();
});

// ----------------------------------------------------
// 2. Cargar catálogo de productos (para el select)
// ----------------------------------------------------
async function cargarCatalogo() {
  mpCatalogo.innerHTML = '<option value="">Cargando…</option>';

  try {
    const r = await fetch(api("action=productos_catalogo"));
    const j = await r.json();

    mpCatalogo.innerHTML = '<option value="">-- Selecciona un producto --</option>';

    (j.items || j.data || []).forEach(p => {
      let base = p.precio_compra ?? p.precio_base ?? 0;
      if (base === null || base === undefined || base === '') base = 0;

      const opt = document.createElement('option');
      opt.value = p.id;
      opt.textContent = `${p.nombre} (Base: $${Number(base).toFixed(2)})`;
      opt.dataset.base = base;
      opt.dataset.nombre = p.nombre;
      mpCatalogo.appendChild(opt);
    });

  } catch {
    uiToast("Error cargando catálogo de productos", "danger");
  }
}

// ----------------------------------------------------
// 3. Cargar productos asignados al proveedor
// ----------------------------------------------------
async function cargarProductosProveedor(idProveedor) {
  mpTablaBody.innerHTML = `
    <tr><td colspan="5" class="text-center py-3">
      <div class="spinner-border spinner-border-sm"></div> Cargando…
    </td></tr>`;

  try {
    const r = await fetch(api(`action=productos_proveedor&id_proveedor=${idProveedor}`));
    const j = await r.json();

    const items = j.items || j.data || [];

    if (!items.length) {
      mpTablaBody.innerHTML = `
        <tr class="mp-empty-row">
          <td colspan="5" class="text-center text-muted py-3">
            No hay productos asignados
          </td>
        </tr>`;
      return;
    }

    mpTablaBody.innerHTML = "";

    items.forEach(p =>
      agregarFilaProducto(
        p.producto_id,
        p.nombre,
        p.precio_base,
        p.precio_compra,
        p.activo
      )
    );

  } catch {
    uiToast("No se pudieron cargar los productos del proveedor", "danger");
  }
}

// ----------------------------------------------------
// 4. Agregar fila a la tabla
// ----------------------------------------------------
function agregarFilaProducto(id, nombre, precioBase, precioProv = "", activo = 1) {
  // si había la fila "No hay productos asignados", la quitamos
  const emptyRow = mpTablaBody.querySelector('.mp-empty-row');
  if (emptyRow) emptyRow.remove();

  let base = Number(precioBase ?? 0);
  let prov = (precioProv !== null && precioProv !== undefined && precioProv !== "")
    ? Number(precioProv)
    : base;

  const tr = document.createElement("tr");
  tr.dataset.id = id;

  tr.innerHTML = `
    <td>${nombre}</td>
    <td>$${base.toFixed(2)}</td>
    <td>
      <input type="number" class="form-control form-control-sm"
             value="${prov}" min="0" step="0.01">
    </td>
    <td class="text-center">
      <input type="checkbox" ${activo ? "checked" : ""}>
    </td>
    <td class="text-end">
      <button class="btn btn-sm btn-outline-danger mp-remove">
        <i class="bi bi-x-lg"></i>
      </button>
    </td>
  `;

  mpTablaBody.appendChild(tr);
}

// ----------------------------------------------------
// 5. Botón agregar producto desde el catálogo
// ----------------------------------------------------
mpBtnAgregar?.addEventListener("click", () => {
  const opt = mpCatalogo.selectedOptions[0];
  if (!opt) return;

  const id    = opt.value;
  const nombre= opt.dataset.nombre;
  const base  = opt.dataset.base;

  // Ver si ya existe
  if (mpTablaBody.querySelector(`tr[data-id="${id}"]`)) {
    uiToast("Este producto ya está agregado.", "warning");
    return;
  }

  agregarFilaProducto(id, nombre, base);
});

// ----------------------------------------------------
// 6. Quitar producto
// ----------------------------------------------------
mpTablaBody?.addEventListener("click", (e) => {
  if (!e.target.closest(".mp-remove")) return;
  e.target.closest("tr")?.remove();

  // si se quedó vacío, mostramos nuevamente el mensaje
  if (!mpTablaBody.querySelector('tr')) {
    mpTablaBody.innerHTML = `
      <tr class="mp-empty-row">
        <td colspan="5" class="text-center text-muted py-3">
          No hay productos asignados
        </td>
      </tr>`;
  }
});

// ----------------------------------------------------
// 7. Guardar productos del proveedor
// ----------------------------------------------------
mpBtnGuardar?.addEventListener("click", async () => {
  const items = [];

  mpTablaBody.querySelectorAll("tr").forEach(tr => {
    const id        = tr.dataset.id;
    const inpPrecio = tr.querySelector("input[type=number]");
    const chk       = tr.querySelector("input[type=checkbox]");

    // saltar filas sin inputs (como la de "No hay productos asignados")
    if (!id || !inpPrecio || !chk) return;

    const precio = inpPrecio.value;
    const activo = chk.checked ? 1 : 0;

    items.push({
      producto_id: id,
      precio_compra: precio,
      activo
    });
  });

   // Si no hay productos, igual permitimos guardar:
  if (!items.length) {
    uiToast("Este proveedor quedará sin productos asignados.", "info");
    // OJO: no hacemos return; mandamos el arreglo vacío al backend
  }


  try {
    const r = await fetch(api("action=productos_save"), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        id_proveedor: currentProveedorId,
        items
      })
    });

    const j = await r.json();
    if (!j.ok) throw new Error(j.msg || "Error al guardar productos");

    uiToast("Productos guardados correctamente", "success");
    mpModal.hide();

  } catch (err) {
    uiToast(err.message || "Error al guardar productos", "danger");
  }
});



})();
