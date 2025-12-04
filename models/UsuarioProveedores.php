<?php
declare(strict_types=1);

namespace Models;

use PDO;
use PDOException;
use Exception;

final class UsuarioProveedores
{
    public function __construct(private PDO $db) {}

    /* =====================================================
       LISTAR con búsqueda y paginación
    ===================================================== */
    public function listar(string $q, int $page, int $per): array
    {
        $page = max(1, $page);
        $per  = max(1, min(100, $per));
        $off  = ($page - 1) * $per;
        $like = "%{$q}%";

        try {
            $sql = "SELECT id, empresa, nit, nombre_contacto, telefono, email,
                           direccion, ciudad, condiciones_pago, estado, creado
                    FROM proveedores
                    WHERE (empresa LIKE ?
                       OR  nit LIKE ?
                       OR  nombre_contacto LIKE ?
                       OR  ciudad LIKE ?)
                    ORDER BY id DESC
                    LIMIT ?, ?";
            $st = $this->db->prepare($sql);
            $st->bindValue(1, $like, PDO::PARAM_STR);
            $st->bindValue(2, $like, PDO::PARAM_STR);
            $st->bindValue(3, $like, PDO::PARAM_STR);
            $st->bindValue(4, $like, PDO::PARAM_STR);
            $st->bindValue(5, (int)$off, PDO::PARAM_INT);
            $st->bindValue(6, (int)$per, PDO::PARAM_INT);
            $st->execute();
            $items = $st->fetchAll(PDO::FETCH_ASSOC);

            $sql2 = "SELECT COUNT(*)
                     FROM proveedores
                     WHERE (empresa LIKE ?
                        OR  nit LIKE ?
                        OR  nombre_contacto LIKE ?
                        OR  ciudad LIKE ?)";
            $st2 = $this->db->prepare($sql2);
            $st2->bindValue(1, $like, PDO::PARAM_STR);
            $st2->bindValue(2, $like, PDO::PARAM_STR);
            $st2->bindValue(3, $like, PDO::PARAM_STR);
            $st2->bindValue(4, $like, PDO::PARAM_STR);
            $st2->execute();
            $total = (int)$st2->fetchColumn();

            return [
                'items' => $items,
                'page'  => $page,
                'per'   => $per,
                'total' => $total
            ];
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /* =====================================================
       OBTENER UNO
    ===================================================== */
    public function obtener(int $id): ?array
    {
        try {
            $st = $this->db->prepare(
                "SELECT id, empresa, nit, nombre_contacto, telefono, email,
                        direccion, ciudad, condiciones_pago, estado, creado
                 FROM proveedores
                 WHERE id = :id"
            );
            $st->execute([':id' => $id]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /* =====================================================
       CREAR
    ===================================================== */
    public function crear(array $d): int
    {
        try {
            $sql = "INSERT INTO proveedores
                    (empresa, nit, nombre_contacto, telefono, email, direccion,
                     ciudad, condiciones_pago, estado, creado)
                    VALUES
                    (:empresa, :nit, :nombre_contacto, :telefono, :email, :direccion,
                     :ciudad, :condiciones_pago, :estado, NOW())";
            $st = $this->db->prepare($sql);
            $st->execute([
                ':empresa'          => $d['empresa'],
                ':nit'              => $d['nit'],
                ':nombre_contacto'  => $d['nombre_contacto'],
                ':telefono'         => $d['telefono'],
                ':email'            => $d['email'],
                ':direccion'        => $d['direccion'],
                ':ciudad'           => $d['ciudad'],
                ':condiciones_pago' => $d['condiciones_pago'],
                ':estado'           => $d['estado'] ?? 'activo',
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            // 23000 = duplicados -> lo traduce el controller
            throw $e;
        }
    }

    /* =====================================================
       ACTUALIZAR
    ===================================================== */
    public function actualizar(int $id, array $d): void
    {
        try {
            $sql = "UPDATE proveedores SET
                        empresa = :empresa,
                        nit = :nit,
                        nombre_contacto = :nombre_contacto,
                        telefono = :telefono,
                        email = :email,
                        direccion = :direccion,
                        ciudad = :ciudad,
                        condiciones_pago = :condiciones_pago,
                        estado = :estado
                    WHERE id = :id";
            $st = $this->db->prepare($sql);
            $st->execute([
                ':empresa'          => $d['empresa'],
                ':nit'              => $d['nit'],
                ':nombre_contacto'  => $d['nombre_contacto'],
                ':telefono'         => $d['telefono'],
                ':email'            => $d['email'],
                ':direccion'        => $d['direccion'],
                ':ciudad'           => $d['ciudad'],
                ':condiciones_pago' => $d['condiciones_pago'],
                ':estado'           => $d['estado'],
                ':id'               => $id,
            ]);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /* =====================================================
       ELIMINAR
    ===================================================== */
    public function eliminar(int $id): void
    {
        try {
            $st = $this->db->prepare("DELETE FROM proveedores WHERE id = :id");
            $st->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /* =====================================================
       TOGGLE ESTADO
    ===================================================== */
    public function toggleEstado(int $id): array
    {
        try {
            $st = $this->db->prepare("SELECT estado FROM proveedores WHERE id = :id");
            $st->execute([':id' => $id]);
            $p = $st->fetch(PDO::FETCH_ASSOC);
            if (!$p) {
                throw new Exception('Proveedor no encontrado');
            }

            $nuevo = (strcasecmp($p['estado'] ?? '', 'activo') === 0)
                ? 'inactivo'
                : 'activo';

            $up = $this->db->prepare("UPDATE proveedores SET estado = :e WHERE id = :id");
            $up->execute([':e' => $nuevo, ':id' => $id]);

            return ['estado' => $nuevo];
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /* =====================================================
       PRODUCTOS DEL PROVEEDOR (tabla proveedor_producto)
    ===================================================== */

    /** Catálogo de productos activos (para el <select> del modal) */
    public function productosCatalogo(): array
    {
        // OJO: ajusta 'precio_compra' al nombre real de tu campo en productos
        $sql = "SELECT 
                    id, 
                    nombre, 
                    precio_compra
                FROM productos
                WHERE estado = 'activo'
                ORDER BY nombre ASC";

        $st = $this->db->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Devuelve los productos que maneja un proveedor concreto */
    public function productosDeProveedor(int $idProv): array
    {
        $sql = "SELECT 
                    pp.producto_id,
                    p.nombre,
                    -- precio base del producto (tabla productos)
                    p.precio_compra AS precio_base,
                    -- precio de compra que maneja este proveedor
                    pp.precio_compra AS precio_compra,
                    pp.activo
                FROM proveedor_producto pp
                JOIN productos p ON p.id = pp.producto_id
                WHERE pp.proveedor_id = :prov
                ORDER BY p.nombre ASC";

        $st = $this->db->prepare($sql);
        $st->execute([':prov' => $idProv]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Guarda la lista de productos que maneja un proveedor.
     * Estrategia: borrar los registros actuales y volver a insertar $items.
     *
     * $items = [
     *   ['producto_id' => 3, 'precio_compra' => 1100.00, 'activo' => 1],
     *   ...
     * ]
     */
    public function guardarProductosProveedor(int $idProv, array $items): void
    {
        $this->db->beginTransaction();

        try {
            // 1) Borrar relaciones actuales
            $del = $this->db->prepare(
                "DELETE FROM proveedor_producto WHERE proveedor_id = :id"
            );
            $del->execute([':id' => $idProv]);

            // 2) Insertar nuevas
            if (!empty($items)) {
                $ins = $this->db->prepare(
                    "INSERT INTO proveedor_producto
                     (proveedor_id, producto_id, precio_compra, activo)
                     VALUES (:prov, :prod, :precio, :activo)"
                );

                foreach ($items as $row) {
                    $prodId = (int)($row['producto_id'] ?? 0);
                    if ($prodId <= 0) {
                        continue;
                    }

                    $precio = isset($row['precio_compra'])
                        ? (float)$row['precio_compra']
                        : 0.0;
                    if ($precio < 0) {
                        $precio = 0.0;
                    }

                    $activo = !empty($row['activo']) ? 1 : 0;

                    $ins->execute([
                        ':prov'   => $idProv,
                        ':prod'   => $prodId,
                        ':precio' => $precio,
                        ':activo' => $activo,
                    ]);
                }
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
