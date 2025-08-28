<?php
namespace Models;

use PDO;

final class RepoVentas {
  private PDO $pdo;
  public function __construct(PDO $pdo){ $this->pdo = $pdo; }

  /** Tarjeta: más vendidos (unidades) */
  public function topVendidos(int $limit = 10): array {
    // ventas (id, fecha, cliente_id, total)
    // ventas_detalle (venta_id, producto_id, cantidad, precio)
    $sql = "SELECT p.id, p.nombre, p.img, SUM(vd.cantidad) AS unidades
              FROM ventas_detalle vd
              JOIN productos p ON p.id = vd.producto_id
          GROUP BY p.id, p.nombre, p.img
          ORDER BY unidades DESC
             LIMIT :lim";
    $st = $this->pdo->prepare($sql);
    $st->bindValue(':lim', $limit, \PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  /** Chart: clientes que más compran (importe o unidades) */
  public function topClientesMontoMesActual(int $limit = 10): array {
    $sql = "SELECT c.id, c.nombres AS nombre,
                   SUM(vd.cantidad * vd.precio) AS monto
              FROM ventas v
              JOIN ventas_detalle vd ON vd.venta_id = v.id
              JOIN clientes c        ON c.id = v.cliente_id
             WHERE YEAR(v.fecha) = YEAR(CURDATE()) AND MONTH(v.fecha) = MONTH(CURDATE())
          GROUP BY c.id, c.nombres
          ORDER BY monto DESC
             LIMIT :lim";
    $st = $this->pdo->prepare($sql);
    $st->bindValue(':lim', $limit, \PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  /** KPI: ventas del mes (conteo de ventas o suma de importe) */
  public function totalVentasMes(): int {
    $sql = "SELECT COUNT(*) FROM ventas WHERE YEAR(fecha)=YEAR(CURDATE()) AND MONTH(fecha)=MONTH(CURDATE())";
    return (int)$this->pdo->query($sql)->fetchColumn();
  }
}
