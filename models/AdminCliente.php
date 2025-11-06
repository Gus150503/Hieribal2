<?php
namespace Models;

use PDO;

class Cliente
{
    private PDO $db;

    public function __construct(array $config)
    {
        $db = $config['db'] ?? [];
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
            $db['host'] ?? '127.0.0.1',
            $db['name'] ?? 'hieribal',
            $db['charset'] ?? 'utf8mb4'
        );
        $this->db = new PDO($dsn, $db['user'] ?? 'root', $db['pass'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    // === LISTAR ===
    public function listar(string $q, int $page, int $per): array
    {
        $off  = ($page - 1) * $per;
        $like = "%{$q}%";

        $sql = "SELECT id_cliente, cedula, nombres, apellidos, telefono, correo, estado, fecha_registro
                FROM clientes
                WHERE (cedula LIKE ? OR nombres LIKE ? OR apellidos LIKE ? OR correo LIKE ?)
                ORDER BY id_cliente DESC
                LIMIT ?, ?";
        $st = $this->db->prepare($sql);
        $st->bindValue(1,$like);$st->bindValue(2,$like);$st->bindValue(3,$like);$st->bindValue(4,$like);
        $st->bindValue(5,$off,PDO::PARAM_INT);$st->bindValue(6,$per,PDO::PARAM_INT);
        $st->execute();
        $items = $st->fetchAll();

        $st2 = $this->db->prepare("SELECT COUNT(*) FROM clientes WHERE (cedula LIKE ? OR nombres LIKE ? OR apellidos LIKE ? OR correo LIKE ?)");
        $st2->execute([$like,$like,$like,$like]);
        $total = (int)$st2->fetchColumn();

        return ['items'=>$items,'page'=>$page,'per'=>$per,'total'=>$total];
    }

    // === OBTENER ===
    public function obtener(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM clientes WHERE id_cliente=:id");
        $st->execute([':id'=>$id]);
        return $st->fetch() ?: null;
    }

    // === CREAR ===
    public function crear(array $d): int
    {
        $hash = password_hash($d['contraseña'], PASSWORD_DEFAULT);
        $st = $this->db->prepare("INSERT INTO clientes
            (cedula,nombres,apellidos,telefono,correo,contraseña,estado,fecha_registro)
            VALUES(:cedula,:nombres,:apellidos,:telefono,:correo,:pass,'Activo',NOW())");
        $st->execute([
            ':cedula'=>$d['cedula'],
            ':nombres'=>$d['nombres'],
            ':apellidos'=>$d['apellidos'],
            ':telefono'=>$d['telefono'],
            ':correo'=>$d['correo'],
            ':pass'=>$hash
        ]);
        return (int)$this->db->lastInsertId();
    }

    // === ACTUALIZAR ===
    public function actualizar(int $id, array $d): void
    {
        $set = "cedula=:cedula,nombres=:nombres,apellidos=:apellidos,telefono=:telefono,correo=:correo";
        $params = [
            ':cedula'=>$d['cedula'],
            ':nombres'=>$d['nombres'],
            ':apellidos'=>$d['apellidos'],
            ':telefono'=>$d['telefono'],
            ':correo'=>$d['correo'],
            ':id'=>$id
        ];

        if (!empty($d['contraseña'])) {
            $set .= ",contraseña=:pass";
            $params[':pass'] = password_hash($d['contraseña'], PASSWORD_DEFAULT);
        }

        $sql = "UPDATE clientes SET {$set} WHERE id_cliente=:id";
        $st = $this->db->prepare($sql);
        $st->execute($params);
    }

    // === ELIMINAR ===
    public function eliminar(int $id): void
    {
        $st = $this->db->prepare("DELETE FROM clientes WHERE id_cliente=:id");
        $st->execute([':id'=>$id]);
    }

    // === ACTIVAR/INACTIVAR ===
    public function toggleEstado(int $id): array
    {
        $st = $this->db->prepare("SELECT estado FROM clientes WHERE id_cliente=?");
        $st->execute([$id]);
        $u = $st->fetch();
        if (!$u) throw new \Exception('Cliente no encontrado.');

        $nuevo = strcasecmp($u['estado'],'Activo')===0 ? 'Inactivo' : 'Activo';
        $st2 = $this->db->prepare("UPDATE clientes SET estado=? WHERE id_cliente=?");
        $st2->execute([$nuevo,$id]);
        return ['estado'=>$nuevo];
    }
}
