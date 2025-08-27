document.addEventListener('DOMContentLoaded', () => {
/* Sidebar móvil (off-canvas) */
const toggleBtn  = document.getElementById('sidebarToggle');
const sidebarEl  = document.getElementById('adminSidebar');
const backdropEl = document.getElementById('sidebarBackdrop');

const openSidebar  = () => {
  document.body.classList.add('sidebar-open');
  toggleBtn?.setAttribute('aria-expanded', 'true');
};
const closeSidebar = () => {
  document.body.classList.remove('sidebar-open');
  toggleBtn?.setAttribute('aria-expanded', 'false');
};
const toggleSidebar = () => {
  const willOpen = !document.body.classList.contains('sidebar-open');
  willOpen ? openSidebar() : closeSidebar();
};

toggleBtn?.addEventListener('click', toggleSidebar);
backdropEl?.addEventListener('click', closeSidebar);
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSidebar(); });
// cerrar al navegar
sidebarEl?.querySelectorAll('a').forEach(a => a.addEventListener('click', closeSidebar));

  /* Carrusel básico con auto-ocultado de flechas */
  document.querySelectorAll('[data-slider]').forEach(slider => {
    const track = slider.querySelector('[data-track]');
    const prev  = slider.querySelector('[data-prev]');
    const next  = slider.querySelector('[data-next]');
    const step = 220;

    const update = () => {
      const canScroll = track.scrollWidth > track.clientWidth + 2;
      [prev, next].forEach(b => b && (b.style.display = canScroll ? 'block' : 'none'));
      if (!canScroll) return;

      prev.disabled = track.scrollLeft <= 0;
      const atEnd = Math.ceil(track.scrollLeft + track.clientWidth) >= track.scrollWidth;
      next.disabled = atEnd;
    };

    prev?.addEventListener('click', () => track.scrollBy({ left: -step, behavior: 'smooth' }));
    next?.addEventListener('click', () => track.scrollBy({ left:  step, behavior: 'smooth' }));
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
  });

  /* ===== Charts ===== */
  if (typeof Chart === 'undefined') return;
  const gridColor = 'rgba(17,24,39,.06)';

  const mountEmpty = (canvas, text='Sin datos') => {
    const card = canvas.closest('.chart-card'); if (!card) return;
    if (!card.querySelector('.chart-empty')) {
      const d = document.createElement('div');
      d.className = 'chart-empty'; d.textContent = text;
      card.appendChild(d);
    }
  };
  const unmountEmpty = (canvas) => {
    const card = canvas.closest('.chart-card');
    const d = card?.querySelector('.chart-empty'); if (d) d.remove();
  };

  const mkBar = (id, labels=[], values=[], horizontal=false) => {
    const el = document.getElementById(id);
    if (!el) return;
    const allZero = !values.length || values.every(v => Number(v) === 0);
    if (allZero) { mountEmpty(el); return; }
    unmountEmpty(el);

    new Chart(el.getContext('2d'), {
      type: 'bar',
      data: { labels, datasets: [{ label: 'Cantidad', data: values, borderWidth: 1 }] },
      options: {
        responsive: true, maintainAspectRatio: false,
        indexAxis: horizontal ? 'y' : 'x',
        scales: {
          x: { grid: { color: gridColor } },
          y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } }
        },
        plugins: { legend: { display: false } }
      }
    });
  };

  /* Datos inyectados desde PHP */
  const D = window.__charts || {
    lowStock:{labels:[],values:[]},
    toOrder:{labels:[],values:[]},
    topClients:{labels:[],values:[]}
  };

  mkBar('barLowStock',   D.lowStock.labels,  D.lowStock.values);      // Productos por acabarse
  mkBar('barToOrder',    D.toOrder.labels,   D.toOrder.values);       // Productos por pedir
  mkBar('barTopClients', D.topClients.labels, D.topClients.values, true); // Clientes que más compran (horizontal)
});

