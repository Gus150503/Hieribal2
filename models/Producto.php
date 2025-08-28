<?php
namespace Models;

use Core\Database;
use PDO;

final class Producto {
    private PDO $db;

    public function __construct(array $config) {
        $this->db = Database::get($config['db']);
    }

    /** Total de productos “activos”. Si no usas un flag real, cuenta todos. */
    public function totalActivos(): int {
        // Ajusta esta condición a tu esquema real de “activo”
        $sql = "SELECT COUNT(*) FROM productos
                WHERE estado IS NULL OR estado = 1 OR estado = 'Activo'";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    /** Serie para “Productos por acabarse”. Tu tabla no tiene stock ⇒ vacío. */
    public function porAcabarse(int $limit = 10): array {
        // Cuando agregues columnas de inventario, cambia este método.
        return [[], []];
    }

    /** Serie para “Productos por pedir”. Tu tabla no tiene min/max ⇒ vacío. */
    public function porPedir(int $limit = 10): array {
        return [[], []];
    }
}
