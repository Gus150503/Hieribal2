<?php
namespace Models;

use PDO;

final class UsuarioAdmin {
    private PDO $pdo;

    public function __construct(array $config) {
        $this->pdo = \Core\Database::get($config['db']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /** Lista con búsqueda y paginación */
    public function listar(string $q = '', int $page = 1, int $perPage = 10): array {
        $where = '';
        $params = [];
        if ($q !== '') {
            $where = "WHERE (usuario LIKE :q OR correo LIKE :q OR nombres LIKE :q OR apellidos LIKE :q)";
            $params[':q'] = "%$q%";
        }

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT id_usuario, usuario, rol, nombres, apellidos, correo, fecha_creacion, estado
                FROM usuarios $where
                ORDER BY id_usuario DESC
                LIMIT :lim OFFSET :off";
        $st = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $st->bindValue($k, $v, PDO::PARAM_STR);
        $st->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $st2 = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios " . ($where ?: ''));
        foreach ($params as $k => $v) $st2->bindValue($k, $v, PDO::PARAM_STR);
        $st2->execute();
        $total = (int)$st2->fetchColumn();

        return ['data' => $rows, 'total' => $total, 'page' => $page, 'perPage' => $perPage];
    }

    public function obtener(int $id): ?array {
        $st = $this->pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function existeUsuarioOCorreo(string $usuario, string $correo, ?int $ignorandoId = null): bool {
        $sql = "SELECT 1 FROM usuarios WHERE (usuario = :u OR correo = :c)";
        $params = [':u' => $usuario, ':c' => $correo];
        if ($ignorandoId) { $sql .= " AND id_usuario <> :id"; $params[':id'] = $ignorandoId; }
        $sql .= " LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchColumn() !== false;
    }

    public function crear(array $d): int {
        if ($this->existeUsuarioOCorreo($d['usuario'], $d['correo'])) {
            throw new \RuntimeException("Usuario o correo ya existen");
        }
        $hash = password_hash($d['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (usuario, password, rol, nombres, apellidos, correo, fecha_creacion, estado)
                VALUES (:usuario, :password, :rol, :nombres, :apellidos, :correo, NOW(), :estado)";
        $st = $this->pdo->prepare($sql);
        $st->execute([
            ':usuario'   => $d['usuario'],
            ':password'  => $hash,
            ':rol'       => $d['rol'],
            ':nombres'   => $d['nombres'],
            ':apellidos' => $d['apellidos'],
            ':correo'    => $d['correo'],
            ':estado'    => $d['estado'] ?? 'activo',
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar(int $id, array $d): void {
        if ($this->existeUsuarioOCorreo($d['usuario'], $d['correo'], $id)) {
            throw new \RuntimeException("Usuario o correo ya existen");
        }
        $setPwd = '';
        $params = [
            ':usuario' => $d['usuario'],
            ':rol' => $d['rol'],
            ':nombres' => $d['nombres'],
            ':apellidos' => $d['apellidos'],
            ':correo' => $d['correo'],
            ':estado' => $d['estado'],
            ':id' => $id
        ];
        if (!empty($d['password'])) {
            $setPwd = ", password = :password";
            $params[':password'] = password_hash($d['password'], PASSWORD_DEFAULT);
        }
        $sql = "UPDATE usuarios SET 
                    usuario = :usuario,
                    rol = :rol,
                    nombres = :nombres,
                    apellidos = :apellidos,
                    correo = :correo,
                    estado = :estado
                    $setPwd
                WHERE id_usuario = :id";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
    }

    public function eliminar(int $id): void {
        $st = $this->pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
        $st->execute([':id' => $id]);
    }

    public function toggleEstado(int $id): string {
        $u = $this->obtener($id);
        if (!$u) throw new \RuntimeException("No existe");
        $nuevo = ($u['estado'] === 'activo') ? 'inactivo' : 'activo';
        $st = $this->pdo->prepare("UPDATE usuarios SET estado = :e WHERE id_usuario = :id");
        $st->execute([':e' => $nuevo, ':id' => $id]);
        return $nuevo;
    }
}
