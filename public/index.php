<?php
declare(strict_types=1);

use Controllers\AuthController;
use Controllers\HomeController;
use Controllers\AdminAuthController;
use Controllers\AdminDashboardController;
use Controllers\AdminUsuariosController;

// =============================
//  ENTORNO Y AUTOLOAD
// =============================
define('APP_ENV', 'local');

// Zona horaria y reporting (solo local)
date_default_timezone_set('America/Bogota');
if (APP_ENV === 'local') {
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
}

// Helper para logs centralizados
function app_log(string $text): void {
    $file = __DIR__ . '/../storage/logs/app.log';
    if (!is_dir(dirname($file))) @mkdir(dirname($file), 0775, true);
    // Rotación simple (5MB)
    if (file_exists($file) && filesize($file) > 5 * 1024 * 1024) {
        @rename($file, $file . '.' . date('Ymd_His'));
    }
    @file_put_contents($file, '['.date('Y-m-d H:i:s')."] $text\n", FILE_APPEND);
}

// --- 1) Convertir Warnings/Notices en Excepciones
set_error_handler(function (int $severity, string $message, string $file, int $line) {
    if (!(error_reporting() & $severity)) return false; // respetar @
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// --- 2) Excepciones no capturadas
set_exception_handler(function (Throwable $e) {
    $line = sprintf("[%s] Uncaught: %s in %s:%d\nTrace:\n%s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    app_log($line);

    http_response_code(500);

    $isAjax = (
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
    ) || (
        strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false
    );

    if (APP_ENV === 'local') {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine()
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo "<pre>{$line}</pre>";
        }
    } else {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Ha ocurrido un error. Intenta más tarde.'], JSON_UNESCAPED_UNICODE);
        } else {
            echo "Ha ocurrido un error inesperado. Intenta más tarde.";
        }
    }
});

// --- 3) Errores fatales al cerrar
register_shutdown_function(function () {
    $err = error_get_last();
    if (!$err) return;
    $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (!in_array($err['type'], $fatal, true)) return;

    $line = sprintf("[%s] Fatal: %s in %s:%d\n",
        date('Y-m-d H:i:s'),
        $err['message'],
        $err['file'],
        $err['line']
    );
    app_log($line);

    http_response_code(500);
    echo (APP_ENV === 'local')
        ? "<pre>{$line}</pre>"
        : "Ha ocurrido un error inesperado. Intenta más tarde.";
});

require __DIR__ . '/../vendor/autoload.php';

// =============================
//  CONFIGURACIÓN
// =============================
$appCfg = __DIR__ . '/../config/app.php';
$envCfg = __DIR__ . '/../config/env.php';

if (is_file($appCfg)) {
    $config = require $appCfg;
} elseif (is_file($envCfg)) {
    $config = require $envCfg;
} else {
    http_response_code(500);
    // Cae en el exception handler si hay problemas arriba
    throw new RuntimeException('No se encontró config/app.php ni config/env.php');
}

// =============================
//  CONTROLADORES
// =============================
$auth   = new AuthController($config);
$home   = new HomeController($config);
$adminA = new AdminAuthController($config);
$adminD = new AdminDashboardController($config);

// =============================
//  ROUTER
// =============================
$r = $_GET['r'] ?? 'home';
$r = trim(str_replace('/', '_', $r), '_');

switch ($r) {
    /* ====== Público / Home ====== */
    case 'home':        $home->index();      break;
    case 'dashboard':   $home->dashboard();  break; // dashboard público/cliente

    /* ====== Auth de clientes ====== */
    case 'login':            $auth->loginForm();     break;
    case 'do_login':         $auth->login();         break;
    case 'register':         $auth->registroForm();  break;
    case 'do_register':      $auth->registrar();     break;
    case 'logout':           $auth->logout();        break;
    case 'check_field':      $auth->checkField();    break;
    case 'forgot':           $auth->forgotForm();    break;
    case 'do_forgot':        $auth->forgot();        break;
    case 'reset':            $auth->resetForm();     break;
    case 'do_reset':         $auth->reset();         break;
    case 'google_start':     $auth->googleStart();   break;
    case 'google_callback':  $auth->googleCallback();break;
    case 'verify':           $auth->verify();        break;

    /* ====== Admin ====== */
    case 'admin_login':         $adminA->loginForm();     break;
    case 'admin_do_login':      $adminA->login();         break;
    case 'admin_logout':        $adminA->logout();        break;
    case 'admin_dashboard':     $adminD->index();         break;
    case 'admin_inventario':    $adminD->inventario();    break;
    case 'admin_productos':     $adminD->productos();     break;
    case 'admin_configuracion': $adminD->configuracion(); break;

    /* ====== Módulo Usuarios (Admin) ====== */
    case 'admin_usuarios':                (new AdminUsuariosController($config))->index();             break;
    case 'admin_usuarios_api':            (new AdminUsuariosController($config))->api();               break;
    case 'admin_usuarios_verify_email':   (new AdminUsuariosController($config))->verifyEmail();       break;
    case 'admin_usuarios_resend_verif':   (new AdminUsuariosController($config))->resendVerification();break;
    case 'admin_config_api':
    require __DIR__ . '/api/config_api.php';
    break;

    /* (Compat) ruta vieja -> nueva */
    case 'usuarioadmin':
        header('Location: ' . (($config['app']['base_url'] ?? '') . '/?r=admin_usuarios'), true, 302);
        exit;

    /* ====== 404 ====== */
    default:
        http_response_code(404);
        echo '404 Página no encontrada';
}
