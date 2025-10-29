(function () {
  if (window.__PRODUCTOS_JS_BOUND__) return;
  window.__PRODUCTOS_JS_BOUND__ = true;

  'use strict';

  const API = window.PRODUCTO_API || '?r=admin_productos_api';

  const state = { page: 1, per: 10, total: 0, q: '' };
  let __SEQ__ = 0;
  let imagenBase64 = null; // Para guardar la imagen convertida

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
    tbl.innerHTML = `<tr><td colspan="18" class="text-center py-3">
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
      tbl.innerHTML = `<tr><td colspan="18" class="text-center text-danger py-3">Error cargando datos</td></tr>`;
    }
  }

  function renderTabla(items) {
  if (!items.length) {
    tbl.innerHTML = `<tr><td colspan="18" class="text-center text-muted py-3">Sin resultados</td></tr>`;
    return;
  }

  tbl.innerHTML = '';
  for (const p of items) {
    const tr = document.createElement('tr');
    tr.dataset.id = p.id;
    const activo = String(p.estado || '').toLowerCase() === 'activo';

    // Renderizar imagen bonita
    let imagenHtml = '';
    if (p.imagen && p.imagen.trim()) {
      imagenHtml = `<img src="${escapeHtml(p.imagen)}" alt="${escapeHtml(p.nombre)}" class="producto-img" 
        onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'producto-img-placeholder\\'><i class=\\'bi bi-image\\'></i></div>';">`;
    } else {
      imagenHtml = `<div class="producto-img-placeholder"><i class="bi bi-box-seam"></i></div>`;
    }

    tr.innerHTML = `
      <td>${p.id}</td>
      <td class="fw-semibold">${escapeHtml(p.nombre)}</td>
      <td>${escapeHtml(p.categoria ?? '')}</td>
      <td>${escapeHtml(p.marca ?? '')}</td>
      <td>${escapeHtml(p.presentacion ?? '')}</td>
      <td>${fmtNumber(p.stock_actual)}</td>
      <td>${fmtNumber(p.stock_minimo)}</td>
      <td>${escapeHtml(p.descripcion ?? '')}</td>
      <td>${escapeHtml(p.lote ?? '')}</td>
      <td>${escapeHtml(p.fecha_vencimiento ?? '')}</td>
      <td>$${fmtMoney(p.precio_compra)}</td>
      <td>$${fmtMoney(p.precio_venta)}</td>
      <td>${fmtNumber(p.iva)}%</td>
      <td>${escapeHtml(p.codigo_barras ?? '')}</td>
      <td>${escapeHtml(p.ubicacion ?? '')}</td>
      <td>${activo
        ? '<span class="badge bg-success-subtle text-success border">Activo</span>'
        : '<span class="badge bg-secondary-subtle text-secondary border">Inactivo</span>'}</td>
      <td class="text-center">${imagenHtml}</td>
      <td class="text-end">
        <div class="btn-group btn-group-sm">
          <button class="btn btn-outline-primary" data-edit="${p.id}" title="Editar">
            <i class="bi bi-pencil-square"></i>
          </button>
          <button class="btn btn-outline-danger" data-del="${p.id}" title="Eliminar">
            <i class="bi bi-trash"></i>
          </button>
          <button class="btn btn-outline-secondary" data-toggle="${p.id}" title="${activo ? 'Desactivar' : 'Activar'}">
            <i class="bi ${activo ? 'bi-toggle-on' : 'bi-toggle-off'}"></i>
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

  // Manejo de tipo de imagen (URL vs Archivo)
  const tipoURL = $('#tipoURL');
  const tipoArchivo = $('#tipoArchivo');
  const seccionURL = $('#seccionURL');
  const seccionArchivo = $('#seccionArchivo');
  const inputImagen = $('#imagen');
  const archivoImagen = $('#archivoImagen');
  const previewImagen = $('#previewImagen');
  const imgPreview = $('#imgPreview');
  const btnLimpiarImagen = $('#btnLimpiarImagen');

  // Cambiar entre URL y Archivo
  tipoURL?.addEventListener('change', () => {
    if (tipoURL.checked) {
      seccionURL.style.display = 'block';
      seccionArchivo.style.display = 'none';
      imagenBase64 = null;
      archivoImagen.value = '';
    }
  });

  tipoArchivo?.addEventListener('change', () => {
    if (tipoArchivo.checked) {
      seccionURL.style.display = 'none';
      seccionArchivo.style.display = 'block';
      inputImagen.value = '';
    }
  });

  // Preview de URL
  inputImagen?.addEventListener('input', () => {
    const url = inputImagen.value.trim();
    if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
      imgPreview.src = url;
      previewImagen.style.display = 'block';
      imgPreview.onerror = () => {
        previewImagen.style.display = 'none';
        toast('No se pudo cargar la imagen desde esa URL', 'warning');
      };
    } else {
      previewImagen.style.display = 'none';
    }
  });

  // Preview y conversión de archivo a Base64
  archivoImagen?.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    // Validar tamaño (2MB)
    if (file.size > 2 * 1024 * 1024) {
      toast('La imagen es muy grande. Máximo 2MB', 'warning');
      archivoImagen.value = '';
      return;
    }

    // Validar tipo
    if (!file.type.startsWith('image/')) {
      toast('Por favor selecciona una imagen válida', 'warning');
      archivoImagen.value = '';
      return;
    }

    // Convertir a Base64
    const reader = new FileReader();
    reader.onload = (ev) => {
      imagenBase64 = ev.target.result;
      imgPreview.src = imagenBase64;
      previewImagen.style.display = 'block';
      toast('Imagen cargada correctamente', 'success');
    };
    reader.onerror = () => {
      toast('Error al cargar la imagen', 'danger');
    };
    reader.readAsDataURL(file);
  });

  // Limpiar imagen
  btnLimpiarImagen?.addEventListener('click', () => {
    inputImagen.value = '';
    archivoImagen.value = '';
    imagenBase64 = null;
    previewImagen.style.display = 'none';
    imgPreview.src = '';
  });

  $('#btnNuevoProd')?.addEventListener('click', ()=>{
    frm.reset(); frm.classList.remove('was-validated');
    $('#idProducto').value='';
    $('#modalProdTitle').textContent='Nuevo producto';
    
    // Limpiar estados de validación personalizados
    const camposRequeridos = ['#nombre', '#marca', '#precio_compra', '#precio_venta'];
    camposRequeridos.forEach(sel => {
      const campo = $(sel);
      if (campo) {
        campo.classList.remove('is-invalid');
        const feedback = campo.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
          feedback.remove();
        }
      }
    });
    
    // Resetear imagen
    imagenBase64 = null;
    previewImagen.style.display = 'none';
    imgPreview.src = '';
    tipoURL.checked = true;
    seccionURL.style.display = 'block';
    seccionArchivo.style.display = 'none';
    
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
        
        for (const [k,v] of Object.entries(d)){ 
          if (frm[k]) frm[k].value = v ?? ''; 
        }
        
        // Manejar imagen en edición
        if (d.imagen && d.imagen.trim()) {
          inputImagen.value = d.imagen;
          imgPreview.src = d.imagen;
          previewImagen.style.display = 'block';
          tipoURL.checked = true;
          seccionURL.style.display = 'block';
          seccionArchivo.style.display = 'none';
        } else {
          previewImagen.style.display = 'none';
        }
        
        $('#modalProdTitle').textContent='Editar producto';
        bsModal?.show();
      }catch{ toast('Error al cargar','danger'); }
    }

    // Eliminar
    if (btn.dataset.del){
      if (!confirm('¿Está seguro de eliminar este producto?')) return;
      toast('Eliminando producto...', 'info');
      const fd=new FormData(); fd.append('id', id);
      const r = await fetch(`${API}&action=delete`, { method:'POST', body:fd });
      const j = await r.json();
      if (j.ok){ toast('✓ Producto eliminado correctamente','success'); listar(state.page); }
      else toast(j.msg || 'Error al eliminar','danger');
    }

    // Toggle
    if (btn.dataset.toggle){
      const fd=new FormData(); fd.append('id', id);
      const r = await fetch(`${API}&action=toggle`, { method:'POST', body:fd });
      const j = await r.json();
      if (j.ok){ toast('✓ Estado actualizado correctamente','success'); listar(state.page); }
      else toast(j.msg || 'Error al cambiar estado','danger');
    }
  });

  // Función para validar campos manualmente
  function validarCampoRequerido(campo, nombreCampo) {
    const valor = campo.value?.trim();
    
    // Remover feedback anterior si existe
    const feedbackAnterior = campo.nextElementSibling;
    if (feedbackAnterior && feedbackAnterior.classList.contains('invalid-feedback')) {
      feedbackAnterior.remove();
    }
    
    if (!valor) {
      campo.classList.add('is-invalid');
      const feedback = document.createElement('div');
      feedback.className = 'invalid-feedback';
      feedback.textContent = `${nombreCampo} es requerido.`;
      campo.parentNode.insertBefore(feedback, campo.nextSibling);
      return false;
    } else {
      campo.classList.remove('is-invalid');
      return true;
    }
  }

  // Guardar con validaciones personalizadas
  frm?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    
    // Obtener campos
    const nombre = $('#nombre');
    const marca = $('#marca');
    const precioCompra = $('#precio_compra');
    const precioVenta = $('#precio_venta');
    
    // Validar campos obligatorios personalizados
    let esValido = true;
    
    if (nombre && !validarCampoRequerido(nombre, 'Nombre')) esValido = false;
    if (marca && !validarCampoRequerido(marca, 'Marca')) esValido = false;
    if (precioCompra && !validarCampoRequerido(precioCompra, 'Precio Compra')) esValido = false;
    if (precioVenta && !validarCampoRequerido(precioVenta, 'Precio Venta')) esValido = false;
    
    if (!esValido) {
      frm.classList.add('was-validated');
      toast('⚠ Por favor complete todos los campos requeridos', 'warning');
      return;
    }
    
    // Validar que los precios sean mayores a 0
    if (precioCompra && parseFloat(precioCompra.value) <= 0) {
      validarCampoRequerido(precioCompra, 'Precio Compra debe ser mayor a 0');
      toast('⚠ El Precio Compra debe ser mayor a 0', 'warning');
      return;
    }
    
    if (precioVenta && parseFloat(precioVenta.value) <= 0) {
      validarCampoRequerido(precioVenta, 'Precio Venta debe ser mayor a 0');
      toast('⚠ El Precio Venta debe ser mayor a 0', 'warning');
      return;
    }
    
    // Validación nativa del HTML
    if (!frm.checkValidity()){ 
      frm.classList.add('was-validated'); 
      toast('⚠ Por favor revise todos los campos', 'warning');
      return; 
    }

    const fd = new FormData(frm);
    
    // Si se subió un archivo, usar el base64, sino usar la URL
    if (imagenBase64) {
      fd.set('imagen', imagenBase64);
    }
    
    const id = fd.get('id');
    const action = id ? 'update' : 'create';

    toast('Guardando...', 'info');

    try{
      const r = await fetch(`${API}&action=${action}`, { method:'POST', body:fd });
      const j = await r.json();
      if (!j.ok) throw new Error(j.msg || 'Error al guardar');
      bsModal?.hide();
      toast(id ? '✓ Producto actualizado correctamente' : '✓ Producto creado correctamente', 'success');
      imagenBase64 = null; // Limpiar después de guardar
      listar(id?state.page:1);
    }catch(err){ toast('✗ ' + err.message,'danger'); }
  });

  // Agregar validación en tiempo real (opcional pero mejora UX)
  ['#nombre', '#marca', '#precio_compra', '#precio_venta'].forEach(sel => {
    const campo = $(sel);
    if (campo) {
      campo.addEventListener('blur', () => {
        if (campo.value?.trim()) {
          campo.classList.remove('is-invalid');
          const feedback = campo.nextElementSibling;
          if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.remove();
          }
        }
      });
    }
  });

  // Init
  listar(1);
})();