// assets/js/admin_cajero.js
// =====================================================
// Evitar doble carga/binding del script
// =====================================================
(function () {
  if (window.__CAJERO_JS_BOUND__) {
    console.warn('admin_cajero.js ya estaba cargado; ignoro segunda carga');
    return;
  }
  window.__CAJERO_JS_BOUND__ = true;

  'use strict';

  // ===== Base y endpoint =====
  const base = location.pathname.replace(/\/public\/?$/, '') + '/public';
  const api  = (params = '') => `${base}/?r=admin_cajero_api&${params}`;

  // ===== Estado =====
  const carrito   = []; // [{id_producto, nombre, precio, cantidad}]
  const histState = { page: 1, per: 20, total: 0 };

  // ===== Selectores =====
  const $  = (s) => document.querySelector(s);
  const $$ = (s) => Array.from(document.querySelectorAll(s));

  // Campos cliente
  const inpCliNombre   = $('#cliNombre');
  const inpCliApellido = $('#cliApellido');
  const inpCliCedula   = $('#cliCedula');
  const selMetodoPago  = $('#metodoPago');

  // Campos venta
  const selProducto   = $('#productoSelect');
  const inpCantidad   = $('#cantidadProducto');
  const btnAgregar    = $('#btnAgregarProducto');
  const tbodyCarrito  = $('#tablaCarrito tbody');
  const spanTotal     = $('#totalVenta');
  const btnGuardar    = $('#btnGuardarVenta');
  const msgBox        = $('#cjMsg');
  const tbodyHist     = $('#tablaHistorial tbody');

  // =====================================================
  // Toasts
  // =====================================================
  function ensureToastCSS() {
    if (document.getElementById('_cajero_toast_css')) return;
    const css = document.createElement('style');
    css.id = '_cajero_toast_css';
    css.textContent = `
    .toast-host{
      position: fixed;
      right: 16px;
      bottom: 16px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      z-index: 1080;
      pointer-events: none;
    }
    .toast{
      pointer-events: auto;
      min-width: 280px;
      max-width: 420px;
      padding: 10px 12px;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,.18);
      background: #111;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 10px;
      opacity: .98;
      transform: translateY(20px);
      animation: _toastSlideIn .20s ease-out forwards;
      border: 1px solid transparent;
    }
    .toast .btn-close{
      margin-left: auto;
      filter: invert(1);
      opacity: .8;
    }
    .toast-success{ background:#0f5132; color:#d1f7e5; border-color:#0f5132; }
    .toast-danger { background:#842029; color:#ffd7db; border-color:#842029; }
    .toast-warning{ background:#664d03; color:#fff3cd; border-color:#664d03; }
    .toast-info   { background:#0b5ed7; color:#dbe8ff; border-color:#0b5ed7; }
    .toast .dot{ width:8px; height:8px; border-radius:50%; background: currentColor; opacity:.8; }

    @keyframes _toastSlideIn { to { transform: translateY(0); } }
    `;
    document.head.appendChild(css);
  }

  function ensureToastHost(){
    let host = document.getElementById('toastHost');
    if(!host){
      host = document.createElement('div');
      host.id = 'toastHost';
      host.className = 'toast-host';
      document.body.appendChild(host);
    }
    return host;
  }

  function uiToast(msg, variant='info', ms=3500){
    const host = ensureToastHost();
    const t = document.createElement('div');
    t.className = `toast toast-${variant}`;
    t.innerHTML = `
      <div class="dot"></div>
      <div class="toast-msg">${escapeHtml(String(msg))}</div>
      <button class="btn-close" aria-label="Cerrar"></button>
    `;
    host.appendChild(t);

    const close = () => t.remove();
    t.querySelector('.btn-close')?.addEventListener('click', close);
    const timer = setTimeout(close, ms);
    t.addEventListener('mouseenter', () => clearTimeout(timer), { once:true });
  }

  // =====================================================
  // Utils
  // =====================================================
  function escapeHtml(s) {
    return (s ?? '').toString().replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[m]));
  }

  function money(v) {
    const n = Number(v || 0);
    return '$' + n.toLocaleString('es-CO', { minimumFractionDigits: 0 });
  }

  async function resToJsonSafe(res){
    try { return await res.json(); }
    catch { return { ok:false, msg:'Respuesta inválida del servidor' }; }
  }

  function setMsg(text, ok = true) {
    if (!msgBox) return;
    msgBox.textContent = text;
    msgBox.className = ok ? 'text-success small' : 'text-danger small';
  }

  // Impedir "-" y "e" en inputs numéricos (sólo 1 en adelante)
  function bloquearMinusYExponente(input){
    if (!input) return;
    input.addEventListener('keydown', (e) => {
      if (e.key === '-' || e.key === 'e' || e.key === 'E') {
        e.preventDefault();
      }
    });
    input.addEventListener('input', (e) => {
      let v = String(e.target.value || '').replace(/[^\d]/g, '');
      if (v === '' || v === '0') v = '1';
      e.target.value = v;
    });
  }

  // Solo letras y espacios (para nombre / apellido)
  function soloLetras(input){
    if (!input) return;
    input.addEventListener('input', (e) => {
      let v = String(e.target.value || '');
      v = v.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
      e.target.value = v;
    });
  }

  // Solo números, máximo 10 dígitos (para cédula)
  function configurarCedula(input){
    if (!input) return;
    input.addEventListener('keydown', (e) => {
      if (e.key === '-' || e.key === 'e' || e.key === 'E' || e.key === '.' || e.key === ',') {
        e.preventDefault();
      }
    });
    input.addEventListener('input', (e) => {
      let v = String(e.target.value || '').replace(/\D/g, '');
      if (v.length > 10) v = v.slice(0, 10);
      e.target.value = v;
    });
  }

  // =====================================================
  // Modal de pago / cambio
  // =====================================================
  function pedirPagoEnModal(total, resumenTexto) {
    const modalEl   = document.getElementById('ventaModal');
    if (!modalEl || !window.bootstrap) {
      // Fallback por si falta el modal alguna vez
      let pagoStr = prompt(
        `Total a pagar: ${money(total)}\n\n` +
        '¿Con cuánto paga el cliente? (solo números, sin puntos ni comas)'
      );
      if (pagoStr === null) return Promise.resolve(null);
      pagoStr = pagoStr.trim().replace(/[^\d]/g, '');
      const pago = parseFloat(pagoStr || '0');
      if (!pago || pago < total) return Promise.resolve(null);
      return Promise.resolve({ pago, cambio: pago - total });
    }

    const lblTotal   = modalEl.querySelector('#vmTotal');
    const inpPagaCon = modalEl.querySelector('#vmPagaCon');
    const lblCambio  = modalEl.querySelector('#vmCambio');
    const divResumen = modalEl.querySelector('#vmResumen');
    const btnOk      = modalEl.querySelector('#vmBtnConfirmar');

    lblTotal.textContent   = money(total);
    lblCambio.textContent  = '$0';
    inpPagaCon.value       = '';
    inpPagaCon.classList.remove('is-invalid');
    divResumen.innerHTML   = resumenTexto.replace(/\n/g, '<br>');

    const bs = new bootstrap.Modal(modalEl, { backdrop: 'static' });

    const calcCambio = () => {
      const raw = String(inpPagaCon.value || '').replace(/[^\d]/g, '');
      inpPagaCon.value = raw;
      const pago = parseFloat(raw || '0');
      if (!pago || pago < total) {
        lblCambio.textContent = '$0';
        inpPagaCon.classList.toggle('is-invalid', !!raw);
        return;
      }
      const cambio = pago - total;
      lblCambio.textContent = money(cambio);
      inpPagaCon.classList.remove('is-invalid');
    };

    const p = new Promise(resolve => {
      const onConfirm = () => {
        const pago = parseFloat(inpPagaCon.value || '0');
        if (!pago || pago < total) {
          inpPagaCon.classList.add('is-invalid');
          return;
        }
        const cambio = pago - total;
        cleanup();
        bs.hide();
        resolve({ pago, cambio });
      };

      const onHidden = () => {
        cleanup();
        resolve(null); // canceló
      };

      const cleanup = () => {
        btnOk.removeEventListener('click', onConfirm);
        modalEl.removeEventListener('hidden.bs.modal', onHidden);
        inpPagaCon.removeEventListener('input', calcCambio);
      };

      btnOk.addEventListener('click', onConfirm);
      modalEl.addEventListener('hidden.bs.modal', onHidden, { once:true });
      inpPagaCon.addEventListener('input', calcCambio);

      bs.show();
      setTimeout(() => inpPagaCon.focus(), 250);
    });

    return p;
  }

  // =====================================================
  // Productos -> dropdown
  // =====================================================
  async function cargarProductos() {
    if (!selProducto) return;

    selProducto.innerHTML = '<option value="">Cargando productos…</option>';

    try {
      const res = await fetch(api('action=productos'));
      if (!res.ok) {
        throw new Error(`HTTP ${res.status}`);
      }
      const j = await resToJsonSafe(res);

      if (j.ok === false) {
        throw new Error(j.msg || 'Error de API al cargar productos');
      }

      const items = j.items || j.productos || j.data || [];
      if (!Array.isArray(items) || !items.length) {
        selProducto.innerHTML = '<option value="">No hay productos activos</option>';
        return;
      }

      selProducto.innerHTML = '<option value="">Seleccione un producto…</option>';

      for (const p of items) {
        const id = p.id ?? p.id_producto ?? p.ID ?? null;
        const nombre = p.nombre ?? p.nombre_producto ?? p.descripcion ?? 'Producto sin nombre';
        const precio = Number(
          (p.precio_venta ?? p.precio ?? p.precio_unitario ?? 0)
        );

        if (!id || !precio) continue;

        const opt = document.createElement('option');
        opt.value = id;
        opt.textContent = `${nombre} - ${money(precio)}`;
        opt.dataset.nombre = nombre;
        opt.dataset.precio = String(precio);
        selProducto.appendChild(opt);
      }
    } catch (e) {
      console.error('Error cargando productos:', e);
      selProducto.innerHTML = '<option value="">Error cargando productos</option>';
      uiToast('No se pudieron cargar los productos.', 'danger');
    }
  }

  // =====================================================
  // Carrito
  // =====================================================
  function encontrarItem(idProducto) {
    return carrito.find(it => it.id_producto === idProducto);
  }

  function actualizarTotal() {
    let total = 0;
    carrito.forEach(it => total += it.cantidad * it.precio);
    if (spanTotal) spanTotal.textContent = money(total);
  }

  function renderCarrito() {
    if (!tbodyCarrito) return;
    if (!carrito.length) {
      tbodyCarrito.innerHTML = `
        <tr>
          <td colspan="5" class="text-center text-muted py-3">
            No hay productos en el carrito.
          </td>
        </tr>`;
      actualizarTotal();
      return;
    }

    tbodyCarrito.innerHTML = '';
    for (const it of carrito) {
      const tr = document.createElement('tr');
      tr.dataset.idProducto = String(it.id_producto);
      tr.innerHTML = `
        <td>${escapeHtml(it.nombre)}</td>
        <td style="width:80px;">
          <input type="number" min="1" class="form-control form-control-sm cj-cant"
                 value="${it.cantidad}">
        </td>
        <td>${money(it.precio)}</td>
        <td>${money(it.cantidad * it.precio)}</td>
        <td class="text-end">
          <button type="button" class="btn btn-outline-danger btn-sm cj-del">
            <i class="bi bi-x-lg"></i>
          </button>
        </td>
      `;
      tbodyCarrito.appendChild(tr);
    }

    // bloquear "-" y "e" en cantidades del carrito
    tbodyCarrito.querySelectorAll('.cj-cant').forEach(bloquearMinusYExponente);

    actualizarTotal();
  }

  function agregarAlCarrito() {
    if (!selProducto || !inpCantidad) return;

    const id = parseInt(selProducto.value, 10);
    const cantidad = parseFloat(inpCantidad.value || '1');

    if (!id || isNaN(id)) {
      uiToast('Selecciona un producto.', 'warning');
      return;
    }
    if (!cantidad || cantidad <= 0) {
      uiToast('Cantidad inválida.', 'warning');
      return;
    }

    const opt = selProducto.options[selProducto.selectedIndex];
    const nombre = opt.dataset.nombre || opt.textContent || '';
    const precio = parseFloat(opt.dataset.precio || '0');

    if (!precio || precio <= 0) {
      uiToast('El producto no tiene precio válido.', 'danger');
      return;
    }

    const existing = encontrarItem(id);
    if (existing) {
      existing.cantidad += cantidad;
    } else {
      carrito.push({
        id_producto: id,
        nombre,
        precio,
        cantidad
      });
    }

    renderCarrito();
    setMsg('Producto agregado al carrito.', true);
  }

  function onCarritoClick(e) {
    const btnDel    = e.target.closest('.cj-del');
    const inputCant = e.target.closest('.cj-cant');

    // Eliminar
    if (btnDel) {
      const tr = btnDel.closest('tr');
      if (!tr) return;
      const id = parseInt(tr.dataset.idProducto || '0', 10);
      const idx = carrito.findIndex(it => it.id_producto === id);
      if (idx >= 0) {
        carrito.splice(idx, 1);
        renderCarrito();
      }
      return;
    }

    // Cambiar cantidad
    if (inputCant && inputCant instanceof HTMLInputElement) {
      const tr = inputCant.closest('tr');
      if (!tr) return;
      const id = parseInt(tr.dataset.idProducto || '0', 10);
      const it = encontrarItem(id);
      if (!it) return;

      let nueva = parseFloat(inputCant.value || '1');
      if (!nueva || nueva <= 0) nueva = 1;
      inputCant.value = String(nueva);
      it.cantidad = nueva;
      renderCarrito();
      return;
    }
  }

  // ================================
  // Validar datos del cliente
  // ================================
  function validarCliente() {
    if (!inpCliNombre || !inpCliCedula) return { ok: true };

    const nombre   = (inpCliNombre.value || '').trim();
    const apellido = (inpCliApellido?.value || '').trim();
    const cedula   = (inpCliCedula.value || '').trim();

    inpCliNombre.classList.remove('is-invalid');
    inpCliApellido?.classList.remove('is-invalid');
    inpCliCedula.classList.remove('is-invalid');

    if (!nombre) {
      inpCliNombre.classList.add('is-invalid');
      uiToast('El nombre del cliente es obligatorio.', 'warning');
      return { ok: false };
    }
    const reNombre = /^[A-Za-zÁÉÍÓÚÑáéíóúñ\s]+$/;
    if (!reNombre.test(nombre)) {
      inpCliNombre.classList.add('is-invalid');
      uiToast('El nombre solo debe contener letras y espacios.', 'warning');
      return { ok: false };
    }

    if (apellido && !reNombre.test(apellido)) {
      inpCliApellido.classList.add('is-invalid');
      uiToast('El apellido solo debe contener letras y espacios.', 'warning');
      return { ok: false };
    }

    if (!cedula) {
      inpCliCedula.classList.add('is-invalid');
      uiToast('La cédula es obligatoria.', 'warning');
      return { ok: false };
    }
    const reCedula = /^\d{1,10}$/;
    if (!reCedula.test(cedula)) {
      inpCliCedula.classList.add('is-invalid');
      uiToast('La cédula debe tener solo números (máx. 10).', 'warning');
      return { ok: false };
    }

    return { ok: true, nombre, apellido, cedula };
  }

  // =====================================================
  // Guardar venta (usando modal de pago)
  // =====================================================
  async function guardarVenta() {
    if (!carrito.length) {
      uiToast('El carrito está vacío.', 'warning');
      return;
    }

    // Validar datos de cliente
    const valCli = validarCliente();
    if (!valCli.ok) return;

    const nombre   = valCli.nombre;
    const apellido = valCli.apellido || '';
    const cedula   = valCli.cedula;

    const metodoPago = selMetodoPago?.value || 'efectivo';

    const total = carrito.reduce((acc, it) => acc + it.cantidad * it.precio, 0);

    const resumen = carrito
      .map(it => `• ${it.nombre} x ${it.cantidad} = ${money(it.cantidad * it.precio)}`)
      .join('\n');

    // Modal para pedir con cuánto paga
    const pagoInfo = await pedirPagoEnModal(total, resumen);
    if (!pagoInfo) {
      return;
    }

    const { pago, cambio } = pagoInfo;

    const fd = new FormData();
    fd.append('items', JSON.stringify(carrito));
    fd.append('pago_efectivo', String(pago));
    fd.append('cli_nombre', nombre);
    fd.append('cli_apellido', apellido);
    fd.append('cli_cedula', cedula);
    fd.append('metodo_pago', metodoPago);

    try {
      if (btnGuardar) {
        btnGuardar.disabled = true;
        btnGuardar.innerHTML =
          '<span class="spinner-border spinner-border-sm me-1"></span>Guardando…';
      }

      const res = await fetch(api('action=crear_venta'), {
        method: 'POST',
        body: fd
      });

      if (!res.ok) {
        const jErr = await resToJsonSafe(res);
        const msg = jErr.msg || jErr.error || `HTTP ${res.status}`;
        throw new Error(msg);
      }

      const j = await resToJsonSafe(res);

      if (j.ok === false) {
        throw new Error(j.msg || 'No se pudo guardar la venta');
      }

      const idVenta = j.id_venta ?? j.id ?? null;

      uiToast('Venta registrada correctamente.', 'success');
      carrito.splice(0, carrito.length);
      renderCarrito();
      setMsg(`Venta guardada con éxito. Cambio: ${money(cambio)}.`, true);
      cargarHistorial(1);

      // 🔹 Abrir factura en PDF en nueva pestaña
      if (idVenta) {
        const urlFactura = `${base}/?r=admin_cajero_factura&id_venta=${encodeURIComponent(idVenta)}`;
        window.open(urlFactura, '_blank');
      }

    } catch (err) {
      console.error('Error guardar venta:', err);
      uiToast(err.message || 'Error al guardar venta', 'danger');
      setMsg('Error al guardar la venta.', false);
    } finally {
      if (btnGuardar) {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Guardar venta';
      }
    }
  }

  // =====================================================
  // Historial del cajero
  // =====================================================
  async function cargarHistorial(page = 1) {
    if (!tbodyHist) return;
    histState.page = page < 1 ? 1 : page;

    tbodyHist.innerHTML = `
      <tr>
        <td colspan="4" class="py-3 text-center">
          <div class="spinner-border spinner-border-sm me-2"></div> Cargando…
        </td>
      </tr>
    `;

    try {
      const res = await fetch(api(`action=historial&page=${histState.page}&per=${histState.per}`));
      const j = await resToJsonSafe(res);
      const items = j.items || [];
      histState.total = parseInt(j.total ?? items.length, 10);

      if (!items.length) {
        tbodyHist.innerHTML = `
          <tr>
            <td colspan="4" class="py-3 text-center text-muted">
              Aún no tienes ventas registradas.
            </td>
          </tr>
        `;
        return;
      }

      tbodyHist.innerHTML = '';
      for (const v of items) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${v.id_venta}</td>
          <td>${escapeHtml(v.fecha_venta || '')}</td>
          <td>${escapeHtml(v.cliente || 'N/A')}</td>
          <td>${money(v.total || 0)}</td>
        `;
        tbodyHist.appendChild(tr);
      }
    } catch (err) {
      console.error('Error cargar historial:', err);
      tbodyHist.innerHTML = `
        <tr>
          <td colspan="4" class="py-3 text-center text-danger">
            Error al cargar el historial.
          </td>
        </tr>
      `;
    }
  }

  // =====================================================
  // Init
  // =====================================================
  function boot() {
    ensureToastCSS();

    // Validaciones de campos cliente
    soloLetras(inpCliNombre);
    soloLetras(inpCliApellido);
    configurarCedula(inpCliCedula);

    if (selProducto) {
      cargarProductos();
    }
    if (inpCantidad) {
      bloquearMinusYExponente(inpCantidad);
    }
    if (tbodyCarrito) {
      renderCarrito();
      tbodyCarrito.addEventListener('click', onCarritoClick);
      tbodyCarrito.addEventListener('input', onCarritoClick);
    }
    if (btnAgregar) {
      btnAgregar.addEventListener('click', agregarAlCarrito);
    }
    if (btnGuardar) {
      btnGuardar.addEventListener('click', guardarVenta);
    }
    if (tbodyHist) {
      cargarHistorial(1);
    }

    // bloquear "-" y "e" en el input del modal de pago
    const inpModalPago = document.getElementById('vmPagaCon');
    if (inpModalPago) {
      bloquearMinusYExponente(inpModalPago);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
