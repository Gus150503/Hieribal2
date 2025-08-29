<?php
namespace Core;

/**
 * Clase base para los controladores.
 * Render, redirects y helpers de auth/roles.
 */
abstract class Controller {
    protected array $config;

    public function __construct(array $config) {
        $this->config = $config;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /** ---------------- Render / Navegación ---------------- */

    protected function render(string $vista, array $data = [], string $titulo = 'App'): void {
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../views/' . $vista . '.php';
        $contenido = ob_get_clean();
        require __DIR__ . '/../views/plantilla.php';
        exit;
    }

    protected function baseUrl(string $path = ''): string {
        $base = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');
        if ($path === '') return $base;
        return $base . (str_starts_with($path, '/') ? '' : '/') . $path;
    }

    protected function redirect(string $to, int $code = 302): void {
        $to = trim($to);
        if (preg_match('#^https?://#i', $to)) {
            header('Location: ' . $to, true, $code);
            exit;
        }
        header('Location: ' . $this->baseUrl($to), true, $code);
        exit;
    }

    /** ---------------- Utils request ---------------- */

    protected function isPost(): bool {
        return (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');
    }

    protected function isAjax(): bool {
        $xrw = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        $ct  = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
        $acc = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
        return $xrw === 'xmlhttprequest'
            || str_contains($ct, 'json')
            || str_contains($acc, 'application/json');
    }

    protected function post(string $key, $default = '') {
        return $_POST[$key] ?? $default;
    }

    /** ---------------- Auth / Roles ---------------- */

    /** Devuelve el usuario de sesión (o []) */
    protected function currentUser(): array {
        return $_SESSION['admin'] ?? [];
    }

    /** Devuelve el rol normalizado: admin | cajero | empleado */
    protected function currentRole(): string {
        $rol = $this->currentUser()['rol'] ?? '';
        return strtolower(is_string($rol) ? $rol : '');
    }

    /** Exige sesión iniciada; si no, redirige a login */
    protected function requireLogin(): void {
        if (empty($_SESSION['admin'])) {
            $_SESSION['admin_error'] = 'Inicia sesión para continuar.';
            $this->redirect('/?r=admin_login');
        }
    }

    /**
     * Exige rol dentro de la lista dada.
     * - En peticiones AJAX/JSON responde 403 con JSON.
     * - En vistas redirige al dashboard con mensaje.
     */
    protected function requireRole(array $allowed): void {
        $this->requireLogin();
        $role = $this->currentRole();
        $allow = array_map('strtolower', $allowed);
        if (!in_array($role, $allow, true)) {
            if ($this->isAjax()) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'msg' => 'No tienes permisos para realizar esta acción.']);
                exit;
            }
            $_SESSION['admin_error'] = 'No tienes permisos para entrar aquí.';
            $this->redirect('/?r=admin/dashboard');
        }
    }

    /**
     * Niega la acción a ciertos roles (útil para mutaciones en APIs).
     * Responde 403 en JSON.
     */
    protected function denyRoles(array $denied): void {
        $role = $this->currentRole();
        $deny = array_map('strtolower', $denied);
        if (in_array($role, $deny, true)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'msg' => 'Acción no permitida para tu rol.']);
            exit;
        }
    }

    /** Azúcar sintáctico: ¿es admin/cajero/empleado? */
    protected function isAdmin(): bool    { return $this->currentRole() === 'admin'; }
    protected function isCajero(): bool   { return $this->currentRole() === 'cajero'; }
    protected function isEmpleado(): bool { return $this->currentRole() === 'empleado'; }
}
