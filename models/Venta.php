<?php
namespace Models;

use Core\Database;
use PDO;

final class Venta
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = Database::get($config['db']);
    }

    /** Total de ventas del mes actual (cuenta registros). Cambia a SUM(total) si quieres monto. */
    public function totalDelMes(): int
    {
        $sql = "SELECT COALESCE(COUNT(*),0)
                  FROM ventas
                 WHERE DATE_FORMAT(fecha, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
                   AND COALESCE(eliminado,0)=0";
        return (int)$this->pdo->query($sql)->fetchColumn();
    }

    /** Top clientes por cantidad de compras -> [labels, values] */
    public function clientesQueMasCompran(int $limit = 10): array
    {
        $sql = "SELECT c.nombre, COUNT(*) compras
                  FROM ventas v
                  JOIN clientes c ON c.id = v.cliente_id
                 WHERE COALESCE(v.eliminado,0)=0
              GROUP BY c.id, c.nombre
              ORDER BY compras DESC
                 LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return [array_column($rows, 'nombre'), array_map('intval', array_column($rows, 'compras'))];
    }
}
