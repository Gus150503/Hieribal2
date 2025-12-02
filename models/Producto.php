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

    /**
     * Carrusel: destacados
     * Usa inventario: suma el stock por producto y ordena de mayor a menor.
     */
    public function destacados(int $limit = 10): array {
        $sql = "
            SELECT 
                p.id,
                p.nombre,
                p.imagen AS img,
                COALESCE(SUM(i.stock), 0) AS stock
            FROM productos p
            LEFT JOIN inventario i 
                ON i.id_producto = p.id 
               AND (i.estado IS NULL OR i.estado = 'disponible')
            WHERE LOWER(p.estado) = 'activo'
            GROUP BY p.id, p.nombre, p.imagen
            ORDER BY stock DESC, p.nombre ASC
            LIMIT :lim
        ";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Carrusel: agotados
     * Productos cuyo stock total (inventario) es 0 o menor.
     */
    public function agotados(int $limit = 10): array {
        $sql = "
            SELECT 
                p.id,
                p.nombre,
                p.imagen AS img,
                COALESCE(SUM(i.stock), 0) AS stock
            FROM productos p
            LEFT JOIN inventario i 
                ON i.id_producto = p.id 
               AND (i.estado IS NULL OR i.estado = 'disponible')
            WHERE LOWER(p.estado) = 'activo'
            GROUP BY p.id, p.nombre, p.imagen
            HAVING stock <= 0
            ORDER BY p.nombre ASC
            LIMIT :lim
        ";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Chart: por acabarse
     * Productos con stock total > 0 y <= 5 (umbral), usando inventario.
     * Devuelve [labels, values]
     */
    public function porAcabarse(int $limit = 10, int $umbral = 5): array {
        $sql = "
            SELECT 
                p.nombre,
                COALESCE(SUM(i.stock), 0) AS stock
            FROM productos p
            LEFT JOIN inventario i 
                ON i.id_producto = p.id 
               AND (i.estado IS NULL OR i.estado = 'disponible')
            WHERE LOWER(p.estado) = 'activo'
            GROUP BY p.id, p.nombre
            HAVING stock > 0 AND stock <= :u
            ORDER BY stock ASC, p.nombre ASC
            LIMIT :lim
        ";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':u', $umbral, PDO::PARAM_INT);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            array_column($rows, 'nombre'),
            array_map('intval', array_column($rows, 'stock')),
        ];
    }

    /**
     * Chart: por pedir
     * Usa inventario.stock_minimo vs SUM(inventario.stock)
     * Devuelve [labels, values] donde value = faltante.
     */
public function porPedir(int $limit = 10): array {
    $sql = "
        SELECT 
            p.nombre,
            COALESCE(SUM(i.stock), 0) AS stock,
            COALESCE(MIN(i.stock_minimo), 0) AS stock_minimo,
            COALESCE(MIN(i.stock_minimo), 0) - COALESCE(SUM(i.stock), 0) AS faltante
        FROM productos p
        LEFT JOIN inventario i 
            ON i.id_producto = p.id 
           AND (i.estado IS NULL OR i.estado = 'disponible')
        WHERE LOWER(p.estado) = 'activo'
        GROUP BY p.id, p.nombre
        HAVING stock_minimo > 0 AND stock < stock_minimo
        ORDER BY faltante DESC, p.nombre ASC
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
        // aseguramos que no vaya número negativo
        $faltante[] = max(0, (int)$row['faltante']);
    }

    return [$labels, $faltante];
}

}
