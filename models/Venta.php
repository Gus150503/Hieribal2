<?php
namespace Models;

use PDO;
use Core\Database;

final class Venta {
    private PDO $pdo;

    public function __construct(array $config) {
        $this->pdo = Database::get($config['db']);
    }

    /** KPI: total ventas del mes */
    public function totalDelMes(): int {
        $sql = "SELECT COUNT(*)
                  FROM ventas
                 WHERE YEAR(fecha_venta) = YEAR(CURDATE())
                   AND MONTH(fecha_venta) = MONTH(CURDATE())";
        return (int)$this->pdo->query($sql)->fetchColumn();
    }

    /** * Carrusel: productos más vendidos. 
     * NOTA: Como ya no usas tabla detalle_ventas/carrito en el cajero, 
     * esta función devolverá un array vacío para evitar errores hasta que implementes 
     * un guardado de nombres de productos en la venta.
     */
    /** Carrusel: productos más vendidos del mes (AHORA DESDE detalle_ventas) */
    public function topProductos(int $limit = 10): array {
        $sql = "SELECT
                    p.nombre,
                    p.imagen AS img,
                    SUM(d.cantidad) AS unidades
                FROM ventas v
                INNER JOIN detalle_venta d ON d.id_venta = v.id_venta
                INNER JOIN productos p      ON p.id = d.id_producto
                WHERE YEAR(v.fecha_venta) = YEAR(CURDATE())
                  AND MONTH(v.fecha_venta) = MONTH(CURDATE())
                GROUP BY p.id, p.nombre, p.imagen
                ORDER BY unidades DESC
                LIMIT :lim";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$r) {
            if (!isset($r['img']) || $r['img'] === '') {
                $r['img'] = null;
            }
        }
        return $rows;
    }

    /**
     * Chart: top clientes del mes.
     * Lee directamente de la tabla 'ventas' usando el nombre del cliente.
     */
    public function topClientes(int $limit = 10, string $modo = 'compras'): array {
        if ($modo === 'monto') {
            // Sumamos el total de dinero gastado por cliente
            $sql = "SELECT 
                        CONCAT(nombre_cliente, ' ', COALESCE(apellido_cliente, '')) AS nombre, 
                        SUM(total) AS val
                    FROM ventas
                    WHERE YEAR(fecha_venta) = YEAR(CURDATE())
                      AND MONTH(fecha_venta) = MONTH(CURDATE())
                    GROUP BY cedula_cliente, nombre_cliente, apellido_cliente
                    ORDER BY val DESC
                    LIMIT :lim";
        } else {
            // Contamos cuántas veces ha comprado el cliente
            $sql = "SELECT 
                        CONCAT(nombre_cliente, ' ', COALESCE(apellido_cliente, '')) AS nombre, 
                        COUNT(*) AS val
                    FROM ventas
                    WHERE YEAR(fecha_venta) = YEAR(CURDATE())
                      AND MONTH(fecha_venta) = MONTH(CURDATE())
                    GROUP BY cedula_cliente, nombre_cliente, apellido_cliente
                    ORDER BY val DESC
                    LIMIT :lim";
        }

        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $labels = array_column($rows, 'nombre');
        $values = array_map(static fn($v) => (float)$v, array_column($rows, 'val'));
        
        return [$labels, $values];
    }
}