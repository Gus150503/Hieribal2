<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Core\Database;
use Models\UsuarioInventario;

final class AdminInventario extends Controller
{
    private UsuarioInventario $model;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->model = new UsuarioInventario(Database::get($config['db']));
    }

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
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['admin'])) {
            $_SESSION['admin_error'] = 'Inicia sesión para continuar.';
            header('Location: /?r=admin_login');
            exit;
        }
    }

    private function friendlyDbError(\Throwable $e): string
    {
        if ($e instanceof \PDOException && $e->getCode() === '23000') {
            return 'Error de integridad de datos.';
        }
        return $e->getMessage();
    }

    /** Vista principal */
    public function index(): void
    {
        $this->ensureAdmin();
        $this->render('admin/inventario/index', [
            'page_title' => 'Inventario',
            'esAdmin'    => true,
            'extra_js'   => [$this->config['app']['base_url'] . '/assets/js/admin_inventario.js?v=1'],
        ]);
    }

    /** API CRUD */
    public function api(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $m = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_REQUEST['action'] ?? '';

            if ($m === 'GET' && $action === 'list') {
                $q    = trim((string)($_GET['q'] ?? ''));
                $page = max(1, (int)($_GET['page'] ?? 1));
                $per  = max(1, min(100, (int)($_GET['per'] ?? 10)));
                $data = $this->model->listar($q, $page, $per);
                $this->json($data);
                return;
            }

            if ($m === 'GET' && $action === 'get') {
                $id = (int)($_GET['id'] ?? 0);
                if ($id <= 0) {
                    $this->fail('ID inválido');
                    return;
                }
                $row = $this->model->obtener($id);
                $this->json(['data' => $row]);
                return;
            }

            if ($m === 'POST' && $action === 'create') {
                $d = $this->sanitize($_POST, true);
                $id = $this->model->crear($d);
                $this->ok(['id' => $id], 201);
                return;
            }

            if ($m === 'POST' && $action === 'update') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $this->fail('ID inválido');
                    return;
                }
                $d = $this->sanitize($_POST, false);
                $this->model->actualizar($id, $d);
                $this->ok();
                return;
            }

            if ($m === 'POST' && $action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $this->fail('ID inválido');
                    return;
                }
                $this->model->eliminar($id);
                $this->ok();
                return;
            }

            if ($m === 'POST' && $action === 'toggle') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $this->fail('ID inválido');
                    return;
                }
                $res = $this->model->toggleEstado($id);
                $this->ok(['estado' => $res['estado'] ?? null]);
                return;
            }

            $this->fail('Acción no válida', 400);

        } catch (\Throwable $e) {
            $this->fail($this->friendlyDbError($e), 500);
        }
    }

    /** Sanitización de inputs */
    private function sanitize(array $in, bool $creating): array
    {
        $producto_id = (int)($in['producto_id'] ?? 0);
        if ($producto_id <= 0) {
            throw new \Exception('Producto inválido');
        }

        $codigo_interno = trim($in['codigo_interno'] ?? '');
        $stock          = is_numeric($in['stock'] ?? null) ? (int)$in['stock'] : 0;
        $stock_minimo   = is_numeric($in['stock_minimo'] ?? null) ? (int)$in['stock_minimo'] : 0;
        $stock_maximo   = is_numeric($in['stock_maximo'] ?? null) ? (int)$in['stock_maximo'] : 0;
        $punto_reorden  = is_numeric($in['punto_reorden'] ?? null) ? (int)$in['punto_reorden'] : 0;
        $ubicacion      = trim($in['ubicacion'] ?? '');

        $estadoIn = strtolower(trim($in['estado'] ?? 'disponible'));
        $allowed  = ['disponible', 'agotado', 'pendiente'];
        $estado   = in_array($estadoIn, $allowed, true) ? $estadoIn : 'disponible';

        return [
            'producto_id'    => $producto_id,
            'codigo_interno' => $codigo_interno,
            'stock'          => $stock,
            'stock_minimo'   => $stock_minimo,
            'stock_maximo'   => $stock_maximo,
            'punto_reorden'  => $punto_reorden,
            'ubicacion'      => $ubicacion,
            'estado'         => $estado,
        ];
    }
}
