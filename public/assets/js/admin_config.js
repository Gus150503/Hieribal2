// public/assets/js/admin_config.js
(function () {
  'use strict';

  // ====== Evitar doble carga ======
  if (window.__ADMIN_CONFIG_JS_BOUND__) return;
  window.__ADMIN_CONFIG_JS_BOUND__ = true;

  // ====== Base API (funciona bajo /.../public) ======
  const PUBLIC_SEG = '/public';
  const ix = location.pathname.indexOf(PUBLIC_SEG);
  const base = ix >= 0 ? location.pathname.slice(0, ix + PUBLIC_SEG.length) : PUBLIC_SEG;
  const api  = (params) => `${base}/?r=admin_config_api&${params}`;

  // ====== Atajos DOM ======
  const $  = (s) => document.querySelector(s);
  const $$ = (s) => Array.from(document.querySelectorAll(s));
  const xhr = { 'X-Requested-With': 'XMLHttpRequest' };

  // ====== Toast minimalista ======
  function ensureToastCSS() {
    if ($('#_toast_cfg_css')) return;
    const css = document.createElement('style');
    css.id = '_toast_cfg_css';
    css.textContent = `
      .toast-host{position:fixed;top:14px;right:14px;z-index:9999;display:flex;flex-direction:column;gap:10px}
      .toast{
        display:flex;align-items:center;gap:.6rem;min-width:260px;max-width:360px;
        padding:.65rem .8rem;border:1px solid var(--border);border-radius:12px;
        background:#fff;color:#0f172a;box-shadow:var(--shadow);transition:transform .15s,opacity .15s
      }
      :root[data-theme="dark"] .toast{background:#0f172a;color:var(--ink)}
      .toast.success{border-color:rgba(25,135,84,.35)}
      .toast.error{border-color:rgba(192,58,43,.35)}
      .toast .dot{width:.6rem;height:.6rem;border-radius:50%;background:var(--brand)}
      .toast.error .dot{background:#c03a2b}
      .toast .msg{flex:1}
      .toast .btnx{border:0;background:transparent;opacity:.6;cursor:pointer;font-size:1rem;line-height:1}
      .toast .btnx:hover{opacity:1}
    `;
    document.head.appendChild(css);
  }
  function uiToast(msg, type='success', ms=2600){
    ensureToastCSS();
    let host = $('#toastHost'); if(!host){ host=document.createElement('div'); host.id='toastHost'; host.className='toast-host'; document.body.appendChild(host); }
    const el = document.createElement('div');
    el.className = `toast ${type==='error'?'error':'success'}`;
    el.innerHTML = `<div class="dot"></div><div class="msg">${escapeHtml(String(msg))}</div><button class="btnx" aria-label="Cerrar">×</button>`;
    host.appendChild(el);
    const close = () => { el.style.opacity='0'; el.style.transform='translateY(-4px)'; setTimeout(()=>el.remove(), 180); };
    el.querySelector('.btnx')?.addEventListener('click', close);
    const t = setTimeout(close, ms);
    el.addEventListener('mouseenter', () => clearTimeout(t), { once:true });
  }
  function escapeHtml(s){return (s??'').toString().replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));}

  // ====== Tabs (sin Bootstrap) ======
  function activateTabByHash(hash) {
    if (!hash) return;
    const id   = hash.startsWith('#') ? hash.slice(1) : hash;
    const pane = document.getElementById(id);
    const link = document.querySelector(`.nav-tabs .nav-link[href="#${id}"]`);
    if (!pane || !link) return;

    $$('.nav-tabs .nav-link').forEach(a => a.classList.remove('active'));
    link.classList.add('active');

    $$('.tab-pane').forEach(p => p.classList.remove('active','show'));
    pane.classList.add('active','show');
  }
  function initTabs() {
    $$('.nav-tabs .nav-link').forEach(a => {
      a.addEventListener('click', (e) => {
        const href = a.getAttribute('href') || '';
        if (!href.startsWith('#')) return;
        e.preventDefault();
        if (history.pushState) history.pushState(null, '', href); else location.hash = href;
        activateTabByHash(href);
      });
    });
    window.addEventListener('hashchange', () => activateTabByHash(location.hash));
    const first = $$('.nav-tabs .nav-link')[0]?.getAttribute('href') || '#tabEmpresa';
    activateTabByHash(location.hash || first);
  }

  // ====== Tema (claro/oscuro/auto) ======
  function resolveAutoDark() {
    return window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }
  function applyTheme(theme) {
    let t = theme || 'light';
    if (t === 'auto') t = resolveAutoDark();
    document.documentElement.setAttribute('data-theme', t);
  }
  // Preview inmediato al cambiar controles
  function bindThemePreview() {
    $('#ui_tema')?.addEventListener('change', (e)=>{
      applyTheme(e.target.value || 'light');
    });
    $('#ui_color_principal')?.addEventListener('input', (e)=>{
      const v = e.target.value || '#198754';
      document.documentElement.style.setProperty('--brand', v);
      document.documentElement.style.setProperty('--ring', hexToRing(v));
    });
  }

  // ====== Helpers ======
  function hexToRing(hex){
    try{
      const v = (hex||'').replace('#','');
      const r = parseInt(v.substring(0,2),16) || 25,
            g = parseInt(v.substring(2,4),16) || 135,
            b = parseInt(v.substring(4,6),16) || 84;
      return `rgba(${r},${g},${b},.18)`;
    }catch{ return 'rgba(25,135,84,.18)'; }
  }

  // ====== Cargar configuración ======
  async function cargar() {
    try {
      const res  = await fetch(api('action=get'), { credentials:'same-origin', headers:xhr });
      const text = await res.text();
      let j; try { j = JSON.parse(text); } catch { throw new Error(`Respuesta inválida del servidor: ${text.slice(0,120)}…`); }
      if (!res.ok || !j.ok) throw new Error(j.msg || `HTTP ${res.status}`);

      const d = j.data || {};

      // Empresa
      $('#empresa_nombre')    && ($('#empresa_nombre').value    = d.empresa_nombre ?? '');
      $('#empresa_ruc')       && ($('#empresa_ruc').value       = d.empresa_ruc ?? '');
      $('#empresa_direccion') && ($('#empresa_direccion').value = d.empresa_direccion ?? '');

      // Correo
      $('#correo_host')      && ($('#correo_host').value      = d.correo_host ?? '');
      $('#correo_puerto')    && ($('#correo_puerto').value    = d.correo_puerto ?? 587);
      $('#correo_seguridad') && ($('#correo_seguridad').value = d.correo_seguridad ?? 'tls');
      $('#correo_usuario')   && ($('#correo_usuario').value   = d.correo_usuario ?? '');
      $('#correo_from')      && ($('#correo_from').value      = d.correo_from ?? '');
      $('#correo_activo')    && ($('#correo_activo').checked  = !!d.correo_activo);

      // UI (si hay preferencia local, úsala para reflejar el estado actual)
      const lsTema  = localStorage.getItem('ui_tema');
      const lsBrand = localStorage.getItem('ui_color_principal');
      const tema    = (lsTema ?? d.ui_tema ?? 'light');
      const brand   = (lsBrand ?? d.ui_color_principal ?? '#198754');

      $('#ui_tema')            && ($('#ui_tema').value            = tema);
      $('#ui_color_principal') && ($('#ui_color_principal').value = brand);

      // Aplicar en vivo
      applyTheme(tema);
      document.documentElement.style.setProperty('--brand', brand);
      document.documentElement.style.setProperty('--ring', hexToRing(brand));

      // Si el tema es auto, reacciona a cambios del SO
      try {
        const mm = window.matchMedia('(prefers-color-scheme: dark)');
        mm.onchange = () => { if (($('#ui_tema')?.value || 'light') === 'auto') applyTheme('auto'); };
      } catch {}
    } catch (e) {
      console.error(e);
      uiToast(e.message || 'Error al cargar configuración', 'error', 3800);
    }
  }

  // ====== Guardar configuración ======
  async function guardar() {
    const items = {
      // Empresa
      empresa_nombre:     $('#empresa_nombre')?.value.trim() ?? '',
      empresa_ruc:        $('#empresa_ruc')?.value.trim() ?? '',
      empresa_direccion:  $('#empresa_direccion')?.value.trim() ?? '',
      // Correo
      correo_host:        $('#correo_host')?.value.trim() ?? '',
      correo_puerto:      parseInt($('#correo_puerto')?.value || '0', 10) || 0,
      correo_seguridad:   $('#correo_seguridad')?.value || 'tls',
      correo_usuario:     $('#correo_usuario')?.value.trim() ?? '',
      correo_from:        $('#correo_from')?.value.trim() ?? '',
      correo_activo:      $('#correo_activo')?.checked ? 1 : 0,
      // UI
      ui_tema:            $('#ui_tema')?.value || 'light',
      ui_color_principal: $('#ui_color_principal')?.value || '#198754',
    };

    if (items.correo_activo && (!items.correo_host || !items.correo_usuario || !items.correo_from)) {
      uiToast('Completa host, usuario y remitente para activar el correo.', 'error', 4200);
      return;
    }

    const btn = $('#btnGuardarCfg');
    const prev = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando…'; }

    try {
      const fd = new FormData();
      Object.entries(items).forEach(([k,v]) => fd.append(`items[${k}]`, v));

      const res  = await fetch(api('action=update'), { method:'POST', body:fd, credentials:'same-origin', headers:xhr });
      const text = await res.text();
      let j; try { j = JSON.parse(text); } catch { throw new Error(`Respuesta inválida del servidor: ${text.slice(0,120)}…`); }
      if (!res.ok || !j.ok) throw new Error(j.msg || `HTTP ${res.status}`);

      // Aplicar en vivo + persistir preferencia local
      applyTheme(items.ui_tema);
      document.documentElement.style.setProperty('--brand', items.ui_color_principal);
      document.documentElement.style.setProperty('--ring', hexToRing(items.ui_color_principal));
      try {
        localStorage.setItem('ui_tema', items.ui_tema);
        localStorage.setItem('ui_color_principal', items.ui_color_principal);
      } catch {}

      uiToast('Configuración guardada correctamente.');
    } catch (e) {
      console.error(e);
      uiToast(e.message || 'Error al guardar', 'error', 4200);
    } finally {
      if (btn) { btn.disabled = false; btn.innerHTML = prev; }
    }
  }

  // ====== Init ======
  document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    bindThemePreview();
    cargar();
    $('#btnGuardarCfg')?.addEventListener('click', guardar, { once:false });
  });
})();
