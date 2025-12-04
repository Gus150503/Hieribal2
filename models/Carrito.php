<?php
namespace Models;

use Core\Model;

class Carrito extends Model
{
    public function guardar(array $data, int $clienteId): bool
    {
        $sql = "INSERT INTO carrito (cliente_id, producto_id, cantidad, precio)
                VALUES (:cliente_id, :producto_id, :cantidad, :precio)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':cliente_id'  => $clienteId,
            ':producto_id' => $data['producto_id'],
            ':cantidad'    => $data['cantidad'],
            ':precio'      => $data['precio']
        ]);
    }

    public function listar(int $clienteId): array
    {
        $sql = "SELECT c.id, p.nombre, p.img, c.cantidad, c.precio
                FROM carrito c
                INNER JOIN productos p ON c.producto_id = p.id
                WHERE c.cliente_id = :cliente";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cliente' => $clienteId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
