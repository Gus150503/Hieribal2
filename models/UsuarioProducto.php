<?php
namespace Models;

use PDO;

class UsuarioProducto {
    private PDO $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    public function crear(array $d): int {
        $sql = "INSERT INTO productos
        (nombre, categoria, marca, presentacion, unidad, descripcion, lote, f_vencimiento,
         precio_compra, precio_venta, iva, codigo_sku, ubicacion, estado, imagen)
        VALUES
        (:nombre, :categoria, :marca, :presentacion, :unidad, :descripcion, :lote, :f_vencimiento,
         :precio_compra, :precio_venta, :iva, :codigo_sku, :ubicacion, :estado, :imagen)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($d);
        return (int)$this->db->lastInsertId();
    }

    public function actualizar(int $id, array $d): void {
        $d['id'] = $id;
        $sql = "UPDATE productos SET
            nombre=:nombre, categoria=:categoria, marca=:marca, presentacion=:presentacion,
            unidad=:unidad, descripcion=:descripcion, lote=:lote, f_vencimiento=:f_vencimiento,
            precio_compra=:precio_compra, precio_venta=:precio_venta, iva=:iva,
            codigo_sku=:codigo_sku, ubicacion=:ubicacion, estado=:estado, imagen=:imagen
            WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($d);
    }

    public function obtener(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function listar(string $q = '', int $page = 1, int $per = 10): array {
        $offset = ($page - 1) * $per;
        $sql = "SELECT * FROM productos
                WHERE nombre LIKE :q OR codigo_sku LIKE :q
                ORDER BY id DESC LIMIT :per OFFSET :off";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':q', "%$q%");
        $stmt->bindValue(':per', $per, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = $this->db->query("SELECT COUNT(*) FROM productos")->fetchColumn();

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per' => $per];
    }

    public function eliminar(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function toggleEstado(int $id): array {
        $row = $this->obtener($id);
        if (!$row) return [];
        $nuevo = ($row['estado'] === 'activo') ? 'inactivo' : 'activo';
        $stmt = $this->db->prepare("UPDATE productos SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo, $id]);
        return ['estado' => $nuevo];
    }
}
