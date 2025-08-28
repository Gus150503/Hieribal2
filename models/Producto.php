<?php
namespace Models;

use PDO;
use Core\Database;

final class Producto {
    private PDO $pdo;
    public function __construct(array $config) { $this->pdo = Database::get($config['db']); }

    // KPI: total activos (si usas estado='activo')
    public function totalActivos(): int {
        $st = $this->pdo->query("SELECT COUNT(*) FROM productos WHERE estado='activo'");
        return (int)$st->fetchColumn();
    }

    /** Carrusel: destacados (no tienes columna 'destacado', así que usamos mayor stock/unidad) */
    public function destacados(int $limit = 10): array {
        $sql = "SELECT nombre, imagen AS img, unidad 
                  FROM productos 
                 WHERE estado='activo'
              ORDER BY unidad DESC
                 LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Carrusel: agotados (unidad <= 0) */
    public function agotados(int $limit = 10): array {
        $sql = "SELECT nombre, imagen AS img 
                  FROM productos 
                 WHERE unidad <= 0
              ORDER BY nombre
                 LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Chart: por acabarse (unidad > 0 y <= 5) -> [labels, values] */
    public function porAcabarse(int $limit = 10): array {
        $sql = "SELECT nombre, unidad 
                  FROM productos 
                 WHERE unidad > 0 AND unidad <= 5
              ORDER BY unidad ASC
                 LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return [array_column($rows, 'nombre'), array_map('intval', array_column($rows, 'unidad'))];
    }

    /** Chart: por pedir (min_stock - unidad) -> [labels, values] */
    public function porPedir(int $limit = 10): array {
        $sql = "SELECT nombre, (min_stock - unidad) AS faltante
                  FROM productos
                 WHERE min_stock IS NOT NULL AND unidad < min_stock
              ORDER BY faltante DESC
                 LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return [array_column($rows, 'nombre'), array_map('intval', array_column($rows, 'faltante'))];
    }
}
