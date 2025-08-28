(() => {
  const API = (window.APP && window.APP.api && window.APP.api.usuarios) || '?r=admin_usuarios_api';

  const $  = (s, c=document) => c.querySelector(s);
  const tbl = $('#tblUsuarios');
  const pag = $('#paginacion');
  const lblTotal = $('#lblTotal');
  const frm = $('#frmUsuario');
  const modalEl = $('#modalUsuario');
  const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
  const ttl = $('#ttlModal');

  let page = 1, per = 10, q = '';

  const escapeHtml = s => (s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

  async function fetchJSON(url, opts) {
    const r = await fetch(url, opts);
    if (!r.ok) throw new Error(await r.text() || ('HTTP ' + r.status));
    return r.json();
  }

  async function cargar(){
    const data = await fetchJSON(`${API}&action=list&q=${encodeURIComponent(q)}&page=${page}&per=${per}`);
    const rows = data.data || [];
    tbl.innerHTML = rows.map(r => `
      <tr>
        <td>${r.id_usuario}</td>
        <td>${escapeHtml(r.usuario)}</td>
        <td>${escapeHtml(r.rol)}</td>
        <td>${escapeHtml(r.nombres)} ${escapeHtml(r.apellidos)}</td>
        <td>${escapeHtml(r.correo)}</td>
        <td><span class="badge bg-${r.estado==='activo'?'success':'secondary'}">${escapeHtml(r.estado)}</span></td>
        <td>${escapeHtml(r.fecha_creacion ?? '')}</td>
        <td class="d-flex gap-1">
          <button class="btn btn-sm btn-outline-primary" data-editar="${r.id_usuario}"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-warning" data-toggle="${r.id_usuario}"><i class="bi bi-power"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-eliminar="${r.id_usuario}"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    `).join('');

    const total = data.total ?? 0;
    const pages = Math.max(1, Math.ceil(total / per));
    lblTotal.textContent = `${total} usuario(s) • pág ${page}/${pages}`;
    pag.innerHTML = '';
    for (let i=1;i<=pages;i++){
      const li = document.createElement('li');
      li.className = `page-item ${i===page?'active':''}`;
      li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
      li.addEventListener('click', e => { e.preventDefault(); page=i; cargar(); });
      pag.appendChild(li);
    }
  }

  $('#btnBuscar')?.addEventListener('click', () => { q = $('#txtBuscar').value.trim(); page=1; cargar(); });
  $('#txtBuscar')?.addEventListener('keydown', e => { if (e.key==='Enter'){ e.preventDefault(); $('#btnBuscar').click(); } });

  $('#btnNuevo')?.addEventListener('click', () => {
    frm.reset();
    $('#id_usuario').value = '';
    $('#password').value = '';
    ttl.textContent = 'Nuevo usuario';
    frm.classList.remove('was-validated');
    modal?.show();
  });

  tbl?.addEventListener('click', async (e)=>{
    const idE = e.target.closest('[data-editar]')?.dataset.editar;
    const idT = e.target.closest('[data-toggle]')?.dataset.toggle;
    const idD = e.target.closest('[data-eliminar]')?.dataset.eliminar;

    if (idE){
      const r = await fetchJSON(`${API}&action=get&id=${idE}`);
      const u = r.data;
      if (!u) return;
      $('#id_usuario').value = u.id_usuario;
      $('#usuario').value = u.usuario ?? '';
      $('#correo').value = u.correo ?? '';
      $('#nombres').value = u.nombres ?? '';
      $('#apellidos').value = u.apellidos ?? '';
      $('#rol').value = u.rol ?? 'empleado';
      $('#estado').value = u.estado ?? 'activo';
      $('#password').value = '';
      ttl.textContent = 'Editar usuario';
      frm.classList.remove('was-validated');
      modal?.show();
    }

    if (idT){
      if (!confirm('¿Cambiar estado?')) return;
      await fetchJSON(API + '&action=toggle', { method:'POST', body: toForm({ id_usuario:idT }) });
      cargar();
    }

    if (idD){
      if (!confirm('¿Eliminar usuario?')) return;
      await fetchJSON(API + '&action=delete', { method:'POST', body: toForm({ id_usuario:idD }) });
      cargar();
    }
  });

  frm?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if (!frm.checkValidity()){ frm.classList.add('was-validated'); return; }
    const id = $('#id_usuario').value;
    const action = id ? 'update' : 'create';
    const body = new FormData(frm);
    const r = await fetchJSON(API + '&action=' + action, { method:'POST', body });
    if (r.ok){ modal?.hide(); cargar(); } else { alert(r.msg || 'Error'); }
  });

  function toForm(obj){ const fd=new FormData(); Object.entries(obj).forEach(([k,v])=>fd.append(k,v)); return fd; }

  document.addEventListener('DOMContentLoaded', cargar);
})();
