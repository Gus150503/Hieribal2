// admin_proveedores.js
(() => {
  if (window.__PROV_JS_BOUND__) return; window.__PROV_JS_BOUND__ = true;

  'use strict';

  const $  = sel => document.querySelector(sel);
  const tblBody = $('#tblProveedor tbody');

  const state = { q:'', page:1, per:10, total:0 };
  let __SEQ__ = 0;

  // Helpers UI muy simples
  function badgeEstado(estado){
    const on = String(estado||'').toLowerCase()==='activo';
    return on
      ? '<span class="badge bg-success-subtle text-success border">Activo</span>'
      : '<span class="badge bg-secondary-subtle text-secondary border">Inactivo</span>';
  }
  function escapeHtml(s){ return (s??'').toString().replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }
  function setLoading(){
    tblBody.innerHTML = `<tr><td colspan="12" class="py-4 text-center">
      <div class="spinner-border spinner-border-sm me-2"></div> Cargando…
    </td></tr>`;
  }
  function emptyState(){
    tblBody.innerHTML = `<tr><td colspan="12" class="py-4 text-center text-muted">Sin resultados</td></tr>`;
  }
  function updateTotal(){ const el = $('#totalProveedores'); if (el) el.textContent = `${state.total} registro(s)`; }

  async function listar(page = state.page){
    state.page = page<1 ? 1 : page;
    const q = encodeURIComponent(state.q||'');
    setLoading();
    const my = ++__SEQ__;
    try{
      const res = await fetch(`${window.PROVEEDOR_API}&action=list&q=${q}&page=${state.page}&per=${state.per}`);
      const j = await res.json();
      if (my !== __SEQ__) return;
      const items = j.items || [];
      state.total = parseInt(j.total ?? items.length,10);
      state.page  = parseInt(j.page  ?? state.page,10);
      state.per   = parseInt(j.per   ?? state.per,10);

      const pages = Math.max(1, Math.ceil((state.total||0)/(state.per||10)));
      if (state.total>0 && state.page>pages) return listar(pages);

      if (!items.length){ emptyState(); renderPager(); updateTotal(); return; }
      renderTabla(items);
      renderPager();
      updateTotal();
    }catch(e){
      emptyState();
    }
  }

  function renderTabla(items){
    tblBody.innerHTML = '';
    for(const p of items){
      const isActive = String(p.estado||'').toLowerCase()==='activo';
      const tr = document.createElement('tr');
      tr.dataset.id = p.id;

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
            <button class="btn btn-outline-primary" title="Editar" data-editar="${p.id}">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-outline-danger" title="Eliminar" data-eliminar="${p.id}">
              <i class="bi bi-trash"></i>
            </button>
            <button class="btn btn-outline-secondary"
                    title="${isActive?'Desactivar':'Activar'}"
                    data-toggle="${p.id}" data-active="${isActive?1:0}">
              <i class="bi ${isActive?'bi-toggle-on':'bi-toggle-off'}"></i>
            </button>
          </div>
        </td>
      `;
      tblBody.appendChild(tr);
    }
  }

  function renderPager(){
    const ul = $('#paginador'); if(!ul) return;
    const pages = Math.max(1, Math.ceil(state.total / state.per));
    let html = '';
    const prevD = state.page<=1 ? ' disabled' : '';
    html += `<li class="page-item${prevD}"><button class="page-link" data-page="${state.page-1}">&laquo;</button></li>`;
    const win=2; let s=Math.max(1,state.page-win), e=Math.min(pages,state.page+win);
    if(s>1){ html+=`<li class="page-item"><button class="page-link" data-page="1">1</button></li>${s>2?'<li class="page-item disabled"><span class="page-link">…</span></li>':''}`; }
    for(let p=s;p<=e;p++) html+=`<li class="page-item ${p===state.page?'active':''}"><button class="page-link" data-page="${p}">${p}</button></li>`;
    if(e<pages){ html+=`${e<pages-1?'<li class="page-item disabled"><span class="page-link">…</span></li>':''}<li class="page-item"><button class="page-link" data-page="${pages}">${pages}</button></li>`; }
    const nextD = state.page>=pages ? ' disabled' : '';
    html += `<li class="page-item${nextD}"><button class="page-link" data-page="${state.page+1}">&raquo;</button></li>`;
    ul.innerHTML = html;
  }

  // Paginador & tamaño
  $('#paginador')?.addEventListener('click', e=>{
    const btn = e.target.closest('[data-page]'); if(!btn) return;
    const to = parseInt(btn.dataset.page,10);
    const pages = Math.max(1, Math.ceil(state.total / state.per));
    if(to>=1 && to<=pages && to!==state.page) listar(to);
  });
  $('#perPage')?.addEventListener('change', e=>{ state.per = parseInt(e.target.value,10)||10; listar(1); });

  // Búsqueda
  $('#btnBuscarProveedor')?.addEventListener('click', ()=>{ state.q = $('#qProveedor').value.trim(); listar(1); });
  $('#qProveedor')?.addEventListener('keydown', e=>{
    if(e.key==='Enter'){ e.preventDefault(); state.q=e.target.value.trim(); listar(1); }
  });

  // Acciones de fila
  tblBody.addEventListener('click', async (e)=>{
    const btn = e.target.closest('button'); if(!btn) return;
    const id =
      +btn.dataset.editar ||
      +btn.dataset.eliminar ||
      +btn.dataset.toggle;

    // Editar
    if(btn.dataset.editar){
      const r = await fetch(`${window.PROVEEDOR_API}&action=get&id=${id}`); const j = await r.json();
      if(!j || !j.data){ alert('No se pudo cargar el proveedor'); return; }
      const f = $('#frmProveedor');
      const d = j.data;
      f.idProveedor.value = d.id;
      f.empresa.value = d.empresa||'';
      f.nit.value = d.nit||'';
      f.nombre_contacto.value = d.nombre_contacto||'';
      f.telefono.value = d.telefono||'';
      f.email.value = d.email||'';
      f.direccion.value = d.direccion||'';
      f.ciudad.value = d.ciudad||'';
      f.condiciones_pago.value = d.condiciones_pago||'';
      f.estado.value = (d.estado||'activo').toLowerCase()==='inactivo'?'inactivo':'activo';
      $('#modalTitleProveedor').textContent = 'Editar Proveedor';
      new bootstrap.Modal($('#modalProveedor'), {backdrop:'static'}).show();
      return;
    }

    // Eliminar
    if(btn.dataset.eliminar){
      if(!confirm('¿Eliminar este proveedor?')) return;
      const fd=new FormData(); fd.append('id',id);
      const r = await fetch(`${window.PROVEEDOR_API}&action=delete`, {method:'POST', body:fd});
      const j = await r.json();
      if(!j.ok){ alert(j.msg||'No se pudo eliminar'); return; }
      listar(state.page);
      return;
    }

    // Toggle estado
    if(btn.dataset.toggle){
      const active = btn.dataset.active === '1';
      const verbo = active ? 'desactivar' : 'activar';
      if(!confirm(`¿Seguro que deseas ${verbo} este proveedor?`)) return;
      const fd=new FormData(); fd.append('id',id);
      const r = await fetch(`${window.PROVEEDOR_API}&action=toggle`, {method:'POST', body:fd});
      const j = await r.json();
      if(!j.ok){ alert(j.msg||'No se pudo cambiar el estado'); return; }

      // Update visual inmediato
      const tr = tblBody.querySelector(`tr[data-id="${id}"]`);
      const tdEstado = tr?.querySelector('td[data-col="estado"]');
      if(tdEstado) tdEstado.innerHTML = badgeEstado(j.estado);

      btn.dataset.active = (String(j.estado||'').toLowerCase()==='activo') ? '1' : '0';
      btn.title = btn.dataset.active==='1' ? 'Desactivar' : 'Activar';
      const icon = btn.querySelector('i');
      if(icon) icon.className = `bi ${btn.dataset.active==='1' ? 'bi-toggle-on' : 'bi-toggle-off'}`;

      // Re-listar para quedar 100% en sync si cambió filtro/paginación
      listar(state.page);
    }
  });

  // Submit del formulario (create/update)
  const form = $('#frmProveedor');
  form?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if(!form.checkValidity()){ form.classList.add('was-validated'); return; }
    const fd = new FormData(form);
    const id = +(fd.get('id')||0);
    const action = id>0 ? 'update' : 'create';
    const btn = form.querySelector('button[type="submit"]');
    const prev = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando…';
    try{
      const r = await fetch(`${window.PROVEEDOR_API}&action=${action}`, {method:'POST', body:fd});
      const j = await r.json();
      if(!j.ok) throw new Error(j.msg || 'Error al guardar');
      bootstrap.Modal.getInstance($('#modalProveedor'))?.hide();
      form.reset(); form.classList.remove('was-validated');
      listar(state.page);
    }catch(err){ alert(err.message||'Error al guardar'); }
    finally{ btn.disabled=false; btn.innerHTML=prev; }
  });

  // Boot
  function boot(){ listar(1); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
