<?php

declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Core\Database;
use Models\UsuarioProducto;
use PDOException;
use Models\Categoria;   // ← NUEVO


final class AdminProducto extends Controller
{
    private UsuarioProducto $Model;

    public function __construct(array $config)
    {
        parent::__construct($config);

        try {
            $pdo = Database::get($config['db']);
            $this->Model = new UsuarioProducto($pdo);
        } catch (PDOException $e) {
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    /* ---------- Helpers ---------- */
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
            header('Location: /?r=admin_login');
            exit;
        }
    }

    private function friendlyDbError(\Throwable $e): string
    {
        if ($e instanceof \PDOException && $e->getCode() === '23000') {
            $msg = $e->getMessage();
            if (stripos($msg, 'codigo_sku') !== false) return 'Ese SKU ya existe.';
            if (stripos($msg, 'duplicate') !== false || stripos($msg, '1062') !== false) {
                return 'Datos duplicados.';
            }
        }
        return $e->getMessage();
    }

    private function procesarImagen(): ?string
    {
        // 1. Si subieron archivo
        if (!empty($_FILES['imagen_archivo']['name'])) {

            $file = $_FILES['imagen_archivo'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $permitidas)) {
                throw new \Exception("Formato de imagen no permitido");
            }

            $nuevo = uniqid('prod_') . "." . $ext;

            $rutaFS = __DIR__ . "/../public/assets/img/" . $nuevo;


            if (!move_uploaded_file($file['tmp_name'], $rutaFS)) {
                throw new \Exception("No se pudo subir la imagen");
            }

            // URL final
            $base = rtrim($this->config['app']['base_url'], '/');
            return $base . "/assets/img/" . $nuevo;
        }

        // 2. Si vino URL en el POST
        if (!empty($_POST['imagen'])) {
            return trim($_POST['imagen']);
        }

        return null;
    }


    /* ---------- VISTA ---------- */
    public function index(): void
    {
        $this->ensureAdmin();
        $base = rtrim($this->config['app']['base_url'] ?? '', '/');
        $this->render('admin/productos/index', [
            'page_title' => 'Productos',
            'esAdmin'    => true,
            'extra_css'  => [$base . '/assets/css/admin_productos.css?v=1'],
            'extra_js'   => [$base . '/assets/js/admin_productos.js?v=2'],
        ]);
    }

    /* ---------- API CRUD ---------- */
    public function api(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $m      = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_REQUEST['action'] ?? '';

            if ($m === 'GET' && $action === 'list') {
                try {
                    $q    = trim((string)($_GET['q'] ?? ''));
                    $page = max(1, (int)($_GET['page'] ?? 1));
                    $per  = max(1, min(100, (int)($_GET['per'] ?? 10)));
                    $data = $this->Model->listar($q, $page, $per);
                    $this->json($data);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            if ($m === 'GET' && $action === 'get') {
                try {
                    $id = (int)($_GET['id'] ?? 0);
                    if ($id <= 0) {
                        $this->fail('ID inválido');
                        return;
                    }
                    $row = $this->Model->obtener($id);
                    $this->json(['data' => $row]);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            if ($m === 'POST' && $action === 'create') {
                try {
                    $d  = $this->sanitize($_POST, true);
                    $d['imagen'] = $this->procesarImagen();
                    $id = $this->Model->crear($d);
                    $this->ok(['id' => $id], 201);
                } catch (\Throwable $ex) {
                    $this->fail($this->friendlyDbError($ex), 500);
                }
                return;
            }

            if ($m === 'POST' && $action === 'update') {
                try {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id <= 0) {
                        $this->fail('ID inválido');
                        return;
                    }
                    $d = $this->sanitize($_POST, false);
                    $d['imagen'] = $this->procesarImagen();
                    $this->Model->actualizar($id, $d);
                    $this->ok();
                } catch (\Throwable $ex) {
                    $this->fail($this->friendlyDbError($ex), 500);
                }
                return;
            }

            if ($m === 'POST' && $action === 'delete') {
                try {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id <= 0) {
                        $this->fail('ID inválido');
                        return;
                    }
                    $this->Model->eliminar($id);
                    $this->ok();
                } catch (\Throwable $ex) {
                    $this->fail($this->friendlyDbError($ex), 500);
                }
                return;
            }

            if ($m === 'POST' && $action === 'toggle') {
                try {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id <= 0) {
                        $this->fail('ID inválido');
                        return;
                    }
                    $res = $this->Model->toggleEstado($id);
                    $this->ok(['estado' => $res['estado'] ?? null]);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            $this->fail('Acción no válida', 400);
        } catch (\Throwable $e) {
            $this->fail($e->getMessage(), 500);
        }
    }

    /* ---------- Sanitización Productos (según tu esquema) ---------- */
    private function sanitize(array $in, bool $creating): array
    {
        $nombre = trim($in['nombre'] ?? '');
        if ($nombre === '' || strlen($nombre) > 255) throw new \Exception('Nombre inválido');

        $categoria      = trim($in['categoria'] ?? '');
        $marca          = trim($in['marca'] ?? '');
        $presentacion   = trim($in['presentacion'] ?? '');
        $descripcion    = trim($in['descripcion'] ?? '');
        $stock_actual   = is_numeric($in['stock_actual'] ?? null) ? (int)$in['stock_actual'] : 0;
        $stock_minimo   = is_numeric($in['stock_minimo'] ?? null) ? (int)$in['stock_minimo'] : 0;
        $lote           = trim($in['lote'] ?? '');
        $f_vencimiento  = trim($in['f_vencimiento'] ?? '');
        $precio_compra  = is_numeric($in['precio_compra'] ?? null) ? (float)$in['precio_compra'] : 0.0;
        $precio_venta   = is_numeric($in['precio_venta'] ?? null) ? (float)$in['precio_venta']  : 0.0;
        $iva            = is_numeric($in['iva'] ?? null)           ? (float)$in['iva']          : 0.0;
        $codigo_sku     = trim($in['codigo_sku'] ?? '');
        $ubicacion      = trim($in['ubicacion'] ?? '');
        $imagen         = trim($in['imagen'] ?? '');
        $estadoIn       = strtolower(trim($in['estado'] ?? 'activo'));
        $estado         = in_array($estadoIn, ['activo', 'inactivo'], true) ? $estadoIn : 'activo';

        if ($stock_actual < 0 || $stock_minimo < 0) {
            throw new \Exception('El stock no puede ser negativo');
        }
        if ($stock_minimo > $stock_actual && $stock_actual > 0) {
            // permisivo, pero te aviso
            // throw new \Exception('El stock mínimo no puede exceder el stock actual');
        }
        if ($f_vencimiento !== '') {
            $ts = strtotime($f_vencimiento);
            if ($ts === false) throw new \Exception('Fecha de vencimiento inválida');
            $f_vencimiento = date('Y-m-d', $ts);
        } else {
            $f_vencimiento = null;
        }

        return [
            'nombre'         => $nombre,
            'categoria'      => $categoria,
            'marca'          => $marca,
            'presentacion'   => $presentacion,
            'descripcion'    => $descripcion,
            'stock_actual'   => $stock_actual,
            'stock_minimo'   => $stock_minimo,
            'lote'           => $lote,
            'f_vencimiento'  => $f_vencimiento,
            'precio_compra'  => $precio_compra,
            'precio_venta'   => $precio_venta,
            'iva'            => $iva,
            'codigo_sku'     => $codigo_sku,
            'ubicacion'      => $ubicacion,
            'estado'         => $estado,
            'imagen'         => ($imagen !== '') ? $imagen : null,
        ];
    }
}
