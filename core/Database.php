<?php
namespace Core;

use PDO;
use PDOException;

/**
 * Clase Database
 * - Se encarga de abrir la conexión a la base de datos usando PDO.
 * - Utiliza el patrón Singleton para no abrir múltiples conexiones.
 */
final class Database {
    private static ?PDO $pdo = null;

    public static function get(array $cfg): PDO {
        if (!self::$pdo) {
            try {
                self::$pdo = new PDO(
                    $cfg['dsn'],
                    $cfg['user'],
                    $cfg['pass'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Excepciones en errores
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Arrays asociativos
                    ]
                );
            } catch (PDOException $e) {
                // ⚠️ Aquí manejas el error como quieras:
                // - Mostrar un mensaje al usuario
                // - Registrar el error en logs
                // - Re-lanzar la excepción si quieres que suba
                die("❌ Error al conectar a la base de datos: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
