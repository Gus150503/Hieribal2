<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Models\Reportes;

final class AdminReportesController extends Controller
{
    private Reportes $repo;

    public function __construct(array $config)
    {
        parent::__construct($config);
        // Ya no le pasamos $config al modelo; el modelo se conecta solo
        $this->repo = new Reportes();
    }

    /** Pantalla principal de tarjetas de reportes */
    public function index(): void
    {
        $base = rtrim($this->config['app']['base_url'] ?? '', '/');

        $this->render(
            'admin/reportes/index',
            [
                'page_title' => 'Reportes del sistema',
                'esAdmin'    => true, // 👈 usa layout del panel
                'extra_css'  => [$base . '/assets/css/admin_reportes.css?v=1'],
                'extra_js'   => [],
            ]
        );
    }

    /* ===========================
       EXPORTADORES EXCEL
       =========================== */

    public function inventarioExcel(): void
    {
        $rows = $this->repo->inventario();
        $this->sendExcel('reporte_inventario.xls', function () use ($rows) {
            echo "<table border='1'>
                    <tr>
                        <th>ID</th>
                        <th>ID producto</th>
                        <th>Código interno</th>
                        <th>Stock</th>
                        <th>Stock mínimo</th>
                        <th>Stock máximo</th>
                        <th>Punto de reorden</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                    </tr>";
            foreach ($rows as $r) {
                echo "<tr>
                        <td>{$r['id']}</td>
                        <td>{$r['id_producto']}</td>
                        <td>{$r['codigo_interno']}</td>
                        <td>{$r['stock']}</td>
                        <td>{$r['stock_minimo']}</td>
                        <td>{$r['stock_maximo']}</td>
                        <td>{$r['punto_reorden']}</td>
                        <td>{$r['ubicacion']}</td>
                        <td>{$r['estado']}</td>
                      </tr>";
            }
            echo "</table>";
        });
    }

    public function ventasExcel(): void
    {
        $rows = $this->repo->ventas();
        $this->sendExcel('reporte_ventas.xls', function () use ($rows) {
            echo "<table border='1'>
                    <tr>
                        <th>ID venta</th>
                        <th>ID carrito</th>
                        <th>Total</th>
                        <th>Paga con</th>
                        <th>Cambio</th>
                        <th>Fecha venta</th>
                        <th>Método de pago</th>
                        <th>Nombre cliente</th>
                        <th>Apellido cliente</th>
                        <th>Cédula cliente</th>
                    </tr>";
            foreach ($rows as $r) {
                echo "<tr>
                        <td>{$r['id_venta']}</td>
                        <td>{$r['id_carrito']}</td>
                        <td>{$r['total']}</td>
                        <td>{$r['pago_con']}</td>
                        <td>{$r['cambio']}</td>
                        <td>{$r['fecha_venta']}</td>
                        <td>{$r['metodo_pago']}</td>
                        <td>{$r['nombre_cliente']}</td>
                        <td>{$r['apellido_cliente']}</td>
                        <td>{$r['cedula_cliente']}</td>
                      </tr>";
            }
            echo "</table>";
        });
    }

    public function proveedoresExcel(): void
    {
        $rows = $this->repo->proveedores();
        $this->sendExcel('reporte_proveedores.xls', function () use ($rows) {
            echo "<table border='1'>
                    <tr>
                        <th>ID</th>
                        <th>Empresa</th>
                        <th>NIT</th>
                        <th>Nombre contacto</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th>Ciudad</th>
                        <th>Condiciones pago</th>
                        <th>Estado</th>
                        <th>Creado</th>
                    </tr>";
            foreach ($rows as $r) {
                echo "<tr>
                        <td>{$r['id']}</td>
                        <td>{$r['empresa']}</td>
                        <td>{$r['nit']}</td>
                        <td>{$r['nombre_contacto']}</td>
                        <td>{$r['telefono']}</td>
                        <td>{$r['email']}</td>
                        <td>{$r['direccion']}</td>
                        <td>{$r['ciudad']}</td>
                        <td>{$r['condiciones_pago']}</td>
                        <td>{$r['estado']}</td>
                        <td>{$r['creado']}</td>
                      </tr>";
            }
            echo "</table>";
        });
    }

public function clientesExcel(): void
{
    $rows = $this->repo->clientes();

    $this->sendExcel('reporte_clientes.xls', function () use ($rows) {
        echo "<table border='1'>
                <tr>
                    <th>ID Cliente</th>
                    <th>Cédula</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Estado</th>
                    <th>Fecha registro</th>
                </tr>";
        foreach ($rows as $r) {
            echo "<tr>
                    <td>{$r['id_cliente']}</td>
                    <td>{$r['cedula']}</td>
                    <td>{$r['nombres']}</td>
                    <td>{$r['apellidos']}</td>
                    <td>{$r['telefono']}</td>
                    <td>{$r['correo']}</td>
                    <td>{$r['estado']}</td>
                    <td>{$r['fecha_registro']}</td>
                  </tr>";
        }
        echo "</table>";
    });
}


    public function usuariosExcel(): void
    {
        $rows = $this->repo->usuarios();
        $this->sendExcel('reporte_usuarios.xls', function () use ($rows) {
            echo "<table border='1'>
                    <tr>
                        <th>ID usuario</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Correo</th>
                        <th>Correo verificado</th>
                        <th>Fecha creación</th>
                        <th>Estado</th>
                    </tr>";
            foreach ($rows as $r) {
                echo "<tr>
                        <td>{$r['id_usuario']}</td>
                        <td>{$r['usuario']}</td>
                        <td>{$r['rol']}</td>
                        <td>{$r['nombres']}</td>
                        <td>{$r['apellidos']}</td>
                        <td>{$r['correo']}</td>
                        <td>{$r['correo_verificado']}</td>
                        <td>{$r['fecha_creacion']}</td>
                        <td>{$r['estado']}</td>
                      </tr>";
            }
            echo "</table>";
        });
    }

    public function devolucionesExcel(): void
    {
        $rows = $this->repo->devoluciones();
        $this->sendExcel('reporte_devoluciones.xls', function () use ($rows) {
            echo "<table border='1'>
                    <tr>
                        <th>ID</th>
                        <th>ID cliente</th>
                        <th>ID proveedor</th>
                        <th>ID producto</th>
                        <th>Cantidad</th>
                        <th>Número orden</th>
                        <th>Motivo devolución</th>
                        <th>Origen</th>
                        <th>Fecha compra</th>
                        <th>Fecha devolución</th>
                        <th>Estado</th>
                        <th>Observaciones</th>
                    </tr>";
            foreach ($rows as $r) {
                echo "<tr>
                        <td>{$r['id']}</td>
                        <td>{$r['cliente_id']}</td>
                        <td>{$r['proveedor_id']}</td>
                        <td>{$r['producto_id']}</td>
                        <td>{$r['cantidad']}</td>
                        <td>{$r['numero_orden']}</td>
                        <td>{$r['motivo_devolucion']}</td>
                        <td>{$r['origen']}</td>
                        <td>{$r['fecha_compra']}</td>
                        <td>{$r['fecha_devolucion']}</td>
                        <td>{$r['estado']}</td>
                        <td>{$r['observaciones']}</td>
                      </tr>";
            }
            echo "</table>";
        });
    }

    /* ===========================
       Helper para enviar el Excel
       =========================== */
    private function sendExcel(string $filename, callable $printer): void
    {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "<meta charset='UTF-8'>";
        $printer();
        exit;
    }
}
