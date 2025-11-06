(function () {
  if (window.__CLIENTES_JS_BOUND__) return;
  window.__CLIENTES_JS_BOUND__ = true;

  'use strict';

  const base = location.pathname.replace(/\/public\/?$/, '') + '/public';
  const api  = (params) => `${base}/?r=admin_clientes_api&${params}`;

  const state = { page: 1, per: 10, total: 0, q: '' };
  let __LIST_REQ_SEQ__ = 0;

  const $  = (s) => document.querySelector(s);
  const tbl = $('#tblClientes tbody');

  function escapeHtml(s){return (s??'').toString().replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));}

  function uiToast(msg,variant='info'){alert(msg);} // Simplificado o usa tu toast de usuarios

  async function listar(page=state.page){
    state.page=page;
    const q=encodeURIComponent(state.q||'');
    const mySeq=++__LIST_REQ_SEQ__;
    const res=await fetch(api(`action=list&q=${q}&page=${state.page}&per=${state.per}`));
    const j=await res.json();
    if(mySeq!==__LIST_REQ_SEQ__)return;
    const items=j.items||[];
    state.total=j.total||0;
    renderTabla(items);
  }

  function renderTabla(items){
    tbl.innerHTML='';
    if(!items.length){
      tbl.innerHTML='<tr><td colspan="8" class="text-center text-muted py-3">Sin resultados</td></tr>';
      return;
    }
    for(const c of items){
      const tr=document.createElement('tr');
      tr.dataset.id=c.id_cliente;
      const isActive=String(c.estado||'').toLowerCase().startsWith('activo');
      tr.innerHTML=`
      <td>${c.id_cliente}</td>
      <td>${escapeHtml(c.cedula)}</td>
      <td>${escapeHtml(c.nombres)} ${escapeHtml(c.apellidos)}</td>
      <td>${escapeHtml(c.telefono||'-')}</td>
      <td>${escapeHtml(c.correo)}</td>
      <td>${isActive?'<span class="badge bg-success">Activo</span>':'<span class="badge bg-secondary">Inactivo</span>'}</td>
      <td>${c.fecha_registro||''}</td>
      <td class="text-end">
        <div class="btn-group btn-group-sm">
          <button class="btn btn-outline-primary" data-editar="${c.id_cliente}"><i class="bi bi-pencil-square"></i></button>
          <button class="btn btn-outline-danger" data-eliminar="${c.id_cliente}"><i class="bi bi-trash"></i></button>
          <button class="btn btn-outline-secondary" data-toggle="${c.id_cliente}" data-active="${isActive?1:0}">
            <i class="bi ${isActive?'bi-toggle-on':'bi-toggle-off'}"></i>
          </button>
        </div>
      </td>`;
      tbl.appendChild(tr);
    }
  }

  tbl.addEventListener('click',async(e)=>{
    const btn=e.target.closest('button'); if(!btn)return;
    const id=+btn.dataset.editar||+btn.dataset.eliminar||+btn.dataset.toggle;
    if(btn.dataset.eliminar){
      if(!confirm('¿Eliminar cliente?'))return;
      await fetch(api('action=delete'),{method:'POST',body:new URLSearchParams({id_cliente:id})});
      listar();
      return;
    }
    if(btn.dataset.toggle){
      await fetch(api('action=toggle'),{method:'POST',body:new URLSearchParams({id_cliente:id})});
      listar();
      return;
    }
  });

  document.addEventListener('DOMContentLoaded',()=>listar());
})();
