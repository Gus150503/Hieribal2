<?php

declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Core\Database;
use Models\UsuarioProducto;
use PDOException;

/** controllers/AdminProducto.php
 *  MODULO PRODUCTOS / Juliana Lugo / Controlador de administración de productos.
 *
 * Expone:
 *  - La vista principal del módulo (index).
 *  - Un endpoint API para CRUD (create, read, update, delete) vía JSON.
 */
final class AdminProducto extends Controller
{
    /**
     * @var UsuarioProducto
     */
    private UsuarioProducto $model;

    /**
     * Constructor.
     *
     * Inicializa la conexión a base de datos y el modelo de productos.
     *
     * @param array $config Configuración general de la aplicación.
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        try {
            $pdo         = Database::get($config['db']);
            $this->model = new UsuarioProducto($pdo);
        } catch (PDOException $e) {
            // En un entorno real, se recomienda registrar el error en un log
            // y mostrar un mensaje genérico al usuario.
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    /* ============================================================== *  
     * Helpers de respuesta JSON                                      *
     * ============================================================== */

    /**
     * Envía una respuesta JSON con código HTTP.
     *
     * @param array $data   Datos a codificar en JSON.
     * @param int   $status Código de estado HTTP.
     */
    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Respuesta JSON de éxito con estructura estándar.
     *
     * @param array $extra  Datos adicionales a incluir.
     * @param int   $status Código de estado HTTP.
     */
    private function ok(array $extra = [], int $status = 200): void
    {
        $this->json(['ok' => true] + $extra, $status);
    }

    /**
     * Respuesta JSON de error con estructura estándar.
     *
     * @param string $msg    Mensaje de error para el cliente.
     * @param int    $status Código de estado HTTP.
     * @param array  $extra  Datos adicionales a incluir.
     */
    private function fail(string $msg, int $status = 400, array $extra = []): void
    {
        $this->json(['ok' => false, 'msg' => $msg] + $extra, $status);
    }

    /**
     * Verifica que exista una sesión de administrador activa.
     *
     * Si no existe, redirige al login de administrador.
     */
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

    /**
     * Convierte errores de base de datos en mensajes más amigables.
     *
     * @param \Throwable $e Excepción capturada.
     *
     * @return string Mensaje listo para mostrar al usuario.
     */
    private function friendlyDbError(\Throwable $e): string
    {
        if ($e instanceof \PDOException && $e->getCode() === '23000') {
            $msg = $e->getMessage();

            // 🔹 FK: producto usado en carrito / otros módulos
            if (
                stripos($msg, 'fk_carrito_productos') !== false ||
                stripos($msg, 'foreign key constraint fails') !== false
            ) {
                return '⚠️ No se puede eliminar este producto porque está en uso en otros módulos '
                     . '(por ejemplo, en el carrito de compras). '
                     . 'Si no deseas que se siga usando, cámbialo a INACTIVO.';
            }

            // 🔹 SKU duplicado
            if (stripos($msg, 'codigo_sku') !== false) {
                return 'Ese SKU ya existe. Por favor usa otro código.';
            }

            // 🔹 Otros duplicados
            if (stripos($msg, 'duplicate') !== false || stripos($msg, '1062') !== false) {
                return 'Datos duplicados. Verifica que no estés repitiendo información única.';
            }
        }

        // Mensaje genérico para cualquier otro error
        return 'Ocurrió un error al procesar la operación. Intenta de nuevo.';
    }

    /**
     * Procesa la imagen enviada en el formulario.
     *
     * - Si viene un archivo en `imagen_archivo`, lo valida y lo mueve a assets/img/.
     * - Si viene una URL en `imagen`, la retorna tal cual.
     * - Si no se envía nada, retorna null.
     *
     * @return string|null URL final de la imagen o null si no se envió.
     *
     * @throws \Exception Si el formato o la subida del archivo no son válidos.
     */
    private function procesarImagen(): ?string
    {
        // 1. Si subieron archivo
        if (!empty($_FILES['imagen_archivo']['name'])) {
            $file = $_FILES['imagen_archivo'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $permitidas, true)) {
                throw new \Exception('Formato de imagen no permitido');
            }

            // Nombre único para evitar colisiones
            $nuevo = uniqid('prod_', true) . '.' . $ext;

            // Ruta física donde se guarda el archivo
            $rutaFS = __DIR__ . '/../public/assets/img/' . $nuevo;

            if (!move_uploaded_file($file['tmp_name'], $rutaFS)) {
                throw new \Exception('No se pudo subir la imagen');
            }

            // URL pública final
            $base = rtrim($this->config['app']['base_url'], '/');

            return $base . '/assets/img/' . $nuevo;
        }

