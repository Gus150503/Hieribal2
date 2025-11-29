<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Core\Database;
use Models\AdminDevoluciones;
use PDOException;

final class AdminDevolucionesController extends Controller
{
    private AdminDevoluciones $Devoluciones;

    public function __construct(array $config)
    {
        parent::__construct($config);

        try {
            // Igual que en AdminProducto
            $pdo = Database::get($config['db']);
            $this->Devoluciones = new AdminDevoluciones($pdo);
        } catch (PDOException $e) {
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    // ... TODO LO DEMÁS IGUAL QUE YA LO TENÍAS ...

        /* =====================================================
        Helpers JSON + sesión admin
        ===================================================== */

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


        /* =====================================================
        Vista principal
                ===================================================== */
            public function index(): void
        {
            $this->ensureAdmin();

            // Si necesitas combos (solo productos)
            $productos = $this->Devoluciones->getProductos();

            $base = $this->config['app']['base_url'] ?? '';

            $this->render('admin/devoluciones/index', [
                'page_title'  => 'Gestión de Devoluciones',
                'esAdmin'     => true,

                'productos'   => $productos,

                'extra_css' => [$base . '/assets/css/admin_devoluciones.css?v=1'],
                'extra_js'  => [$base . '/assets/js/admin_devoluciones.js?v=1'],
            ]);
        }


        /* =====================================================
        API CRUD
        ===================================================== */
        public function api(): void
        {
            $this->ensureAdmin();
            header('Content-Type: application/json; charset=utf-8');

            try {
                $m      = $_SERVER['REQUEST_METHOD'] ?? 'GET';
                $action = $_REQUEST['action'] ?? '';

                /* ===== LIST ===== */
                if ($m === 'GET' && $action === 'list') {
                    try {
                        $q    = trim((string)($_GET['q'] ?? ''));
                        $page = max(1, (int)($_GET['page'] ?? 1));
                        $per  = max(1, min(50, (int)($_GET['per'] ?? 10)));

                        $data = $this->Devoluciones->listar($q, $page, $per);
                        $this->json($data);
                    } catch (\Throwable $e) {
                        $this->fail($this->friendlyDbError($e), 500);
                    }
                    return;
                }

                /* ===== GET ONE ===== */
                if ($m === 'GET' && $action === 'get') {
                    try {
                        $id = (int)($_GET['id'] ?? 0);
                        if ($id <= 0) { $this->fail('ID inválido'); return; }
                        $row = $this->Devoluciones->obtener($id);
                        $this->json(['data' => $row]);
                    } catch (\Throwable $e) {
                        $this->fail($this->friendlyDbError($e), 500);
                    }
                    return;
                }

                /* ===== CREATE ===== */
                if ($m === 'POST' && $action === 'create') {
                    try {
                        $d  = $this->sanitize($_POST);
                        $id = $this->Devoluciones->crear($d);
                        $this->ok(['id' => $id], 201);
                    } catch (\Throwable $ex) {
                        $this->fail($this->friendlyDbError($ex), 500);
                    }
                    return;
                }

                /* ===== UPDATE ===== */
                if ($m === 'POST' && $action === 'update') {
                    try {
                        $id = (int)($_POST['id'] ?? 0);
                        if ($id <= 0) { $this->fail('ID inválido'); return; }

                        $d = $this->sanitize($_POST);
                        $this->Devoluciones->actualizar($id, $d);
                        $this->ok();
                    } catch (\Throwable $ex) {
                        $this->fail($this->friendlyDbError($ex), 500);
                    }
                    return;
                }

                /* ===== DELETE ===== */
                if ($m === 'POST' && $action === 'delete') {
                    try {
                        $id = (int)($_POST['id'] ?? 0);
                        if ($id <= 0) { $this->fail('ID inválido'); return; }
                        $this->Devoluciones->eliminar($id);
                        $this->ok();
                    } catch (\Throwable $ex) {
                        $this->fail($this->friendlyDbError($ex), 500);
                    }
                    return;
                }

                $this->fail('Acción no válida', 400);
            } catch (\Throwable $e) {
                $this->fail($e->getMessage(), 500);
            }
        }


        /* =====================================================
        Sanitización / Validación
        ===================================================== */
        private function sanitize(array $in): array
        {
            $nombre_cliente    = trim($in['nombre_cliente']    ?? '');
            $correo            = trim($in['correo']            ?? '');
            $numero_orden      = trim($in['numero_orden']      ?? '');
            $telefono          = trim($in['telefono']          ?? '');
            $producto          = trim($in['producto']          ?? '');
            $motivo            = trim($in['motivo_devolucion'] ?? '');
            $fecha_compra      = trim($in['fecha_compra']      ?? '');
            $fecha_devolucion  = trim($in['fecha_devolucion']  ?? '');
            $observaciones     = trim($in['observaciones']     ?? '');
            $estado            = trim($in['estado']            ?? '');

            if ($nombre_cliente === '')   throw new \Exception('Nombre del cliente requerido');
            if ($correo === '')           throw new \Exception('Correo requerido');
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Correo inválido');
            }
            if ($numero_orden === '')     throw new \Exception('Número de orden requerido');
            if ($telefono === '')         throw new \Exception('Teléfono requerido');
            if ($producto === '')         throw new \Exception('Producto requerido');
            if ($motivo === '')           throw new \Exception('Motivo de devolución requerido');
            if ($fecha_compra === '')     throw new \Exception('Fecha de compra requerida');
            if ($fecha_devolucion === '') throw new \Exception('Fecha de devolución requerida');
            if ($estado === '')           throw new \Exception('Estado requerido');

            return [
                'nombre_cliente'    => $nombre_cliente,
                'correo'            => $correo,
                'numero_orden'      => $numero_orden,
                'telefono'          => $telefono,
                'producto'          => $producto,
                'motivo_devolucion' => $motivo,
                'fecha_compra'      => $fecha_compra,
                'fecha_devolucion'  => $fecha_devolucion,
                'observaciones'     => $observaciones !== '' ? $observaciones : null,
                'estado'            => $estado,
            ];
        }


        /* =====================================================
        Traducción amigable de errores SQL
        ===================================================== */
        private function friendlyDbError(\Throwable $e): string
        {
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                $msg = $e->getMessage();
                if (stripos($msg, 'foreign key') !== false) return 'Violación de integridad referencial.';
                if (stripos($msg, 'duplicate') !== false || stripos($msg, '1062') !== false) {
                    return 'Registro duplicado (orden ya existe?).';
                }
            }
            return $e->getMessage();
        }
    }
