document.addEventListener('DOMContentLoaded', () => {
  const root        = document.body;
  const sidebarEl   = document.getElementById('adminSidebar');
  const backdropEl  = document.getElementById('sidebarBackdrop');

  // Botones que abren/cerran en móvil (usa los que tengas)
  const toggleBtns  = [
    document.querySelector('[data-toggle-sidebar]'),
    document.getElementById('menuToggle'),
    document.getElementById('sidebarToggle'),
  ].filter(Boolean);

  // Botón de cerrar dentro del sidebar (opcional)
  const closeBtn    = sidebarEl?.querySelector('.sidebar-close');

  // Botón de pin para colapsar en desktop (tu pinBtn existente)
  const pinBtn      = document.getElementById('pinBtn');

  const isDesktop = () => window.matchMedia('(min-width: 992px)').matches;

  /* =========================
   *  Móvil: Off-canvas
   * ========================= */
  const lockScroll = () => {
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';
  };
  const unlockScroll = () => {
    document.documentElement.style.overflow = '';
    document.body.style.overflow = '';
  };

  const setTogglesExpanded = (val) => {
    toggleBtns.forEach(b => b?.setAttribute('aria-expanded', String(val)));
  };

  const openSidebarMobile = () => {
    root.classList.add('sidebar-open');
    backdropEl && (backdropEl.hidden = false);
    setTogglesExpanded(true);
    lockScroll();
  };

  const closeSidebarMobile = () => {
    root.classList.remove('sidebar-open');
    backdropEl && (backdropEl.hidden = true);
    setTogglesExpanded(false);
    unlockScroll();
  };

  const toggleMobile = () => {
    if (root.classList.contains('sidebar-open')) closeSidebarMobile();
    else openSidebarMobile();
  };

  // Click en toggles (hamburguesa)
  toggleBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      if (isDesktop()) {
        // En desktop el mismo botón actúa como colapsador
        toggleCollapsedDesktop();
      } else {
        toggleMobile();
      }
    });
  });

  // Backdrop cierra
  backdropEl?.addEventListener('click', closeSidebarMobile);

  // Botón cerrar dentro del sidebar (si existe)
  closeBtn?.addEventListener('click', closeSidebarMobile);

  // Cerrar al navegar en móvil
  sidebarEl?.addEventListener('click', (ev) => {
    const a = ev.target.closest('a');
    if (!a) return;
    if (!isDesktop()) closeSidebarMobile();
  });

  // Cerrar con ESC en móvil
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && root.classList.contains('sidebar-open') && !isDesktop()) {
      closeSidebarMobile();
    }
  });

  // Al redimensionar: si paso a desktop, garantizo estado limpio móvil
  window.addEventListener('resize', () => {
    if (isDesktop()) {
      closeSidebarMobile();
    }
  });

  /* =========================
   *  Desktop: Colapsado
   * ========================= */
  const syncPinIcon = () => {
    if (!pinBtn) return;
    const icon = pinBtn.querySelector('i');
    if (!icon) return;

    const collapsed = root.classList.contains('sidebar-collapsed');
    if (collapsed) {
      icon.classList.remove('bi-chevron-double-left');
      icon.classList.add('bi-chevron-double-right');
      pinBtn.title = 'Expandir';
    } else {
      icon.classList.remove('bi-chevron-double-right');
      icon.classList.add('bi-chevron-double-left');
      pinBtn.title = 'Colapsar';
    }
  };

  const toggleCollapsedDesktop = () => {
    root.classList.toggle('sidebar-collapsed');
    syncPinIcon();
  };

  // Click del pin para desktop
  pinBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    // Si está en móvil, tratamos el pin como cerrar/abrir
    if (!isDesktop()) {
      toggleMobile();
      return;
    }
    toggleCollapsedDesktop();
  });

  // Sincroniza ícono del pin al cargar
  syncPinIcon();
});