<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Models\Cliente;

final class AdminClientesController extends Controller
{
    private Cliente $Cliente;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->Cliente = new Cliente($config);
    }

    /* ============================
       Helpers comunes JSON
       ============================ */
    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function ok(array $extra = [], int $status = 200): void
    {
        $this->json(['ok' => true] + $extra, $status);
    }

    private function fail(string $msg, int $status = 400, array $extra = []): void
    {
        $this->json(['ok' => false, 'msg' => $msg] + $extra, $status);
    }

    private function ensureAdmin(): void
    {
        if (session_status() !== \PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['admin'])) {
            $_SESSION['admin_error'] = 'Inicia sesión para continuar.';
            header('Location: /?r=admin_login'); exit;
        }
    }

    /* ============================
       Vista principal
       ============================ */
    public function index(): void
    {
        $this->ensureAdmin();
        $this->render('admin/clientes/index', [
            'page_title' => 'Clientes',
            'esAdmin'    => true,
            'extra_css'  => [$this->config['app']['base_url'] . '/assets/css/admin_clientes.css?v=1'],
            'extra_js'   => [$this->config['app']['base_url'] . '/assets/js/admin_clientes.js?v=1'],
        ]);
    }

    /* ============================
       API CRUD
       ============================ */
    public function api(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $m      = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_REQUEST['action'] ?? '';

            // === LISTAR ===
            if ($m === 'GET' && $action === 'list') {
                $q    = trim((string)($_GET['q'] ?? ''));
                $page = max(1, (int)($_GET['page'] ?? 1));
                $per  = max(1, min(50, (int)($_GET['per'] ?? 10)));
                $data = $this->Cliente->listar($q, $page, $per);
                $this->json($data);
                return;
            }

            // === OBTENER UNO ===
            if ($m === 'GET' && $action === 'get') {
                $id = (int)($_GET['id'] ?? 0);
                if ($id <= 0) { $this->fail('ID inválido'); return; }
                $row = $this->Cliente->obtener($id);
                $this->json(['data' => $row]);
                return;
            }

            // === CREAR ===
            if ($m === 'POST' && $action === 'create') {
                $d  = $this->sanitize($_POST, true);
                $id = $this->Cliente->crear($d);
                $this->ok(['id' => $id], 201);
                return;
            }

            // === ACTUALIZAR ===
            if ($m === 'POST' && $action === 'update') {
                $id = (int)($_POST['id_cliente'] ?? 0);
                if ($id <= 0) { $this->fail('ID inválido'); return; }
                $d = $this->sanitize($_POST, false);
                $this->Cliente->actualizar($id, $d);
                $this->ok();
                return;
            }

            // === ELIMINAR ===
            if ($m === 'POST' && $action === 'delete') {
                $id = (int)($_POST['id_cliente'] ?? 0);
                if ($id <= 0) { $this->fail('ID inválido'); return; }
                $this->Cliente->eliminar($id);
                $this->ok();
                return;
            }

            // === TOGGLE ESTADO ===
            if ($m === 'POST' && $action === 'toggle') {
                $id = (int)($_POST['id_cliente'] ?? 0);
                if ($id <= 0) { $this->fail('ID inválido'); return; }
                $res = $this->Cliente->toggleEstado($id);
                $msg = ($res['estado'] === 'Activo')
                    ? 'Cliente activado.'
                    : 'Cliente inactivado.';
                $this->ok(['estado'=>$res['estado'],'msg'=>$msg]);
                return;
            }

            $this->fail('Acción no válida', 400);

        } catch (\Throwable $e) {
            $this->fail($e->getMessage(), 500);
        }
    }

    /* ============================
       Sanitización y validación
       ============================ */
    private function sanitize(array $in, bool $creating): array
    {
        $nameRe = '/^[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,60}$/u';
        $email  = trim($in['correo'] ?? '');
        $cedula = trim($in['cedula'] ?? '');
        $nombres = trim($in['nombres'] ?? '');
        $apellidos = trim($in['apellidos'] ?? '');
        $telefono = trim($in['telefono'] ?? '');
        $password = trim($in['contraseña'] ?? '');

        if (!preg_match('/^\d{6,15}$/', $cedula)) throw new \Exception('Cédula inválida.');
        if (!preg_match($nameRe, $nombres)) throw new \Exception('Nombres inválidos.');
        if (!preg_match($nameRe, $apellidos)) throw new \Exception('Apellidos inválidos.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new \Exception('Correo inválido.');
        if ($creating && strlen($password) < 8) throw new \Exception('Contraseña muy corta (mín. 8).');

        return [
            'cedula' => $cedula,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => $telefono,
            'correo' => $email,
            'contraseña' => $password,
            'estado' => 'Activo',
        ];
    }
}
