<?php
/* views/admin/_shell_end.php */
$extra_js       = $extra_js ?? [];
$carga_chartjs  = $carga_chartjs ?? false;
$base           = $this->config['app']['base_url'] ?? '';
?>
  </main>
</div><!-- /.admin-wrap -->

<!-- JS comunes -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="<?= $base ?>/assets/js/app.js" defer></script>

<?php if ($carga_chartjs): ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>
<?php endif; ?>

<?php foreach ($extra_js as $src): ?>
  <script src="<?= htmlspecialchars($src) ?>" defer></script>
<?php endforeach; ?>

<!-- Sidebar toggle (persistente) -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const wrap     = document.querySelector('.admin-wrap');
  const sidebar  = document.getElementById('adminSidebar');
  const toggle   = document.querySelector('[data-collapse]');
  const KEY      = 'admin_sidebar_collapsed';

  const apply = (v) => wrap.classList.toggle('is-collapsed', !!v);

  // Restaurar estado
  apply(localStorage.getItem(KEY) === '1');

  toggle?.addEventListener('click', () => {
    const now = !wrap.classList.contains('is-collapsed');
    localStorage.setItem(KEY, now ? '1' : '0');
    apply(now);
  });
});
</script>

</body>
</html>
