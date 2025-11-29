<?php
    declare(strict_types=1);

    namespace Controllers;

    use Core\Controller;
    use Models\AdminVenta;

    final class AdminVentasController extends Controller
    {
        private AdminVenta $Venta;

        public function __construct(array $config)
        {
            parent::__construct($config);
            $this->Venta = new AdminVenta($config);
        }

        /* ============================
        Helpers JSON y sesión
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
                header('Location: /?r=admin_login'); 
                exit;
            }
        }

        /* ============================
        Vista
        ============================ */
        public function index(): void
        {
            $this->ensureAdmin();

            // combos
            $productos  = $this->Venta->getProductos();
            $clientes   = $this->Venta->getClientes();
            $vendedores = $this->Venta->getVendedores();

            $base = $this->config['app']['base_url'] ?? '';

            $this->render('admin/ventas/index', [
                'page_title'  => 'Reporte de ventas',
                'esAdmin'     => true,
                'productos'   => $productos,
                'clientes'    => $clientes,
                'vendedores'  => $vendedores,
                'extra_css'   => [$base . '/assets/css/admin_ventas.css?v=1'], // si quieres css propio
                'extra_js'    => [$base . '/assets/js/admin_ventas.js?v=1'],
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

                /* LIST */
                if ($m === 'GET' && $action === 'list') {
                    try {
                        $q    = trim((string)($_GET['q'] ?? ''));
                        $page = max(1, (int)($_GET['page'] ?? 1));
                        $per  = max(1, min(50, (int)($_GET['per'] ?? 10)));

                        $data = $this->Venta->listar($q, $page, $per);
                        $this->json($data);
                    } catch (\Throwable $e) {
                        $this->fail($this->friendlyDbError($e), 500);
                    }
                    return;
                }

                /* GET ONE */
                if ($m === 'GET' && $action === 'get') {
                    try {
                        $id = (int)($_GET['id'] ?? 0);
                        if ($id <= 0) { $this->fail('ID inválido'); return; }
                        $row = $this->Venta->obtener($id);
                        $this->json(['data' => $row]);
                    } catch (\Throwable $e) {
                        $this->fail($this->friendlyDbError($e), 500);
                    }
                    return;
                }

                /* CREATE */
                if ($m === 'POST' && $action === 'create') {
                    try {
                        $d  = $this->sanitize($_POST);
                        $id = $this->Venta->crear($d);
                        $this->ok(['id' => $id], 201);
                    } catch (\Throwable $ex) {
                        $this->fail($this->friendlyDbError($ex), 500);
                    }
                    return;
                }

                /* UPDATE */
                if ($m === 'POST' && $action === 'update') {
                    try {
                        $id = (int)($_POST['id'] ?? 0);
                        if ($id <= 0) { $this->fail('ID inválido'); return; }

                        $d = $this->sanitize($_POST);
                        $this->Venta->actualizar($id, $d);
                        $this->ok();
                    } catch (\Throwable $ex) {
                        $this->fail($this->friendlyDbError($ex), 500);
                    }
                    return;
                }

                /* DELETE */
                if ($m === 'POST' && $action === 'delete') {
                    try {
                        $id = (int)($_POST['id'] ?? 0);
                        if ($id <= 0) { $this->fail('ID inválido'); return; }
                        $this->Venta->eliminar($id);
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

        /* ============================
        Sanitización / validación
        ============================ */
        private function sanitize(array $in): array
        {
            $numero_factura = trim($in['numero_factura'] ?? '');
            $producto_id    = (int)($in['producto_id'] ?? 0);
            $cantidad       = (int)($in['cantidad'] ?? 0);
            $precio         = (float)($in['precio'] ?? 0);
            $total          = (float)($in['total'] ?? ($cantidad * $precio));
            $cliente_id     = (int)($in['cliente_id'] ?? 0);
            $vendedor_id    = (int)($in['vendedor_id'] ?? 0);
            $metodo_pago    = trim($in['metodo_pago'] ?? '');
            $fecha          = trim($in['fecha'] ?? '');
            $observaciones  = trim($in['observaciones'] ?? '');

            if ($numero_factura === '') {
                throw new \Exception('Número de factura requerido');
            }
            if ($producto_id <= 0) throw new \Exception('Producto requerido');
            if ($cliente_id <= 0)  throw new \Exception('Cliente requerido');
            if ($vendedor_id <= 0) throw new \Exception('Vendedor requerido');
            if ($cantidad <= 0)    throw new \Exception('Cantidad debe ser mayor a 0');
            if ($precio <= 0)      throw new \Exception('Precio debe ser mayor a 0');
            if ($total <= 0)       throw new \Exception('Total inválido');
            if ($metodo_pago === '') throw new \Exception('Método de pago requerido');
            if ($fecha === '')       throw new \Exception('Fecha requerida');

            // pequeña validación de fecha YYYY-MM-DD
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                throw new \Exception('Fecha inválida');
            }

            return [
                'numero_factura' => $numero_factura,
                'producto_id'    => $producto_id,
                'cantidad'       => $cantidad,
                'precio'         => $precio,
                'total'          => $total,
                'cliente_id'     => $cliente_id,
                'vendedor_id'    => $vendedor_id,
                'metodo_pago'    => $metodo_pago,
                'fecha'          => $fecha,
                'observaciones'  => $observaciones !== '' ? $observaciones : null,
            ];
        }

        private function friendlyDbError(\Throwable $e): string
        {
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                $msg = $e->getMessage();
                if (stripos($msg, 'foreign key') !== false) return 'Error de integridad referencial (FK).';
                if (stripos($msg, 'duplicate') !== false || stripos($msg, '1062') !== false) {
                    return 'Registro duplicado (número de factura ya existe?).';
                }
            }
            return $e->getMessage();
        }
    }
