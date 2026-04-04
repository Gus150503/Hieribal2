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
        public function obtenerParaExcel(): array
        {
            $sql = "
                SELECT 
                    v.id_venta,
                    v.fecha_venta,
                    v.metodo_pago,
                    v.nombre_cliente,
                    v.apellido_cliente,
                    v.cedula_cliente,
                    d.id_producto,
                    p.nombre AS producto,
                    d.cantidad,
                    d.precio,
                    d.subtotal,
                    v.total
                FROM detalle_venta d
                INNER JOIN ventas v ON v.id_venta = d.id_venta
                INNER JOIN productos p ON p.id = d.id_producto
                ORDER BY v.fecha_venta DESC
            ";

            return $this->db->query($sql)->fetchAll();
        }
    public function inventarioExcel(): void
    {
        $rows = $this->repo->inventario();
        $this->sendExcel('Reporte_Inventario.xls', function () use ($rows) {
          echo "<table border='0' width='100%'>
        <tr>
        <td colspan='9' style='
                background-color:#0d6efd;
                color:white;
                font-size:18px;
                font-weight:bold;
                text-align:center;
                padding:10px;
            '>
                REPORTE DE INVENTARIO
        </td>
        </tr>
        </table>";
            echo "<table border='0' width='100%' align='center'>
            <tr>
            <td style='
            border-bottom:2px solid #0d6efd;
                background-color:#cfe2ff;
                color:#084298;
                font-size:13px;
                text-align:center;
                padding:8px;
                font-weight:500;
                ' align='center'
            '>
                Generado el: " . date('d/m/Y H:i') . "
        </td>
        </tr>
        </table>";


            echo "<table border='1'>
            <tr>
                <th style='background-color:#0d6efd;color:#ffffff;'>ID</th>
                <th style='background-color:#0d6efd;color:#ffffff;'>ID producto</th>
                <th style='background-color:#0d6efd;color:#ffffff;'>Código interno</th>
                <th style='background-color:#0d6efd;color:#ffffff;'>Stock</th>
                <th style='background-color:#0d6efd;color:#ffffff;'>Stock mínimo</th>
                <th style='background-color:#0d6efd;color:#ffffff;'>Stock máximo</th>
                <th style='background-color:#0d6efd;color:#ffffff;'>Punto de reorden</th>
                <th style='background-color:#0d6efd;color:#ffffff;'>Ubicación</th>
                <th style='background-color:#0d6efd;color:#ffffff;'>Estado</th>
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
        $this->sendExcel('Reporte_Ventas_Cajero.xls', function () use ($rows) {
          echo "<table border='0' width='100%'>
        <tr>
        <td colspan='10' style='
                background-color:#ffc107; color:black;
                color:white;
                font-size:18px;
                font-weight:bold;
                text-align:center;
                padding:10px;
            '>
                REPORTE DE VENTAS DE CAJERO
        </td>
        </tr>
        </table>";
            echo "<table border='0' width='100%' align='center'>
            <tr>
            <td style='
            border-bottom:2px solid #ffc107;
                background-color:#ffdd77;
                color:#cc9c0b;
                font-size:13px;
                text-align:center;
                padding:8px;
                font-weight:500;
                ' align='center'
            '>
                Generado el: " . date('d/m/Y H:i') . "
        </td>
        </tr>
        </table>";


            echo "<table border='1'>
            <tr>
                <th style='background-color:#ffc107;color:#ffffff;'>ID Venta</th>
                <th style='background-color:#ffc107;color:#ffffff;'>ID Carrito</th>               
                <th style='background-color:#ffc107;color:#ffffff;'>Total </th>
                <th style='background-color:#ffc107;color:#ffffff;'>Pago con</th>
                <th style='background-color:#ffc107;color:#ffffff;'>Cambio</th>
                <th style='background-color:#ffc107;color:#ffffff;'>Fecha de venta</th>               
                <th style='background-color:#ffc107;color:#ffffff;'>Metodo de Pago</th>
                <th style='background-color:#ffc107;color:#ffffff;'>Nombre Cliente</th>
                <th style='background-color:#ffc107;color:#ffffff;'>Apellido Cliente </th>
                <th style='background-color:#ffc107;color:#ffffff;'>Cedula Cliente</th>
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
        $this->sendExcel('Reporte_Proveedores.xls', function () use ($rows) {
          echo "<table border='0' width='100%'>
        <tr>
        <td colspan='11' style='
                background-color:#226700; color:black;
                color:white;
                font-size:18px;
                font-weight:bold;
                text-align:center;
                padding:10px;
            '>
                REPORTE DE PROVEEDORES   
        </td>
        </tr>
        </table>";
            echo "<table border='0' width='100%' align='center'>
            <tr>
            <td style='
            border-bottom:2px solid #2e8105;
                background-color:#88bf6d; 
                color:#183d06;
                font-size:13px;
                text-align:center;
                padding:8px;
                font-weight:500;
                ' align='center'
            '>
                Generado el: " . date('d/m/Y H:i') . "
        </td>
        </tr>
        </table>";


            echo "<table border='1'>
            <tr>
                <th style='background-color:#2e8105;color:#ffffff;'>ID Venta</th>
                <th style='background-color:#2e8105;color:#ffffff;'>Empresa</th>               
                <th style='background-color:#2e8105;color:#ffffff;'>NIT</th>
                <th style='background-color:#2e8105;color:#ffffff;'>Nombre Contacto</th>
                <th style='background-color:#2e8105;color:#ffffff;'>Telefono</th>
                <th style='background-color:#2e8105;color:#ffffff;'>Email</th>
                <th style='background-color:#2e8105;color:#ffffff;'>Direccion</th>
                <th style='background-color:#2e8105;color:#ffffff;'>Ciudad</th>
                <th style='background-color:#2e8105;color:#ffffff;'>Condiciones de Pago</th>
                <th style='background-color:#2e8105;color:#ffffff;'>Estado</th>
                <th style='background-color:#2e8105;color:#ffffff;'>Creado</th>
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

    $this->sendExcel('Reporte_Clientes.xls', function () use ($rows) {
          echo "<table border='0' width='100%'>
        <tr>
        <td colspan='8' style='
                background-color:#7f827e; color:black;
                color:white;
                font-size:18px;
                font-weight:bold;
                text-align:center;
                padding:10px;
            '>
                REPORTE DE CLIENTES   
        </td>
        </tr>
        </table>";
            echo "<table border='0' width='100%' align='center'>
            <tr>
            <td style='
            border-bottom:2px solid #7f827e;
                background-color:#a0a49e; 
                color:#272927;
                font-size:13px;
                text-align:center;
                padding:8px;
                font-weight:500;
                ' align='center'
            '>
                Generado el: " . date('d/m/Y H:i') . "
        </td>
        </tr>
        </table>";

            echo "<table border='1'>
            <tr>
                <th style='background-color:#7f827e;color:#ffffff;'>ID Cliente</th>
                <th style='background-color:#7f827e;color:#ffffff;'>Cedula</th>               
                <th style='background-color:#7f827e;color:#ffffff;'>Nombres</th>
                <th style='background-color:#7f827e;color:#ffffff;'>Apellidos</th>
                <th style='background-color:#7f827e;color:#ffffff;'>Telefono</th>
                <th style='background-color:#7f827e;color:#ffffff;'>Correo</th>
                <th style='background-color:#7f827e;color:#ffffff;'>Estado</th>
                <th style='background-color:#7f827e;color:#ffffff;'>Fecha de Registro</th>
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
        $this->sendExcel('Reporte_Usuarios.xls', function () use ($rows) {
          echo "<table border='0' width='100%'>
        <tr>
        <td colspan='9' style='
                background-color:#00b1b8; color:black;
                color:white;
                font-size:18px;
                font-weight:bold;
                text-align:center;
                padding:10px;
            '>
                REPORTE DE USUARIOS   
        </td>
        </tr>
        </table>";
            echo "<table border='0' width='100%' align='center'>
            <tr>
            <td style='
            border-bottom:2px solid #00b1b8;
                background-color:#78dde1; 
                color:#036f72;
                font-size:13px;
                text-align:center;
                padding:8px;
                font-weight:500;
                ' align='center'
            '>
                Generado el: " . date('d/m/Y H:i') . "
        </td>
        </tr>
        </table>";

            echo "<table border='1'>
            <tr>
                <th style='background-color:#00b1b8;color:#ffffff;'>ID Usuario</th>
                <th style='background-color:#00b1b8;color:#ffffff;'>Usuario</th>
                <th style='background-color:#00b1b8;color:#ffffff;'>Rol</th>                            
                <th style='background-color:#00b1b8;color:#ffffff;'>Nombres</th>
                <th style='background-color:#00b1b8;color:#ffffff;'>Apellidos</th>
                <th style='background-color:#00b1b8;color:#ffffff;'>Correo</th>
                <th style='background-color:#00b1b8;color:#ffffff;'>Correo Verificado</th>
                <th style='background-color:#00b1b8;color:#ffffff;'>Fecha de Creacion</th>
                <th style='background-color:#00b1b8;color:#ffffff;'>Estado</th>
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
        $this->sendExcel('Reporte_Devoluciones.xls', function () use ($rows) {

          echo "<table border='0' width='100%'>
        <tr>
        <td colspan='12' style='
                background-color:#a50404; color:black;
                color:white;
                font-size:18px;
                font-weight:bold;
                text-align:center;
                padding:10px;
            '>
                REPORTE DE DEVOLUCIONES   
        </td>
        </tr>
        </table>";
            echo "<table border='0' width='100%' align='center'>
            <tr>
            <td style='
            border-bottom:2px solid #a50404;
                background-color:#d75454; 
                color:#700b0b;
                font-size:13px;
                text-align:center;
                padding:8px;
                font-weight:500;
                ' align='center'
            '>
                Generado el: " . date('d/m/Y H:i') . "
        </td>
        </tr>
        </table>";

            echo "<table border='1'>
            <tr>
                <th style='background-color:#a50404;color:#ffffff;'>ID</th>
                <th style='background-color:#a50404;color:#ffffff;'>ID Cliente</th>  
                <th style='background-color:#a50404;color:#ffffff;'>ID Proveedor</th>         
                <th style='background-color:#a50404;color:#ffffff;'>ID Producto</th>       
                <th style='background-color:#a50404;color:#ffffff;'>Cantidad</th>
                <th style='background-color:#a50404;color:#ffffff;'>N° Orden</th>
                <th style='background-color:#a50404;color:#ffffff;'>Motivo de Devolucion</th>
                <th style='background-color:#a50404;color:#ffffff;'>Origen</th>
                <th style='background-color:#a50404;color:#ffffff;'>Fecha de Compra</th>
                <th style='background-color:#a50404;color:#ffffff;'>Fecha de Devolucion</th>
                <th style='background-color:#a50404;color:#ffffff;'>Estado</th>
                <th style='background-color:#a50404;color:#ffffff;'>Observaciones</th>
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

        echo "
        <meta charset='UTF-8'>
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
            }

            th {
                color: #dee2e6;
                font-weight: bold;
                text-align: center;
                padding: 8px;
                border: 1px solid #000000;
            }

            td {
                padding: 6px;
                border: 1px solid #dee2e6;
                text-align: center;
            }

            tr:nth-child(even) {
                background-color: #f8f9fa;
            }

            tr:hover td {
                background-color: #e9f2ff;
            }
        </style>
        ";       

        $printer();
        exit;
        }
    }
