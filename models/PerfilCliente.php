<?php

namespace Models;

use Core\Database;
use PDO;

class PerfilCliente
{
    private PDO $db;

    public function __construct(array $config)
    {
        $this->db = Database::get($config['db']);
    }

    public function obtenerDatos($id)
    {
        // ==========================
        // 🔥 TOTAL PEDIDOS (carrito)
        // ==========================
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT fecha_agregado) as total
            FROM carrito
            WHERE id_cliente = :id
        ");
        $stmt->execute([':id' => $id]);
        $pedidos = $stmt->fetch(PDO::FETCH_ASSOC);

        // ==========================
        // 💰 TOTAL GASTADO
        // ==========================
        $stmt = $this->db->prepare("
            SELECT SUM(subtotal) as gastado
            FROM carrito
            WHERE id_cliente = :id
        ");
        $stmt->execute([':id' => $id]);
        $gastado = $stmt->fetch(PDO::FETCH_ASSOC);

        // ==========================
        // 🛒 ITEMS CARRITO
        // ==========================
        $stmt = $this->db->prepare("
            SELECT SUM(cantidad) as items
            FROM carrito
            WHERE id_cliente = :id
        ");
        $stmt->execute([':id' => $id]);
        $carrito = $stmt->fetch(PDO::FETCH_ASSOC);

        // ==========================
        // 📦 COMPRAS POR MES
        // ==========================
        $stmt = $this->db->prepare("
            SELECT MONTH(fecha_agregado) as mes, SUM(subtotal) as total
            FROM carrito
            WHERE id_cliente = :id
            GROUP BY MONTH(fecha_agregado)
        ");
        $stmt->execute([':id' => $id]);
        $comprasMes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ==========================
        // 🔥 TOP PRODUCTOS
        // ==========================
        $stmt = $this->db->prepare("
            SELECT nombre_producto, SUM(cantidad) as total
            FROM carrito
            WHERE id_cliente = :id
            GROUP BY nombre_producto
            ORDER BY total DESC
            LIMIT 5
        ");
        $stmt->execute([':id' => $id]);
        $topProductos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 🔥 CALCULAR PORCENTAJES
        $totalGeneral = 0;
        foreach ($topProductos as $p) {
            $totalGeneral += $p['total'];
        }

        foreach ($topProductos as &$p) {
            $p['porcentaje'] = ($totalGeneral > 0)
                ? round(($p['total'] / $totalGeneral) * 100, 1)
                : 0;
        }

        return [
            "pedidos" => $pedidos,
            "gastado" => $gastado,
            "carrito" => $carrito,
            "comprasMes" => $comprasMes,
            "topProductos" => $topProductos
        ];
    }
}