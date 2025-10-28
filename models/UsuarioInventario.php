<?php
declare(strict_types=1);

namespace Models;

use PDO;

final class UsuarioInventario
{
    private PDO $db;

    /**
     * Recibe un PDO (tal como haces con Database::get($config['db']))
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
        // Por si acaso: asegúrate de tener estos atributos en tu Database::get()
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * Listar con búsqueda + paginación
     * Devuelve items/page/per/total para que el JS funcione igual que usuarios.
     * Busca por codigo_interno y ubicacion.
     */
    public function listar(string $q = '', int $page = 1, int $per = 10): array
    {
        $page = max(1, $page);
        $per  = max(1, min(50, $per));
        $off  = ($page - 1) * $per;

        $where  = '';
        $params = [];
        if ($q !== '') {
            $where = "WHERE codigo_interno LIKE ? OR ubicacion LIKE ?";
            $like  = "%{$q}%";
            $params[] = $like;
            $params[] = $like;
        }

        // Alias id_producto -> producto_id para el frontend
        $sql = "SELECT
                    id,
                    id_producto AS producto_id,
                    codigo_interno,
                    stock,
                    stock_minimo,
                    stock_maximo,
                    punto_reorden,
                    ubicacion,
                    estado
                FROM inventario
                {$where}
                ORDER BY id DESC
                LIMIT ?, ?";
        $st = $this->db->prepare($sql);

        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p, PDO::PARAM_STR);
        }
        $st->bindValue($i++, (int)$off, PDO::PARAM_INT);
        $st->bindValue($i++, (int)$per, PDO::PARAM_INT);
        $st->execute();
        $items = $st->fetchAll();

        $sql2 = "SELECT COUNT(*) FROM inventario {$where}";
        $st2  = $this->db->prepare($sql2);
        $i = 1;
        foreach ($params as $p) {
            $st2->bindValue($i++, $p, PDO::PARAM_STR);
        }
        $st2->execute();
        $total = (int)$st2->fetchColumn();

        return ['items' => $items, 'page' => $page, 'per' => $per, 'total' => $total];
    }

    /** Obtener un registro */
    public function obtener(int $id): ?array
    {
        $st = $this->db->prepare("SELECT
                                     id,
                                     id_producto AS producto_id,
                                     codigo_interno,
                                     stock,
                                     stock_minimo,
                                     stock_maximo,
                                     punto_reorden,
                                     ubicacion,
                                     estado
                                  FROM inventario
                                  WHERE id = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /** Crear (mapea producto_id -> id_producto) */
    public function crear(array $d): int
    {
        $sql = "INSERT INTO inventario
                (id_producto, codigo_interno, stock, stock_minimo, stock_maximo, punto_reorden, ubicacion, estado)
                VALUES
                (:id_producto, :codigo_interno, :stock, :stock_minimo, :stock_maximo, :punto_reorden, :ubicacion, :estado)";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':id_producto'    => (int)$d['producto_id'],
            ':codigo_interno' => (string)$d['codigo_interno'],
            ':stock'          => (int)$d['stock'],
            ':stock_minimo'   => (int)$d['stock_minimo'],
            ':stock_maximo'   => (int)$d['stock_maximo'],
            ':punto_reorden'  => (int)$d['punto_reorden'],
            ':ubicacion'      => (string)$d['ubicacion'],
            ':estado'         => (string)$d['estado'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    /** Actualizar (mapea producto_id -> id_producto) */
    public function actualizar(int $id, array $d): void
    {
        $sql = "UPDATE inventario SET
                    id_producto    = :id_producto,
                    codigo_interno = :codigo_interno,
                    stock          = :stock,
                    stock_minimo   = :stock_minimo,
                    stock_maximo   = :stock_maximo,
                    punto_reorden  = :punto_reorden,
                    ubicacion      = :ubicacion,
                    estado         = :estado
                WHERE id = :id";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':id_producto'    => (int)$d['producto_id'],
            ':codigo_interno' => (string)$d['codigo_interno'],
            ':stock'          => (int)$d['stock'],
            ':stock_minimo'   => (int)$d['stock_minimo'],
            ':stock_maximo'   => (int)$d['stock_maximo'],
            ':punto_reorden'  => (int)$d['punto_reorden'],
            ':ubicacion'      => (string)$d['ubicacion'],
            ':estado'         => (string)$d['estado'],
            ':id'             => $id,
        ]);
    }

    /** Eliminar */
    public function eliminar(int $id): void
    {
        $st = $this->db->prepare("DELETE FROM inventario WHERE id = :id");
        $st->execute([':id' => $id]);
    }

    /** Toggle estado disponible/agotado */
    public function toggleEstado(int $id): array
    {
        $row = $this->obtener($id);
        if (!$row) { return []; }

        $nuevo = (strcasecmp((string)$row['estado'], 'disponible') === 0) ? 'agotado' : 'disponible';

        $st = $this->db->prepare("UPDATE inventario SET estado = :estado WHERE id = :id");
        $st->execute([':estado' => $nuevo, ':id' => $id]);

        return ['estado' => $nuevo];
    }
}
