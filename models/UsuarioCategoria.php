<?php
declare(strict_types=1);

namespace Models;

use PDO;

final class Categoria
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Devuelve todas las categorías activas para los selects
     */
    public function listarActivas(): array
    {
        $sql = "SELECT id, nombre 
                FROM categorias
                WHERE estado = 'activa' OR estado = 'activo' OR estado = 1
                ORDER BY nombre ASC";
        $st = $this->db->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
