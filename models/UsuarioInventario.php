<?php
namespace Models;

use PDO;

class Inventario
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ================== LISTAR ==================
    public function listar(string $q, int $page, int $per): array
    {
        $off  = ($page - 1) * $per;
        $like = "%{$q}%";

        $sql = "SELECT i.*, p.nombre AS producto_nombre
                FROM inventario i
                LEFT JOIN productos p ON i.producto_id = p.id
                WHERE (i.codigo_interno LIKE ? OR p.nombre LIKE ? OR i.ubicacion LIKE ?)
                ORDER BY i.id DESC
                LIMIT ?, ?";
        $st = $this->db->prepare($sql);
        $st->bindValue(1, $like, PDO::PARAM_STR);
        $st->bindValue(2, $like, PDO::PARAM_STR);
        $st->bindValue(3, $like, PDO::PARAM_STR);
        $st->bindValue(4, (int)$off, PDO::PARAM_INT);
        $st->bindValue(5, (int)$per, PDO::PARAM_INT);
        $st->execute();
        $items = $st->fetchAll();

        $sql2 = "SELECT COUNT(*) FROM inventario i
                 LEFT JOIN productos p ON i.producto_id = p.id
                 WHERE (i.codigo_interno LIKE ? OR p.nombre LIKE ? OR i.ubicacion LIKE ?)";
        $st2 = $this->db->prepare($sql2);
        $st2->bindValue(1, $like, PDO::PARAM_STR);
        $st2->bindValue(2, $like, PDO::PARAM_STR);
        $st2->bindValue(3, $like, PDO::PARAM_STR);
        $st2->execute();
        $total = (int)$st2->fetchColumn();

        return ['items' => $items, 'page' => $page, 'per' => $per, 'total' => $total];
    }

    // ================== OBTENER ==================
    public function obtener(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM inventario WHERE id=:id");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    // ================== CREAR ==================
    public function crear(array $d): int
    {
        $sql = "INSERT INTO inventario
                (producto_id,codigo_interno,stock,stock_minimo,stock_maximo,punto_reorden,ubicacion,estado)
                VALUES
                (:producto_id,:codigo_interno,:stock,:stock_minimo,:stock_maximo,:punto_reorden,:ubicacion,:estado)";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':producto_id'   => $d['producto_id'],
            ':codigo_interno'=> $d['codigo_interno'],
            ':stock'         => $d['stock'],
            ':stock_minimo'  => $d['stock_minimo'],
            ':stock_maximo'  => $d['stock_maximo'],
            ':punto_reorden' => $d['punto_reorden'],
            ':ubicacion'     => $d['ubicacion'],
            ':estado'        => $d['estado'] ?? 'disponible',
        ]);
        return (int)$this->db->lastInsertId();
    }

    // ================== ACTUALIZAR ==================
    public function actualizar(int $id, array $d): void
    {
        $sql = "UPDATE inventario SET
                producto_id=:producto_id,codigo_interno=:codigo_interno,stock=:stock,
                stock_minimo=:stock_minimo,stock_maximo=:stock_maximo,punto_reorden=:punto_reorden,
                ubicacion=:ubicacion,estado=:estado
                WHERE id=:id";
        $st = $this->db->prepare($sql);
        $st->execute([
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

    // ================== TOGGLE ESTADO ==================
    public function toggleEstado(int $id): array
    {
        $st = $this->db->prepare("SELECT id, estado FROM inventario WHERE id=? LIMIT 1");
        $st->execute([$id]);
        $inv = $st->fetch(PDO::FETCH_ASSOC);
        if (!$inv) throw new \Exception('Inventario no encontrado');

        // alterna entre 'disponible' y 'agotado'
        $nuevoEstado = (strcasecmp($inv['estado'], 'disponible') === 0) ? 'agotado' : 'disponible';

        $this->db->prepare("UPDATE inventario SET estado=? WHERE id=?")
                 ->execute([$nuevoEstado, $id]);

        return ['estado' => $nuevoEstado];
    }
}
