<?php
namespace Models;

use Core\Database;
use PDO;

final class Producto
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = Database::get($config['db']);
    }

    /** Total de productos “activos”. Ajusta la condición a tu esquema si no usas `eliminado`. */
    public function totalActivos(): int
    {
        $sql = "SELECT COUNT(*) FROM productos WHERE COALESCE(eliminado,0)=0";
        return (int)$this->pdo->query($sql)->fetchColumn();
    }

    /** Productos por acabarse -> [labels, values] */
    public function porAcabarse(int $limit = 10): array
    {
        $sql = "SELECT nombre, stock
                  FROM productos
                 WHERE COALESCE(eliminado,0)=0
              ORDER BY stock ASC
                 LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return [array_column($rows, 'nombre'), array_map('intval', array_column($rows, 'stock'))];
    }

    /** Productos por pedir (stock <= min_stock) -> [labels, values] */
    public function porPedir(int $limit = 10): array
    {
        $sql = "SELECT nombre, stock
                  FROM productos
                 WHERE COALESCE(eliminado,0)=0
                   AND min_stock IS NOT NULL
                   AND stock <= min_stock
              ORDER BY stock ASC
                 LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return [array_column($rows, 'nombre'), array_map('intval', array_column($rows, 'stock'))];
    }
}
