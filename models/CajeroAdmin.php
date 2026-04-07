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

    public function listarClientes(): array
    {
        $sql = "SELECT id_cliente, nombre_cliente, documento
                FROM clientes
                ORDER BY nombre_cliente ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /* ============================
       PRODUCTOS
       ============================ */

    /** Búsqueda por nombre/código: AHORA MIRA EL STOCK DE INVENTARIO */
    public function buscarProductos(string $q): array
    {
        $like = '%' . $q . '%';
        // Hacemos un JOIN con inventario para traer el stock real
        $sql = "SELECT p.id AS id_producto,
                       p.nombre AS nombre_producto,
                       p.precio_venta,
                       i.stock AS stock,
                       p.codigo_sku AS codigo
                FROM productos p
                INNER JOIN inventario i ON p.id = i.id_producto
                WHERE (p.nombre LIKE :q OR p.codigo_sku LIKE :q)
                AND i.stock > 0 
                ORDER BY p.nombre ASC
                LIMIT 30";
        $st = $this->db->prepare($sql);
        $st->bindValue(':q', $like, PDO::PARAM_STR);
        $st->execute();
        return $st->fetchAll();
    }

    /** Lista inicial: AHORA MIRA EL STOCK DE INVENTARIO */
    public function listarProductos(): array
    {
        $sql = "SELECT p.id AS id_producto,
                       p.nombre AS nombre_producto,
                       p.precio_venta,
                       i.stock AS stock_actual
                FROM productos p
                INNER JOIN inventario i ON p.id = i.id_producto
                WHERE p.estado = 'activo' 
                  AND i.stock > 0
                ORDER BY p.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /* ============================
       VENTAS
       ============================ */

    public function crearVenta(
        int $idUsuario,
        array $items,
        float $pagoCon,
        string $metodoPago,
        string $cliNombre,
        string $cliApellido,
        string $cliCedula
    ): int {

        if (empty($items)) {
            throw new \RuntimeException('No hay productos en la venta');
        }

        // 0) Normalizar IDs
        $idsProductos = [];
        foreach ($items as $it) {
            $idProd = (int)($it['id_producto'] ?? 0);
            if ($idProd > 0) {
                $idsProductos[] = $idProd;
            }
        }
        $idsProductos = array_values(array_unique($idsProductos));

        $this->db->beginTransaction();

        try {
            // 1) LEER STOCK REAL DESDE TABLA INVENTARIO
            $in = implode(',', array_fill(0, count($idsProductos), '?'));
            $sqlStock = "SELECT i.id_producto, p.nombre, i.stock
                         FROM inventario i
                         INNER JOIN productos p ON i.id_producto = p.id
                         WHERE i.id_producto IN ($in)
                         FOR UPDATE";

            $stmtStock = $this->db->prepare($sqlStock);
            $stmtStock->execute($idsProductos);
            $rows = $stmtStock->fetchAll();

            $stockMap = [];
            foreach ($rows as $r) {
                $stockMap[(int)$r['id_producto']] = [
                    'nombre' => $r['nombre'],
                    'stock'  => (float)$r['stock'],
                ];
            }

            // 2) VALIDAR STOCK
            foreach ($items as $it) {
                $idProd   = (int)$it['id_producto'];
                $cantidad = (float)$it['cantidad'];
                if (!isset($stockMap[$idProd])) {
                    throw new \RuntimeException("Producto no disponible en inventario (ID $idProd)");
                }
                if ($cantidad > $stockMap[$idProd]['stock']) {
                    throw new \RuntimeException("Stock insuficiente para {$stockMap[$idProd]['nombre']} (Disponible: {$stockMap[$idProd]['stock']})");
                }
            }

            // 3) TOTAL
            $total = 0;
            foreach ($items as $it) {
                $total += $it['precio'] * $it['cantidad'];
            }
            $cambio = max(0, $pagoCon - $total);

            // 4) INSERTAR VENTA
            $sqlVenta = "INSERT INTO ventas
                (total, fecha_venta, metodo_pago, nombre_cliente, apellido_cliente, cedula_cliente, pago_con, cambio)
                VALUES (:tot, NOW(), :metodo, :nom, :ape, :ced, :pago, :cambio)";

            $stmtVenta = $this->db->prepare($sqlVenta);
            $stmtVenta->execute([
                ':tot'    => $total,
                ':metodo' => $metodoPago,
                ':nom'    => $cliNombre ?: null,
                ':ape'    => $cliApellido ?: null,
                ':ced'    => $cliCedula ?: null,
                ':pago'   => $pagoCon,
                ':cambio' => $cambio,
            ]);
            $idVenta = (int)$this->db->lastInsertId();

            // 5) DETALLE + DESCUENTO DOBLE (INVENTARIO Y PRODUCTOS)
            $sqlDet = "INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio, subtotal)
                       VALUES (:idv, :pid, :cant, :precio, :sub)";
            $stmtDet = $this->db->prepare($sqlDet);

            // Query para descontar de INVENTARIO (La real)
            $stmtUpdInv = $this->db->prepare("UPDATE inventario SET stock = stock - :cant WHERE id_producto = :idp");
            
            // Query para descontar de PRODUCTOS (Para no romper tus vistas actuales)
            $stmtUpdPro = $this->db->prepare("UPDATE productos SET stock_actual = stock_actual - :cant WHERE id = :idp");

            foreach ($items as $it) {
                $idProd   = (int)$it['id_producto'];
                $precio   = (float)$it['precio'];
                $cantidad = (float)$it['cantidad'];
                $subtotal = $precio * $cantidad;

                // Guardar detalle
                $stmtDet->execute([':idv'=>$idVenta, ':pid'=>$idProd, ':cant'=>$cantidad, ':precio'=>$precio, ':sub'=>$subtotal]);

                // Descontar de Inventario
                $stmtUpdInv->execute([':cant' => $cantidad, ':idp' => $idProd]);

                // Descontar de Productos
                $stmtUpdPro->execute([':cant' => $cantidad, ':idp' => $idProd]);
            }

            $this->db->commit();
            return $idVenta;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /* ... Resto de funciones: obtenerVenta, obtenerDetalleVenta, historialPorUsuario (se mantienen igual) ... */
    
    public function obtenerVenta(int $idVenta): ?array
    {
        $sql = "SELECT * FROM ventas WHERE id_venta = :id";
        $st = $this->db->prepare($sql);
        $st->execute([':id' => $idVenta]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function obtenerDetalleVenta(int $idVenta): array
    {
        $sql = "SELECT d.*, p.nombre AS nombre_producto 
                FROM detalle_venta d
                INNER JOIN productos p ON p.id = d.id_producto
                WHERE d.id_venta = :id";
        $st = $this->db->prepare($sql);
        $st->execute([':id' => $idVenta]);
        return $st->fetchAll();
    }

    public function historialPorUsuario(int $idUsuario, int $page, int $per): array
    {
        $off = ($page - 1) * $per;
        $sql = "SELECT v.id_venta, v.fecha_venta, v.total, 
                TRIM(CONCAT(COALESCE(v.nombre_cliente, ''), ' ', COALESCE(v.apellido_cliente, ''))) AS cliente
                FROM ventas v ORDER BY v.fecha_venta DESC LIMIT :off, :per";
        $st = $this->db->prepare($sql);
        $st->bindValue(':off', (int)$off, PDO::PARAM_INT);
        $st->bindValue(':per', (int)$per, PDO::PARAM_INT);
        $st->execute();
        $items = $st->fetchAll();
        $total = (int)$this->db->query("SELECT COUNT(*) FROM ventas")->fetchColumn();
        return ['items' => $items, 'page' => $page, 'per' => $per, 'total' => $total];
    }
}