<?php
declare(strict_types=1);

namespace Models;

use PDO;

final class UsuarioInventario
{
    /** models/UsuarioInventario.php
     *  MODULO INVENTARIOS / Juliana Lugo / Clase modelo para gestión de inventario.
     *
     * @var PDO
     */
    private PDO $db;

    /**
     * Constructor.
     *
     * Recibe una instancia de PDO (se recomienda obtenerla desde Database::get($config['db'])).
     * Aquí también se configuran algunos atributos básicos de PDO.
     *
     * @param PDO $db Conexión PDO ya inicializada.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;

        // Asegurar modo de errores por excepción.
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Devolver arrays asociativos por defecto.
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Desactivar emulación de prepares para usar prepares nativos.
        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * Listar registros de inventario con búsqueda y paginación.
     * 
     * Busca por:
     *  - codigo_interno
     *  - ubicacion
     *
     * Devuelve un arreglo con la forma:
     *  [
     *      'items' => [...],
     *      'page'  => (int),
     *      'per'   => (int),
     *      'total' => (int)
     *  ]
     *
     * @param string $q    Texto de búsqueda (opcional).
     * @param int    $page Número de página (>=1).
     * @param int    $per  Registros por página (máx. 50).
     *
     * @return array
     */
    public function listar(string $q = '', int $page = 1, int $per = 10): array
    {
        // Normalizar página y tamaño.
        $page = max(1, $page);
        $per  = max(1, min(50, $per));
        $off  = ($page - 1) * $per;

        $where  = '';
        $params = [];

        // Si hay texto de búsqueda, se arma el WHERE y parámetros.
        if ($q !== '') {
            $where    = "WHERE codigo_interno LIKE ? OR ubicacion LIKE ?";
            $like     = "%{$q}%";
            $params[] = $like;
            $params[] = $like;
        }

        // Alias id_producto -> producto_id para el frontend.
        $sql = "SELECT
                    i.id,
                    i.id_producto AS producto_id,
                    p.nombre AS producto_nombre,
                    i.codigo_interno,
                    i.stock,
                    i.stock_minimo,
                    i.stock_maximo,
                    i.punto_reorden,
                    i.ubicacion,
                    i.estado
                FROM inventario i
                INNER JOIN productos p ON p.id = i.id_producto
                {$where}
                ORDER BY i.id DESC
                LIMIT ?, ?";

        $st = $this->db->prepare($sql);

        // Enlazar parámetros de búsqueda (si los hay).
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p, PDO::PARAM_STR);
        }

        // Enlazar offset y límite para la paginación.
        $st->bindValue($i++, (int) $off, PDO::PARAM_INT);
        $st->bindValue($i++, (int) $per, PDO::PARAM_INT);

        $st->execute();
        $items = $st->fetchAll();

        // Consulta para obtener el total de registros (para la paginación).
        $sql2 = "SELECT COUNT(*) FROM inventario {$where}";
        $st2  = $this->db->prepare($sql2);
        $i    = 1;

        foreach ($params as $p) {
            $st2->bindValue($i++, $p, PDO::PARAM_STR);
        }

        $st2->execute();
        $total = (int) $st2->fetchColumn();

        return [
            'items' => $items,
            'page'  => $page,
            'per'   => $per,
            'total' => $total,
        ];
    }

    /**
     * Obtener un registro de inventario por ID.
     *
     * @param int $id ID del registro de inventario.
     *
     * @return array|null Arreglo asociativo con los datos o null si no existe.
     */
    public function obtener(int $id): ?array
    {
        $st = $this->db->prepare(
            "SELECT
                i.id,
                i.id_producto AS producto_id,
                p.nombre AS producto_nombre,
                i.codigo_interno,
                i.stock,
                i.stock_minimo,
                i.stock_maximo,
                i.punto_reorden,
                i.ubicacion,
                i.estado
            FROM inventario i
            INNER JOIN productos p ON p.id = i.id_producto
            WHERE i.id = :id"
        );

        $st->execute([':id' => $id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    //**Obtener Producto en inventario */
    /**
     * Obtener catálogo de productos (id y nombre) para usar en el inventario.
     *
     * NOTA: este método utiliza $this->pdo según tu implementación original.
     *       No se modifican nombres de propiedades ni estructura, tal como solicitaste.
     *
     * @return array Lista de productos [ ['id' => ..., 'nombre' => ...], ... ].
     */
    public function obtenerProductos()
    {
        $query = $this->pdo->query("SELECT id, nombre FROM productos ORDER BY nombre ASC");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo registro de inventario.
     *
     * Mapea:
     *  - producto_id (dato de entrada) -> id_producto (campo en BD).
     *
     * @param array $d Datos validados del inventario.
     *
     * @return int ID generado del nuevo registro.
     */
    public function crear(array $d): int
    {
        $sql = "INSERT INTO inventario
                (id_producto, codigo_interno, stock, stock_minimo, stock_maximo, punto_reorden, ubicacion, estado)
                VALUES
                (:id_producto, :codigo_interno, :stock, :stock_minimo, :stock_maximo, :punto_reorden, :ubicacion, :estado)";

        $st = $this->db->prepare($sql);

        $st->execute([
            ':id_producto'    => (int) $d['producto_id'],
            ':codigo_interno' => (string) $d['codigo_interno'],
            ':stock'          => (int) $d['stock'],
            ':stock_minimo'   => (int) $d['stock_minimo'],
            ':stock_maximo'   => (int) $d['stock_maximo'],
            ':punto_reorden'  => (int) $d['punto_reorden'],
            ':ubicacion'      => (string) $d['ubicacion'],
            ':estado'         => (string) $d['estado'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar un registro de inventario existente.
     *
     * Mapea:
     *  - producto_id (dato de entrada) -> id_producto (campo en BD).
     *
     * @param int   $id ID del registro de inventario a actualizar.
     * @param array $d  Datos validados del inventario.
     *
     * @return void
     */
    public function actualizar(int $id, array $d): void
    {
        $sql = "UPDATE inventario SET
                    id_producto    = :id_producto,
                    codigo_interno = :codigo_interno,
                    stock          = :stock,
                    stock_minimo   = :stock_minimo,
                    stock_maximo   = :stock_maximo,
                    punto_reorden  = :punto_reorden,
                    ubicacion      = :ubicacion,
                    estado         = :estado
                WHERE id = :id";

        $st = $this->db->prepare($sql);

        $st->execute([
            ':id_producto'    => (int) $d['producto_id'],
            ':codigo_interno' => (string) $d['codigo_interno'],
            ':stock'          => (int) $d['stock'],
            ':stock_minimo'   => (int) $d['stock_minimo'],
            ':stock_maximo'   => (int) $d['stock_maximo'],
            ':punto_reorden'  => (int) $d['punto_reorden'],
            ':ubicacion'      => (string) $d['ubicacion'],
            ':estado'         => (string) $d['estado'],
            ':id'             => $id,
        ]);
    }

    /**
     * Eliminar un registro de inventario por ID.
     *
     * @param int $id ID del registro a eliminar.
     *
     * @return void
     */
    public function eliminar(int $id): void
    {
        $st = $this->db->prepare("DELETE FROM inventario WHERE id = :id");
        $st->execute([':id' => $id]);
    }

    /**
     * Alternar el estado disponible/agotado de un registro de inventario.
     *
     * - Si el estado actual es 'disponible' → pasa a 'agotado'.
     * - En cualquier otro caso → pasa a 'disponible'.
     *
     * @param int $id ID del registro de inventario.
     *
     * @return array Arreglo con la nueva clave 'estado' o [] si no existe.
     */
    public function toggleEstado(int $id): array
    {
        $row = $this->obtener($id);

        // Si no existe, devolvemos arreglo vacío.
        if (!$row) {
            return [];
        }

        // Cambiar entre 'disponible' y 'agotado'.
        $nuevo = (strcasecmp((string) $row['estado'], 'disponible') === 0)
            ? 'agotado'
            : 'disponible';

        $st = $this->db->prepare("UPDATE inventario SET estado = :estado WHERE id = :id");
        $st->execute([':estado' => $nuevo, ':id' => $id]);

        return ['estado' => $nuevo];
    }
}
