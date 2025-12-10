<?php
declare(strict_types=1);

namespace Models;

use PDO;
use PDOException;
use Exception;

/**models/UsuarioProveedores.php
 *  MODULO PROVEEDORES / Juliana Lugo / Clase modelo para gestión de proveedores.
 * Gestiona proveedores y sus productos relacionados.
 * 
 * Incluye:
 * - CRUD completo de proveedores
 * - Paginación y búsqueda
 * - Asociación de productos a proveedores
 */
final class UsuarioProveedores
{
    /**
     * Constructor: recibe una instancia de PDO ya configurada.
     */
    public function __construct(private PDO $db) {}

    /* =====================================================
       LISTAR con búsqueda y paginación
       ===================================================== */
    /**
     * Lista proveedores aplicando filtros de búsqueda y paginación.
     *
     * @param string $q     Texto a buscar (empresa, nit, contacto, ciudad).
     * @param int    $page  Página actual.
     * @param int    $per   Registros por página.
     *
     * @return array        Lista de proveedores + datos de paginación.
     * @throws PDOException
     */
    public function listar(string $q, int $page, int $per): array
    {
        // Normalización de paginación
        $page = max(1, $page);
        $per  = max(1, min(100, $per));
        $off  = ($page - 1) * $per;
        $like = "%{$q}%";

        try {
            // Consulta principal
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

            // Bind seguro
            $st->bindValue(1, $like, PDO::PARAM_STR);
            $st->bindValue(2, $like, PDO::PARAM_STR);
            $st->bindValue(3, $like, PDO::PARAM_STR);
            $st->bindValue(4, $like, PDO::PARAM_STR);
            $st->bindValue(5, (int)$off, PDO::PARAM_INT);
            $st->bindValue(6, (int)$per, PDO::PARAM_INT);

            $st->execute();
            $items = $st->fetchAll(PDO::FETCH_ASSOC);

            // Total de registros
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
    /**
     * Obtiene un solo proveedor por ID.
     */
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
    /**
     * Crea un nuevo proveedor en la base de datos.
     *
     * @return int ID del proveedor insertado.
     */
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
            throw $e;
        }
    }

    /* =====================================================
       ACTUALIZAR
       ===================================================== */
    /**
     * Actualiza un proveedor existente.
     */
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
    /**
     * Elimina un proveedor por ID.
     */
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
    /**
     * Alterna estado: activo ↔ inactivo.
     *
     * @return array Nuevo estado.
     */
    public function toggleEstado(int $id): array
    {
        try {
            // Leer estado actual
            $st = $this->db->prepare("SELECT estado FROM proveedores WHERE id = :id");
            $st->execute([':id' => $id]);
            $p = $st->fetch(PDO::FETCH_ASSOC);

            if (!$p) {
                throw new Exception('Proveedor no encontrado');
            }

            // Alternancia
            $nuevo = (strcasecmp($p['estado'] ?? '', 'activo') === 0)
                ? 'inactivo'
                : 'activo';

            // Guardar cambio
            $up = $this->db->prepare("UPDATE proveedores SET estado = :e WHERE id = :id");
            $up->execute([':e' => $nuevo, ':id' => $id]);

            return ['estado' => $nuevo];

        } catch (PDOException $e) {
            throw $e;
        }
    }

    /* =====================================================
       PRODUCTOS DEL PROVEEDOR
       ===================================================== */

    /**
     * Catálogo de productos activos para el selector de proveedores.
     */
    public function productosCatalogo(): array
    {
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

    /**
     * Devuelve los productos asociados a un proveedor.
     */
    public function productosDeProveedor(int $idProv): array
    {
        $sql = "SELECT 
                    pp.producto_id,
                    p.nombre,
                    p.precio_compra AS precio_base,
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
     * Guarda las relaciones producto ↔ proveedor.
     * 
     * Estrategia:
     *   1. Borrar asociaciones actuales
     *   2. Insertar nuevas
     */
    public function guardarProductosProveedor(int $idProv, array $items): void
    {
        $this->db->beginTransaction();

        try {
            // Eliminar relaciones existentes
            $del = $this->db->prepare(
                "DELETE FROM proveedor_producto WHERE proveedor_id = :id"
            );
            $del->execute([':id' => $idProv]);

            // Insertar nuevas asociaciones
            if (!empty($items)) {
                $ins = $this->db->prepare(
                    "INSERT INTO proveedor_producto
                     (proveedor_id, producto_id, precio_compra, activo)
                     VALUES (:prov, :prod, :precio, :activo)"
                );

                foreach ($items as $row) {
                    $prodId = (int)($row['producto_id'] ?? 0);
                    if ($prodId <= 0) continue;

                    $precio = isset($row['precio_compra'])
                        ? (float)$row['precio_compra']
                        : 0.0;

                    if ($precio < 0) $precio = 0.0;

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
