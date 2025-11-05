// assets/js/admin_inventario.js
// =====================================================
// Módulo Inventario con Selector de Productos
// =====================================================
(function () {
  if (window.__INVENTARIO_JS_BOUND__) return;
  window.__INVENTARIO_JS_BOUND__ = true;

  'use strict';

  const API = window.INVENTARIO_API || '?r=admin_inventario_api';
  const API_PRODUCTOS = window.PRODUCTO_API || '?r=admin_productos_api';

  // ===== Estado global =====
  const state = { page: 1, per: 10, total: 0, q: '' };
  let __SEQ__ = 0;
  let productosCache = {};

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

  function escapeHtml(s){ return (s??'').toString().replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }

  // ===== Cargar productos en el select =====
  async function cargarProductosEnSelect() {
    const select = $('#producto_id');
    const cargando = $('#cargandoProductos');
    
    if (!select) return;
    
    cargando.style.display = 'block';
    select.innerHTML = '<option value="">Cargando productos...</option>';
    
    try {
      const res = await fetch(`${API_PRODUCTOS}&action=list&per=1000`);
      const j = await res.json();
      const productos = j.items || j.data || [];
      
      if (productos.length === 0) {
        select.innerHTML = '<option value="">No hay productos registrados</option>';
        cargando.style.display = 'none';
        toast('No hay productos disponibles', 'warning');
        return;
      }
      
      // Guardar en cache
      productos.forEach(p => {
        productosCache[p.id] = p;
      });
      
      // Llenar el select
      select.innerHTML = '<option value="">Seleccione un producto...</option>';
      productos.forEach(p => {
        const option = document.createElement('option');
        option.value = p.id;
        option.textContent = `${p.nombre} ${p.marca ? '- ' + p.marca : ''} ${p.categoria ? '(' + p.categoria + ')' : ''}`;
        option.dataset.producto = JSON.stringify(p);
        select.appendChild(option);
      });
      
      cargando.style.display = 'none';
      console.log(`✓ Cargados ${productos.length} productos`);
      
    } catch (err) {
      console.error('Error cargando productos:', err);
      select.innerHTML = '<option value="">Error al cargar productos</option>';
      cargando.style.display = 'none';
      toast('Error al cargar productos', 'danger');
    }
  }

  // ===== Mostrar info del producto seleccionado =====
  const productoSelect = $('#producto_id');
  const infoProducto = $('#infoProducto');
  
  productoSelect?.addEventListener('change', (e) => {
    const selectedOption = e.target.options[e.target.selectedIndex];
    
    if (!e.target.value || !selectedOption.dataset.producto) {
      infoProducto.style.display = 'none';
      return;
    }
    
    try {
      const producto = JSON.parse(selectedOption.dataset.producto);
      
      // Mostrar info
      $('#prodNombre').textContent = producto.nombre || 'Sin nombre';
      $('#prodMarca').textContent = producto.marca || 'Sin marca';
      $('#prodCategoria').textContent = producto.categoria || 'Sin categoría';
      $('#prodStock').textContent = producto.stock_actual || '0';
      $('#prodPrecio').textContent = producto.precio_venta || '0.00';
      
      // Imagen
      const img = $('#prodImg');
      if (producto.imagen) {
        img.src = producto.imagen;
        img.style.display = 'block';
        img.onerror = () => img.style.display = 'none';
      } else {
        img.style.display = 'none';
      }
      
      infoProducto.style.display = 'block';
      
      // Autocompletar código interno si está vacío
      const codigoInternoInput = $('#codigo_interno');
      if (codigoInternoInput && !codigoInternoInput.value.trim()) {
        const timestamp = Date.now().toString().slice(-4);
        codigoInternoInput.value = `INV-${producto.id}-${timestamp}`;
      }
      
    } catch (err) {
      console.error('Error al mostrar producto:', err);
    }
  });

  // ===== Cargar listado de inventario =====
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
      
      // Buscar nombre del producto en cache
      let nombreProducto = `ID: ${i.producto_id}`;
      if (productosCache[i.producto_id]) {
        nombreProducto = productosCache[i.producto_id].nombre;
      }
      
      tr.innerHTML = `
        <td>${i.id}</td>
        <td class="fw-semibold">${escapeHtml(nombreProducto)}</td>
        <td>${escapeHtml(i.codigo_interno || 'N/A')}</td>
        <td>${i.stock || 0}</td>
        <td>${i.stock_minimo || 0}</td>
        <td>${i.stock_maximo || 0}</td>
        <td>${i.punto_reorden || 0}</td>
        <td>${escapeHtml(i.ubicacion || 'N/A')}</td>
        <td><span class="badge ${i.estado === 'agotado' ? 'bg-danger' : i.estado === 'disponible' ? 'bg-success' : 'bg-warning'}">${escapeHtml(i.estado || 'disponible')}</span></td>
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
    if (!ul) return;
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
      if (p > 0 && p <= Math.ceil(state.total / state.per)) listar(p);
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
    frm.classList.remove('was-validated');
    $('#idInventario').value = '';
    $('#modalTitle').textContent = 'Nuevo Inventario';
    infoProducto.style.display = 'none';
    bsModal?.show();
  });

  // ===== Eventos de la tabla =====
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
        
        // Llenar formulario
        for (const [k, v] of Object.entries(i)) {
          if (frm[k]) frm[k].value = v ?? '';
        }
        
        // Seleccionar producto y mostrar info
        if (i.producto_id && productoSelect) {
          productoSelect.value = i.producto_id;
          productoSelect.dispatchEvent(new Event('change'));
        }
        
        $('#modalTitle').textContent = 'Editar Inventario';
        bsModal?.show();
      } catch {
        toast('Error al cargar inventario', 'danger');
      }
    }

    // Eliminar
    if (btn.dataset.del) {
      if (!confirm('¿Está seguro de eliminar este registro de inventario?')) return;
      toast('Eliminando...', 'info');
      const fd = new FormData(); 
      fd.append('id', id);
      try {
        const r = await fetch(`${API}&action=delete`, { method: 'POST', body: fd });
        const j = await r.json();
        if (j.ok) { 
          toast('✓ Inventario eliminado correctamente', 'success'); 
          listar(state.page); 
        } else {
          toast(j.msg || 'Error al eliminar', 'danger');
        }
      } catch (err) {
        toast('Error al eliminar', 'danger');
      }
    }

    // Toggle estado
    if (btn.dataset.toggle) {
      const fd = new FormData(); 
      fd.append('id', id);
      try {
        const r = await fetch(`${API}&action=toggle`, { method: 'POST', body: fd });
        const j = await r.json();
        if (j.ok) { 
          toast('✓ Estado actualizado correctamente', 'success'); 
          listar(state.page); 
        } else {
          toast(j.msg || 'Error al cambiar estado', 'danger');
        }
      } catch (err) {
        toast('Error al cambiar estado', 'danger');
      }
    }
  });

  // ===== Guardar =====
  frm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Validar formulario
    if (!frm.checkValidity()) {
      frm.classList.add('was-validated');
      toast('⚠ Por favor complete todos los campos requeridos', 'warning');
      return;
    }

    const fd = new FormData(frm);
    const id = fd.get('id');
    const action = id ? 'update' : 'create';

    toast('Guardando...', 'info');

    try {
      const r = await fetch(`${API}&action=${action}`, { method: 'POST', body: fd });
      const j = await r.json();
      if (!j.ok) throw new Error(j.msg || 'Error al guardar');
      bsModal?.hide();
      toast(id ? '✓ Inventario actualizado correctamente' : '✓ Inventario creado correctamente', 'success');
      listar(id ? state.page : 1);
    } catch (err) {
      toast('✗ ' + err.message, 'danger');
    }
  });

  // ===== Cargar modal cuando se abre =====
  modalEl?.addEventListener('shown.bs.modal', () => {
    cargarProductosEnSelect();
  });

  // ===== Solución para backdrop que queda pegado =====
  modalEl?.addEventListener('hidden.bs.modal', () => {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  });

  // ===== Init =====
  cargarProductosEnSelect().then(() => {
    listar(1);
  });
})();