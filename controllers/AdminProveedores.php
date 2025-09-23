<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;

use Models\UsuarioProveedores;

final class AdminProveedores extends Controller
{
    private UsuarioProveedores $Model;

public function __construct(array $config)
{
    parent::__construct($config);
    // ✅ Crear la conexión PDO a partir de config
    $pdo = \Core\Database::get($config['db']);
    $this->Model = new UsuarioProveedores($pdo);
}


    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function ok(array $extra = [], int $status = 200): void { $this->json(['ok'=>true] + $extra, $status); }
    private function fail(string $msg, int $status = 400, array $extra = []) { $this->json(['ok'=>false,'msg'=>$msg] + $extra, $status); }

    private function ensureAdmin(): void
    {
        if (session_status() !== \PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['admin'])) {
            $_SESSION['admin_error'] = 'Inicia sesión para continuar.';
            header('Location: /?r=admin_login'); exit;
        }
    }

    private function friendlyDbError(\Throwable $e): string
    {
        if ($e instanceof \PDOException && $e->getCode() === '23000') {
            if (stripos($e->getMessage(), 'nit') !== false) return 'Ese NIT ya existe.';
            return 'Datos duplicados.';
        }
        return $e->getMessage();
    }

    public function index(): void
    {
        $this->ensureAdmin();
        $this->render('admin/proveedores/index', [
            'page_title' => 'Proveedores',
            'esAdmin'    => true,
            'extra_js'   => [$this->config['app']['base_url'] . '/assets/js/admin_proveedores.js?v=1'],
        ]);
    }

    public function api(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $m = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_REQUEST['action'] ?? '';

            if ($m === 'GET' && $action === 'list') {
                try {
                    $q = trim((string)($_GET['q'] ?? ''));
                    $page = max(1, (int)($_GET['page'] ?? 1));
                    $per = max(1, min(100, (int)($_GET['per'] ?? 10)));
                    $data = $this->Model->listar($q, $page, $per);
                    $this->json($data);
                } catch (\Throwable $e) { $this->fail($this->friendlyDbError($e), 500); }
                return;
            }

            if ($m === 'GET' && $action === 'get') {
                try {
                    $id = (int)($_GET['id'] ?? 0);
                    if ($id <= 0) { $this->fail('ID inválido'); return; }
                    $row = $this->Model->obtener($id);
                    $this->json(['data'=>$row]);
                } catch (\Throwable $e) { $this->fail($this->friendlyDbError($e), 500); }
                return;
            }

            if ($m === 'POST' && $action === 'create') {
                try {
                    $d = $this->sanitize($_POST, true);
                    $id = $this->Model->crear($d);
                    $this->ok(['id'=>$id], 201);
                } catch (\Throwable $e) { $this->fail($this->friendlyDbError($e), 500); }
                return;
            }

            if ($m === 'POST' && $action === 'update') {
                try {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id <= 0) { $this->fail('ID inválido'); return; }
                    $d = $this->sanitize($_POST, false);
                    $this->Model->actualizar($id, $d);
                    $this->ok();
                } catch (\Throwable $e) { $this->fail($this->friendlyDbError($e), 500); }
                return;
            }

            if ($m === 'POST' && $action === 'delete') {
                try {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id <= 0) { $this->fail('ID inválido'); return; }
                    $this->Model->eliminar($id);
                    $this->ok();
                } catch (\Throwable $e) { $this->fail($this->friendlyDbError($e), 500); }
                return;
            }

            if ($m === 'POST' && $action === 'toggle') {
                try {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id <= 0) { $this->fail('ID inválido'); return; }
                    $res = $this->Model->toggleEstado($id);
                    $this->ok(['estado'=>$res['estado'] ?? null]);
                } catch (\Throwable $e) { $this->fail($this->friendlyDbError($e), 500); }
                return;
            }

            $this->fail('Acción no válida', 400);
        } catch (\Throwable $e) {
            $this->fail($e->getMessage(), 500);
        }
    }

    private function sanitize(array $in, bool $creating): array
    {
        $empresa = trim($in['empresa'] ?? '');
        if ($empresa === '' || strlen($empresa) > 255) throw new \Exception('Empresa inválida');

        $nit = trim($in['nit'] ?? '');
        $nombre_contacto = trim($in['nombre_contacto'] ?? '');
        $telefono = trim($in['telefono'] ?? '');
        $email = trim($in['email'] ?? '');
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new \Exception('Email inválido');

        $direccion = trim($in['direccion'] ?? '');
        $ciudad = trim($in['ciudad'] ?? '');
        $condiciones_pago = trim($in['condiciones_pago'] ?? '');

        $estadoIn = strtolower(trim($in['estado'] ?? 'activo'));
        $estado = in_array($estadoIn, ['activo','inactivo'], true) ? $estadoIn : 'activo';

        return [
            'empresa' => $empresa,
            'nit' => $nit,
            'nombre_contacto' => $nombre_contacto,
            'telefono' => $telefono,
            'email' => $email,
            'direccion' => $direccion,
            'ciudad' => $ciudad,
            'condiciones_pago' => $condiciones_pago,
            'estado' => $estado,
        ];
    }
}
