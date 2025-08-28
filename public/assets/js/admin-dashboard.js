document.addEventListener('DOMContentLoaded', () => {
  /* =========================
   * Sidebar móvil (off-canvas)
   * ========================= */
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
  sidebarEl?.querySelectorAll('a').forEach(a => a.addEventListener('click', closeSidebar));


  /* =========================
   * Carruseles
   * - .slider          -> modo múltiple (scroll por tarjeta)
   * - .slider--single  -> 1 tarjeta centrada, autoplay
   * ========================= */
  document.querySelectorAll('[data-slider]').forEach(slider => {
    const track = slider.querySelector('[data-track]');
    const prev  = slider.querySelector('[data-prev]');
    const next  = slider.querySelector('[data-next]');
    if (!track) return;

    const cards = track.querySelectorAll('.slide-card');
    const hasEmpty = !!track.querySelector('.slide-empty');
    if (hasEmpty || cards.length === 0) {
      [prev, next].forEach(b => b && (b.style.display = 'none'));
      return;
    }

    const isSingle = slider.classList.contains('slider--single');

    // ---------- MODO UNA TARJETA (centrada + espaciadores laterales) ----------
    if (isSingle) {
      let idx = 0;

      // define el ancho de espaciadores ::before/::after (para centrar 1º y último)
      const setEdge = (i = idx) => {
        const card = cards[i];
        const pad = Math.max(0, (track.clientWidth - card.clientWidth) / 2);
        track.style.setProperty('--edge', pad + 'px');
        return pad;
      };

      // scrollLeft exacto que centra la tarjeta i (considera --edge)
      const centerLeftFor = (i) => {
        const card = cards[i];
        const before = setEdge(i);                                 // espaciador
        const leftInTrack = card.offsetLeft - track.offsetLeft;    // incluye ::before
        let target = leftInTrack - before;

        const maxLeft = track.scrollWidth - track.clientWidth;
        if (target < 0) target = 0;
        if (target > maxLeft) target = maxLeft;
        return target;
      };

      const goTo = (i, behavior = 'smooth') => {
        idx = (i + cards.length) % cards.length;
        track.scrollTo({ left: centerLeftFor(idx), behavior });
      };

      const goNext = () => goTo(idx + 1);
      const goPrev = () => goTo(idx - 1);

      // autoplay (3.5s) con pausa al hover
      let timer = setInterval(goNext, 3500);
      slider.addEventListener('mouseenter', () => { clearInterval(timer); });
      slider.addEventListener('mouseleave', () => { timer = setInterval(goNext, 3500); });

      prev?.addEventListener('click', goPrev);
      next?.addEventListener('click', goNext);

      // tras scroll, fija índice al más centrado y micro-snap final
      let scrollEnd;
      track.addEventListener('scroll', () => {
        clearTimeout(scrollEnd);
        scrollEnd = setTimeout(() => {
          const viewCenter = track.scrollLeft + track.clientWidth / 2;
          let best = 0, bestDist = Infinity;
          cards.forEach((card, i) => {
            const left = card.offsetLeft - track.offsetLeft;
            const cardCenter = left + card.clientWidth / 2;
            const d = Math.abs(cardCenter - viewCenter);
            if (d < bestDist) { bestDist = d; best = i; }
          });
          if (best !== idx) idx = best;

          const desired = centerLeftFor(idx);
          if (Math.abs(track.scrollLeft - desired) > 1) {
            track.scrollTo({ left: desired, behavior: 'smooth' });
          }
        }, 80);
      }, { passive: true });

      // re-centrar al redimensionar
      const reflow = () => goTo(idx, 'auto');
      window.addEventListener('resize', () => setTimeout(reflow, 60));

      // inicio centrado
      goTo(0, 'auto');
      return; // no seguir con modo múltiple
    }

    // ---------- MODO MÚLTIPLE ----------
    const css = getComputedStyle(track);
    const gap = parseFloat(css.gap || css.columnGap || '16') || 16;

    const measureStep = () => {
      const firstW = cards[0].getBoundingClientRect().width;
      return firstW + gap;
    };
    let step = measureStep();

    const update = () => {
      step = measureStep();
      const canScroll = track.scrollWidth > track.clientWidth + 2;
      [prev, next].forEach(b => b && (b.style.display = canScroll ? 'block' : 'none'));
      if (!canScroll) return;
      prev.disabled = track.scrollLeft <= 0;
      const atEnd = Math.ceil(track.scrollLeft + track.clientWidth) >= track.scrollWidth;
      next.disabled = atEnd;
    };

    const goMulti = dir => {
      track.scrollBy({ left: dir * step, behavior: 'smooth' });
      setTimeout(update, 350);
    };

    prev?.addEventListener('click', () => goMulti(-1));
    next?.addEventListener('click', () => goMulti(1));
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', () => setTimeout(update, 100));
    update();
  });


  /* =========================
   * Charts (Chart.js)
   * ========================= */
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
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: horizontal ? 'y' : 'x',
        scales: {
          x: { grid: { color: gridColor } },
          y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } }
        },
        plugins: { legend: { display: false } }
      }
    });
  };

  const D = window.__charts || {
    lowStock:{labels:[],values:[]},
    toOrder:{labels:[],values:[]},
    topClients:{labels:[],values:[]}
  };

  mkBar('barLowStock',   D.lowStock.labels,  D.lowStock.values);
  mkBar('barToOrder',    D.toOrder.labels,   D.toOrder.values);
  mkBar('barTopClients', D.topClients.labels, D.topClients.values, true);
});
