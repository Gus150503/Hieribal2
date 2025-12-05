<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Core\Database;
use Models\UsuarioProveedores;
use PDOException;

/** controllers/AdminProveedores.php
 * MODULO PROVEEDORES / Juliana Lugo  / Controlador de administración de Proveedores. 
 *
 * Se encarga de:
 *  - Renderizar la vista principal del módulo de proveedores.
 *  - Exponer la API CRUD para gestionar proveedores.
 *  - Gestionar el catálogo de productos asociados a cada proveedor.
 */
final class AdminProveedores extends Controller
{
    /**
     * Modelo que encapsula la lógica de acceso a datos de proveedores.
     */
    private UsuarioProveedores $Model;

    /**
     * Constructor.
     *
     * @param array $config Configuración general de la aplicación (DB, rutas, etc.).
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        // Inicializa la conexión PDO y el modelo de proveedores
        $pdo = Database::get($config['db']);
        $this->Model = new UsuarioProveedores($pdo);
    }

    /* =========================================================
     * Helpers JSON (respuestas estándar para la API)
     * ========================================================= */

    /**
     * Envía una respuesta JSON con código HTTP.
     *
     * @param array $data   Datos a codificar en JSON.
     * @param int   $status Código de estado HTTP (por defecto 200).
     */
    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Respuesta JSON de éxito.
     *
     * @param array $extra  Datos adicionales a incluir.
     * @param int   $status Código HTTP (200 por defecto).
     */
    private function ok(array $extra = [], int $status = 200): void
    {
        $this->json(['ok' => true] + $extra, $status);
    }

    /**
     * Respuesta JSON de error.
     *
     * @param string $msg    Mensaje de error legible.
     * @param int    $status Código HTTP (400 por defecto).
     * @param array  $extra  Datos extra para depuración si se requiere.
     */
    private function fail(string $msg, int $status = 400, array $extra = []): void
    {
        $this->json(['ok' => false, 'msg' => $msg] + $extra, $status);
    }

