<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Models\Reportes;

final class AdminReportesController extends Controller
{
    private Reportes $repo;
    // Definimos los colores corporativos para no repetirlos
    private string $colorPrincipal = "#28a745"; // Verde Mi Hierbal
    private string $colorSecundario = "#d4edda"; // Verde claro para subtítulos
    private string $colorTexto = "#ffffff";
    private string $colorTextoOscuro = "#155724";

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->repo = new Reportes();
    }

    public function index(): void
    {
        $base = rtrim($this->config['app']['base_url'] ?? '', '/');
        $this->render(
            'admin/reportes/index',
            [
                'page_title' => 'Reportes del sistema',
                'esAdmin'    => true,
                'extra_css'  => [$base . '/assets/css/admin_reportes.css?v=1'],
                'extra_js'   => [],
            ]
        );
    }

    /* ===========================
       EXPORTADORES EXCEL (MODIFICADOS A VERDE)
       =========================== */

    public function inventarioExcel(): void
    {
        $rows = $this->repo->inventario();
        $this->sendExcel('Reporte_Inventario.xls', function () use ($rows) {
            $this->imprimirCabeceraCorporativa('REPORTE DE INVENTARIO', 9);
            
            echo "<table border='1'>
            <tr>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>ID</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>ID producto</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Código interno</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Stock</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Stock mínimo</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Stock máximo</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Punto de reorden</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Ubicación</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Estado</th>
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
            // Se ajustó el colspan de 10 a 9 porque quitamos una columna
            $this->imprimirCabeceraCorporativa('REPORTE DE VENTAS - MI HIERBAL', 9);
            
            echo "<table border='1'>
            <tr>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>ID Venta</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Total</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Pago con</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Cambio</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Fecha de venta</th>               
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Metodo de Pago</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Nombre Cliente</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Apellido Cliente </th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Cedula Cliente</th>
            </tr>";
            foreach ($rows as $r) {
                echo "<tr>
                        <td>{$r['id_venta']}</td>
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
            $this->imprimirCabeceraCorporativa('REPORTE DE PROVEEDORES', 11);
            
            echo "<table border='1'>
            <tr>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>ID</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Empresa</th>               
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>NIT</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Nombre Contacto</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Telefono</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Email</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Direccion</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Ciudad</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Condiciones de Pago</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Estado</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Creado</th>
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
            $this->imprimirCabeceraCorporativa('REPORTE DE CLIENTES', 8);
            
            echo "<table border='1'>
            <tr>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>ID Cliente</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Cedula</th>               
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Nombres</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Apellidos</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Telefono</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Correo</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Estado</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Fecha de Registro</th>
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
            $this->imprimirCabeceraCorporativa('REPORTE DE USUARIOS', 9);
            
            echo "<table border='1'>
            <tr>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>ID Usuario</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Usuario</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Rol</th>                                     
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Nombres</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Apellidos</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Correo</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Correo Verificado</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Fecha de Creacion</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Estado</th>
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
            $this->imprimirCabeceraCorporativa('REPORTE DE DEVOLUCIONES', 12);
            
            echo "<table border='1'>
            <tr>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>ID</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>ID Cliente</th>  
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>ID Proveedor</th>          
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>ID Producto</th>        
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Cantidad</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>N° Orden</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Motivo de Devolucion</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Origen</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Fecha de Compra</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Fecha de Devolucion</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Estado</th>
                <th style='background-color:{$this->colorPrincipal};color:{$this->colorTexto};'>Observaciones</th>
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

    /** Helper para imprimir el encabezado verde de Mi Hierbal */
    private function imprimirCabeceraCorporativa(string $titulo, int $colspan): void
    {
        echo "<table border='0' width='100%'>
        <tr>
            <td colspan='{$colspan}' style='background-color:{$this->colorPrincipal}; color:white; font-size:20px; font-weight:bold; text-align:center; padding:15px;'>
                MI HIERBAL - {$titulo}
            </td>
        </tr>
        <tr>
            <td colspan='{$colspan}' style='background-color:{$this->colorSecundario}; color:{$this->colorTextoOscuro}; font-size:13px; text-align:center; padding:8px; border-bottom:2px solid {$this->colorPrincipal};'>
                Generado el: " . date('d/m/Y H:i') . "
            </td>
        </tr>
        </table><br>";
    }

    private function sendExcel(string $filename, callable $printer): void
    {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "<meta charset='UTF-8'>
        <style>
            table { border-collapse: collapse; width: 100%; }
            th { border: 1px solid #000000; padding: 8px; text-align: center; }
            td { border: 1px solid #dee2e6; padding: 6px; text-align: center; }
            tr:nth-child(even) { background-color: #f2f9f2; }
        </style>";       

        $printer();
        exit;
    }
}