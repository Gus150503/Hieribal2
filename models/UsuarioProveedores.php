<?php
namespace Models;

use PDO;

class UsuarioProveedores
{
    private PDO $db;

    // ✅ Recibe un PDO directo, no array
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ================== LISTAR ==================
    public function listar(string $q, int $page, int $per): array
    {
        $off  = ($page - 1) * $per;
        $like = "%{$q}%";

        $sql = "SELECT *
                FROM proveedores
                WHERE (empresa LIKE ? OR nit LIKE ? OR nombre_contacto LIKE ? OR ciudad LIKE ?)
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
        $items = $st->fetchAll();

        $sql2 = "SELECT COUNT(*) FROM proveedores
                 WHERE (empresa LIKE ? OR nit LIKE ? OR nombre_contacto LIKE ? OR ciudad LIKE ?)";
        $st2 = $this->db->prepare($sql2);
        $st2->bindValue(1, $like, PDO::PARAM_STR);
        $st2->bindValue(2, $like, PDO::PARAM_STR);
        $st2->bindValue(3, $like, PDO::PARAM_STR);
        $st2->bindValue(4, $like, PDO::PARAM_STR);
        $st2->execute();
        $total = (int)$st2->fetchColumn();

        return ['items' => $items, 'page' => $page, 'per' => $per, 'total' => $total];
    }

    // ================== OBTENER ==================
    public function obtener(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM proveedores WHERE id=:id");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    // ================== CREAR ==================
    public function crear(array $d): int
    {
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
        return (int)$this->db->lastInsertId();
    }

    // ================== ACTUALIZAR ==================
    public function actualizar(int $id, array $d): void
    {
        $sql = "UPDATE proveedores SET
                empresa=:empresa,nit=:nit,nombre_contacto=:nombre_contacto,
                telefono=:telefono,email=:email,direccion=:direccion,
                ciudad=:ciudad,condiciones_pago=:condiciones_pago,estado=:estado
                WHERE id=:id";
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
    }

    // ================== TOGGLE ESTADO ==================
    public function toggleEstado(int $id): array
    {
        $st = $this->db->prepare("SELECT id, estado FROM proveedores WHERE id=? LIMIT 1");
        $st->execute([$id]);
        $p = $st->fetch(PDO::FETCH_ASSOC);
        if (!$p) throw new \Exception('Proveedor no encontrado');

        $nuevoEstado = (strcasecmp($p['estado'], 'activo') === 0) ? 'inactivo' : 'activo';

        $this->db->prepare("UPDATE proveedores SET estado=? WHERE id=?")
                 ->execute([$nuevoEstado, $id]);

        return ['estado' => $nuevoEstado];
    }
}
