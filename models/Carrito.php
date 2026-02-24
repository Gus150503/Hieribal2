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

        // 👈 Igual que Cliente.php
        $this->pdo = Database::get($config['db']);
    }

    /**
     * Guarda TODOS los items del carrito.
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

        $sql = "INSERT INTO carrito (
            id_producto,
            nombre_producto,
            cantidad,
            precio,
            subtotal,
            id_cliente,
            fecha_agregado,
            telefono_envio,
            direccion_envio,
            metodo_pago,
            notas
        ) VALUES (
            :id_producto,
            :nombre_producto,
            :cantidad,
            :precio,
            :subtotal,
            :id_cliente,
            NOW(),
            :telefono_envio,
            :direccion_envio,
            :metodo_pago,
            :notas
        )";


        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);

            foreach ($items as $item) {
                $idProducto = (int)$item['id'];
                $nombre     = (string)$item['nombre'];
                $cantidad   = (int)$item['cantidad'];
                $precio     = (float)$item['precio'];
                $subtotal   = $precio * $cantidad;

                $stmt->execute([
                    ':id_producto'     => $idProducto,
                    ':nombre_producto' => $nombre,
                    ':cantidad'        => $cantidad,
                    ':precio'          => $precio,
                    ':subtotal'        => $subtotal,
                    ':id_cliente'      => $clienteId,
                    ':telefono_envio'  => $telefono,
                    ':direccion_envio' => $direccion,
                    ':metodo_pago'     => $pago,
                    ':notas'           => $notas,
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
        $sql = "SELECT 
                    id_carrito,
                    id_producto,
                    nombre_producto,
                    cantidad,
                    precio,
                    subtotal,
                    fecha_agregado
                FROM carrito
                WHERE id_cliente = :cliente
                ORDER BY fecha_agregado DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':cliente' => $clienteId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
