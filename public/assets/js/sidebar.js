document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('pinBtn');

  btn?.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-collapsed');

    // cambia el icono
    const icon = btn.querySelector('i');
    if (document.body.classList.contains('sidebar-collapsed')) {
      icon.classList.remove('bi-chevron-double-left');
      icon.classList.add('bi-chevron-double-right');
      btn.title = "Expandir";
    } else {
      icon.classList.remove('bi-chevron-double-right');
      icon.classList.add('bi-chevron-double-left');
      btn.title = "Colapsar";
    }
  });
});
document.addEventListener('DOMContentLoaded', () => {
  const btn   = document.querySelector('[data-toggle-sidebar]');
  const back  = document.getElementById('sidebarBackdrop');
  const root  = document.body;
  const menuT = document.getElementById('menuToggle'); // 🔽 nuevo

  const open  = () => root.classList.add('sidebar-open');
  const close = () => root.classList.remove('sidebar-open');

  btn?.addEventListener('click', () => {
    root.classList.contains('sidebar-open') ? close() : open();
  });

  menuT?.addEventListener('click', () => {   // 🔽 nuevo
    root.classList.contains('sidebar-open') ? close() : open();
  });

  back?.addEventListener('click', close);

  // Cierra al navegar
  document.querySelectorAll('#adminSidebar a')
    .forEach(a => a.addEventListener('click', close));
});
