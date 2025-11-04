// assets/js/admin_inventario.js
// =====================================================
// Módulo Inventario (similar a admin_usuarios.js)
// =====================================================
(function () {
  if (window.__INVENTARIO_JS_BOUND__) return;
  window.__INVENTARIO_JS_BOUND__ = true;

  'use strict';

  const API = window.INVENTARIO_API || '?r=admin_inventario_api';

  // ===== Estado global =====
  const state = { page: 1, per: 10, total: 0, q: '' };
  let __SEQ__ = 0;

  // ===== Selectores =====
  const $ = (s) => document.querySelector(s);
  const tbl = $('#tblInventario tbody');

  // ===== Toast simple =====
  function toast(msg, variant = 'info') {
    const color = { success: '#198754', danger: '#dc3545', info: '#0d6efd', warning: '#ffc107' }[variant] || '#333';
    const el = document.createElement('div');
    el.textContent = msg;
    Object.assign(el.style, {
      background: color, color: '#fff', padding: '10px 14px',
      borderRadius: '8px', position: 'fixed', right: '16px', bottom: '16px',
      zIndex: 9999, fontSize: '14px', boxShadow: '0 4px 14px rgba(0,0,0,.2)'
    });
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 2800);
  }

  // ===== Cargar listado =====
  async function listar(page = 1) {
    state.page = page;
    const q = encodeURIComponent(state.q);
    const seq = ++__SEQ__;

    tbl.innerHTML = `<tr><td colspan="10" class="text-center py-3">
      <div class="spinner-border spinner-border-sm me-2"></div> Cargando…
    </td></tr>`;

    try {
      const res = await fetch(`${API}&action=list&q=${q}&page=${state.page}&per=${state.per}`);
      const j = await res.json();
      if (seq !== __SEQ__) return;
      const items = j.data || j.items || [];
      state.total = j.total || items.length;
      renderTabla(items);
      renderPager();
      $('#totalInventario').textContent = `${state.total} registro(s)`;
    } catch (err) {
      console.error(err);
      tbl.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-3">Error cargando datos</td></tr>`;
    }
  }

  function renderTabla(items) {
    if (!items.length) {
      tbl.innerHTML = `<tr><td colspan="10" class="text-center text-muted py-3">Sin resultados</td></tr>`;
      return;
    }
    tbl.innerHTML = '';
    for (const i of items) {
      const tr = document.createElement('tr');
      tr.dataset.id = i.id;
      tr.innerHTML = `
        <td>${i.id}</td>
        <td>${i.producto_id}</td>
        <td>${i.codigo_interno}</td>
        <td>${i.stock}</td>
        <td>${i.stock_minimo}</td>
        <td>${i.stock_maximo}</td>
        <td>${i.punto_reorden}</td>
        <td>${i.ubicacion}</td>
        <td><span class="badge ${i.estado === 'agotado' ? 'bg-danger' : 'bg-success'}">${i.estado}</span></td>
        <td class="text-end">
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" data-edit="${i.id}" title="Editar">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-outline-danger" data-del="${i.id}" title="Eliminar">
              <i class="bi bi-trash"></i>
            </button>
            <button class="btn btn-outline-secondary" data-toggle="${i.id}" title="Cambiar estado">
              <i class="bi ${i.estado === 'agotado' ? 'bi-toggle-off' : 'bi-toggle-on'}"></i>
            </button>
          </div>
        </td>`;
      tbl.appendChild(tr);
    }
  }

  function renderPager() {
    const ul = $('#paginador');
    const pages = Math.max(1, Math.ceil(state.total / state.per));
    let html = '';
    const prev = state.page <= 1 ? 'disabled' : '';
    const next = state.page >= pages ? 'disabled' : '';
    html += `<li class="page-item ${prev}"><button class="page-link" data-page="${state.page - 1}">&laquo;</button></li>`;
    for (let p = 1; p <= pages; p++) {
      html += `<li class="page-item ${p === state.page ? 'active' : ''}">
        <button class="page-link" data-page="${p}">${p}</button></li>`;
    }
    html += `<li class="page-item ${next}"><button class="page-link" data-page="${state.page + 1}">&raquo;</button></li>`;
    ul.innerHTML = html;
  }

  $('#paginador')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-page]');
    if (btn) {
      const p = parseInt(btn.dataset.page, 10);
      if (p > 0) listar(p);
    }
  });

  $('#perPage')?.addEventListener('change', (e) => {
    state.per = parseInt(e.target.value, 10) || 10;
    listar(1);
  });

  $('#btnBuscar')?.addEventListener('click', () => {
    state.q = $('#q').value.trim();
    listar(1);
  });

  $('#q')?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      state.q = e.target.value.trim();
      listar(1);
    }
  });

  // ===== Modal =====
  const modalEl = $('#modalInventario');
  const frm = $('#frmInventario');
  let bsModal = null;
  if (modalEl && window.bootstrap)
    bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static' });

  $('#btnNuevo')?.addEventListener('click', () => {
    frm.reset();
    $('#idInventario').value = '';
    $('#modalTitle').textContent = 'Nuevo Inventario';
    bsModal?.show();
  });

  tbl.addEventListener('click', async (e) => {
    const btn = e.target.closest('button');
    if (!btn) return;
    const id = btn.dataset.edit || btn.dataset.del || btn.dataset.toggle;
    if (!id) return;

    // Editar
    if (btn.dataset.edit) {
      try {
        const r = await fetch(`${API}&action=get&id=${id}`);
        const j = await r.json();
        const i = j.data;
        if (!i) return toast('Inventario no encontrado', 'warning');
        for (const [k, v] of Object.entries(i)) {
          if (frm[k]) frm[k].value = v ?? '';
        }
        $('#modalTitle').textContent = 'Editar Inventario';
        bsModal?.show();
      } catch {
        toast('Error al cargar inventario', 'danger');
      }
    }

    // Eliminar
    if (btn.dataset.del) {
      if (!confirm('¿Eliminar este registro?')) return;
      const fd = new FormData(); fd.append('id', id);
      const r = await fetch(`${API}&action=delete`, { method: 'POST', body: fd });
      const j = await r.json();
      if (j.ok) { toast('Inventario eliminado', 'success'); listar(); }
      else toast(j.msg || 'Error al eliminar', 'danger');
    }

    // Cambiar estado
    if (btn.dataset.toggle) {
      const fd = new FormData(); fd.append('id', id);
      const r = await fetch(`${API}&action=toggle`, { method: 'POST', body: fd });
      const j = await r.json();
      if (j.ok) { toast(j.msg || 'Estado actualizado', 'success'); listar(); }
      else toast(j.msg || 'Error al cambiar estado', 'danger');
    }
  });

  // ===== Guardar =====
  frm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(frm);
    const id = fd.get('id');
    const action = id ? 'update' : 'create';
    try {
      const r = await fetch(`${API}&action=${action}`, { method: 'POST', body: fd });
      const j = await r.json();
      if (!j.ok) throw new Error(j.msg || 'Error al guardar');
      bsModal?.hide();
      toast(id ? 'Inventario actualizado' : 'Inventario creado', 'success');
      listar();
    } catch (err) {
      toast(err.message, 'danger');
    }
  });

  // ===== Init =====
  listar(1);
})();
