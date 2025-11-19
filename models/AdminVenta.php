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
            rv.id,
            rv.numero_factura,
            rv.producto_id,
            rv.cantidad,
            rv.precio,
            rv.total,
            rv.cliente_id,
            rv.vendedor_id,
            rv.metodo_pago,
            rv.fecha,
            rv.observaciones,
            rv.created_at,
            p.nombre AS producto_nombre,
            CONCAT(c.nombres,' ',c.apellidos) AS cliente_nombre,
            u.usuario AS vendedor_nombre
        FROM reporte_venta rv
        LEFT JOIN productos p   ON rv.producto_id = p.id
        LEFT JOIN clientes c    ON rv.cliente_id = c.id_cliente
        LEFT JOIN usuarios u    ON rv.vendedor_id = u.id_usuario
        WHERE (
                rv.numero_factura LIKE ?
             OR IFNULL(p.nombre,'') LIKE ?
             OR CONCAT(IFNULL(c.nombres,''),' ',IFNULL(c.apellidos,'')) LIKE ?
             OR IFNULL(u.usuario,'') LIKE ?
        )
        ORDER BY rv.id DESC
        LIMIT ?, ?
    ";
    $st = $this->db->prepare($sql);
    $st->bindValue(1, $like, PDO::PARAM_STR);
    $st->bindValue(2, $like, PDO::PARAM_STR);
    $st->bindValue(3, $like, PDO::PARAM_STR);
    $st->bindValue(4, $like, PDO::PARAM_STR);
    $st->bindValue(5, (int)$off, PDO::PARAM_INT);
    $st->bindValue(6, (int)$per, PDO::PARAM_INT);
    $st->execute();
    $items = $st->fetchAll();

    $sql2 = "
        SELECT COUNT(*)
        FROM reporte_venta rv
        LEFT JOIN productos p   ON rv.producto_id = p.id
        LEFT JOIN clientes c    ON rv.cliente_id = c.id_cliente
        LEFT JOIN usuarios u    ON rv.vendedor_id = u.id_usuario
        WHERE (
                rv.numero_factura LIKE ?
             OR IFNULL(p.nombre,'') LIKE ?
             OR CONCAT(IFNULL(c.nombres,''),' ',IFNULL(c.apellidos,'')) LIKE ?
             OR IFNULL(u.usuario,'') LIKE ?
        )";
    $st2 = $this->db->prepare($sql2);
    $st2->bindValue(1, $like, PDO::PARAM_STR);
    $st2->bindValue(2, $like, PDO::PARAM_STR);
    $st2->bindValue(3, $like, PDO::PARAM_STR);
    $st2->bindValue(4, $like, PDO::PARAM_STR);
    $st2->execute();
    $total = (int)$st2->fetchColumn();

    return [
        'items' => $items,
        'page'  => $page,
        'per'   => $per,
        'total' => $total,
    ];
}


    /* ================== OBTENER ================== */
    public function obtener(int $id): ?array
    {
        $sql = "
            SELECT 
                rv.*,
                p.nombre AS producto_nombre,
                CONCAT(c.nombres,' ',c.apellidos) AS cliente_nombre,
                u.usuario AS vendedor_nombre
            FROM reporte_venta rv
            LEFT JOIN productos p   ON rv.producto_id = p.id
            LEFT JOIN clientes c    ON rv.cliente_id = c.id_cliente
            LEFT JOIN usuarios u    ON rv.vendedor_id = u.id_usuario
            WHERE rv.id = :id
            LIMIT 1
        ";
        $st = $this->db->prepare($sql);
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /* ================== CREAR ================== */
    public function crear(array $d): int
    {
        $sql = "
            INSERT INTO reporte_venta
            (numero_factura, producto_id, cantidad, precio, total,
             cliente_id, vendedor_id, metodo_pago, fecha, observaciones, created_at)
            VALUES
            (:numero_factura, :producto_id, :cantidad, :precio, :total,
             :cliente_id, :vendedor_id, :metodo_pago, :fecha, :observaciones, NOW())
        ";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':numero_factura' => $d['numero_factura'],
            ':producto_id'    => $d['producto_id'],
            ':cantidad'       => $d['cantidad'],
            ':precio'         => $d['precio'],
            ':total'          => $d['total'],
            ':cliente_id'     => $d['cliente_id'],
            ':vendedor_id'    => $d['vendedor_id'],
            ':metodo_pago'    => $d['metodo_pago'],
            ':fecha'          => $d['fecha'],
            ':observaciones'  => $d['observaciones'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    /* ================== ACTUALIZAR ================== */
    public function actualizar(int $id, array $d): void
    {
        $sql = "
            UPDATE reporte_venta
            SET
                numero_factura = :numero_factura,
                producto_id    = :producto_id,
                cantidad       = :cantidad,
                precio         = :precio,
                total          = :total,
                cliente_id     = :cliente_id,
                vendedor_id    = :vendedor_id,
                metodo_pago    = :metodo_pago,
                fecha          = :fecha,
                observaciones  = :observaciones
            WHERE id = :id
        ";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':numero_factura' => $d['numero_factura'],
            ':producto_id'    => $d['producto_id'],
            ':cantidad'       => $d['cantidad'],
            ':precio'         => $d['precio'],
            ':total'          => $d['total'],
            ':cliente_id'     => $d['cliente_id'],
            ':vendedor_id'    => $d['vendedor_id'],
            ':metodo_pago'    => $d['metodo_pago'],
            ':fecha'          => $d['fecha'],
            ':observaciones'  => $d['observaciones'] ?? null,
            ':id'             => $id,
        ]);
    }

    /* ================== ELIMINAR ================== */
    public function eliminar(int $id): void
    {
        $st = $this->db->prepare("DELETE FROM reporte_venta WHERE id = :id");
        $st->execute([':id' => $id]);
    }

    /* ================== COMBOS (productos/clientes/vendedores) ================== */

    public function getProductos(): array
    {
        $st = $this->db->query("SELECT id, nombre FROM productos ORDER BY nombre ASC");
        return $st->fetchAll();
    }

    public function getClientes(): array
    {
        $st = $this->db->query("
            SELECT id_cliente, CONCAT(nombres,' ',apellidos) AS nombre
            FROM clientes
            ORDER BY nombres, apellidos
        ");
        return $st->fetchAll();
    }

    public function getVendedores(): array
    {
        $st = $this->db->query("
            SELECT id_usuario, usuario
            FROM usuarios
            ORDER BY usuario
        ");
        return $st->fetchAll();
    }
}
