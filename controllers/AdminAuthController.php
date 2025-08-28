<?php
namespace Controllers;

use Core\Controller;
use Models\Usuario;

final class AdminAuthController extends Controller {
    private Usuario $usuarios;
    private string $base;

    public function __construct(array $config) {
        parent::__construct($config);
        $this->usuarios = new Usuario($config);

        // Base URL normalizada (solo para assets/vistas)
        $this->base = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');
    }

    /** Vista login admin */
    public function loginForm(): void {
        $error = $_SESSION['admin_error'] ?? null; unset($_SESSION['admin_error']);
        $msg   = $_SESSION['admin_msg']   ?? null; unset($_SESSION['admin_msg']);

        $this->render('admin/login', [
            'error'     => $error,
            'msg'       => $msg,
            'full'      => true,

            // CSS/JS específicos del login admin
            'extra_css' => [
                $this->base . '/assets/css/admin.css',
                'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css',
            ],
            'extra_js'  => [
                $this->base . '/assets/js/admin.js',
            ],
        ], 'Login Admin');
    }

    /** Procesa login admin */
    public function login(): void {
        if (!$this->isPost()) {
            $this->redirect('/?r=admin_login');
        }

        $user = trim((string)$this->post('usuario'));
        $pass = (string)$this->post('password');

        if ($user === '' || $pass === '') {
            $_SESSION['admin_error'] = 'Usuario y contraseña son obligatorios.';
            $this->redirect('/?r=admin_login');
        }

        // Verifica credenciales (el modelo usa password_verify y estado Activo)
        $u = $this->usuarios->verificarPassword($user, $pass);
        if ($u === false) {
            $_SESSION['admin_error'] = 'Credenciales inválidas o usuario inactivo.';
            $this->redirect('/?r=admin_login');
        }

        // Normaliza rol y limita acceso
        $rol = strtolower(trim((string)($u['rol'] ?? '')));
        if (!in_array($rol, ['admin','manager'], true)) {
            $_SESSION['admin_error'] = 'No tienes permisos para acceder al panel.';
            $this->redirect('/?r=admin_login');
        }

        // Sesión
        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id'      => (int)($u['id_usuario'] ?? 0),
            'usuario' => (string)($u['usuario'] ?? ''),
            'nombre'  => trim((string)($u['nombres'] ?? '') . ' ' . (string)($u['apellidos'] ?? '')),
            'rol'     => $rol,
            'correo'  => (string)($u['correo'] ?? ''),
        ];

        $this->redirect('/?r=admin_dashboard');
    }

    /** Cierra sesión admin */
public function logout(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

    // Limpiar sesión
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();

    // Redirigir usando el router (?r=home), NO a /home/index.php
    $base = rtrim($this->config['app']['base_url'] ?? '', '/'); // p.ej. /Hieribal2/public
    header('Location: ' . $base . '/?r=home');
    exit;
}


}
