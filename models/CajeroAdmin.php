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

          /**
     * Crear venta del cajero
     *
     * @param int    $idUsuario   ID del cajero (usuario logueado)
     * @param array  $items       [{id_producto, cantidad, precio, nombre}]
     * @param float  $pagoCon     Con cuánto paga el cliente
     * @param string $metodoPago  'efectivo' | 'tarjeta' | etc
     * @param string $cliNombre
     * @param string $cliApellido
     * @param string $cliCedula
     */
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

    // ==========================
    // 0) NORMALIZAR IDS
    // ==========================
    $idsProductos = [];
    foreach ($items as $it) {
        $idProd = (int)($it['id_producto'] ?? 0);
        if ($idProd > 0) {
            $idsProductos[] = $idProd;
        }
    }
    $idsProductos = array_values(array_unique($idsProductos));
    if (empty($idsProductos)) {
        throw new \RuntimeException('No hay productos válidos en la venta');
    }

    $this->db->beginTransaction();

    try {
        // ==========================
        // 1) LEER STOCK ACTUAL (FOR UPDATE)
        // ==========================
        $in = implode(',', array_fill(0, count($idsProductos), '?'));
        $sqlStockSel = "
            SELECT id, nombre, stock_actual
            FROM productos
            WHERE id IN ($in)
            FOR UPDATE
        ";
        $stSel = $this->db->prepare($sqlStockSel);
        $stSel->execute($idsProductos);
        $rowsStock = $stSel->fetchAll();

        // Indexar por id
        $stockPorId = [];
        foreach ($rowsStock as $r) {
            $stockPorId[(int)$r['id']] = [
                'nombre' => $r['nombre'],
                'stock'  => (float)$r['stock_actual'],
            ];
        }

        // ==========================
        // 2) VALIDAR STOCK SUFICIENTE
        // ==========================
        foreach ($items as $it) {
            $idProd   = (int)($it['id_producto'] ?? 0);
            $cantidad = (float)($it['cantidad'] ?? 0);

            if ($idProd <= 0 || $cantidad <= 0) {
                throw new \RuntimeException('Producto o cantidad inválida.');
            }

            if (!isset($stockPorId[$idProd])) {
                throw new \RuntimeException('El producto con ID ' . $idProd . ' no existe.');
            }

            $disp = $stockPorId[$idProd]['stock'];
            if ($cantidad > $disp) {
                $nombre = $stockPorId[$idProd]['nombre'];
                throw new \RuntimeException(
                    "Stock insuficiente para '{$nombre}'. ".
                    "Disponible: {$disp}, solicitado: {$cantidad}."
                );
            }
        }

        // ==========================
        // 3) Calcular total
        // ==========================
        $total = 0.0;
        foreach ($items as $it) {
            $precio   = (float)($it['precio'] ?? 0);
            $cantidad = (float)($it['cantidad'] ?? 0);
            $total   += $precio * $cantidad;
        }

        $cambio = max(0, $pagoCon - $total);

        // ==========================
        // 4) Insertar CABECERA en `ventas`
        // ==========================
        $sqlVenta = "INSERT INTO ventas
                     (id_carrito, total, fecha_venta, metodo_pago,
                      nombre_cliente, apellido_cliente, cedula_cliente,
                      pago_con, cambio)
                     VALUES
                     (NULL, :tot, NOW(), :metodo,
                      :nom, :ape, :ced,
                      :pago, :cambio)";
        $stVenta = $this->db->prepare($sqlVenta);
        $stVenta->execute([
            ':tot'    => $total,
            ':metodo' => $metodoPago,
            ':nom'    => $cliNombre ?: null,
            ':ape'    => $cliApellido ?: null,
            ':ced'    => $cliCedula ?: null,
            ':pago'   => $pagoCon,
            ':cambio' => $cambio,
        ]);

        $idVenta = (int)$this->db->lastInsertId();

            // ==========================
            // 5) Insertar DETALLE en `detalle_venta`
            // ==========================
            $sqlDet = "INSERT INTO detalle_venta
                    (id_venta, id_producto, cantidad, precio, subtotal)
                    VALUES
                    (:idv, :pid, :cant, :precio, :sub)";
            $stDet = $this->db->prepare($sqlDet);

        // ==========================
        // 6) Actualizar STOCK (stock_actual)
        // ==========================
        $sqlStockUpd = "
            UPDATE productos
            SET stock_actual = stock_actual - :cant
            WHERE id = :idp
        ";
        $stStock = $this->db->prepare($sqlStockUpd);

        foreach ($items as $it) {
            $idProd   = (int)($it['id_producto'] ?? 0);
            $precio   = (float)($it['precio'] ?? 0);
            $cantidad = (float)($it['cantidad'] ?? 0);

            $subtotal = $precio * $cantidad;

            // ✅ guardar detalle
            $stDet->execute([
                ':idv'    => $idVenta,
                ':pid'    => $idProd,
                ':cant'   => $cantidad,
                ':precio' => $precio,
                ':sub'    => $subtotal,
            ]);

            // ✅ actualizar stock (esto ya lo tienes)
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
    } catch (\Throwable $e) {
        $this->db->rollBack();
        throw $e;
    }
}


        public function historialPorUsuario(int $idUsuario, int $page, int $per): array
        {
            $off = ($page - 1) * $per;

            $sql = "SELECT
                        v.id_venta,
                        v.fecha_venta,
                        v.total,
                        TRIM(
                        CONCAT(
                            COALESCE(v.nombre_cliente, ''),
                            ' ',
                            COALESCE(v.apellido_cliente, '')
                        )
                        ) AS cliente
                    FROM ventas v
                    ORDER BY v.fecha_venta DESC
                    LIMIT :off, :per";

            $st = $this->db->prepare($sql);
            $st->bindValue(':off', (int)$off, PDO::PARAM_INT);
            $st->bindValue(':per', (int)$per, PDO::PARAM_INT);
            $st->execute();
            $items = $st->fetchAll();

            $sql2 = "SELECT COUNT(*) FROM ventas";
            $total = (int)$this->db->query($sql2)->fetchColumn();

            return [
                'items' => $items,
                'page'  => $page,
                'per'   => $per,
                'total' => $total
            ];
        }

        public function obtenerVenta(int $idVenta): ?array
    {
        $sql = "SELECT
                    v.id_venta,
                    v.fecha_venta,
                    v.total,
                    v.metodo_pago,
                    v.nombre_cliente,
                    v.apellido_cliente,
                    v.cedula_cliente,
                    v.pago_con,
                    v.cambio
                FROM ventas v
                WHERE v.id_venta = :id";
        $st = $this->db->prepare($sql);
        $st->execute([':id' => $idVenta]);
        $row = $st->fetch();
        return $row ?: null;
    }

        public function obtenerDetalleVenta(int $idVenta): array
        {
            $sql = "SELECT
                        d.id_producto,
                        p.nombre AS nombre_producto,
                        d.cantidad,
                        d.precio AS precio_unitario,
                        d.subtotal
                    FROM detalle_venta d
                    INNER JOIN productos p ON p.id = d.id_producto
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
