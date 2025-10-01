<?php
declare(strict_types=1);

namespace Models;

use PDO;

final class UsuarioInventario
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /** Listar con búsqueda y paginación */
    public function listar(string $q = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $where = '';
        $params = [];

        if ($q !== '') {
            $where = "WHERE codigo_interno LIKE :q OR ubicacion LIKE :q";
            $params[':q'] = "%$q%";
        }

        $stmt = $this->db->prepare("
            SELECT * FROM inventario
            $where
            ORDER BY id DESC
            LIMIT :per OFFSET :off
        ");
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':per', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM inventario $where");
        foreach ($params as $k => $v) {
            $countStmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        return ['data' => $rows, 'total' => $total];
    }

    /** Obtener un registro */
    public function obtener(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM inventario WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Crear */
    public function crear(array $d): int
    {
        $sql = "INSERT INTO inventario 
            (producto_id, codigo_interno, stock, stock_minimo, stock_maximo, punto_reorden, ubicacion, estado) 
            VALUES 
            (:producto_id, :codigo_interno, :stock, :stock_minimo, :stock_maximo, :punto_reorden, :ubicacion, :estado)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':producto_id'   => $d['producto_id'],
            ':codigo_interno'=> $d['codigo_interno'],
            ':stock'         => $d['stock'],
            ':stock_minimo'  => $d['stock_minimo'],
            ':stock_maximo'  => $d['stock_maximo'],
            ':punto_reorden' => $d['punto_reorden'],
            ':ubicacion'     => $d['ubicacion'],
            ':estado'        => $d['estado'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Actualizar */
    public function actualizar(int $id, array $d): bool
    {
        $sql = "UPDATE inventario SET 
            producto_id = :producto_id,
            codigo_interno = :codigo_interno,
            stock = :stock,
            stock_minimo = :stock_minimo,
            stock_maximo = :stock_maximo,
            punto_reorden = :punto_reorden,
            ubicacion = :ubicacion,
            estado = :estado
        WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':producto_id'   => $d['producto_id'],
            ':codigo_interno'=> $d['codigo_interno'],
            ':stock'         => $d['stock'],
            ':stock_minimo'  => $d['stock_minimo'],
            ':stock_maximo'  => $d['stock_maximo'],
            ':punto_reorden' => $d['punto_reorden'],
            ':ubicacion'     => $d['ubicacion'],
            ':estado'        => $d['estado'],
            ':id'            => $id,
        ]);
    }

    /** Eliminar */
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM inventario WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /** Cambiar estado disponible/agotado */
    public function toggleEstado(int $id): array
    {
        $row = $this->obtener($id);
        if (!$row) return [];

        $nuevo = $row['estado'] === 'disponible' ? 'agotado' : 'disponible';

        $stmt = $this->db->prepare("UPDATE inventario SET estado = :estado WHERE id = :id");
        $stmt->execute([':estado' => $nuevo, ':id' => $id]);

        return ['estado' => $nuevo];
    }
}
