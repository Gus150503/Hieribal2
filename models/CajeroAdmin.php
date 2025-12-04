<?php
declare(strict_types=1);

namespace Models;

use PDO;
use PDOException;

class CajeroAdmin
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

    /* ============================
       CLIENTES
       ============================ */

    /** Para llenar el select de clientes */
    public function listarClientes(): array
    {
        // Ajusta nombres de columnas según tu tabla `clientes`
        $sql = "SELECT id_cliente, nombre_cliente, documento
                FROM clientes
                ORDER BY nombre_cliente ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /* ============================
       PRODUCTOS
       ============================ */

    /** Búsqueda por nombre/código para el autocompletado */
    public function buscarProductos(string $q): array
    {
        $like = '%' . $q . '%';

        // Ajusta columnas: id_producto, nombre_producto, precio_venta, stock, codigo, etc.
        $sql = "SELECT id_producto,
                       nombre       AS nombre_producto,
                       precio_venta,
                       stock,
                       codigo
                FROM productos
                WHERE nombre LIKE :q OR codigo LIKE :q
                ORDER BY nombre ASC
                LIMIT 30";
        $st = $this->db->prepare($sql);
        $st->bindValue(':q', $like, PDO::PARAM_STR);
        $st->execute();
        return $st->fetchAll();
    }

    /* ============================
       CREAR VENTA
       ============================ */

    /**
     * $items = [
     *   ['id_producto'=>1,'cantidad'=>2,'precio'=>1500],
     *   ...
     * ]
     */
    public function crearVenta(int $idUsuario, int $idCliente, array $items, float $pagoEfectivo = 0.0): int
    {
        if (empty($items)) {
            throw new \RuntimeException('No hay productos en la venta');
        }

        $this->db->beginTransaction();
        try {
            // Calcular total
            $total = 0.0;
            foreach ($items as $it) {
                $precio   = (float)($it['precio'] ?? 0);
                $cantidad = (float)($it['cantidad'] ?? 0);
                $total   += $precio * $cantidad;
            }

            // Insertar cabecera en `ventas`
            // Ajusta nombres de columnas según tu tabla `ventas`
            $sqlVenta = "INSERT INTO ventas
                         (id_cliente, id_usuario, fecha_venta, total, pago_efectivo)
                         VALUES (:cli, :usu, NOW(), :tot, :pago)";
            $stVenta = $this->db->prepare($sqlVenta);
            $stVenta->execute([
                ':cli'  => $idCliente ?: null,
                ':usu'  => $idUsuario,
                ':tot'  => $total,
                ':pago' => $pagoEfectivo,
            ]);

            $idVenta = (int)$this->db->lastInsertId();

            // Insertar detalle e ir descontando stock
            // Ajusta nombres si en tu tabla se llaman distinto
            $sqlDet = "INSERT INTO historial_pedido
                       (id_venta, id_producto, cantidad, precio_unitario, subtotal)
                       VALUES (:idv, :idp, :cant, :precio, :sub)";
            $stDet = $this->db->prepare($sqlDet);

            $sqlStock = "UPDATE productos
                         SET stock = stock - :cant
                         WHERE id_producto = :idp";
            $stStock = $this->db->prepare($sqlStock);

            foreach ($items as $it) {
                $idProd   = (int)($it['id_producto'] ?? 0);
                $precio   = (float)($it['precio'] ?? 0);
                $cantidad = (float)($it['cantidad'] ?? 0);

                if ($idProd <= 0 || $cantidad <= 0) {
                    throw new \RuntimeException('Producto o cantidad inválida.');
                }

                $subtotal = $precio * $cantidad;

                $stDet->execute([
                    ':idv'   => $idVenta,
                    ':idp'   => $idProd,
                    ':cant'  => $cantidad,
                    ':precio'=> $precio,
                    ':sub'   => $subtotal,
                ]);

                $stStock->execute([
                    ':cant' => $cantidad,
                    ':idp'  => $idProd,
                ]);
            }

            $this->db->commit();
            return $idVenta;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /* ============================
       HISTORIAL DE UN CAJERO
       ============================ */

    public function historialPorUsuario(int $idUsuario, int $page, int $per): array
    {
        $off = ($page - 1) * $per;

        // Ajusta nombres de columnas/tablas según tu estructura real
        $sql = "SELECT v.id_venta,
                       v.fecha_venta,
                       v.total,
                       c.nombre_cliente AS cliente
                FROM ventas v
                LEFT JOIN clientes c ON c.id_cliente = v.id_cliente
                WHERE v.id_usuario = :idu
                ORDER BY v.fecha_venta DESC
                LIMIT :off, :per";
        $st = $this->db->prepare($sql);
        $st->bindValue(':idu', $idUsuario, PDO::PARAM_INT);
        $st->bindValue(':off', (int)$off, PDO::PARAM_INT);
        $st->bindValue(':per', (int)$per, PDO::PARAM_INT);
        $st->execute();
        $items = $st->fetchAll();

        $sql2 = "SELECT COUNT(*)
                 FROM ventas
                 WHERE id_usuario = :idu";
        $st2 = $this->db->prepare($sql2);
        $st2->bindValue(':idu', $idUsuario, PDO::PARAM_INT);
        $st2->execute();
        $total = (int)$st2->fetchColumn();

        return ['items' => $items, 'page' => $page, 'per' => $per, 'total' => $total];
    }

    /* ============================
       DETALLE DE UNA VENTA
       ============================ */

    public function obtenerVenta(int $idVenta): ?array
    {
        $sql = "SELECT v.*, c.nombre_cliente AS cliente
                FROM ventas v
                LEFT JOIN clientes c ON c.id_cliente = v.id_cliente
                WHERE v.id_venta = :id";
        $st = $this->db->prepare($sql);
        $st->execute([':id' => $idVenta]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function obtenerDetalleVenta(int $idVenta): array
    {
        $sql = "SELECT d.id_producto,
                       p.nombre       AS nombre_producto,
                       d.cantidad,
                       d.precio_unitario,
                       d.subtotal
                FROM historial_pedido d
                INNER JOIN productos p ON p.id_producto = d.id_producto
                WHERE d.id_venta = :id";
        $st = $this->db->prepare($sql);
        $st->execute([':id' => $idVenta]);
        return $st->fetchAll();
    }

public function listarProductos(): array
{
    $sql = "SELECT 
                id AS id_producto,
                nombre AS nombre_producto,
                precio_venta
            FROM productos
            WHERE estado = 'activo'
            ORDER BY nombre ASC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}


}
