<?php
namespace Controllers;

use Models\UsuarioAdmin;

final class AdminUsuariosController {
    private array $config;
    private UsuarioAdmin $Usuario;

    public function __construct(array $config) {
        $this->config = $config;
        $this->Usuario = new UsuarioAdmin($config);
    }

public function index(): void {
    // 1) Sesión + guardia de admin (igual que en dashboard)
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['admin'])) {
        $_SESSION['admin_error'] = 'Inicia sesión para continuar.';
        header('Location: /?r=admin_login');
        exit;
    }

    // 2) Variables que el layout espera
    $config      = $this->config;                 // <-- IMPORTANTE: el layout usa $config
    $title       = 'Usuarios';
    $pageId      = 'admin_usuarios';
    $pageStyles  = ['/assets/css/admin_usuarios.css'];   // opcional
    $pageScripts = ['/assets/js/admin_usuarios.js'];     // opcional

    // 3) Render de la vista (solo contenido, sin <html> ni barra)
    ob_start();
    require __DIR__ . '/../views/admin/usuarioadmin.php';
    $content = ob_get_clean();

    // 4) Incluir el layout admin (pondrá la barra lateral y el CSS global)
    require __DIR__ . '/../views/admin/layout.php';
}


    /** Endpoint API para AJAX */
    public function api(): void {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $m = $_SERVER['REQUEST_METHOD'];
            $action = $_REQUEST['action'] ?? '';

            if ($m === 'GET' && $action === 'list') {
                $q = trim($_GET['q'] ?? '');
                $page = max(1, (int)($_GET['page'] ?? 1));
                $per = max(1, min(50, (int)($_GET['per'] ?? 10)));
                echo json_encode($this->Usuario->listar($q, $page, $per)); return;
            }

            if ($m === 'GET' && $action === 'get') {
                $id = (int)($_GET['id'] ?? 0);
                echo json_encode(['data' => $this->Usuario->obtener($id)]); return;
            }

            if ($m === 'POST' && $action === 'create') {
                $d = $this->sanitize($_POST);
                $id = $this->Usuario->crear($d);
                echo json_encode(['ok' => true, 'id' => $id]); return;
            }

            if ($m === 'POST' && $action === 'update') {
                $id = (int)($_POST['id_usuario'] ?? 0);
                $d = $this->sanitize($_POST);
                $this->Usuario->actualizar($id, $d);
                echo json_encode(['ok' => true]); return;
            }

            if ($m === 'POST' && $action === 'delete') {
                $id = (int)($_POST['id_usuario'] ?? 0);
                $this->Usuario->eliminar($id);
                echo json_encode(['ok' => true]); return;
            }

            if ($m === 'POST' && $action === 'toggle') {
                $id = (int)($_POST['id_usuario'] ?? 0);
                $estado = $this->Usuario->toggleEstado($id);
                echo json_encode(['ok' => true, 'estado' => $estado]); return;
            }

            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Acción no válida']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    private function sanitize(array $in): array {
        return [
            'usuario'   => trim($in['usuario'] ?? ''),
            'password'  => trim($in['password'] ?? ''), // opcional en update
            'rol'       => trim($in['rol'] ?? 'empleado'),
            'nombres'   => trim($in['nombres'] ?? ''),
            'apellidos' => trim($in['apellidos'] ?? ''),
            'correo'    => trim($in['correo'] ?? ''),
            'estado'    => trim($in['estado'] ?? 'activo'),
        ];
    }
}
