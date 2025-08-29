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

        // Verifica credenciales (el modelo debe validar estado Activo)
        $u = $this->usuarios->verificarPassword($user, $pass);
        if ($u === false) {
            $_SESSION['admin_error'] = 'Credenciales inválidas o usuario inactivo.';
            $this->redirect('/?r=admin_login');
        }

        // Normaliza rol de BD: Admin, Cajero, Empleado -> admin|cajero|empleado
        $rolRaw = (string)($u['rol'] ?? '');
        $rol = strtolower(trim($rolRaw));
        // (opcional: mapea sinónimos)
        if ($rol === 'administrador') $rol = 'admin';

        // Permitir panel a admin, cajero y empleado
        $permitidos = ['admin','cajero','empleado'];
        if (!in_array($rol, $permitidos, true)) {
            $_SESSION['admin_error'] = 'No tienes permisos para acceder al panel.';
            $this->redirect('/?r=admin_login');
        }

        // Sesión
        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id'      => (int)($u['id_usuario'] ?? 0),
            'usuario' => (string)($u['usuario'] ?? ''),
            'nombre'  => trim(((string)($u['nombres'] ?? '')) . ' ' . ((string)($u['apellidos'] ?? ''))),
            'rol'     => $rol, // <-- en minúsculas: admin|cajero|empleado
            'correo'  => (string)($u['correo'] ?? ''),
        ];

        // Redirección consistente con tus rutas (sidebar usa admin/dashboard)
        $this->redirect('/?r=admin/dashboard');
    }

    /** Cierra sesión admin */
    public function logout(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();

        // Usa router (?r=home)
        $base = rtrim($this->config['app']['base_url'] ?? '', '/');
        header('Location: ' . $base . '/?r=home');
        exit;
    }
}
