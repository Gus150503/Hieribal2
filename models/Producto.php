<?php
namespace Models;

use PDO;
use Core\Database;

final class Producto {
    private PDO $pdo;

    public function __construct(array $config) {
        $this->pdo = Database::get($config['db']);
    }

    /** Total productos activos */
    public function totalActivos(): int {
        $st = $this->pdo->query("SELECT COUNT(*) FROM productos WHERE LOWER(estado) = 'activo'");
        return (int)$st->fetchColumn();
    }

    /** Inventario destacado (mayor stock_actual) */
    public function destacados(int $limit = 10): array {
        $sql = "
            SELECT 
                id,
                nombre,
                imagen AS img,
                stock_actual AS stock
            FROM productos
            WHERE LOWER(estado) = 'activo'
            ORDER BY stock_actual DESC, nombre ASC
            LIMIT :lim
        ";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Agotados (stock_actual = 0) */
    public function agotados(int $limit = 10): array {
        $sql = "
            SELECT 
                id,
                nombre,
                imagen AS img,
                stock_actual AS stock
            FROM productos
            WHERE LOWER(estado) = 'activo'
              AND stock_actual <= 0
            ORDER BY nombre ASC
            LIMIT :lim
        ";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Productos por acabarse (stock_actual entre 1 y umbral) */
    public function porAcabarse(int $limit = 10, int $umbral = 5): array {
        $sql = "
            SELECT 
                nombre,
                stock_actual AS stock
            FROM productos
            WHERE LOWER(estado) = 'activo'
              AND stock_actual > 0 
              AND stock_actual <= :u
            ORDER BY stock_actual ASC, nombre ASC
            LIMIT :lim
        ";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':u', $umbral, PDO::PARAM_INT);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            array_column($rows, 'nombre'),
            array_map('intval', array_column($rows, 'stock'))
        ];
    }

    /** Productos por pedir (stock_actual < stock_minimo) */
    public function porPedir(int $limit = 10): array {
        $sql = "
            SELECT 
                nombre,
                stock_actual,
                stock_minimo,
                (stock_minimo - stock_actual) AS faltante
            FROM productos
            WHERE LOWER(estado) = 'activo'
              AND stock_minimo > 0
              AND stock_actual < stock_minimo
            ORDER BY faltante DESC, nombre ASC
            LIMIT :lim
        ";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();

        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $labels   = [];
        $faltante = [];

        foreach ($rows as $row) {
            $labels[]   = $row['nombre'];
            $faltante[] = max(0, (int)$row['faltante']);
        }

        return [$labels, $faltante];
    }
}
