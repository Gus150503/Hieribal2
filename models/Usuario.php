<?php
declare(strict_types=1);

namespace Models;

use Core\Database;
use PDO;
use PDOException;
use Exception;

final class Usuario {
    private PDO $pdo;

    public function __construct(array $config) {
        // Asegúrate de pasar $config que tenga ['db'] con dsn, user, pass
        $this->pdo = Database::get($config['db']);
    }

    /**
     * Verifica credenciales.
     * - Retorna array (row sin 'password') si válidas y estado 'Activo'
     * - Retorna false si no existe, inactivo o password incorrecta
     * - Lanza Exception si ocurre error de BD
     */
    public function verificarPassword(string $usuarioOCorreo, string $password): array|false {
        try {
            $sql = "SELECT 
                        id_usuario,
                        usuario,
                        correo,
                        nombres,
                        apellidos,
                        rol,
                        estado,
                        password
                    FROM usuarios
                    WHERE (usuario = :u OR correo = :u)
                    LIMIT 1";

            $st = $this->pdo->prepare($sql);
            $st->execute([':u' => $usuarioOCorreo]);
            $row = $st->fetch(PDO::FETCH_ASSOC);

            if (!$row) return false; // no existe

            // Debe estar Activo
            if (strcasecmp((string)$row['estado'], 'Activo') !== 0) {
                return false;
            }

            $stored = (string)$row['password'];
            if (!password_verify($password, $stored)) {
                return false;
            }

            // Rehash automático si el algoritmo/params cambiaron
            if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                try {
                    $this->actualizarPasswordHash((int)$row['id_usuario'], password_hash($password, PASSWORD_DEFAULT));
                } catch (PDOException $rehashEx) {
                    // No bloquees el login por error de rehash: solo loguéalo
                    $this->log('rehash', $rehashEx);
                }
            }

            unset($row['password']); // nunca retornar el hash
            return $row;

        } catch (PDOException $e) {
            $this->log('verificarPassword', $e);
            throw new Exception('No se pudo verificar las credenciales.'); // mensaje de negocio
        }
    }

    private function actualizarPasswordHash(int $idUsuario, string $nuevoHash): void {
        $up = $this->pdo->prepare("UPDATE usuarios SET password = :h WHERE id_usuario = :id");
        $up->execute([':h' => $nuevoHash, ':id' => $idUsuario]);
    }

    /** Total de usuarios */
    public function totalUsuarios(): int {
        try {
            return (int)$this->pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        } catch (PDOException $e) {
            $this->log('totalUsuarios', $e);
            throw new Exception('No se pudo obtener el total de usuarios.');
        }
    }

    /** Total por estado ('Activo' / 'Inactivo') */
    public function totalPorEstado(string $estado): int {
        try {
            $st = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE estado = :e");
            $st->execute([':e' => $estado]);
            return (int)$st->fetchColumn();
        } catch (PDOException $e) {
            $this->log('totalPorEstado', $e);
            throw new Exception('No se pudo obtener el total por estado.');
        }
    }

    /** Total por rol */
    public function totalPorRol(string $rol): int {
        try {
            $st = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE rol = :r");
            $st->execute([':r' => $rol]);
            return (int)$st->fetchColumn();
        } catch (PDOException $e) {
            $this->log('totalPorRol', $e);
            throw new Exception('No se pudo obtener el total por rol.');
        }
    }

    /** Empleados con ≥ 1 año (usa usuarios.fecha_creacion como “ingreso”) */
    public function conAnioAntiguedad(int $limit = 10): array {
        try {
            $sql = "SELECT CONCAT(nombres,' ',apellidos) AS nombre,
                           NULL AS img, /* ajusta si tienes columna foto */
                           DATE_FORMAT(fecha_creacion, '%Y-%m-%d') AS desde
                      FROM usuarios
                     WHERE DATEDIFF(CURDATE(), fecha_creacion) >= 365
                  ORDER BY fecha_creacion DESC
                     LIMIT :lim";
            $st = $this->pdo->prepare($sql);
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->log('conAnioAntiguedad', $e);
            throw new Exception('No se pudo obtener la lista de antigüedad.');
        }
    }

    /** Últimos usuarios creados */
    public function ultimos(int $limit = 5): array {
        try {
            $st = $this->pdo->prepare(
                "SELECT id_usuario, usuario, rol, estado, fecha_creacion
                   FROM usuarios
               ORDER BY fecha_creacion DESC
                  LIMIT :lim"
            );
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->log('ultimos', $e);
            throw new Exception('No se pudo obtener la lista de usuarios recientes.');
        }
    }

    /* -------------------- utilidades -------------------- */

    private function log(string $accion, \Throwable $e): void {
        $dir = __DIR__ . '/../storage/logs';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $line = sprintf("[%s] Usuario::%s | %s\n", date('Y-m-d H:i:s'), $accion, $e->getMessage());
        @file_put_contents("$dir/app.log", $line, FILE_APPEND);
    }
}
