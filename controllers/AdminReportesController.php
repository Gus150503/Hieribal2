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
                <th style='background-color:#0d6efd;color:#000000;'>ID</th>
                <th style='background-color:#0d6efd;color:#000000;'>ID producto</th>
                <th style='background-color:#0d6efd;color:#000000;'>Código interno</th>
                <th style='background-color:#0d6efd;color:#000000;'>Stock</th>
                <th style='background-color:#0d6efd;color:#000000;'>Stock mínimo</th>
                <th style='background-color:#0d6efd;color:#000000;'>Stock máximo</th>
                <th style='background-color:#0d6efd;color:#000000;'>Punto de reorden</th>
                <th style='background-color:#0d6efd;color:#000000;'>Ubicación</th>
                <th style='background-color:#0d6efd;color:#000000;'>Estado</th>
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
        $this->sendExcel('Reporte_Ventas.xls', function () use ($rows) {
            echo "<table border='1'>
            <tr>
                <th style='background-color:#fbff00;color:#000000;'>ID Venta</th>
                <th style='background-color:#fbff00;color:#000000;'>ID Carrito</th>               
                <th style='background-color:#fbff00;color:#000000;'>Total </th>
                <th style='background-color:#fbff00;color:#000000;'>Pago con</th>
                <th style='background-color:#fbff00;color:#000000;'>Cambio</th>
                <th style='background-color:#fbff00;color:#000000;'>Fecha de venta</th>               
                <th style='background-color:#fbff00;color:#000000;'>Metodo de Pago</th>
                <th style='background-color:#fbff00;color:#000000;'>Nombre Cliente</th>
                <th style='background-color:#fbff00;color:#000000;'>Apellido Cliente </th>
                <th style='background-color:#fbff00;color:#000000;'>Cedula Cliente</th>
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
            echo "<table border='1'>
            <tr>
                <th style='background-color:#226700;color:#000000;'>ID Venta</th>
                <th style='background-color:#226700;color:#000000;'>Empresa</th>               
                <th style='background-color:#226700;color:#000000;'>NIT</th>
                <th style='background-color:#226700;color:#000000;'>Nombre Contacto</th>
                <th style='background-color:#226700;color:#000000;'>Telefono</th>
                <th style='background-color:#226700;color:#000000;'>Email</th>
                <th style='background-color:#226700;color:#000000;'>Direccion</th>
                <th style='background-color:#226700;color:#000000;'>Ciudad</th>
                <th style='background-color:#226700;color:#000000;'>Condiciones de Pago</th>
                <th style='background-color:#226700;color:#000000;'>Estado</th>
                <th style='background-color:#226700;color:#000000;'>Creado</th>
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
            echo "<table border='1'>
            <tr>
                <th style='background-color:#696e67;color:#000000;'>ID Cliente</th>
                <th style='background-color:#696e67;color:#000000;'>Cedula</th>               
                <th style='background-color:#696e67;color:#000000;'>Nombres</th>
                <th style='background-color:#696e67;color:#000000;'>Apellidos</th>
                <th style='background-color:#696e67;color:#000000;'>Telefono</th>
                <th style='background-color:#696e67;color:#000000;'>Correo</th>
                <th style='background-color:#696e67;color:#000000;'>Estado</th>
                <th style='background-color:#696e67;color:#000000;'>Fecha de Registro</th>
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
            echo "<table border='1'>
            <tr>
                <th style='background-color:#00b1b8;color:#000000;'>ID Usuario</th>
                <th style='background-color:#00b1b8;color:#000000;'>Usuario</th>
                <th style='background-color:#00b1b8;color:#000000;'>Rol</th>                            
                <th style='background-color:#00b1b8;color:#000000;'>Nombres</th>
                <th style='background-color:#00b1b8;color:#000000;'>Apellidos</th>
                <th style='background-color:#00b1b8;color:#000000;'>Correo</th>
                <th style='background-color:#00b1b8;color:#000000;'>Correo Verificado</th>
                <th style='background-color:#00b1b8;color:#000000;'>Fecha de Creacion</th>
                <th style='background-color:#00b1b8;color:#000000;'>Estado</th>
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
            echo "<table border='1'>
            <tr>
                <th style='background-color:#a50404;color:#000000;'>ID</th>
                <th style='background-color:#a50404;color:#000000;'>ID Cliente</th>  
                <th style='background-color:#a50404;color:#000000;'>ID Proveedor</th>         
                <th style='background-color:#a50404;color:#000000;'>ID Producto</th>       
                <th style='background-color:#a50404;color:#000000;'>Cantidad</th>
                <th style='background-color:#a50404;color:#000000;'>N° Orden</th>
                <th style='background-color:#a50404;color:#000000;'>Motivo de Devolucion</th>
                <th style='background-color:#a50404;color:#000000;'>Origen</th>
                <th style='background-color:#a50404;color:#000000;'>Fecha de Compra</th>
                <th style='background-color:#a50404;color:#000000;'>Fecha de Devolucion</th>
                <th style='background-color:#a50404;color:#000000;'>Estado</th>
                <th style='background-color:#a50404;color:#000000;'>Observaciones</th>
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
