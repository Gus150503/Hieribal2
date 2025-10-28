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
        // Inyecta PDO desde tu wrapper Database
        $this->model = new UsuarioInventario(Database::get($config['db']));
    }

    /* Helpers JSON */
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

    private function friendlyDbError(\Throwable $e): string
    {
        if ($e instanceof \PDOException && $e->getCode() === '23000') {
            $msg = $e->getMessage();
            if (stripos($msg, 'codigo_interno') !== false || stripos($msg, 'duplicate') !== false || stripos($msg, '1062') !== false) {
                return 'Ese código interno ya existe.';
            }
            return 'Error de integridad de datos.';
        }
        return $e->getMessage();
    }

    /** Vista */
    public function index(): void
    {
        $this->ensureAdmin();

        $this->render('admin/inventario/index', [
            'page_title' => 'Inventario',
            'esAdmin'    => true,
            'extra_js'   => [$this->config['app']['base_url'] . '/assets/js/admin_inventario.js?v=2'],
        ]);
    }

    /** API CRUD */
    public function api(): void
    {
        $this->ensureAdmin();

        try {
            $m      = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_REQUEST['action'] ?? '';

            /* LIST */
            if ($m === 'GET' && $action === 'list') {
                $q    = trim((string)($_GET['q'] ?? ''));
                $page = max(1, (int)($_GET['page'] ?? 1));
                $per  = max(1, min(50, (int)($_GET['per'] ?? 10)));

                $data = $this->model->listar($q, $page, $per); // items/page/per/total
                $this->json($data);
                return;
            }

            /* GET ONE */
            if ($m === 'GET' && $action === 'get') {
                $id = (int)($_GET['id'] ?? 0);
                if ($id <= 0) { $this->fail('ID inválido', 400); return; }

                $row = $this->model->obtener($id);
                if (!$row) { $this->fail('No encontrado', 404); return; }

                $this->json(['data' => $row], 200);
                return;
            }

            /* CREATE */
            if ($m === 'POST' && $action === 'create') {
                try {
                    $d  = $this->sanitize($_POST, true);
                    $id = $this->model->crear($d);
                    $this->ok(['id' => $id], 201);
                } catch (\Throwable $ex) {
                    $code = ($ex instanceof \Exception) ? 422 : 500;
                    $this->fail($this->friendlyDbError($ex), $code);
                }
                return;
            }

            /* UPDATE */
            if ($m === 'POST' && $action === 'update') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) { $this->fail('ID inválido', 400); return; }

                try {
                    $d = $this->sanitize($_POST, false);
                    $this->model->actualizar($id, $d);
                    $this->ok();
                } catch (\Throwable $ex) {
                    $code = ($ex instanceof \Exception) ? 422 : 500;
                    $this->fail($this->friendlyDbError($ex), $code);
                }
                return;
            }

            /* DELETE */
            if ($m === 'POST' && $action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) { $this->fail('ID inválido', 400); return; }

                try {
                    $this->model->eliminar($id);
                    $this->ok();
                } catch (\Throwable $ex) {
                    $this->fail($this->friendlyDbError($ex), 500);
                }
                return;
            }

            /* TOGGLE disponible/agotado */
            if ($m === 'POST' && $action === 'toggle') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) { $this->fail('ID inválido', 400); return; }

                try {
                    $res = $this->model->toggleEstado($id); // ['estado'=>...]
                    if (empty($res)) { $this->fail('No encontrado', 404); return; }
                    $this->ok([
                        'estado' => $res['estado'] ?? null,
                        'msg'    => ($res['estado'] === 'disponible')
                            ? 'Producto marcado como disponible.'
                            : 'Producto marcado como agotado.'
                    ]);
                } catch (\Throwable $ex) {
                    $this->fail($this->friendlyDbError($ex), 500);
                }
                return;
            }

            $this->fail('Acción no válida', 400);

        } catch (\Throwable $e) {
            $this->fail($this->friendlyDbError($e), 500);
        }
    }

    /** Sanitización / validación */
    private function sanitize(array $in, bool $creating): array
    {
        $producto_id  = (int)($in['producto_id'] ?? 0);
        $codigo       = trim((string)($in['codigo_interno'] ?? ''));
        $stock        = is_numeric($in['stock'] ?? null) ? (int)$in['stock'] : 0;
        $stock_min    = is_numeric($in['stock_minimo'] ?? null) ? (int)$in['stock_minimo'] : 0;
        $stock_max    = is_numeric($in['stock_maximo'] ?? null) ? (int)$in['stock_maximo'] : 0;
        $reorden      = is_numeric($in['punto_reorden'] ?? null) ? (int)$in['punto_reorden'] : 0;
        $ubicacion    = trim((string)($in['ubicacion'] ?? ''));

        if ($producto_id <= 0) throw new \Exception('Producto inválido');
        if ($codigo !== '' && \strlen($codigo) < 3) throw new \Exception('Código interno inválido (mín. 3)');
        if ($stock < 0 || $stock_min < 0 || $stock_max < 0 || $reorden < 0) {
            throw new \Exception('Los valores de stock y reorden no pueden ser negativos');
        }
        if ($stock_min > $stock_max) throw new \Exception('El stock mínimo no puede superar el máximo');
        if ($stock > $stock_max && $stock_max > 0) throw new \Exception('El stock no puede superar el máximo');
        if ($reorden > $stock_max && $stock_max > 0) throw new \Exception('El punto de reorden no puede superar el stock máximo');
        if ($ubicacion !== '' && \strlen($ubicacion) > 100) {
            throw new \Exception('La ubicación es demasiado larga (máx. 100)');
        }

        $estadoIn = strtolower(trim((string)($in['estado'] ?? 'disponible')));
        $allowed  = ['disponible', 'agotado', 'pendiente'];
        $estado   = in_array($estadoIn, $allowed, true) ? $estadoIn : 'disponible';

        return [
            'producto_id'    => $producto_id,
            'codigo_interno' => $codigo,
            'stock'          => $stock,
            'stock_minimo'   => $stock_min,
            'stock_maximo'   => $stock_max,
            'punto_reorden'  => $reorden,
            'ubicacion'      => $ubicacion,
            'estado'         => $estado,
        ];
    }
}
