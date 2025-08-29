<?php
namespace Models;

use PDO;
use Core\Database;

final class Producto {
    private PDO $pdo;

    public function __construct(array $config) {
        $this->pdo = Database::get($config['db']);
    }

    /** KPI: total activos (case-insensitive por si guardas 'Activo' o 'activo') */
    public function totalActivos(): int {
        $st = $this->pdo->query("SELECT COUNT(*) FROM productos WHERE LOWER(estado)='activo'");
        return (int)$st->fetchColumn();
    }

    /** Carrusel: destacados (ordenados por mayor stock_actual) */
    public function destacados(int $limit = 10): array {
        $sql = "SELECT nombre, imagen AS img, stock_actual AS stock
                  FROM productos
                 WHERE LOWER(estado)='activo'
              ORDER BY stock_actual DESC
                 LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Carrusel: agotados (stock_actual <= 0) */
    public function agotados(int $limit = 10): array {
        $sql = "SELECT nombre, imagen AS img
                  FROM productos
                 WHERE stock_actual <= 0
              ORDER BY nombre
                 LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Chart: por acabarse (0 < stock_actual <= 5) -> [labels, values] */
    public function porAcabarse(int $limit = 10): array {
        $sql = "SELECT nombre, stock_actual
                  FROM productos
                 WHERE stock_actual > 0 AND stock_actual <= 5
              ORDER BY stock_actual ASC
                 LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return [
            array_column($rows, 'nombre'),
            array_map('intval', array_column($rows, 'stock_actual')),
        ];
    }

    /** Chart: por pedir (stock_actual < stock_minimo) -> [labels, values] */
    public function porPedir(int $limit = 10): array {
        $sql = "SELECT nombre, (stock_minimo - stock_actual) AS faltante
                  FROM productos
                 WHERE stock_minimo IS NOT NULL AND stock_actual < stock_minimo
              ORDER BY faltante DESC
                 LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return [
            array_column($rows, 'nombre'),
            array_map('intval', array_column($rows, 'faltante')),
        ];
    }
}
