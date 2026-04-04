<?php

namespace Models;

use Core\Database;
use PDO;
use PDOException;
use RuntimeException;

final class Carrito
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        if (empty($config['db'])) {
            throw new RuntimeException('Configuración de BD no encontrada');
        }
        $this->pdo = Database::get($config['db']);
    }

    /**
     * Guarda el carrito y descuenta del inventario automáticamente
     */
    public function guardar(
        array $items,
        int $clienteId,
        string $telefono,
        string $direccion,
        string $pago,
        string $notas = ''
    ): bool {
        if (empty($items)) return false;

        // SQL para registrar la compra
        $sqlCarrito = "INSERT INTO carrito (
            id_producto, nombre_producto, cantidad, precio, subtotal, 
            id_cliente, fecha_agregado, telefono_envio, direccion_envio, 
            metodo_pago, notas
        ) VALUES (
            :id_producto, :nombre_producto, :cantidad, :precio, :subtotal, 
            :id_cliente, NOW(), :telefono_envio, :direccion_envio, 
            :metodo_pago, :notas
        )";

        // SQL para descontar del inventario
        $sqlStock = "UPDATE inventario SET stock = stock - :cantidad WHERE id_producto = :id_p";

        try {
            $this->pdo->beginTransaction();
            
            $stmtC = $this->pdo->prepare($sqlCarrito);
            $stmtS = $this->pdo->prepare($sqlStock);

            foreach ($items as $item) {
                $idProd   = (int)$item['id'];
                $cant     = (int)$item['cantidad'];
                $prec     = (float)$item['precio'];
                $subtotal = $prec * $cant;

                // 1. Insertar en tabla carrito
                $stmtC->execute([
                    ':id_producto'     => $idProd,
                    ':nombre_producto' => (string)$item['nombre'],
                    ':cantidad'        => $cant,
                    ':precio'          => $prec,
                    ':subtotal'        => $subtotal,
                    ':id_cliente'      => $clienteId,
                    ':telefono_envio'  => $telefono,
                    ':direccion_envio' => $direccion,
                    ':metodo_pago'     => $pago,
                    ':notas'           => $notas,
                ]);

                // 2. Descontar del inventario [Referencia image_c94b7f.png]
                $stmtS->execute([
                    ':cantidad' => $cant,
                    ':id_p'     => $idProd
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function listar(int $clienteId): array
    {
        $sql = "SELECT id_carrito, id_producto, nombre_producto, cantidad, precio, subtotal, fecha_agregado
                FROM carrito WHERE id_cliente = :cliente ORDER BY fecha_agregado DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':cliente' => $clienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}