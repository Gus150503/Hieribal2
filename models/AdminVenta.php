<?php
namespace Models;

use PDO;

class AdminVenta
{
    private PDO $db;

    public function __construct(array $config)
    {
        $db      = $config['db'] ?? [];
        $host    = $db['host']    ?? '127.0.0.1';
        $name    = $db['name']    ?? 'hieribal';
        $user    = $db['user']    ?? 'root';
        $pass    = $db['pass']    ?? '';
        $charset = $db['charset'] ?? 'utf8mb4';

        $dsn  = "mysql:host={$host};dbname={$name};charset={$charset}";
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->db = new PDO($dsn, $user, $pass, $opts);
    }

    /* ================== LISTAR ================== */
    public function listar(string $q, int $page, int $per): array
    {
        $off  = ($page - 1) * $per;
        $like = "%{$q}%";

        $sql = "
            SELECT 
                v.id_venta,
                v.fecha_venta,
                v.metodo_pago,
                CONCAT(v.nombre_cliente,' ',v.apellido_cliente) AS cliente,
                p.nombre AS producto,
                d.cantidad,
                d.precio,
                d.subtotal
            FROM detalle_venta d
            INNER JOIN ventas v ON v.id_venta = d.id_venta
            INNER JOIN productos p ON p.id = d.id_producto
            WHERE (
                v.id_venta LIKE ?
                OR p.nombre LIKE ?
                OR CONCAT(v.nombre_cliente,' ',v.apellido_cliente) LIKE ?
            )
            ORDER BY v.fecha_venta DESC
            LIMIT ?, ?
        ";

        $st = $this->db->prepare($sql);
        $st->execute([$like, $like, $like, $off, $per]);

        $items = $st->fetchAll();

        $sql2 = "
            SELECT COUNT(*)
            FROM detalle_venta d
            INNER JOIN ventas v ON v.id_venta = d.id_venta
            INNER JOIN productos p ON p.id = d.id_producto
            WHERE (
                v.id_venta LIKE ?
                OR p.nombre LIKE ?
                OR CONCAT(v.nombre_cliente,' ',v.apellido_cliente) LIKE ?
            )
        ";

        $st2 = $this->db->prepare($sql2);
        $st2->execute([$like, $like, $like]);

        $total = (int)$st2->fetchColumn();

        return [
            'items' => $items,
            'page'  => $page,
            'per'   => $per,
            'total' => $total,
        ];
    }

    /* ================== ELIMINAR ================== */
    public function eliminar(int $idVenta): void
    {
        // elimina detalles primero (por seguridad)
        $st = $this->db->prepare("DELETE FROM detalle_venta WHERE id_venta = :id");
        $st->execute([':id' => $idVenta]);

        // luego la venta
        $st2 = $this->db->prepare("DELETE FROM ventas WHERE id_venta = :id");
        $st2->execute([':id' => $idVenta]);
    }

    /* ================== COMBOS ================== */

    public function getProductos(): array
    {
        return $this->db->query("SELECT id, nombre FROM productos ORDER BY nombre ASC")->fetchAll();
    }

    public function getClientes(): array
    {
        return $this->db->query("
            SELECT id_cliente, CONCAT(nombres,' ',apellidos) AS nombre
            FROM clientes
            ORDER BY nombre
        ")->fetchAll();
    }

    public function getVendedores(): array
    {
        return $this->db->query("
            SELECT id_usuario, usuario
            FROM usuarios
            ORDER BY usuario
        ")->fetchAll();
    }
}