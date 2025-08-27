<?php
namespace Models;

use Core\Database;
use PDO;

final class Venta {
    private PDO $db;

    public function __construct(array $config) {
        $this->db = Database::get($config['db']);
    }

    /** Total de ventas del mes (conteo). */
    public function totalDelMes(): int {
        $sql = "SELECT COUNT(*)
                FROM ventas
                WHERE DATE_FORMAT(fecha_venta, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    /** Top clientes. Tu tabla de ventas no referencia clientes ⇒ vacío. */
    public function topClientes(int $limit = 10, string $metric = 'compras'): array {
        // Cuando tengas relación ventas → cliente, implementa el TOP real.
        return [[], []];
    }
}
