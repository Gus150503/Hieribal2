<?php
class View {
  public static function renderAdmin(string $viewPath, array $data = []) {
    extract($data, EXTR_OVERWRITE);
    ob_start();
    include $viewPath;            // genera $content del módulo
    $content = ob_get_clean();
    include __DIR__ . '/../views/admin/layout.php'; // usa $content dentro del layout
  }
}
