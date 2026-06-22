<?php
namespace Models;

use PDO;

final class Reportes
{
    private PDO $pdo;

    public function __construct()
    {
        // AJUSTA ESTOS VALORES A TU ENTORNO
        $host   = 'localhost';
        $dbname = 'hieribal';   // 👈 pon aquí el nombre REAL de tu BD
        $user   = 'root';
        $pass   = '';            // contraseña de MySQL (vacía si usas XAMPP por defecto)

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    /* ==========================
       CONSULTAS PARA REPORTES
    ========================== */

    public function inventario(): array
    {
        $sql = "SELECT 
                    i.id,
                    i.id_producto,
                    i.codigo_interno,
                    i.stock,
                    i.stock_minimo,
                    i.stock_maximo,
                    i.punto_reorden,
                    i.ubicacion,
                    i.estado
                FROM inventario i";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function ventas(): array
    {
        $sql = "SELECT 
                    v.id_venta,
                    v.total,
                    v.pago_con,
                    v.cambio,
                    v.fecha_venta,
                    v.metodo_pago,
                    v.nombre_cliente,
                    v.apellido_cliente,
                    v.cedula_cliente
                FROM ventas v";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function proveedores(): array
    {
        $sql = "SELECT 
                    p.id,
                    p.empresa,
                    p.nit,
                    p.nombre_contacto,
                    p.telefono,
                    p.email,
                    p.direccion,
                    p.ciudad,
                    p.condiciones_pago,
                    p.estado,
                    p.creado
                FROM proveedores p";
        return $this->pdo->query($sql)->fetchAll();
    }

  // models/Reportes.php
public function clientes(): array
{
    // 👇 Solo los campos que realmente necesitas en el reporte
    $sql = "SELECT 
                c.id_cliente,
                c.cedula,
                c.nombres,
                c.apellidos,
                c.telefono,
                c.correo,
                c.estado,
                c.fecha_registro
            FROM clientes c";

    return $this->pdo->query($sql)->fetchAll();
}


    public function usuarios(): array
    {
        $sql = "SELECT 
                    u.id_usuario,
                    u.usuario,
                    u.password,
                    u.rol,
                    u.nombres,
                    u.apellidos,
                    u.correo,
                    u.correo_verificado,
                    u.fecha_creacion,
                    u.estado
                FROM usuarios u";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function devoluciones(): array
    {
        $sql = "SELECT 
                    d.id,
                    d.cliente_id,
                    d.proveedor_id,
                    d.producto_id,
                    d.cantidad,
                    d.numero_orden,
                    d.motivo_devolucion,
                    d.origen,
                    d.fecha_compra,
                    d.fecha_devolucion,
                    d.estado,
                    d.observaciones
                FROM devoluciones d";
        return $this->pdo->query($sql)->fetchAll();
    }
}
