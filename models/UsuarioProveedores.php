<?php
declare(strict_types=1);

namespace Models;

use PDO;
use PDOException;
use Exception;

final class UsuarioProveedores
{
    public function __construct(private PDO $db) {}

    /** LISTAR */
    public function listar(string $q, int $page, int $per): array
    {
        $off  = ($page - 1) * $per;
        $like = "%{$q}%";

        try {
            $sql = "SELECT *
                    FROM proveedores
                    WHERE (empresa LIKE :q OR nit LIKE :q OR nombre_contacto LIKE :q OR ciudad LIKE :q)
                    ORDER BY id DESC
                    LIMIT :per OFFSET :off";
            $st = $this->db->prepare($sql);
            $st->bindValue(':q',   $like, PDO::PARAM_STR);
            $st->bindValue(':per', $per,  PDO::PARAM_INT);
            $st->bindValue(':off', $off,  PDO::PARAM_INT);
            $st->execute();
            $items = $st->fetchAll();

            $st2 = $this->db->prepare(
                "SELECT COUNT(*) FROM proveedores
                 WHERE (empresa LIKE :q OR nit LIKE :q OR nombre_contacto LIKE :q OR ciudad LIKE :q)"
            );
            $st2->bindValue(':q', $like, PDO::PARAM_STR);
            $st2->execute();
            $total = (int) $st2->fetchColumn();

            return ['items' => $items, 'page' => $page, 'per' => $per, 'total' => $total];
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /** OBTENER UNO */
    public function obtener(int $id): ?array
    {
        try {
            $st = $this->db->prepare("SELECT * FROM proveedores WHERE id = :id");
            $st->execute([':id' => $id]);
            $row = $st->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /** CREAR */
    public function crear(array $d): int
    {
        try {
            $sql = "INSERT INTO proveedores
                    (empresa,nit,nombre_contacto,telefono,email,direccion,ciudad,condiciones_pago,estado,creado)
                    VALUES
                    (:empresa,:nit,:nombre_contacto,:telefono,:email,:direccion,:ciudad,:condiciones_pago,:estado,NOW())";
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
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw $e; // 23000 = duplicados (NIT, etc.) -> lo mapea tu controller
        }
    }

    /** ACTUALIZAR */
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

    /** ELIMINAR */
    public function eliminar(int $id): void
    {
        try {
            $st = $this->db->prepare("DELETE FROM proveedores WHERE id = :id");
            $st->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /** TOGGLE ESTADO */
    public function toggleEstado(int $id): array
    {
        try {
            $st = $this->db->prepare("SELECT estado FROM proveedores WHERE id = :id");
            $st->execute([':id' => $id]);
            $p = $st->fetch(PDO::FETCH_ASSOC);
            if (!$p) throw new Exception('Proveedor no encontrado');

            $nuevo = (strcasecmp($p['estado'] ?? '', 'activo') === 0) ? 'inactivo' : 'activo';

            $up = $this->db->prepare("UPDATE proveedores SET estado = :e WHERE id = :id");
            $up->execute([':e' => $nuevo, ':id' => $id]);

            return ['estado' => $nuevo];
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
