// assets/js/admin_inventario.js
// MODULO INVENTARIOS / Juliana Lugo /
(function () {
  if (window.__INVENTARIO_JS_BOUND__) return;
  window.__INVENTARIO_JS_BOUND__ = true;

  'use strict';

  // =========================
  // Endpoints (usa override del index)
  // =========================
  // URL base para inventario (permite override desde index.php)
  const apiBase          = window.INVENTARIO_API   || (location.pathname.replace(/\/public\/?$/, '') + '/public/?r=admin_inventario_api');
   // URL base para productos
  const apiProductosBase = window.PRODUCTO_API     || (location.pathname.replace(/\/public\/?$/, '') + '/public/?r=admin_productos_api');
   // Función helper para armar URLs del API inventario
  const api          = (params='') => `${apiBase}&${params}`;
    // Función helper para productos
  const apiProductos = (params='') => `${apiProductosBase}&${params}`;

  // =========================
  // Estado
  // =========================
  const state = { page: 1, per: 10, total: 0, q: '' };
  let __SEQ__ = 0;

  // =========================
  // Selectores (alineados a tu index.php)
  // =========================
  const $  = (s) => document.querySelector(s);
  const $$ = (s) => Array.from(document.querySelectorAll(s));
  // Elementos de la tabla
  const tbl       = $('#tblInventario');
  const tblBody   = $('#tblInventario tbody');
  // Buscador
  const qInput    = $('#qInventario');
  const btnBuscar = $('#btnBuscarInventario');
  // Paginación
  const perSel    = $('#perPageInventario');
  const pager     = $('#paginadorInventario');
  const totalEl   = $('#totalInventario');
  // Botón nuevo
  const btnNuevo  = $('#btnNuevoInventario');
  // Modal
  const modal     = $('#modalInventario');
  const modalTit  = $('#modalTitleInventario');
  const frm       = $('#frmInventario');
  // Select de productos
  const selProd   = $('#producto_id');

  // =========================
  // Estilos (toasts, inputs validados, tabla)
  // =========================
  function ensureUIStyles() {
    if (document.querySelector('#_nvtoast_css')) return; // evita duplicar
    const css = document.createElement('style');
    css.id = '_nvtoast_css';
    css.textContent = `
      /* ===== TOASTS ===== */
      .nvtoast-host{ position:fixed; right:16px; bottom:16px; display:flex; flex-direction:column; gap:10px; z-index:99999; pointer-events:none }
      .nvtoast{ pointer-events:auto; min-width:280px; max-width:420px; padding:10px 12px; border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,.2); background:#111; color:#fff; display:flex; align-items:center; gap:10px; opacity:.98; transform:translateY(20px); animation:nvtoastSlideIn .2s ease-out forwards; border:1px solid transparent }
      .nvtoast .nvtoast-close{ margin-left:auto; background:none; border:0; color:#fff; opacity:.85; cursor:pointer }
      .nvtoast-success{ background:#198754; color:#eafff4; border-color:#198754 } /* crear (verde) */
      .nvtoast-danger{  background:#dc3545; color:#fff0f1; border-color:#dc3545 }  /* eliminar (rojo) */
      .nvtoast-warning{ background:#ffc107; color:#3a2f00; border-color:#ffc107 }  /* actualizar (amarillo) */
      .nvtoast-info{    background:#0d6efd; color:#eaf2ff; border-color:#0d6efd }  /* editar/toggle (azul) */
      .nvtoast .dot{ width:8px; height:8px; border-radius:50%; background:currentColor; opacity:.85 }
      @keyframes nvtoastSlideIn{ to{ transform:translateY(0) } }
      .flash-success{ animation:nvflashGreen .9s ease-in-out }  @keyframes nvflashGreen{ 0%,100%{background:transparent} 30%{background:#e8f8f0} }
      .flash-danger{  animation:nvflashRed   .9s ease-in-out }  @keyframes nvflashRed  { 0%,100%{background:transparent} 30%{background:#fdecef} }

      /* ===== VALIDACIÓN EN VIVO (como la imagen) ===== */
      .form-control, .form-select { border-radius: .5rem; }
      .is-valid.form-control, .is-valid.form-select{
        border-color:#198754 !important; box-shadow:0 0 0 .2rem rgba(25,135,84,.15) !important;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23198754' class='bi bi-check-lg' viewBox='0 0 16 16'%3E%3Cpath d='M13.485 1.89a.75.75 0 0 1 .025 1.06l-7.25 7.5a.75.75 0 0 1-1.08.02L2.5 8.72a.75.75 0 1 1 1.06-1.06l1.975 1.975 6.72-6.95a.75.75 0 0 1 1.06.205Z'/%3E%3C/svg%3E");
        background-repeat:no-repeat; background-position:right .8rem center; background-size:16px;
        padding-right:2.4rem;
      }
      .is-invalid.form-control, .is-invalid.form-select{
        border-color:#dc3545 !important; box-shadow:0 0 0 .2rem rgba(220,53,69,.12) !important;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23dc3545' class='bi bi-exclamation-circle' viewBox='0 0 16 16'%3E%3Cpath d='M7.001 11a1 1 0 1 0 2 0 1 1 0 0 0-2 0z'/%3E%3Cpath d='M7.002 4a.905.905 0 0 1 .998.917l-.35 3.5a.65.65 0 1 1-1.296 0l-.35-3.5A.905.905 0 0 1 7.002 4z'/%3E%3Cpath d='M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z'/%3E%3C/svg%3E");
        background-repeat:no-repeat; background-position:right .8rem center; background-size:16px;
        padding-right:2.4rem;
      }

      /* Feedback por defecto oculto */
          .invalid-feedback{ display:none; }
          .valid-feedback{ display:none; }

          /* Mostrar SOLO cuando corresponda */
          .is-invalid ~ .invalid-feedback{ display:block; color:#dc3545; }     /* rojo */
          .is-valid   ~ .valid-feedback  { display:block; color:#198754; }     /* verde */

          /* Si es válido, ocultar el texto rojo */
          .is-valid ~ .invalid-feedback{ display:none; }

          /* Si es inválido, ocultar el texto verde (por si lo hubiera) */
          .is-invalid ~ .valid-feedback{ display:none; }

      /* ===== TABLA moderna (bordes y esquinas) ===== */
      .table-modern { border-collapse:separate; border-spacing:0; border:1px solid #e9ecef; border-radius: .75rem; overflow:hidden; }
      .table-modern thead th{ background:#198754; color:#fff; border-bottom:1px solid rgba(255,255,255,.25); }
      .table-modern tbody tr{ border-bottom:1px solid #eef2f5; }
      .table-modern tbody tr:last-child{ border-bottom:0; }
      .table-modern tbody tr:hover{ background:#f8fafb; }
    `;
    document.head.appendChild(css);

    // añade clase de estilo a la tabla

  if (tbl && !tbl.classList.contains('table-modern')) {
    tbl.classList.add('table-modern');
  }
}
  // TOAST helpers
  function ensureToastHost() {
    let host = document.querySelector('#nvtoastHost');
    if (!host) {
      host = document.createElement('div');
      host.id = 'nvtoastHost';
      host.className = 'nvtoast-host';
      document.body.appendChild(host);
    }
    return host;
  }
    // Mostrar mensaje tipo toast
  function uiToast(msg, variant='info', ms=3200) {
    try {
      ensureUIStyles();
      const host = ensureToastHost();
      const el = document.createElement('div');

      // Clase según tipo (success, danger, etc.)
      el.className = `nvtoast nvtoast-${variant}`;
      // Sanitiza texto (evita XSS)
      el.innerHTML = `
        <div class="dot"></div>
        <div class="nvtoast-msg">${(msg ?? '').toString()
          .replace(/&/g,'&amp;').replace(/</g,'&lt;')
          .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;')}</div>
        <button type="button" class="nvtoast-close" aria-label="Cerrar">✕</button>
      `;
      host.appendChild(el);
      const close = () => el.remove();
      
      // Botón cerrar
      el.querySelector('.nvtoast-close')?.addEventListener('click', close);

      // Auto cierre
      const timer = setTimeout(close, ms);

      // Si el mouse entra, no se cierra
      el.addEventListener('mouseenter', () => clearTimeout(timer), { once:true });
    } catch {
       // fallback
      alert((variant.toUpperCase()) + ': ' + msg);
    }
  }

  // =========================
  // MODAL DE CONFIRMACIÓN
  // =========================

  // Confirm modal (usa tu HTML si existe)
  function ensureConfirmModal(){
    if ($('#confirmModal')) return;
    const wrap = document.createElement('div');
    wrap.innerHTML = `
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmTitle">Confirmar</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body" id="confirmBody">¿Seguro?</div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success" id="btnOkConfirm">Sí, continuar</button>
          </div>
        </div>
      </div>
    </div>`;
    document.body.appendChild(wrap.firstElementChild);
  }
    // Función para mostrar confirmación (Promise)
  function uiConfirm({title='Confirmar acción', body='¿Seguro?', confirmText='Sí, continuar', variant='success'} = {}) {
    const modalEl = $('#confirmModal');
        // Si no hay bootstrap usa confirm normal
    if (!modalEl || !window.bootstrap) return Promise.resolve(confirm(body));

        // Configura textos
    $('#confirmTitle').textContent = title;
    $('#confirmBody').innerHTML = (body ?? '').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;')
                                  .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;')
                                  .replace(/\n/g,'<br>');
    const okBtn = $('#btnOkConfirm');
        // Cambia color del botón según acción
    okBtn.className = 'btn ' + (variant === 'danger' ? 'btn-outline-danger'
                          : variant === 'warning' ? 'btn-outline-secondary'
                          : 'btn-success');
    okBtn.textContent = confirmText;

    return new Promise(resolve => {
      const bs = new bootstrap.Modal(modalEl, { backdrop: 'static' });
      const onOk = () => { cleanup(); bs.hide(); resolve(true); };
      const onHide = () => { cleanup(); resolve(false); };
      const cleanup = () => {
        okBtn.removeEventListener('click', onOk);
        modalEl.removeEventListener('hidden.bs.modal', onHide);
      };
      okBtn.addEventListener('click', onOk);
      modalEl.addEventListener('hidden.bs.modal', onHide, { once:true });
      bs.show();
    });
  }

  // =========================
  // Utilidades
  // =========================
    // Convierte objeto a FormData
  function formData(obj){ const fd = new FormData(); Object.entries(obj).forEach(([k,v])=>fd.append(k,v)); return fd; }
    // Convierte respuesta a JSON de forma segura
  async function resToJsonSafe(res){
    try { return await res.json(); }
    catch { return { ok:false, msg:'Respuesta inválida del servidor' }; }
  }

  // =========================
  // Cache productos
  // =========================
  const productosCache = new Map();
    // Carga productos y los guarda en memoria

  async function precargarProductos() {
    try {
      const res = await fetch(apiProductos('action=list&per=1000'));
      const j = await resToJsonSafe(res);
      const items = j.items || j.data || [];
      productosCache.clear();
      for (const p of items) productosCache.set(String(p.id), p);
    } catch { /* silencioso */ }
  }

  // =========================
  // LISTADO
  // =========================

  // Muestra loading
  function setLoading(on) {
    if (!tblBody) return;
    if (on) {
      tblBody.innerHTML = `<tr><td colspan="10" class="py-4 text-center">
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

    const nextDis = state.page >= pages ? ' disabled' : '';
    html += `<li class="page-item${nextDis}">
      <button class="page-link" data-page="${state.page + 1}" aria-label="Siguiente">&raquo;</button>
    </li>`;

    pager.innerHTML = html;
  }
  function updateTotal() {
    if (totalEl) totalEl.textContent = `${state.total} registro(s)`;
  }
  // Función principal para listar inventario
  async function listar(page = state.page) {
    state.page = Math.max(1, page|0);
    const q = encodeURIComponent(state.q || '');
    setLoading(true);
    const seq = ++__SEQ__;

    try {
      const res = await fetch(api(`action=list&q=${q}&page=${state.page}&per=${state.per}`));
      const j   = await resToJsonSafe(res);

      // Evita respuestas viejas
      if (seq !== __SEQ__) return;

      const items = j.items || j.data || [];
      state.total = +j.total || items.length;
      state.page  = +j.page  || state.page;
      state.per   = +j.per   || state.per;

      const pages = Math.max(1, Math.ceil((state.total || 0) / (state.per || 10)));
      if (state.total > 0 && state.page > pages) return listar(pages);

      if (!items.length) {
        tblBody.innerHTML = `<tr><td colspan="10" class="text-center text-muted py-3">Sin resultados</td></tr>`;
        renderPager(); updateTotal(); return;
      }

      renderTabla(items);
      renderPager();
      updateTotal();
    } catch (e) {
      if (seq !== __SEQ__) return;
      tblBody.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-3">No se pudo cargar.</td></tr>`;
      uiToast('No se pudo cargar el inventario.', 'danger');
    }
  }

  function badgeEstado(estado) {
    const v = String(estado||'').toLowerCase();
    if (v.startsWith('agota')) return '<span class="badge bg-danger-subtle text-danger border">Agotado</span>';
    if (v.startsWith('pend'))  return '<span class="badge bg-warning-subtle text-warning border">Pendiente</span>';
    return '<span class="badge bg-success-subtle text-success border">Disponible</span>';
  }

  const alertados = new Set();

  // =========================
  // RENDER TABLA
  // =========================

  function renderTabla(items) {
    if (!tblBody) return;
    tblBody.innerHTML = '';
    for (const i of items) {

      const prodId = String(i.producto_id ?? '');
      const prod = productosCache.get(prodId);
      const nombreProducto = i.producto_nombre ? escapeHtml(i.producto_nombre) : `ID: ${escapeHtml(prodId)}`;
      const stock = +i.stock || 0;
      const punto = +i.punto_reorden || 0;
      //REABASTECIMIENTO
      if (stock <= punto && stock > 0 && !alertados.has(i.id)) {
        alertados.add(i.id);
        uiToast(`⚠ Producto "${nombreProducto}" está en punto de reorden`, 'warning', 5000);
      }

            // Crear fila
      const tr = document.createElement('tr');
      tr.dataset.id = i.id;
      const stockMin = +i.stock_minimo || 0;


      const stockMax = +i.stock_maximo || 0;
      // 🎨 COLORES SEGÚN STOCK
      if (stock === 0) {
        tr.style.backgroundColor = '#fdecef'; // rojo suave
      } else if (stock <= punto) {
        tr.style.backgroundColor = '#fff3cd'; // amarillo
      }

      const isAgotado = String(i.estado||'').toLowerCase().startsWith('agota');
      // HTML de la fila
      tr.innerHTML = `
        <td>${i.id}</td>
        <td class="fw-semibold">${nombreProducto}</td>
        <td>${escapeHtml(i.codigo_interno || 'N/A')}</td>
        <td>${i.stock ?? 0}</td>
        <td>${i.stock_minimo ?? 0}</td>
        <td>${i.stock_maximo ?? 0}</td>
        <td>${i.punto_reorden ?? 0}</td>
        <td>${escapeHtml(i.ubicacion || 'N/A')}</td>
        <td data-col="estado">${badgeEstado(i.estado)}</td>
        <td class="text-end">
          <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" title="Editar" data-editar="${i.id}">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-outline-danger" title="Eliminar" data-eliminar="${i.id}">
              <i class="bi bi-trash"></i>
            </button>
            <button class="btn btn-outline-secondary"
                    title="${isAgotado ? 'Marcar disponible' : 'Marcar agotado'}"
                    data-toggle="${i.id}"
                    data-agotado="${isAgotado ? 1 : 0}">
              <i class="bi ${isAgotado ? 'bi-toggle-off' : 'bi-toggle-on'}"></i>
            </button>
          </div>
        </td>
      `;
      tblBody.appendChild(tr);
    }
  }

  function escapeHtml(s) {
    return (s ?? '').toString().replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[m]));
  }

  // =========================
  // Paginador & filtros
  // =========================
  pager?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-page]'); if (!btn) return;
    const to = parseInt(btn.dataset.page, 10);
    const pages = Math.max(1, Math.ceil((state.total || 0) / (state.per || 10)));
    if (to >= 1 && to <= pages && to !== state.page) listar(to);
  });

  perSel?.addEventListener('change', (e) => {
    state.per = parseInt(e.target.value, 10) || 10;
    listar(1);
  });
  // =========================
  // EVENTOS
  // =========================

  // Buscar
  btnBuscar?.addEventListener('click', () => {
    state.q = qInput?.value.trim() || '';
    listar(1);
  });
  // Enter buscar
  qInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); state.q = e.target.value.trim(); listar(1); }
  });

  // =========================
  // Modal Crear/Editar
  // =========================
    function ensureHidden() {
    if (!modal) return;
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    modal.style.display = 'none';
    document.body.classList.remove('modal-open');
    $$('.modal-backdrop').forEach(b => b.remove());
  }
  if (modal && window.bootstrap) {
    const bs = new bootstrap.Modal(modal, { backdrop: 'static' });
    modal.addEventListener('hidden.bs.modal', () => {
      fillForm({});
      resetLiveValidation();
    });
    modal.__getInstance = () => bootstrap.Modal.getOrCreateInstance(modal, { backdrop: 'static' });
  }

  async function cargarProductosEnSelect() {
    if (!selProd) return;
    if (productosCache.size === 0) await precargarProductos();

    const prev = selProd.value;
    selProd.innerHTML = '<option value="">Seleccione un producto…</option>';
    for (const [id, p] of productosCache.entries()) {
      const opt = document.createElement('option');
      opt.value = id;
      opt.textContent = `${p.nombre || ('ID ' + id)}${p.marca ? ' - ' + p.marca : ''}${p.categoria ? ' ('+p.categoria+')' : ''}`;
      opt.dataset.producto = JSON.stringify(p);
      selProd.appendChild(opt);
    }
    if (prev) selProd.value = prev;
  }

  $('#producto_id')?.addEventListener('change', (e) => {
    const info = $('#infoProducto');
    const opt = e.target.options[e.target.selectedIndex];
    if (!opt || !opt.dataset.producto) { if (info) info.style.display = 'none'; return; }
    try {
      const p = JSON.parse(opt.dataset.producto);
      const set = (id, val) => { const x = $('#'+id); if (x) x.textContent = val; };
      set('prodNombre', p.nombre || 'Sin nombre');
      set('prodMarca', p.marca || 'Sin marca');
      set('prodCategoria', p.categoria || 'Sin categoría');
      set('prodStock', p.stock_actual ?? '0');
      set('prodPrecio', p.precio_venta ?? '0.00');
      const img = $('#prodImg');
      if (img) {
        if (p.imagen) { img.src = p.imagen; img.style.display = 'block'; img.onerror = () => img.style.display = 'none'; }
        else img.style.display = 'none';
      }
      if (info) info.style.display = 'block';

      const codigoInternoInput = $('#codigo_interno');
      if (codigoInternoInput && !codigoInternoInput.value.trim()) {
        const ts = Date.now().toString().slice(-4);
        codigoInternoInput.value = `INV-${p.id}-${ts}`;
      }
    } catch {}
  });

  function fillForm(data = {}) {
    const d = {
      id: '', producto_id:'', codigo_interno:'', stock:'', stock_minimo:'', stock_maximo:'',
      punto_reorden:'', ubicacion:'', estado:'disponible', ...data
    };
    d.estado = ['agotado','pendiente','disponible'].includes(String(d.estado||'').toLowerCase())
      ? String(d.estado).toLowerCase() : 'disponible';

    const set = (id,val)=>{ const elx = frm?.querySelector('#'+id); if(elx) elx.value = val ?? ''; };
    set('idInventario', d.id || '');
    set('producto_id', d.producto_id);
    set('codigo_interno', d.codigo_interno);
    set('stock', d.stock);
    set('stock_minimo', d.stock_minimo);
    set('stock_maximo', d.stock_maximo);
    set('punto_reorden', d.punto_reorden);
    set('ubicacion', d.ubicacion);
    const est = frm?.querySelector('#estado'); if (est) est.value = d.estado;
  }
  function openEditor(data, title) {
    fillForm(data);
    resetLiveValidation();
    if (modalTit) modalTit.textContent = title || 'Nuevo inventario';
     ensureHidden();
    if (modal && window.bootstrap) {
      
      const bs = bootstrap.Modal.getOrCreateInstance(modal);
      bs.show();
    } else if (modal) {
      modal.style.display = 'block';
    }
  }
  function closeEditor(){
    if (modal && window.bootstrap) {
      const bs = bootstrap.Modal.getOrCreateInstance(modal);
      bs.hide();
    }
  }

  btnNuevo?.addEventListener('click', async () => {
    await cargarProductosEnSelect();
    openEditor({}, 'Nuevo inventario');
  });


    modal.addEventListener('hidden.bs.modal', () => {
  document.activeElement?.blur();
});
  // =========================
  // Acciones de tabla
  // =========================
  tblBody?.addEventListener('click', async (e) => {
    const btn = e.target.closest('button'); if (!btn) return;
    const id  = +btn.dataset.editar || +btn.dataset.eliminar || +btn.dataset.toggle;

    // Editar (azul)
    if (btn.dataset.editar) {
      try {
        const r = await fetch(api(`action=get&id=${id}`));
        const j = await resToJsonSafe(r);
        if (!j || !j.data) throw new Error('No se pudo cargar el inventario.');
        await cargarProductosEnSelect();
        openEditor(j.data, 'Editar inventario');
        uiToast('Editando registro…', 'info'); // azul
      } catch (err) { uiToast(err.message || 'Error al cargar inventario', 'danger'); }
      return;
    }

    // Eliminar → rojo
    if (btn.dataset.eliminar) {
      const ok = await uiConfirm({
        title:'Eliminar registro',
        body:'¿Seguro que deseas eliminar este registro de inventario?\nEsta acción no se puede deshacer.',
        confirmText:'Sí, eliminar',
        variant:'danger'
      });
      if (!ok) return;
      try {
        const r = await fetch(api('action=delete'), { method:'POST', body: formData({id}) });
        const j = await resToJsonSafe(r);
        if (!j.ok) throw new Error(j.msg || 'No se pudo eliminar');
        uiToast(j.msg || 'Eliminado exitosamente', 'danger'); // rojo
        listar(state.page);
      } catch (err) { uiToast(err.message || 'Error al eliminar', 'danger'); }
      return;
    }

    // Toggle estado → azul
    if (btn.dataset.toggle) {
      const agotado = btn.dataset.agotado === '1';
      const nuevo = agotado ? 'disponible' : 'agotado';
      const ok = await uiConfirm({
        title: `Marcar ${nuevo}`,
        body: `¿Seguro que deseas marcar este registro como "${nuevo}"?`,
        confirmText: 'Sí, continuar',
        variant: agotado ? 'success' : 'warning'
      });
      if (!ok) return;

      try {
        const r = await fetch(api('action=toggle'), { method:'POST', body: formData({id}) });
        const j = await resToJsonSafe(r);
        if (!j.ok) throw new Error(j.msg || 'No se pudo cambiar el estado');

        applyToggleToRow(id, j.estado);
        uiToast(j.msg || 'Estado actualizado exitosamente', 'info'); // azul
        listar(state.page);
      } catch (err) { uiToast(err.message || 'Error al cambiar estado', 'danger'); }
      return;
    }
  });

  function applyToggleToRow(id, nuevoEstado) {
    const tr = tblBody?.querySelector(`tr[data-id="${id}"]`); if (!tr) return;
    const tdEstado = tr.querySelector('td[data-col="estado"]');
    if (tdEstado) tdEstado.innerHTML = badgeEstado(nuevoEstado);

    const isAgotado = String(nuevoEstado||'').toLowerCase().startsWith('agota');
    const btn = tr.querySelector(`button[data-toggle="${id}"]`);
    if (btn) {
      btn.dataset.agotado = isAgotado ? '1' : '0';
      btn.title = isAgotado ? 'Marcar disponible' : 'Marcar agotado';
      const icon = btn.querySelector('i');
      if (icon) icon.className = `bi ${isAgotado ? 'bi-toggle-off' : 'bi-toggle-on'}`;
    }

    tr.classList.remove('flash-success','flash-danger');
    void tr.offsetWidth;
    tr.classList.add(isAgotado ? 'flash-danger' : 'flash-success');
  }

  // =========================
  // Validación: en vivo + al enviar
  // =========================
  function mark(el){
    if (!el) return;
    if (el.checkValidity()) {
      el.classList.add('is-valid'); el.classList.remove('is-invalid');
    } else {
      el.classList.add('is-invalid'); el.classList.remove('is-valid');
    }
  }
  function attachLiveValidation() {
    if (!frm) return;
    const controls = frm.querySelectorAll('input, select, textarea');
    controls.forEach(el => {
      el.addEventListener('input', () => mark(el));
      el.addEventListener('change', () => mark(el));
      el.addEventListener('blur', () => mark(el));
    });
  }
  function resetLiveValidation() {
    if (!frm) return;
    frm.classList.remove('was-validated');
    frm.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
      el.classList.remove('is-valid','is-invalid');
    });
  }

  // =========================
  // ENVÍO FORMULARIO
  // =========================

  frm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    // pinta estado actual de todos los campos
    frm.querySelectorAll('input, select, textarea').forEach(mark);

    if (!frm.checkValidity()) {
      frm.classList.add('was-validated');
      uiToast('⚠ Por favor complete los campos requeridos', 'warning');
      return;
    }

      const fd = new FormData(frm);

      // =========================
      // VALIDACIONES PERSONALIZADAS FUERTES
      // =========================
      const stock        = Number(fd.get('stock'));
      const stockMin     = Number(fd.get('stock_minimo'));
      const stockMax     = Number(fd.get('stock_maximo'));
      const puntoReorden = Number(fd.get('punto_reorden'));

      // DEBUG (opcional, para ver si entra)
      console.log({ stock, stockMin, stockMax });

      // mínimo > máximo
      if (stockMin > stockMax) {
        uiToast('El stock mínimo no puede ser mayor que el stock máximo', 'danger');
        return ;
      }

      // stock < mínimo
      if (stock < stockMin) {
        $('#stock')?.classList.add('is-invalid');
        $('#stock_minimo')?.classList.add('is-invalid');

        uiToast(`Stock (${stock}) no puede ser menor que mínimo (${stockMin})`, 'danger');
        return ;
      }

      // stock > máximo
      if (stock > stockMax) {
        $('#stock')?.classList.add('is-invalid');
        $('#stock_maximo')?.classList.add('is-invalid');

        uiToast(`Stock (${stock}) no puede ser mayor que máximo (${stockMax})`, 'danger');
        return ;
      }

      // punto de reorden inválido
      if (puntoReorden > stockMax) {
        uiToast('El punto de reorden no puede ser mayor que el stock máximo', 'warning');
        return ;
      }


    const id   = +fd.get('id');
    const isUpdate = !!id;
    const plain = Object.fromEntries(fd.entries());

    const msg = isUpdate
      ? `¿Guardar cambios del inventario?\n\nCódigo: ${plain.codigo_interno || ''}`
      : `¿Crear nuevo registro de inventario?\n\nCódigo: ${plain.codigo_interno || ''}`;

    const ok = await uiConfirm({
      title: isUpdate ? 'Confirmar guardar' : 'Confirmar creación',
      body: msg,
      confirmText: isUpdate ? 'Sí, guardar' : 'Sí, crear',
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
      const j = await resToJsonSafe(r);
      if (!j.ok) throw new Error(j.msg || 'Error al guardar');

      closeEditor();
      // Crear → verde | Actualizar → amarillo
      uiToast( j.msg || (isUpdate ? 'Actualizado exitosamente' : 'Creado exitosamente'),
               isUpdate ? 'warning' : 'success' );
      listar(isUpdate ? state.page : 1);
    } catch (err) {
      uiToast(err.message || 'Error al guardar', 'danger');
    } finally {
      btnSubmit.disabled = false;
      btnSubmit.innerHTML = prevHtml;
    }
  });

  // =========================
  // Init
  // =========================
    function boot(){
      ensureUIStyles();
      ensureConfirmModal();
      attachLiveValidation();

      // Cargar productos sin bloquear inventario
      precargarProductos().catch(() => {});

      // SIEMPRE cargar tabla
      listar(1);
    }
    // Ejecutar al cargar
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
  window.addEventListener('pageshow', (e) => { if (e.persisted) boot(); });

})();
