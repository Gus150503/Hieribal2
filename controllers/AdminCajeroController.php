<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Models\CajeroAdmin; 

final class AdminCajeroController extends Controller
{
    private CajeroAdmin $Cajero;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->Cajero = new CajeroAdmin($config);
    }

    /* ============================
       Helpers comunes JSON y sesión
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

    /** Solo permite Admin y Cajero dentro del panel admin */
    private function ensureCajero(array $rolesPermitidos = ['Admin','Cajero']): void
    {
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            session_start();
        }

        $user = $_SESSION['admin'] ?? null;

        if (!$user) {
            $_SESSION['admin_error'] = 'Inicia sesión para continuar.';
            $base = rtrim($this->config['app']['base_url'] ?? '', '/');
            header("Location: {$base}/?r=admin_login");
            exit;
        }

        $rol = strtolower($user['rol'] ?? '');
        $rolesPermitidos = array_map('strtolower', $rolesPermitidos);

        if (!in_array($rol, $rolesPermitidos, true)) {
            $_SESSION['admin_error'] = 'No tienes permisos para acceder a este módulo.';
            $base = rtrim($this->config['app']['base_url'] ?? '', '/');
            header("Location: {$base}/?r=dashboard");
            exit;
        }
    }

    /** ID del usuario logueado en el panel admin */
    private function currentAdminId(): int
    {
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            session_start();
        }
        $user = $_SESSION['admin'] ?? null;
        return (int)($user['id_usuario'] ?? 0);
    }

    private function friendlyDbError(\Throwable $e): string
    {
        if ($e instanceof \PDOException && $e->getCode() === '23000') {
            $msg = $e->getMessage();
            if (stripos($msg, 'fk_') !== false || stripos($msg, 'foreign key') !== false) {
                return 'No se puede completar la operación por restricciones de datos relacionados.';
            }
            if (stripos($msg, 'duplicate') !== false || stripos($msg, '1062') !== false) {
                return 'Datos duplicados.';
            }
        }
        return $e->getMessage();
    }

    /* ============================
       Vistas
       ============================ */

    /** Vista principal del módulo cajero (POS) */
public function index(): void
{
    $this->ensureCajero();

    $base = rtrim($this->config['app']['base_url'] ?? '', '/');

    $this->render(
        'admin/cajero/index',
        [
            'page_title' => 'Cajero',
            'esAdmin'    => true, // 👈 esto fuerza el layout del panel admin
            'extra_css'  => [$base . '/assets/css/admin_cajero.css?v=1'],
            'extra_js'   => [$base . '/assets/js/admin_cajero.js?v=1'],
        ]
    );
}

    /* ============================
       API Cajero
       ============================ */

    public function api(): void
    {
        $this->ensureCajero();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $m      = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_REQUEST['action'] ?? '';

            /* LISTAR PRODUCTOS PARA EL SELECT */
            if ($m === 'GET' && $action === 'productos') {
                try {
                    $items = $this->Cajero->listarProductos();
                    $this->json(['items' => $items]);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            /* LISTAR CLIENTES (para el combo) */
            if ($m === 'GET' && $action === 'clientes') {
                try {
                    $items = $this->Cajero->listarClientes();
                    $this->json(['items' => $items]);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }


            /* BUSCAR PRODUCTOS (autocompletar) */
            if ($m === 'GET' && $action === 'buscar_producto') {
                try {
                    $q     = trim((string)($_GET['q'] ?? ''));
                    $items = $this->Cajero->buscarProductos($q);
                    $this->json(['items' => $items]);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            /* CREAR VENTA */
            if ($m === 'POST' && $action === 'crear_venta') {
                try {
                    $idUsuario = $this->currentAdminId();
                    if ($idUsuario <= 0) {
                        $this->fail('Usuario no válido', 401);
                        return;
                    }

                    $idCliente = (int)($_POST['id_cliente'] ?? 0);
                    $itemsJson = $_POST['items'] ?? '[]';
                    $items     = json_decode($itemsJson, true) ?: [];

                    if (empty($items)) {
                        $this->fail('No hay productos en la venta');
                        return;
                    }

                    $pagoEfectivo = (float)($_POST['pago_efectivo'] ?? 0);
                    $idVenta      = $this->Cajero->crearVenta($idUsuario, $idCliente, $items, $pagoEfectivo);

                    $this->ok(['id_venta' => $idVenta], 201);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            /* HISTORIAL DEL CAJERO */
            if ($m === 'GET' && $action === 'historial') {
                try {
                    $idUsuario = $this->currentAdminId();
                    if ($idUsuario <= 0) {
                        $this->fail('Usuario no válido', 401);
                        return;
                    }

                    $page = max(1, (int)($_GET['page'] ?? 1));
                    $per  = max(1, min(50, (int)($_GET['per'] ?? 10)));

                    $data = $this->Cajero->historialPorUsuario($idUsuario, $page, $per);
                    $this->json($data);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            /* DETALLE DE UNA VENTA */
            if ($m === 'GET' && $action === 'detalle') {
                try {
                    $idVenta = (int)($_GET['id_venta'] ?? 0);
                    if ($idVenta <= 0) {
                        $this->fail('ID de venta inválido');
                        return;
                    }

                    $venta   = $this->Cajero->obtenerVenta($idVenta);
                    $detalle = $this->Cajero->obtenerDetalleVenta($idVenta);

                    $this->json(['venta' => $venta, 'detalle' => $detalle]);
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
}
