<?php
namespace Models;

use PDO;

class Admincliente
{
    private PDO $db;

    public function __construct(array $config)
    {
        $db      = $config['db'] ?? [];
        $host    = $db['host']    ?? '127.0.0.1';
        $name    = $db['name']    ?? 'hieribal';
        $user    = $db['user']    ?? 'root';
        $pass    = $db['pass']    ?? '';
        $charset = $db['charset'] ?? 'utf8mb4';

        $dsn  = "mysql:host={$host};dbname={$name};charset={$charset}";
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->db = new PDO($dsn, $user, $pass, $opts);
    }

    // ================== LISTAR ==================
    public function listar(string $q, int $page, int $per): array
    {
        $off  = ($page - 1) * $per;
        $like = "%{$q}%";

        // Agregamos estado derivado de verificado
        $sql = "SELECT 
                    id_cliente, cedula, nombres, apellidos, telefono, correo,
                    verificado,
                    CASE WHEN verificado = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado,
                    fecha_registro
                FROM clientes
                WHERE (IFNULL(cedula,'') LIKE ?
                   OR  nombres   LIKE ?
                   OR  apellidos LIKE ?
                   OR  correo    LIKE ?)
                ORDER BY id_cliente DESC
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

        $sql2 = "SELECT COUNT(*) FROM clientes
                 WHERE (IFNULL(cedula,'') LIKE ?
                    OR  nombres   LIKE ?
                    OR  apellidos LIKE ?
                    OR  correo    LIKE ?)";
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
        $st = $this->db->prepare(
            "SELECT 
                id_cliente, cedula, nombres, apellidos, telefono, correo,
                verificado,
                CASE WHEN verificado = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado,
                fecha_registro
             FROM clientes
             WHERE id_cliente = :id"
        );
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    // ================== CREAR ==================
    public function crear(array $d): int
    {
        $hash = password_hash($d['contrasena'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO clientes
                (cedula,nombres,apellidos,telefono,correo,contrasena,fecha_registro,verificado)
                VALUES
                (:cedula,:nombres,:apellidos,:telefono,:correo,:contrasena,NOW(),:verificado)";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':cedula'      => $d['cedula'] ?? null,
            ':nombres'     => $d['nombres'],
            ':apellidos'   => $d['apellidos'],
            ':telefono'    => $d['telefono'] ?? null,
            ':correo'      => $d['correo'],
            ':contrasena'  => $hash,
            ':verificado'  => isset($d['verificado']) ? (int)$d['verificado'] : 1, // por defecto activo
        ]);

        return (int)$this->db->lastInsertId();
    }

    // ================== ACTUALIZAR ==================
    public function actualizar(int $id, array $d): void
    {
        $sets = "cedula=:cedula, nombres=:nombres, apellidos=:apellidos, telefono=:telefono, correo=:correo";
        $params = [
            ':cedula'    => $d['cedula'] ?? null,
            ':nombres'   => $d['nombres'],
            ':apellidos' => $d['apellidos'],
            ':telefono'  => $d['telefono'] ?? null,
            ':correo'    => $d['correo'],
            ':id'        => $id,
        ];

        if (!empty($d['contrasena'])) {
            $sets .= ", contrasena=:contrasena";
            $params[':contrasena'] = password_hash($d['contrasena'], PASSWORD_DEFAULT);
        }

        // Si te llega "estado" desde el form, lo convertimos a verificado
        if (isset($d['estado'])) {
            $sets .= ", verificado=:verificado";
            $params[':verificado'] = (stripos((string)$d['estado'], 'act') === 0) ? 1 : 0;
        }

        $sql = "UPDATE clientes SET {$sets} WHERE id_cliente=:id";
        $st = $this->db->prepare($sql);
        $st->execute($params);
    }

    // ================== ELIMINAR ==================
    public function eliminar(int $id): void
    {
        $st = $this->db->prepare("DELETE FROM clientes WHERE id_cliente=:id");
        $st->execute([':id' => $id]);
    }

    // ================== TOGGLE ESTADO ==================
    // Alterna verificado 0/1 y retorna el estado nuevo
    public function toggleEstado(int $id): array
    {
        // Leer estado actual
        $st = $this->db->prepare("SELECT verificado FROM clientes WHERE id_cliente = ? LIMIT 1");
        $st->execute([$id]);
        $cur = $st->fetchColumn();
        if ($cur === false) {
            throw new \Exception('Cliente no encontrado');
        }

        $new = ((int)$cur === 1) ? 0 : 1;

        // Actualizar
        $up = $this->db->prepare("UPDATE clientes SET verificado = ? WHERE id_cliente = ?");
        $up->execute([$new, $id]);

        return [
            'verificado' => $new,
            'estado'     => $new === 1 ? 'Activo' : 'Inactivo',
        ];
    }
}
