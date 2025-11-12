<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Models\Admincliente;

final class AdminClientesController extends Controller
{
    private Admincliente $cliente;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->cliente = new Admincliente($config);
    }

    /* ========== helpers JSON ========== */
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

    /* ========== vista ========== */
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

    /* ========== API CRUD ========== */
    public function api(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_REQUEST['action'] ?? '';

            // LIST
            if ($method === 'GET' && $action === 'list') {
                $q    = trim((string)($_GET['q'] ?? ''));
                $page = max(1, (int)($_GET['page'] ?? 1));
                $per  = max(1, min(50, (int)($_GET['per'] ?? 10)));
                $data = $this->cliente->listar($q, $page, $per);
                $this->json($data);
                return;
            }

            // GET ONE
            if ($method === 'GET' && $action === 'get') {
                $id = (int)($_GET['id'] ?? 0);
                if ($id <= 0) { $this->fail('ID inválido'); return; }
                $row = $this->cliente->obtener($id);
                $this->json(['data' => $row]);
                return;
            }

            // CREATE
            if ($method === 'POST' && $action === 'create') {
                $d  = $this->sanitize($_POST, true);
                $id = $this->cliente->crear($d);
                $this->ok(['id' => $id], 201);
                return;
            }

            // UPDATE
            if ($method === 'POST' && $action === 'update') {
                $id = (int)($_POST['id_cliente'] ?? 0);
                if ($id <= 0) { $this->fail('ID inválido'); return; }
                $d = $this->sanitize($_POST, false);
                $this->cliente->actualizar($id, $d);
                $this->ok();
                return;
            }

            // DELETE
            if ($method === 'POST' && $action === 'delete') {
                $id = (int)($_POST['id_cliente'] ?? 0);
                if ($id <= 0) { $this->fail('ID inválido'); return; }
                $this->cliente->eliminar($id);
                $this->ok();
                return;
            }

            // TOGGLE ESTADO
            if ($method === 'POST' && $action === 'toggle') {
                $id = (int)($_POST['id_cliente'] ?? 0);
                if ($id <= 0) { $this->fail('ID inválido'); return; }

                $res = $this->cliente->toggleEstado($id);   // debe devolver ['estado' => 'Activo'|'Inactivo']
                $nuevo = (string)($res['estado'] ?? '');
                $msg = (strcasecmp($nuevo, 'Activo') === 0) ? 'Cliente activado.' : 'Cliente inactivado.';

                $this->ok(['estado' => $nuevo, 'msg' => $msg]);
                return;
            }

            $this->fail('Acción no válida', 400);

        } catch (\Throwable $e) {
            $this->fail($e->getMessage(), 500);
        }
    }

    /* ========== sanitize/validar ========== */
    private function sanitize(array $in, bool $creating): array
    {
        $nameRe    = '/^[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,60}$/u';
        $email     = trim($in['correo']     ?? '');
        $cedula    = trim($in['cedula']     ?? '');
        $nombres   = trim($in['nombres']    ?? '');
        $apellidos = trim($in['apellidos']  ?? '');
        $telefono  = trim($in['telefono']   ?? '');
        $password  = trim($in['contraseña'] ?? ''); // usa 'contrasena' en el POST

        if (!preg_match('/^\d{6,15}$/', $cedula))          throw new \Exception('Cédula inválida.');
        if (!preg_match($nameRe, $nombres))                throw new \Exception('Nombres inválidos.');
        if (!preg_match($nameRe, $apellidos))              throw new \Exception('Apellidos inválidos.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))    throw new \Exception('Correo inválido.');
        if ($creating && strlen($password) < 8)            throw new \Exception('Contraseña muy corta (mín. 8).');

        // No fuerces el estado aquí si estás editando
        $out = [
            'cedula'     => $cedula,
            'nombres'    => $nombres,
            'apellidos'  => $apellidos,
            'telefono'   => $telefono,
            'correo'     => $email,
            'contraseña' => $password,   // el modelo decide si hash/ignora vacío en update
        ];
        if ($creating) $out['estado'] = 'Activo';

        return $out;
    }
}
