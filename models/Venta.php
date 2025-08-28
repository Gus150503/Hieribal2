<?php
namespace Models;

use PDO;
use Core\Database;

final class Venta {
    private PDO $pdo;

    public function __construct(array $config) {
        $this->pdo = Database::get($config['db']);
    }

    /** KPI: total ventas del mes (usa ventas.fecha_venta) */
    public function totalDelMes(): int {
        $sql = "SELECT COUNT(*)
                  FROM ventas
                 WHERE YEAR(fecha_venta) = YEAR(CURDATE())
                   AND MONTH(fecha_venta) = MONTH(CURDATE())";
        return (int)$this->pdo->query($sql)->fetchColumn();
    }

    /** Carrusel: productos más vendidos del mes (desde carrito) */
    public function topProductos(int $limit = 10): array {
        $sql = "SELECT
                    COALESCE(p.nombre, c.nombre_producto) AS nombre,
                    p.imagen AS img,
                    SUM(c.cantidad) AS unidades
                FROM ventas v
                JOIN carrito c         ON c.id_carrito = v.id_carrito
                LEFT JOIN productos p  ON p.id = c.id_producto
               WHERE YEAR(v.fecha_venta) = YEAR(CURDATE())
                 AND MONTH(v.fecha_venta) = MONTH(CURDATE())
            GROUP BY COALESCE(p.id, c.id_producto), nombre, img
            ORDER BY unidades DESC
               LIMIT :lim";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Si no hay imagen en productos, deja null para que la vista use placeholder
        foreach ($rows as &$r) {
            if (!isset($r['img']) || $r['img'] === '') {
                $r['img'] = null;
            }
        }
        return $rows;
    }

    /**
     * Chart: top clientes del mes.
     * $modo: 'compras' (unidades) o 'monto' (cantidad*precio).
     * Devuelve [labels[], values[]]
     */
    public function topClientes(int $limit = 10, string $modo = 'compras'): array {
        if ($modo === 'monto') {
            $sql = "SELECT cli.nombres AS nombre, SUM(c.cantidad * c.precio) AS val
                      FROM ventas v
                      JOIN carrito c       ON c.id_carrito = v.id_carrito
                      JOIN clientes cli    ON cli.id_cliente = c.id_cliente
                     WHERE YEAR(v.fecha_venta) = YEAR(CURDATE())
                       AND MONTH(v.fecha_venta) = MONTH(CURDATE())
                  GROUP BY cli.id_cliente, cli.nombres
                  ORDER BY val DESC
                     LIMIT :lim";
        } else {
            $sql = "SELECT cli.nombres AS nombre, SUM(c.cantidad) AS val
                      FROM ventas v
                      JOIN carrito c       ON c.id_carrito = v.id_carrito
                      JOIN clientes cli    ON cli.id_cliente = c.id_cliente
                     WHERE YEAR(v.fecha_venta) = YEAR(CURDATE())
                       AND MONTH(v.fecha_venta) = MONTH(CURDATE())
                  GROUP BY cli.id_cliente, cli.nombres
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
