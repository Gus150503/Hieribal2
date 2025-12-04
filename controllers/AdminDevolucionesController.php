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
            $pdo = Database::get($config['db']);
            $this->Devoluciones = new AdminDevoluciones($pdo);
        } catch (PDOException $e) {
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

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
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            session_start();
        }

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

        $productos   = $this->Devoluciones->getProductos();
        $clientes    = $this->Devoluciones->getClientes();
        $proveedores = $this->Devoluciones->getProveedores(); // ← viene del MODELO

        $base = $this->config['app']['base_url'] ?? '';

        $this->render(
            'admin/devoluciones/index',
            [
                'page_title'  => 'Gestión de Devoluciones',
                'esAdmin'     => true,
                'productos'   => $productos,
                'clientes'    => $clientes,
                'proveedores' => $proveedores,
                'extra_css'   => [$base . '/assets/css/admin_devoluciones.css?v=1'],
                'extra_js'    => [$base . '/assets/js/admin_devoluciones.js?v=1'],
            ]
        );
    }

    /* =====================================================
       API CRUD
    ===================================================== */
    /* =====================================================
   API CRUD
===================================================== */
public function api(): void
{
    $this->ensureAdmin();
    header('Content-Type: application/json; charset=utf-8');

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = $_REQUEST['action'] ?? '';

    try {
        /* ================== LISTAR ================== */
        if ($method === 'GET' && $action === 'list') {
            $q    = trim((string)($_GET['q'] ?? ''));
            $page = max(1, (int)($_GET['page'] ?? 1));
            $per  = max(1, min(50, (int)($_GET['per'] ?? 10)));

            $data = $this->Devoluciones->listar($q, $page, $per);
            $this->json($data);
            return;
        }

        /* =============== PRODUCTOS POR PROVEEDOR (para el combo dependiente) =============== */
        if ($method === 'GET' && $action === 'productos_proveedor') {
            $provId = (int)($_GET['proveedor_id'] ?? 0);
            if ($provId <= 0) {
                $this->fail('Proveedor inválido');
                return;
            }

            $productos = $this->Devoluciones->getProductosPorProveedor($provId);
            $this->json(['data' => $productos]);
            return;
        }

        /* ================== GET ONE ================== */
        if ($method === 'GET' && $action === 'get') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                $this->fail('ID inválido');
                return;
            }

            $row = $this->Devoluciones->obtener($id);
            $this->json(['data' => $row]);
            return;
        }

        /* ================== CREAR ================== */
        if ($method === 'POST' && $action === 'create') {
            $data = $this->sanitize($_POST);
            $id   = $this->Devoluciones->crear($data);
            $this->ok(['id' => $id], 201);
            return;
        }

        /* ================== ACTUALIZAR ================== */
        // SOLO se actualizan fecha_devolucion, estado y observaciones
        if ($method === 'POST' && $action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                $this->fail('ID inválido');
                return;
            }

            $fecha_dev = trim($_POST['fecha_devolucion'] ?? '');
            $estado    = trim($_POST['estado'] ?? '');
            $obs       = trim($_POST['observaciones'] ?? '');

            if ($fecha_dev === '') {
                $this->fail('Fecha de devolución requerida');
                return;
            }
            if ($estado === '') {
                $this->fail('Estado requerido');
                return;
            }

            $this->Devoluciones->actualizarBasico($id, $fecha_dev, $estado, $obs);
            $this->ok();
            return;
        }

        /* ================== ELIMINAR ================== */
        if ($method === 'POST' && $action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                $this->fail('ID inválido');
                return;
            }

            $this->Devoluciones->eliminar($id);
            $this->ok();
            return;
        }

        // Acción no reconocida
        $this->fail('Acción no válida');
    } catch (\Throwable $e) {
        $this->fail($e->getMessage(), 500);
    }
}


    /* =====================================================
       Sanitización y validación
    ===================================================== */
private function sanitize(array $in): array
{
    $origen        = strtolower(trim($in['origen'] ?? 'interno'));
    $cliente_id    = (int)($in['cliente_id'] ?? 0);
    $proveedor_id  = (int)($in['proveedor_id'] ?? 0);
    $producto_id   = (int)($in['producto_id'] ?? 0);
    $cantidad      = (int)($in['cantidad'] ?? 1);
    $numero_orden  = trim($in['numero_orden'] ?? '');
    $motivo        = trim($in['motivo_devolucion'] ?? '');
    $fecha_compra  = trim($in['fecha_compra'] ?? '');
    $fecha_dev     = trim($in['fecha_devolucion'] ?? '');
    $estado        = trim($in['estado'] ?? 'pendiente');
    $observaciones = trim($in['observaciones'] ?? '');

    // 🔹 Normalizamos: solo dos valores reales
    $origen = ($origen === 'cliente') ? 'cliente' : 'interno';

    if ($origen === 'cliente' && $cliente_id <= 0) {
        throw new \Exception('Cliente requerido.');
    }
    if ($origen === 'interno' && $proveedor_id <= 0) {
        throw new \Exception('Proveedor requerido.');
    }

    if ($producto_id <= 0)       throw new \Exception('Producto requerido.');
    if ($cantidad <= 0)          throw new \Exception('Cantidad inválida.');
    if ($numero_orden === '')    throw new \Exception('Número de orden requerido.');
    if ($motivo === '')          throw new \Exception('Motivo requerido.');
    if ($fecha_compra === '')    throw new \Exception('Fecha de compra requerida.');
    if ($fecha_dev === '')       throw new \Exception('Fecha de devolución requerida.');
    if ($estado === '')          throw new \Exception('Estado requerido.');

    return [
        'cliente_id'        => $origen === 'cliente' ? $cliente_id   : null,
        'proveedor_id'      => $origen === 'interno' ? $proveedor_id : null,
        'producto_id'       => $producto_id,
        'cantidad'          => $cantidad,
        'numero_orden'      => $numero_orden,
        'motivo_devolucion' => $motivo,
        'fecha_compra'      => $fecha_compra,
        'fecha_devolucion'  => $fecha_dev,
        'estado'            => $estado,
        'observaciones'     => $observaciones ?: null,
        'origen'            => $origen,
    ];
}

}
