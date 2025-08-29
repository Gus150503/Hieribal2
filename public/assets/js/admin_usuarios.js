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

  // ===== Selectores =====
  const $  = (s) => document.querySelector(s);
  const tbl = $('#tblUsuarios tbody');

  // ===== Util UI =====
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

  // ===== Listado con paginación =====
  async function listar(page = state.page) {
    state.page = page;
    const q = encodeURIComponent(state.q || '');
    setLoading(true);
    try {
      const res = await fetch(api(`action=list&q=${q}&page=${state.page}&per=${state.per}`));
      const j = await res.json();

      const items = j.items || [];
      state.total = parseInt(j.total ?? items.length, 10);
      state.page  = parseInt(j.page  ?? state.page, 10);
      state.per   = parseInt(j.per   ?? state.per, 10);

      renderTabla(items);
      renderPager();
      updateTotal();
    } catch (err) {
      console.error('Error listar:', err);
      emptyState();
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
      if (!j || !j.data) return alert('No se pudo cargar el usuario.');
      openEditor(j.data, 'Editar usuario');
      return;
    }

    if (btn.dataset.eliminar) {
      if (!confirm('¿Eliminar usuario?')) return;
      await fetch(api('action=delete'), { method:'POST', body: formData({id_usuario:id}) });
      listar();
      return;
    }

    if (btn.dataset.toggle) {
      const active = btn.dataset.active === '1';
      const verbo  = active ? 'desactivar' : 'activar';
      const aviso  = active
        ? '\n\nNota: al desactivar se rotará la contraseña automáticamente.'
        : '';

      if (!confirm(`¿Seguro que deseas ${verbo} este usuario?${aviso}`)) return;

      try {
        const r = await fetch(api('action=toggle'), {
          method:'POST',
          body: formData({ id_usuario: id })
        });
        const j = await r.json();
        if (!j.ok) throw new Error(j.msg || 'No se pudo cambiar el estado');

        // Actualización optimista
        applyToggleToRow(id, j.estado);

        alert(j.msg || (j.estado && String(j.estado).toLowerCase().startsWith('activo')
              ? 'Usuario activado.' : 'Usuario desactivado.'));

        // Refrescar para quedar en sync con BD
        listar(state.page);
      } catch (err) {
        alert(err.message || 'Error al cambiar estado');
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
    if (err) return alert(err);

    const btnSubmit = frm.querySelector('button[type="submit"]');
    const prevHtml = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando…';

    try {
      const action = isUpdate ? 'update' : 'create';
      const res = await fetch(api(`action=${action}`), { method:'POST', body: fd });
      const j = await res.json();
      if (!j.ok) throw new Error(j.msg || 'Error al guardar');
      closeEditor();
      listar();
    } catch (er) {
      alert(er.message || 'Error al guardar');
    } finally {
      btnSubmit.disabled = false;
      btnSubmit.innerHTML = prevHtml;
    }
  });

  // ===== Init =====
  function boot(){ ensureHidden(); listar(1); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
  window.addEventListener('pageshow', (e) => { if (e.persisted) boot(); });

  function formData(obj){ const fd=new FormData(); Object.entries(obj).forEach(([k,v])=>fd.append(k,v)); return fd; }
})();
