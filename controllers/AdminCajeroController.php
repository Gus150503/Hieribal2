<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Models\CajeroAdmin;

use Dompdf\Dompdf;
use Dompdf\Options;

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
    private function ensureCajero(array $rolesPermitidos = ['Admin', 'Cajero']): void
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

        // Intentamos varias claves típicas: id_usuario, id, id_admin
        return (int)($user['id_usuario'] ?? $user['id'] ?? $user['id_admin'] ?? 0);
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

            /* LISTAR CLIENTES (para el combo) - por ahora no se usa, pero se deja */
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

        // Datos del cliente mostrador
        $cliNombre   = trim((string)($_POST['cli_nombre']   ?? ''));
        $cliApellido = trim((string)($_POST['cli_apellido'] ?? ''));
        $cliCedula   = trim((string)($_POST['cli_cedula']   ?? ''));

        if ($cliNombre === '' || $cliCedula === '') {
            $this->fail('Nombre y cédula del cliente son obligatorios.');
            return;
        }

        $metodoPago = trim((string)($_POST['metodo_pago'] ?? 'efectivo'));
        if ($metodoPago === '') {
            $metodoPago = 'efectivo';
        }

        $itemsJson = $_POST['items'] ?? '[]';
        $items     = json_decode($itemsJson, true) ?: [];

        if (empty($items)) {
            $this->fail('No hay productos en la venta');
            return;
        }

        $pagoEfectivo = (float)($_POST['pago_efectivo'] ?? 0);

        $idVenta = $this->Cajero->crearVenta(
            $idUsuario,
            $items,
            $pagoEfectivo,
            $metodoPago,
            $cliNombre,
            $cliApellido,
            $cliCedula
        );

        $this->ok(['id_venta' => $idVenta], 201);

       } catch (\RuntimeException $e) {
        // 💡 errores lógicos (stock insuficiente, etc.)
        $extra = [];

        // Si el mensaje indica stock insuficiente, marcamos un código específico
        if (stripos($e->getMessage(), 'stock insuficiente') !== false) {
            $extra['error_code'] = 'OUT_OF_STOCK';
        }

        $this->fail($e->getMessage(), 400, $extra);
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

    /* ============================
       FACTURA PDF
       ============================ */
    public function factura(): void
    {
        $this->ensureCajero();

        $idVenta = (int)($_GET['id_venta'] ?? 0);
        if ($idVenta <= 0) {
            http_response_code(400);
            echo 'ID de venta inválido.';
            return;
        }

        $venta   = $this->Cajero->obtenerVenta($idVenta);
        $detalle = $this->Cajero->obtenerDetalleVenta($idVenta);

        if (!$venta) {
            http_response_code(404);
            echo 'Venta no encontrada.';
            return;
        }

        $clienteNombre = trim(
            ($venta['nombre_cliente'] ?? '') . ' ' . ($venta['apellido_cliente'] ?? '')
        ) ?: 'Cliente mostrador';

        $cedula   = $venta['cedula_cliente'] ?? '';
        $fecha    = $venta['fecha_venta'] ?? '';
        $total    = (float)($venta['total'] ?? 0);
        $pagoCon  = (float)($venta['pago_con'] ?? 0);
        $cambio   = (float)($venta['cambio'] ?? max(0, $pagoCon - $total));
        $metodo   = $venta['metodo_pago'] ?? 'efectivo';

        $money = fn($v) => '$' . number_format((float)$v, 0, ',', '.');

        ob_start();
        ?>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
                .factura-box { width: 100%; padding: 10px 15px; }
                h1,h2,h3,h4 { margin: 0; padding: 0; }
                .f-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 10px;
                    border-bottom: 1px solid #ccc;
                    padding-bottom: 8px;
                }
                .f-header-left { font-size: 14px; font-weight: bold; }
                .f-header-right { text-align: right; font-size: 11px; }
                .f-info { margin-top: 10px; margin-bottom: 10px; font-size: 11px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 4px 6px; }
                th { background: #f0f0f0; font-weight: bold; }
                .text-right { text-align: right; }
                .mt-2 { margin-top: 8px; }
                .mt-3 { margin-top: 12px; }
                .small { font-size: 10px; }
            </style>
        </head>
        <body>
        <div class="factura-box">
            <div class="f-header">
                <div class="f-header-left">
                    Mi Hieribal<br>
                    <span class="small">Punto de venta</span>
                </div>
                <div class="f-header-right">
                    <strong>Factura #<?= htmlspecialchars((string)$idVenta) ?></strong><br>
                    Fecha: <?= htmlspecialchars($fecha) ?>
                </div>
            </div>

            <div class="f-info">
                <strong>Cliente:</strong> <?= htmlspecialchars($clienteNombre) ?><br>
                <?php if ($cedula): ?>
                    <strong>Cédula:</strong> <?= htmlspecialchars($cedula) ?><br>
                <?php endif; ?>
                <strong>Método de pago:</strong> <?= htmlspecialchars(ucfirst($metodo)) ?><br>
            </div>

            <table class="mt-2">
                <thead>
                <tr>
                    <th>Producto</th>
                    <th style="width:60px;">Cant.</th>
                    <th style="width:90px;">Precio</th>
                    <th style="width:100px;">Subtotal</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($detalle as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nombre_producto'] ?? '') ?></td>
                        <td class="text-right"><?= htmlspecialchars((string)($item['cantidad'] ?? 0)) ?></td>
                        <td class="text-right"><?= $money($item['precio_unitario'] ?? 0) ?></td>
                        <td class="text-right"><?= $money($item['subtotal'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <table class="mt-2" style="width: 45%; margin-left:auto;">
                <tr>
                    <th>Total</th>
                    <td class="text-right"><?= $money($total) ?></td>
                </tr>
                <tr>
                    <th>Paga con</th>
                    <td class="text-right"><?= $money($pagoCon) ?></td>
                </tr>
                <tr>
                    <th>Cambio</th>
                    <td class="text-right"><?= $money($cambio) ?></td>
                </tr>
            </table>

            <p class="mt-3 small">
                ¡Gracias por tu compra!<br>
                Este documento es generado automáticamente por el sistema de punto de venta.
            </p>
        </div>
        </body>
        </html>
        <?php
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Factura_' . $idVenta . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
    }
}
