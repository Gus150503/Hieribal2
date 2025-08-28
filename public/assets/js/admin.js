console.log('Admin UI listo');

/* ------- Toggle de contraseña (login admin) ------- */
function toggleAdminPassword() {
  const passwordField = document.getElementById('admin-password');
  const icon = document.querySelector('.toggle-password');
  if (!passwordField || !icon) return;
  const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
  passwordField.setAttribute('type', type);
  icon.classList.toggle('bi-eye');
  icon.classList.toggle('bi-eye-slash');
}

document.addEventListener('DOMContentLoaded', () => {
  /* ------- Auto-ocultar mensajes de error ------- */
  const errorMsg = document.getElementById('error-msg');
  if (errorMsg) {
    setTimeout(() => {
      errorMsg.style.opacity = '0';
      setTimeout(() => errorMsg.remove(), 1000);
    }, 4000);
  }

  /* ------- Toggle sidebar (móvil simple) ------- */
  document.querySelector('[data-action="toggle-sidebar"]')
    ?.addEventListener('click', () => {
      document.querySelector('.sidebar')?.classList.toggle('active');
    });

  /* ------- Sidebar: forzar navegación en MISMA pestaña ------- */
  const side = document.getElementById('adminSidebar');

  const scrubLinks = (root) => {
    root.querySelectorAll('a[href]').forEach(a => {
      a.removeAttribute('target');
      // elimina atributos inline tipo onclick, onmousedown, etc.
      [...a.attributes].forEach(att => {
        if (/^on/i.test(att.name)) a.removeAttribute(att.name);
      });
    });
  };

  const goHere = (href) => {
    try { window.location.assign(href); }
    catch { window.location.href = href; }
  };

  if (side) {
    scrubLinks(side);

    // Capturamos TODAS las formas de click antes de que otro JS actúe
    const handler = (e) => {
      const a = e.target.closest('a[href]');
      if (!a) return;
      e.preventDefault();
      a.removeAttribute('target');
      goHere(a.href);
    };
    side.addEventListener('click', handler, true);
    side.addEventListener('mousedown', handler, true);
    side.addEventListener('mouseup', handler, true);
    side.addEventListener('auxclick', handler, true); // click medio/rueda

    // Si otro script vuelve a inyectar _blank/onclick
    const mo = new MutationObserver(() => scrubLinks(side));
    mo.observe(side, { subtree: true, attributes: true, childList: true });
  }

  /* ------- Global: neutraliza cualquier <a target="_blank"> fuera del sidebar ------- */
  const globalHandler = (e) => {
    const a = e.target.closest('a[target="_blank"]');
    if (!a) return;
    e.preventDefault();
    a.removeAttribute('target');
    goHere(a.href);
  };
  document.addEventListener('click', globalHandler, true);
  document.addEventListener('mousedown', globalHandler, true);
  document.addEventListener('mouseup', globalHandler, true);
  document.addEventListener('auxclick', globalHandler, true);
});
