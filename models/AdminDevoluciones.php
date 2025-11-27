<?php
    namespace Models;

    use PDO;

    final class Devoluciones
    {
        private PDO $pdo;

        public function __construct(PDO $pdo)
        {
            $this->pdo = $pdo;
        }

        /* ============================================================
        TOP: Productos con más devoluciones (unidades)
        ============================================================ */
        public function topProductosDevueltos(int $limit = 10): array
        {
            // Asumiendo:
            // devoluciones (id, producto, motivo, fecha_devolucion, ...)
            // productos (id, nombre, img)

            $sql = "SELECT p.id,
                        p.nombre,
                        p.img,
                        COUNT(d.id) AS devoluciones
                    FROM devoluciones d
                    JOIN productos p ON p.nombre = d.producto
                GROUP BY p.id, p.nombre, p.img
                ORDER BY devoluciones DESC
                    LIMIT :lim";

            $st = $this->pdo->prepare($sql);
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();

            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        /* ============================================================
        TOP: Clientes que más devuelven (conteo)
        ============================================================ */
        public function topClientesMesActual(int $limit = 10): array
        {
            // devoluciones (id, nombre_cliente, fecha_devolucion)

            $sql = "SELECT d.nombre_cliente AS cliente,
                        COUNT(*) AS total
                    FROM devoluciones d
                    WHERE YEAR(d.fecha_devolucion) = YEAR(CURDATE())
                    AND MONTH(d.fecha_devolucion) = MONTH(CURDATE())
                GROUP BY d.nombre_cliente
                ORDER BY total DESC
                    LIMIT :lim";

            $st = $this->pdo->prepare($sql);
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();

            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        /* ============================================================
        KPI: total de devoluciones del mes
        ============================================================ */
        public function totalDevolucionesMes(): int
        {
            $sql = "SELECT COUNT(*)
                    FROM devoluciones
                    WHERE YEAR(fecha_devolucion) = YEAR(CURDATE())
                    AND MONTH(fecha_devolucion) = MONTH(CURDATE())";

            return (int)$this->pdo->query($sql)->fetchColumn();
        }
    }