        // 2. Si vino URL en el POST
        if (!empty($_POST['imagen'])) {
            return trim($_POST['imagen']);
        }

        // 3. No se envió imagen
        return null;
    }

    /* ============================================================== *
     * Vista principal                                                *
     * ============================================================== */

    /**
     * Renderiza la vista del listado de productos en el panel de administración.
     *
     * @return void
     */
            public function index(): void
        {
            $this->ensureAdmin();

            $base = rtrim($this->config['app']['base_url'] ?? '', '/');

            // Igual que proveedores
            $titulo     = "Productos";
            $modulo     = "productos";
            $esAdmin    = true;
            $extra_css  = [
                'assets/css/AdminProductos.css?v=1'
            ];
            $extra_js   = [
                'assets/js/admin_productos.js?v=2'
            ];

            $this->render(
                'admin/productos/index',
                compact('titulo','modulo','esAdmin','extra_css','extra_js')
            );
        }
    /* ============================================================== *
     * API CRUD (JSON)                                                *
     * ============================================================== */

    /**
     * Punto de entrada API para las operaciones CRUD de productos.
     *
     * Usa el parámetro `action` para decidir qué operación ejecutar:
     *  - list   (GET)
     *  - get    (GET)
     *  - create (POST)
     *  - update (POST)
     *  - delete (POST)
     *  - toggle (POST)
     *
     * @return void
     */
    public function api(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_REQUEST['action'] ?? '';

            // LISTAR
            if ($method === 'GET' && $action === 'list') {
                try {
                    $q    = trim((string) ($_GET['q'] ?? ''));
                    $page = max(1, (int) ($_GET['page'] ?? 1));
                    $per  = max(1, min(100, (int) ($_GET['per'] ?? 10)));

                    $data = $this->model->listar($q, $page, $per);
                    $this->json($data);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }

                return;
            }

            // OBTENER UN PRODUCTO
            if ($method === 'GET' && $action === 'get') {
                try {
                    $id = (int) ($_GET['id'] ?? 0);

                    if ($id <= 0) {
                        $this->fail('ID inválido');
                        return;
                    }

                    $row = $this->model->obtener($id);
                    $this->json(['data' => $row]);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }

                return;
            }

            // CREAR PRODUCTO
            if ($method === 'POST' && $action === 'create') {
                try {
                    // Sanitizar datos de entrada
                    $data           = $this->sanitize($_POST, true);
                    $data['imagen'] = $this->procesarImagen();

                    // Insertar en BD
                    $id = $this->model->crear($data);

                    $this->ok(['id' => $id], 201);
                } catch (\Throwable $ex) {
                    $this->fail($this->friendlyDbError($ex), 500);
                }

                return;
            }

            // ACTUALIZAR PRODUCTO
            if ($method === 'POST' && $action === 'update') {
                try {
                    $id = (int) ($_POST['id'] ?? 0);

                    if ($id <= 0) {
                        $this->fail('ID inválido');
                        return;
                    }

                    $data           = $this->sanitize($_POST, false);
                    $data['imagen'] = $this->procesarImagen();

                    $this->model->actualizar($id, $data);
                    $this->ok();
                } catch (\Throwable $ex) {
                    $this->fail($this->friendlyDbError($ex), 500);
                }

                return;
            }

            // ELIMINAR PRODUCTO (con validaciones de estado y uso)
            if ($method === 'POST' && $action === 'delete') {
                try {
                    $id = (int) ($_POST['id'] ?? 0);
                    if ($id <= 0) {
                        $this->fail('ID inválido');
                        return;
                    }

                    // Obtener el producto primero
                    $row = $this->model->obtener($id);
                    if (!$row) {
                        $this->fail('El producto no existe.');
                        return;
                    }

                    // 1️⃣ Si está ACTIVO → No permitir borrar
                    if (isset($row['estado']) && strcasecmp($row['estado'], 'activo') === 0) {
                        $this->fail('No puedes eliminar este producto mientras esté ACTIVO. Cámbialo a INACTIVO primero.');
                        return;
                    }

                    // 2️⃣ Si está INACTIVO pero está en uso → mensaje diferente
                    if (method_exists($this->model, 'estaEnUso') && $this->model->estaEnUso($id)) {
                        $this->fail('Este producto está INACTIVO pero sigue siendo utilizado en otros registros, por lo que no puede eliminarse.');
                        return;
                    }

                    // 3️⃣ Si está INACTIVO y NO está en uso → se elimina
                    $this->model->eliminar($id);
                    $this->ok(['msg' => 'Producto eliminado correctamente']);
                } catch (\Throwable $ex) {
                    $this->fail($this->friendlyDbError($ex), 500);
                }

                return;
            }

            // CAMBIAR ESTADO ACTIVO/INACTIVO
            if ($method === 'POST' && $action === 'toggle') {
                try {
                    $id = (int) ($_POST['id'] ?? 0);

                    if ($id <= 0) {
                        $this->fail('ID inválido');
                        return;
                    }

                    $res = $this->model->toggleEstado($id);
                    $this->ok(['estado' => $res['estado'] ?? null]);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }

                return;
            }

            // Si llega aquí, la acción no es válida
            $this->fail('Acción no válida', 400);
        } catch (\Throwable $e) {
            $this->fail($e->getMessage(), 500);
        }
    }

    /* ============================================================== *
     * Sanitización de datos de productos                             *
     * ============================================================== */

    /**
     * Sanitiza y valida los datos de un producto recibidos por POST.
     *
     * @param array $in       Datos de entrada sin filtrar.
     * @param bool  $creating Indica si es creación (true) o actualización (false).
     *
     * @return array Datos limpios y listos para el modelo.
     *
     * @throws \Exception Si algún campo requerido es inválido.
     */
    private function sanitize(array $in, bool $creating): array
    {
        // Nombre (obligatorio, máx 255 caracteres)
        $nombre = trim($in['nombre'] ?? '');

        if ($nombre === '' || strlen($nombre) > 255) {
            throw new \Exception('Nombre inválido');
        }

        // Campos de texto
        $categoria    = trim($in['categoria'] ?? '');
        $marca        = trim($in['marca'] ?? '');
        $presentacion = trim($in['presentacion'] ?? '');
        $descripcion  = trim($in['descripcion'] ?? '');

        // Númericos
        $stockMinimo = is_numeric($in['stock_minimo'] ?? null)
            ? (int) $in['stock_minimo']
            : 0;

        $precioCompra = is_numeric($in['precio_compra'] ?? null)
            ? (float) $in['precio_compra']
            : 0.0;

        $precioVenta = is_numeric($in['precio_venta'] ?? null)
            ? (float) $in['precio_venta']
            : 0.0;

        $iva = ($in['iva'] ?? '0') == '1' ? 1.0 : 0.0;

        // Otros campos de texto
        $lote       = trim($in['lote'] ?? '');
        $codigoSku  = trim($in['codigo_sku'] ?? '');
        $ubicacion  = trim($in['ubicacion'] ?? '');
        $imagen     = trim($in['imagen'] ?? '');
        $estadoIn   = strtolower(trim($in['estado'] ?? 'activo'));

        // Validación de stock mínimo
        if ($stockMinimo < 0) {
            throw new \Exception('El stock no puede ser negativo');
        }

        // Normalización de estado (solo 'activo' o 'inactivo')
        $estado = in_array($estadoIn, ['activo', 'inactivo'], true)
            ? $estadoIn
            : 'activo';

        // Fecha de vencimiento (puede venir vacía)
        $fVencimiento = trim($in['f_vencimiento'] ?? '');

        if ($fVencimiento !== '') {
            $ts = strtotime($fVencimiento);

            if ($ts === false) {
                throw new \Exception('Fecha de vencimiento inválida');
            }

            $fVencimiento = date('Y-m-d', $ts);
        } else {
            $fVencimiento = null;
        }

        return [
            'nombre'        => $nombre,
            'categoria'     => $categoria,
            'marca'         => $marca,
            'presentacion'  => $presentacion,
            'descripcion'   => $descripcion,
            'stock_minimo'  => $stockMinimo,
            'lote'          => $lote,
            'f_vencimiento' => $fVencimiento,
            'precio_compra' => $precioCompra,
            'precio_venta'  => $precioVenta,
            'iva'           => $iva,
            'codigo_sku'    => $codigoSku,
            'ubicacion'     => $ubicacion,
            'estado'        => $estado,
            'imagen'        => ($imagen !== '') ? $imagen : null,
        ];
    }
}
