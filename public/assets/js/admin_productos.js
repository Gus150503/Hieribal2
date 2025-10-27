(function () {
  if (window.__PRODUCTOS_JS_BOUND__) return;
  window.__PRODUCTOS_JS_BOUND__ = true;

  'use strict';

  const API = window.PRODUCTO_API || '?r=admin_productos_api';

  const state = { page: 1, per: 10, total: 0, q: '' };
  let __SEQ__ = 0;

  const $  = (s) => document.querySelector(s);
  const tbl = $('#tblProductos tbody');

  function toast(msg, variant='info'){
    const map = { success:'#198754', danger:'#dc3545', info:'#0d6efd', warning:'#ffc107' };
    const el=document.createElement('div'); el.textContent=msg;
    Object.assign(el.style, {background:map[variant]||'#333', color:'#fff', padding:'10px 14px',
      borderRadius:'8px', position:'fixed', right:'16px', bottom:'16px', zIndex:9999, boxShadow:'0 4px 14px rgba(0,0,0,.2)'});
    document.body.appendChild(el); setTimeout(()=>el.remove(),2400);
  }

  function escapeHtml(s){ return (s??'').toString().replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }
  function fmtMoney(n){ const v=Number(n||0); return isNaN(v)?'0.00':v.toFixed(2); }
  function fmtNumber(n){ const v=Number(n||0); return isNaN(v)?'0':v.toString(); }

  async function listar(page=1){
    state.page = page;
    const q = encodeURIComponent(state.q);
    const seq = ++__SEQ__;
    tbl.innerHTML = `<tr><td colspan="12" class="text-center py-3">
      <div class="spinner-border spinner-border-sm me-2"></div> Cargando…
    </td></tr>`;

    try {
      const res = await fetch(`${API}&action=list&q=${q}&page=${state.page}&per=${state.per}`);
      const j = await res.json();
      if (seq !== __SEQ__) return;
      const items = j.items || j.data || [];
      state.total = j.total || items.length;
      renderTabla(items);
      renderPager();
      const tot = $('#totalProductos'); if (tot) tot.textContent = `${state.total} registro(s)`;
    } catch (err) {
      console.error(err);
      tbl.innerHTML = `<tr><td colspan="12" class="text-center text-danger py-3">Error cargando datos</td></tr>`;
    }
  }

  function renderTabla(items){
    if (!items.length) {
      tbl.innerHTML = `<tr><td colspan="12" class="text-center text-muted py-3">Sin resultados</td></tr>`;
      return;
    }
    tbl.innerHTML = '';
    for (const p of items) {
      const tr = document.createElement('tr');
      tr.dataset.id = p.id;
      const activo = String(p.estado || '').toLowerCase() === 'activo';
      tr.innerHTML = `
        <td>${p.id}</td>
        <td class="fw-semibold">${escapeHtml(p.nombre)}</td>
        <td>${escapeHtml(p.codigo_sku ?? '')}</td>
        <td>${escapeHtml(p.marca ?? '')}</td>
        <td>${escapeHtml(p.categoria ?? '')}</td>
        <td>${fmtNumber(p.stock_actual)}</td>
        <td>${fmtNumber(p.stock_minimo)}</td>
        <td>${fmtMoney(p.precio_compra)}</td>
        <td>${fmtMoney(p.precio_venta)}</td>
        <td>${fmtNumber(p.iva)}%</td>
        <td>${activo
              ? '<span class="badge bg-success-subtle text-success border">Activo</span>'
              : '<span class="badge bg-secondary-subtle text-secondary border">Inactivo</span>'}</td>
        <td class="text-end">
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" data-edit="${p.id}" title="Editar">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-outline-danger" data-del="${p.id}" title="Eliminar">
              <i class="bi bi-trash"></i>
            </button>
            <button class="btn btn-outline-secondary" data-toggle="${p.id}" title="${activo?'Desactivar':'Activar'}">
              <i class="bi ${activo?'bi-toggle-on':'bi-toggle-off'}"></i>
            </button>
          </div>
        </td>`;
      tbl.appendChild(tr);
    }
  }

  function renderPager(){
    const ul = $('#paginadorProd'); if(!ul) return;
    const pages = Math.max(1, Math.ceil(state.total / state.per));
    let html = '';
    const prev = state.page<=1?' disabled':'';
    const next = state.page>=pages?' disabled':'';
    html += `<li class="page-item${prev}"><button class="page-link" data-page="${state.page-1}">&laquo;</button></li>`;
    const win=2; let s=Math.max(1,state.page-win), e=Math.min(pages,state.page+win);
    if (s>1){ html += `<li class="page-item"><button class="page-link" data-page="1">1</button></li>${s>2?'<li class="page-item disabled"><span class="page-link">…</span></li>':''}`; }
    for(let p=s;p<=e;p++) html += `<li class="page-item ${p===state.page?'active':''}"><button class="page-link" data-page="${p}">${p}</button></li>`;
    if (e<pages){ if(e<pages-1) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`; html += `<li class="page-item"><button class="page-link" data-page="${pages}">${pages}</button></li>`; }
    html += `<li class="page-item${next}"><button class="page-link" data-page="${state.page+1}">&raquo;</button></li>`;
    ul.innerHTML = html;
  }

  $('#paginadorProd')?.addEventListener('click', (e)=>{
    const btn = e.target.closest('[data-page]'); if(!btn) return;
    const p = parseInt(btn.dataset.page,10); if(p && p!==state.page) listar(p);
  });

  $('#perPageProd')?.addEventListener('change', e=>{ state.per=parseInt(e.target.value,10)||10; listar(1); });
  $('#btnBuscarProd')?.addEventListener('click', ()=>{ state.q=$('#qProd').value.trim(); listar(1); });
  $('#qProd')?.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); state.q=e.target.value.trim(); listar(1);} });

  // Modal
  const modalEl = $('#modalProducto');
  const frm = $('#frmProducto');
  let bsModal = null;
  if (modalEl && window.bootstrap) bsModal = new bootstrap.Modal(modalEl, { backdrop:'static' });

  $('#btnNuevoProd')?.addEventListener('click', ()=>{
    frm.reset(); frm.classList.remove('was-validated');
    $('#idProducto').value='';
    $('#modalProdTitle').textContent='Nuevo producto';
    bsModal?.show();
  });

  // Acciones de fila
  tbl.addEventListener('click', async (e)=>{
    const btn = e.target.closest('button'); if(!btn) return;
    const id = btn.dataset.edit || btn.dataset.del || btn.dataset.toggle;
    if (!id) return;

    // Editar
    if (btn.dataset.edit){
      try{
        const r = await fetch(`${API}&action=get&id=${id}`);
        const j = await r.json();
        const d = j.data;
        if (!d) return toast('Producto no encontrado','warning');
        for (const [k,v] of Object.entries(d)){ if (frm[k]) frm[k].value = v ?? ''; }
        $('#modalProdTitle').textContent='Editar producto';
        bsModal?.show();
      }catch{ toast('Error al cargar','danger'); }
    }

    // Eliminar
    if (btn.dataset.del){
      if (!confirm('¿Eliminar este producto?')) return;
      const fd=new FormData(); fd.append('id', id);
      const r = await fetch(`${API}&action=delete`, { method:'POST', body:fd });
      const j = await r.json();
      if (j.ok){ toast('Producto eliminado','success'); listar(state.page); }
      else toast(j.msg || 'Error al eliminar','danger');
    }

    // Toggle
    if (btn.dataset.toggle){
      const fd=new FormData(); fd.append('id', id);
      const r = await fetch(`${API}&action=toggle`, { method:'POST', body:fd });
      const j = await r.json();
      if (j.ok){ toast('Estado actualizado','success'); listar(state.page); }
      else toast(j.msg || 'Error al cambiar estado','danger');
    }
  });

  // Guardar
  frm?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if (!frm.checkValidity()){ frm.classList.add('was-validated'); return; }

    const fd = new FormData(frm);
    const id = fd.get('id');
    const action = id ? 'update' : 'create';

    try{
      const r = await fetch(`${API}&action=${action}`, { method:'POST', body:fd });
      const j = await r.json();
      if (!j.ok) throw new Error(j.msg || 'Error al guardar');
      bsModal?.hide();
      toast(id?'Producto actualizado':'Producto creado','success');
      listar(id?state.page:1);
    }catch(err){ toast(err.message,'danger'); }
  });

  // Init
  listar(1);
})();