    /**
     * Verifica que el usuario actual tenga sesión de administrador.
     *
     * Si no está autenticado, lo redirige al login de admin.
     */
    private function ensureAdmin(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['admin'])) {
            $_SESSION['admin_error'] = 'Inicia sesión para continuar.';
            header('Location: /?r=admin_login');
            exit;
        }
    }

    /**
     * Traduce errores de base de datos a mensajes más amigables.
     *
     * @param \Throwable $e Excepción capturada.
     *
     * @return string Mensaje amigable para el usuario.
     */
    private function friendlyDbError(\Throwable $e): string
    {
        if ($e instanceof PDOException && $e->getCode() === '23000') {
            $msg = $e->getMessage();

            // Clave única sobre NIT
            if (stripos($msg, 'nit') !== false) {
                return 'Ese NIT ya existe.';
            }

            // Cualquier otro error de duplicado
            if (
                stripos($msg, 'duplicate') !== false
                || stripos($msg, '1062') !== false
            ) {
                return 'Datos duplicados.';
            }

            // Restricción de llave foránea (por ejemplo, proveedor con productos asociados)
            if (
                stripos($msg, 'foreign key') !== false
                || stripos($msg, '1451') !== false
            ) {
                return 'No se puede eliminar porque tiene información relacionada.';
            }
        }

        // Mensaje por defecto (útil para depuración)
        return $e->getMessage();
    }

    /* =========================================================
     * Vista principal
     * ========================================================= */

    /**
     * Muestra la vista del módulo de proveedores en el panel admin.
     */
    public function index(): void
    {
        $this->ensureAdmin();

        $this->render('admin/proveedores/index', [
            'page_title' => 'Proveedores',
            'esAdmin'    => true,
            'extra_js'   => [
                $this->config['app']['base_url'] . '/assets/js/admin_proveedores.js?v=2',
            ],
        ]);
    }

    /* =========================================================
     * API CRUD + gestión de productos por proveedor
     * ========================================================= */

    /**
     * Punto de entrada principal de la API de Proveedores.
     *
     * Según el método HTTP y el parámetro 'action' realiza:
     *  - list              : listar proveedores
     *  - get               : obtener un proveedor
     *  - create            : crear proveedor
     *  - update            : actualizar proveedor
     *  - delete            : eliminar proveedor
     *  - toggle            : activar / inactivar proveedor
     *  - productos_catalogo: catálogo general de productos (para <select>)
     *  - productos_proveedor: productos asociados a un proveedor
     *  - productos_save    : guardar/actualizar productos de un proveedor
     */
    public function api(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_REQUEST['action'] ?? '';

            /* ================== LISTAR PROVEEDORES ================== */
            if ($method === 'GET' && $action === 'list') {
                try {
                    $q    = trim((string)($_GET['q'] ?? ''));
                    $page = max(1, (int)($_GET['page'] ?? 1));
                    $per  = max(1, min(100, (int)($_GET['per'] ?? 10)));

                    $data  = $this->Model->listar($q, $page, $per);
                    $items = $data['items'] ?? ($data['data'] ?? []);
                    $total = (int)($data['total'] ?? 0);

                    $this->json([
                        'items' => $items,
                        'total' => $total,
                        'page'  => $page,
                        'per'   => $per,
                    ]);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            /* ================== GET ONE PROVEEDOR ================== */
            if ($method === 'GET' && $action === 'get') {
                try {
                    // Soporta tanto id como id_proveedor
                    $id = (int)($_GET['id'] ?? ($_GET['id_proveedor'] ?? 0));
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

            /* ================== NUEVO PROVEEDOR ================== */
            if ($method === 'POST' && $action === 'create') {
                try {
                    $data = $this->sanitize($_POST, true);
                    $id   = $this->Model->crear($data);
                    $this->ok(['id' => $id], 201);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            /* ================== ACTUALIZAR PROVEEDOR ================== */
            if ($method === 'POST' && $action === 'update') {
                try {
                    $id = (int)($_POST['id'] ?? ($_POST['id_proveedor'] ?? 0));
                    if ($id <= 0) {
                        $this->fail('ID inválido');
                        return;
                    }

                    $data = $this->sanitize($_POST, false);
                    $this->Model->actualizar($id, $data);
                    $this->ok();
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            /* ================== ELIMINAR PROVEEDOR ================== */
            if ($method === 'POST' && $action === 'delete') {
                try {
                    $id = (int)($_POST['id'] ?? ($_POST['id_proveedor'] ?? 0));
                    if ($id <= 0) {
                        $this->fail('ID inválido');
                        return;
                    }

                    $this->Model->eliminar($id);
                    $this->ok();
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            /* ================== TOGGLE ESTADO PROVEEDOR ================== */
            if ($method === 'POST' && $action === 'toggle') {
                try {
                    $id = (int)($_POST['id'] ?? ($_POST['id_proveedor'] ?? 0));
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

            /* =====================================================
               PRODUCTOS QUE MANEJA EL PROVEEDOR
               ===================================================== */

            // ---------- Catálogo de productos (para el <select>) ----------
            if ($method === 'GET' && $action === 'productos_catalogo') {
                try {
                    $items = $this->Model->productosCatalogo();
                    $this->json(['items' => $items]);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            // ---------- Lista de productos asociados a un proveedor ----------
            if ($method === 'GET' && $action === 'productos_proveedor') {
                try {
                    $idProveedor = (int)($_GET['id_proveedor'] ?? ($_GET['id'] ?? 0));
                    if ($idProveedor <= 0) {
                        $this->fail('Proveedor inválido');
                        return;
                    }

                    $items = $this->Model->productosDeProveedor($idProveedor);
                    $this->json(['items' => $items]);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            // ---------- Guardar productos de un proveedor (JSON en el body) ----------
            if ($method === 'POST' && $action === 'productos_save') {
                try {
                    // Se espera un JSON en el cuerpo con:
                    // {
                    //   "id_proveedor": 123,
                    //   "items": [ { "id_producto": 1, ... }, ... ]
                    // }
                    $raw     = file_get_contents('php://input');
                    $payload = json_decode($raw, true);

                    if (!is_array($payload)) {
                        $this->fail('JSON inválido');
                        return;
                    }

                    $idProveedor = (int)($payload['id_proveedor'] ?? 0);
                    if ($idProveedor <= 0) {
                        $this->fail('Proveedor inválido');
                        return;
                    }

                    $items = $payload['items'] ?? [];
                    $this->Model->guardarProductosProveedor($idProveedor, $items);

                    $this->ok(['msg' => 'Productos del proveedor actualizados']);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            /* ================== ACCIÓN DESCONOCIDA ================== */
            $this->fail('Acción no válida', 400);
        } catch (\Throwable $e) {
            $this->fail($e->getMessage(), 500);
        }
    }

    /* =========================================================
     * Sanitización de entrada para Proveedores
     * ========================================================= */

    /**
     * Normaliza y valida los datos de entrada de un proveedor.
     *
     * @param array $in       Datos originales (por ejemplo, $_POST).
     * @param bool  $creating Indica si es creación (true) o actualización (false).
     *
     * @return array Datos saneados listos para el modelo.
     *
     * @throws \Exception Cuando alguna validación falla.
     */
    private function sanitize(array $in, bool $creating): array
    {
        // Nombre de la empresa (obligatorio, longitud razonable)
        $empresa = trim($in['empresa'] ?? '');
        if ($empresa === '' || mb_strlen($empresa) > 255) {
            throw new \Exception('Empresa inválida');
        }

        // El resto de campos no son obligatorios pero se normalizan
        $nit             = trim($in['nit'] ?? '');
        $nombreContacto  = trim($in['nombre_contacto'] ?? '');
        $telefono        = trim($in['telefono'] ?? '');
        $email           = trim($in['email'] ?? '');

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Email inválido');
        }

        $direccion       = trim($in['direccion'] ?? '');
        $ciudad          = trim($in['ciudad'] ?? '');
        $condicionesPago = trim($in['condiciones_pago'] ?? '');

        // Estado del proveedor: solo se permite 'activo' o 'inactivo'
        $estadoInput = strtolower(trim($in['estado'] ?? 'activo'));
        $estado      = in_array($estadoInput, ['activo', 'inactivo'], true)
            ? $estadoInput
            : 'activo';

        return [
            'empresa'          => $empresa,
            'nit'              => $nit,
            'nombre_contacto'  => $nombreContacto,
            'telefono'         => $telefono,
            'email'            => $email,
            'direccion'        => $direccion,
            'ciudad'           => $ciudad,
            'condiciones_pago' => $condicionesPago,
            'estado'           => $estado,
        ];
    }
}
