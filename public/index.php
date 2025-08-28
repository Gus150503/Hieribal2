<?php
declare(strict_types=1);

use Controllers\AuthController;
use Controllers\HomeController;
use Controllers\AdminAuthController;
use Controllers\AdminDashboardController;
use Controllers\AdminUsuariosController;

require __DIR__ . '/../vendor/autoload.php';

/* ========= Cargar configuración ========= */
$appCfg = __DIR__ . '/../config/app.php';
$envCfg = __DIR__ . '/../config/env.php';

if (is_file($appCfg)) {
    $config = require $appCfg;
} elseif (is_file($envCfg)) {
    $config = require $envCfg;
} else {
    http_response_code(500);
    die('No se encontró config/app.php ni config/env.php');
}

/* ========= Instanciar controladores ========= */
$auth   = new AuthController($config);
$home   = new HomeController($config);
$adminA = new AdminAuthController($config);
$adminD = new AdminDashboardController($config);

/* ========= Router ========= */
$r = $_GET['r'] ?? 'home';
$r = trim(str_replace('/', '_', $r), '_');

switch ($r) {
  /* ====== Público / Home ====== */
  case 'home':        $home->index();      break;
  case 'dashboard':   $home->dashboard();  break; // dashboard público/cliente
  

  /* ====== Auth de clientes ====== */
  case 'login':           $auth->loginForm();    break;
  case 'do_login':        $auth->login();        break;
  case 'register':        $auth->registroForm(); break;
  case 'do_register':     $auth->registrar();    break;
  case 'logout':          $auth->logout();       break;
  case 'check_field':     $auth->checkField();   break;
  case 'forgot':          $auth->forgotForm();   break;
  case 'do_forgot':       $auth->forgot();       break;
  case 'reset':           $auth->resetForm();    break;
  case 'do_reset':        $auth->reset();        break;
  case 'google_start':    $auth->googleStart();  break;
  case 'google_callback': $auth->googleCallback(); break;
  case 'verify':          $auth->verify();       break;

  /* ====== Admin ====== */
  case 'admin_login':      $adminA->loginForm();   break;
  case 'admin_do_login':   $adminA->login();       break;
  case 'admin_logout':     $adminA->logout();      break;
  case 'admin_dashboard':  $adminD->index();       break;

  /* ====== Módulo Usuarios (Admin) ====== */
  case 'admin_usuarios':
      (new AdminUsuariosController($config))->index();
      break;

  case 'admin_usuarios_api':
      (new AdminUsuariosController($config))->api();
      break;

  /* (Opcional) compat: redirige la ruta antigua a la nueva */
  case 'usuarioadmin':
      header('Location: ' . (($config['app']['base_url'] ?? '') . '/?r=admin_usuarios'), true, 302);
      exit;

  /* ====== 404 ====== */
  default:
    http_response_code(404);
    echo '404 Página no encontrada';
}
