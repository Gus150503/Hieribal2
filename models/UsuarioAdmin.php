<?php
namespace Models;

use PDO;
use PDOException;

class UsuarioAdmin
{
    /** @var PDO */
    private $db;

    public function __construct(array $config)
    {
        // Ajusta a tu config real
        $db = $config['db'] ?? [];
        $host    = $db['host']    ?? '127.0.0.1';
        $name    = $db['name']    ?? 'hieribal';
        $user    = $db['user']    ?? 'root';
        $pass    = $db['pass']    ?? '';
        $charset = $db['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->db = new PDO($dsn, $user, $pass, $opts);
    }

    // ---------- LISTAR ----------
    public function listar(string $q, int $page, int $per): array
{
    $off  = ($page - 1) * $per;
    $like = "%{$q}%";

    // 1) Consulta de filas (placeholders posicionales ?)
    $sql = "SELECT id_usuario, usuario, rol, nombres, apellidos, correo,
                   estado, correo_verificado, fecha_creacion
            FROM usuarios
            WHERE (usuario   LIKE ?
               OR  nombres   LIKE ?
               OR  apellidos LIKE ?
               OR  correo    LIKE ?)
            ORDER BY id_usuario DESC
            LIMIT ?, ?";
    $st = $this->db->prepare($sql);
    // OJO: LIMIT/OFFSET deben ir como enteros
    $st->bindValue(1, $like, \PDO::PARAM_STR);
    $st->bindValue(2, $like, \PDO::PARAM_STR);
    $st->bindValue(3, $like, \PDO::PARAM_STR);
    $st->bindValue(4, $like, \PDO::PARAM_STR);
    $st->bindValue(5, (int)$off, \PDO::PARAM_INT);
    $st->bindValue(6, (int)$per, \PDO::PARAM_INT);
    $st->execute();
    $items = $st->fetchAll();

    // 2) Conteo total (también posicionales)
    $sql2 = "SELECT COUNT(*) FROM usuarios
             WHERE (usuario   LIKE ?
                OR  nombres   LIKE ?
                OR  apellidos LIKE ?
                OR  correo    LIKE ?)";
    $st2 = $this->db->prepare($sql2);
    $st2->bindValue(1, $like, \PDO::PARAM_STR);
    $st2->bindValue(2, $like, \PDO::PARAM_STR);
    $st2->bindValue(3, $like, \PDO::PARAM_STR);
    $st2->bindValue(4, $like, \PDO::PARAM_STR);
    $st2->execute();
    $total = (int)$st2->fetchColumn();

    return ['items' => $items, 'page' => $page, 'per' => $per, 'total' => $total];
}


    // ---------- OBTENER ----------
    public function obtener(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM usuarios WHERE id_usuario=:id");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    // ---------- CREAR ----------
    public function crear(array $d): int
    {
        $hash = password_hash($d['password'], PASSWORD_DEFAULT);
        list($tok, $exp) = $this->genToken();

        $sql = "INSERT INTO usuarios
                (usuario,password,rol,nombres,apellidos,correo,estado,
                 correo_verificado,correo_verificacion_token,correo_verificacion_expira,fecha_creacion)
                VALUES
                (:usuario,:password,:rol,:nombres,:apellidos,:correo,:estado,
                 0,:tok,:exp,NOW())";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':usuario'  => $d['usuario'],
            ':password' => $hash,
            ':rol'      => $d['rol'],
            ':nombres'  => $d['nombres'],
            ':apellidos'=> $d['apellidos'],
            ':correo'   => $d['correo'],
            ':estado'   => $d['estado'],
            ':tok'      => $tok,
            ':exp'      => $exp,
        ]);
        return (int)$this->db->lastInsertId();
    }

    // ---------- ACTUALIZAR ----------
    public function actualizar(int $id, array $d): void
    {
        $sets = "usuario=:usuario, rol=:rol, nombres=:nombres, apellidos=:apellidos, estado=:estado";
        $params = [
            ':usuario'   => $d['usuario'],
            ':rol'       => $d['rol'],
            ':nombres'   => $d['nombres'],
            ':apellidos' => $d['apellidos'],
            ':estado'    => $d['estado'],
            ':id'        => $id,
        ];

        if (!empty($d['correo'])) {
            $sets .= ", correo=:correo";
            $params[':correo'] = $d['correo'];
        }
        if (!empty($d['password'])) {
            $sets .= ", password=:password";
            $params[':password'] = password_hash($d['password'], PASSWORD_DEFAULT);
        }
        if (!empty($d['reset_verif'])) {
            list($tok, $exp) = $this->genToken();
            $sets .= ", correo_verificado=0, correo_verificacion_token=:tok, correo_verificacion_expira=:exp";
            $params[':tok'] = $tok;
            $params[':exp'] = $exp;
        }

        $sql = "UPDATE usuarios SET {$sets} WHERE id_usuario=:id";
        $st = $this->db->prepare($sql);
        $st->execute($params);
    }

    // ---------- ELIMINAR ----------
    public function eliminar(int $id): void
    {
        $st = $this->db->prepare("DELETE FROM usuarios WHERE id_usuario=:id");
        $st->execute([':id' => $id]);
    }

    // ---------- TOGGLE ESTADO ----------
    public function toggleEstado(int $id): string
    {
        $u = $this->obtener($id);
        $esActivo = $u && strcasecmp((string)$u['estado'], 'activo') === 0;
        $nuevo = $esActivo ? 'Inactivo' : 'Activo';
        $st = $this->db->prepare("UPDATE usuarios SET estado=:e WHERE id_usuario=:id");
        $st->execute([':e' => $nuevo, ':id' => $id]);
        return $nuevo;
    }


    // ---------- VERIFICAR EMAIL POR TOKEN ----------
    public function setEmailVerifiedByToken(string $token): bool
    {
        $st = $this->db->prepare(
            "UPDATE usuarios
             SET correo_verificado=1,
                 correo_verificacion_token=NULL,
                 correo_verificacion_expira=NULL
             WHERE correo_verificacion_token=:t
               AND (correo_verificacion_expira IS NULL OR correo_verificacion_expira > NOW())"
        );
        $st->execute([':t' => $token]);
        return $st->rowCount() > 0;
    }

    public function getById(int $id): ?array
    {
        return $this->obtener($id);
    }

    // ---------- UTIL ----------
    private function genToken(): array
    {
        $tok = bin2hex(random_bytes(16));
        $exp = date('Y-m-d H:i:s', time() + 86400); // 24h
        return [$tok, $exp];
    }
}
