(() => {
  const base = location.pathname.replace(/\/public\/?$/, '') + '/public';
  const api  = (params) => `${base}/?r=admin_usuarios_api&${params}`;

  const nameRe = /^[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,60}$/u;
  const userRe = /^[A-Za-z0-9._-]{3,30}$/;

  const $ = s => document.querySelector(s);
  const $$ = s => document.querySelectorAll(s);

  const modal = $('#modalUsuario');
  const frm   = $('#frmUsuario');
  const tbl   = $('#tblUsuarios tbody');

  let editId = 0;

  function openModal(title, data={}) {
    $('#modalTitle').textContent = title;
    modal.style.display = 'block';
    editId = data.id_usuario || 0;

    // reset
    frm.reset();
    $('#id_usuario').value = editId;
    $('#usuario').value = data.usuario || '';
    $('#rol').value     = data.rol || 'empleado';
    $('#nombres').value = data.nombres || '';
    $('#apellidos').value = data.apellidos || '';
    $('#correo').value  = data.correo || '';
    $('#estado').value  = data.estado || 'activo';
    $('#password').value = '';
  }

  function closeModal(){ modal.style.display='none'; }

  $('#btnNuevo')?.addEventListener('click', () => openModal('Nuevo usuario'));
  $('#btnCerrar')?.addEventListener('click', closeModal);

  // Buscar/listar
// Buscar/listar
async function listar() {
  const q = encodeURIComponent($('#q').value || '');
  try {
    const res  = await fetch(api(`action=list&q=${q}&page=1&per=20`));
    const txt  = await res.text();          // leemos texto primero
    let json;
    try {
      json = JSON.parse(txt);               // intentamos parsear JSON
    } catch (e) {
      console.error('Respuesta no JSON del API:', txt);
      throw e;
    }
    if (!res.ok) throw new Error(json.msg || 'Error API');
    renderTabla(json.items || []);
  } catch (err) {
    console.error('Error listar:', err);
    alert('Error al listar: ' + err.message);
  }
}

 function renderTabla(items) {
  tbl.innerHTML = '';
  for (const u of items) {
    const isActive = String(u.estado || '').toLowerCase().startsWith('activo');
    const verifIcon = u.correo_verificado ? '✔' : '✖';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${u.id_usuario}</td>
      <td>${u.usuario}</td>
      <td>${u.nombres} ${u.apellidos}</td>
      <td>${u.correo}</td>
      <td>${u.rol}</td>
      <td>${u.estado}</td>
      <td>${verifIcon}</td>
      <td>
        <button data-editar="${u.id_usuario}">Editar</button>
        <button data-eliminar="${u.id_usuario}">Eliminar</button>
        <button data-toggle="${u.id_usuario}">${isActive ? 'Desactivar' : 'Activar'}</button>
        ${u.correo_verificado ? '' : `<button data-reenviar="${u.id_usuario}">Reenviar verificación</button>`}
      </td>
    `;
    tbl.appendChild(tr);
  }
}


  $('#btnBuscar')?.addEventListener('click', listar);
  $('#q')?.addEventListener('keydown', e => { if (e.key==='Enter') { e.preventDefault(); listar(); }});

  // Delegación de acciones
  tbl.addEventListener('click', async (e) => {
    const btn = e.target.closest('button'); if (!btn) return;
    const id = +btn.dataset.editar || +btn.dataset.eliminar || +btn.dataset.toggle || +btn.dataset.reenviar;

    if (btn.dataset.editar) {
      const r = await fetch(api(`action=get&id=${id}`)); const j = await r.json();
      openModal('Editar usuario', j.data || {});
    }
    if (btn.dataset.eliminar) {
      if (!confirm('¿Eliminar usuario?')) return;
      await fetch(api('action=delete'), { method:'POST', body: formData({id_usuario:id}) });
      listar();
    }
    if (btn.dataset.toggle) {
      await fetch(api('action=toggle'), { method:'POST', body: formData({id_usuario:id}) });
      listar();
    }
    if (btn.dataset.reenviar) {
      location.href = `${base}/?r=admin_usuarios_resend_verif&id=${id}`;
    }
  });

  // Validación del formulario
  frm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const d = Object.fromEntries(new FormData(frm).entries());

    if (!userRe.test(d.usuario))            return alert('Usuario inválido (3-30, solo letras/números . _ -)');
    if (!nameRe.test(d.nombres))            return alert('Nombres: solo letras y espacios (2-60).');
    if (!nameRe.test(d.apellidos))          return alert('Apellidos: solo letras y espacios (2-60).');
    if (!/.+@.+\..+/.test(d.correo))        return alert('Correo inválido.');
    if (!editId && (!d.password || d.password.length < 6)) return alert('Password mínimo 6.');

    const action = editId ? 'update' : 'create';
    const fd = new FormData(frm);
    fd.append('action', action);
    const res = await fetch(api(`action=${action}`), { method:'POST', body: fd });
    const j = await res.json();
    if (!j.ok) return alert(j.msg || 'Error');

    closeModal(); listar();
  });

  function formData(obj) { const fd = new FormData(); Object.entries(obj).forEach(([k,v])=>fd.append(k,v)); return fd; }

  // init
  listar();
})();
