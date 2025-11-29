<?php
namespace Models;

use PDO;

final class AdminDevoluciones
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ============================================================
       Combos: Productos / Clientes
       ============================================================ */

    /**
     * Devuelve lista de productos para el combo
     * productos (id, nombre, img, ...)
     */
    public function getProductos(): array
    {
        $sql = "SELECT id, nombre 
                FROM productos
                ORDER BY nombre ASC";

        $st = $this->pdo->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Devuelve lista de clientes para el combo
     * clientes (id, nombre, correo, telefono, ...)
     */
    public function getClientes(): array
    {
        $sql = "SELECT id, nombre, correo, telefono
                FROM clientes
                ORDER BY nombre ASC";

        $st = $this->pdo->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /* ============================================================
       LISTAR (con búsqueda + paginación)
       ============================================================ */

    /**
     * Lista devoluciones con filtro opcional y paginación.
     *
     * Estructura esperada por el controlador:
     * [
     *   'data'  => [...registros...],
     *   'total' => 123,
     *   'page'  => 1,
     *   'per'   => 10
     * ]
     */
    public function listar(string $q, int $page, int $per): array
    {
        $where  = '';
        $params = [];

        if ($q !== '') {
            $where = "WHERE 
                nombre_cliente    LIKE :q OR
                correo            LIKE :q OR
                numero_orden      LIKE :q OR
                telefono          LIKE :q OR
                producto          LIKE :q OR
                motivo_devolucion LIKE :q";
            $params[':q'] = '%' . $q . '%';
        }

        $offset = ($page - 1) * $per;

        // 1) Total de registros
        $sqlCount = "SELECT COUNT(*) FROM devoluciones {$where}";
        $stCount  = $this->pdo->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $stCount->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stCount->execute();
        $total = (int) $stCount->fetchColumn();

        // 2) Datos paginados
        $sqlData = "SELECT 
                        id,
                        nombre_cliente,
                        correo,
                        numero_orden,
                        telefono,
                        producto,
                        motivo_devolucion,
                        fecha_compra,
                        fecha_devolucion,
                        observaciones,
                        estado
                    FROM devoluciones
                    {$where}
                    ORDER BY fecha_devolucion DESC, id DESC
                    LIMIT :per OFFSET :off";

        $stData = $this->pdo->prepare($sqlData);

        foreach ($params as $k => $v) {
            $stData->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stData->bindValue(':per',  $per,    PDO::PARAM_INT);
        $stData->bindValue(':off',  $offset, PDO::PARAM_INT);

        $stData->execute();
        $rows = $stData->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'data'  => $rows,
            'total' => $total,
            'page'  => $page,
            'per'   => $per,
        ];
    }

    /* ============================================================
       OBTENER UNO
       ============================================================ */
    public function obtener(int $id): ?array
    {
        $sql = "SELECT 
                    id,
                    nombre_cliente,
                    correo,
                    numero_orden,
                    telefono,
                    producto,
                    motivo_devolucion,
                    fecha_compra,
                    fecha_devolucion,
                    observaciones,
                    estado
                FROM devoluciones
                WHERE id = :id
                LIMIT 1";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(':id', $id, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /* ============================================================
       CREAR
       ============================================================ */
    public function crear(array $d): int
    {
        $sql = "INSERT INTO devoluciones (
                    nombre_cliente,
                    correo,
                    numero_orden,
                    telefono,
                    producto,
                    motivo_devolucion,
                    fecha_compra,
                    fecha_devolucion,
                    observaciones,
                    estado
                ) VALUES (
                    :nombre_cliente,
                    :correo,
                    :numero_orden,
                    :telefono,
                    :producto,
                    :motivo_devolucion,
                    :fecha_compra,
                    :fecha_devolucion,
                    :observaciones,
                    :estado
                )";

        $st = $this->pdo->prepare($sql);

        $st->bindValue(':nombre_cliente',    $d['nombre_cliente']);
        $st->bindValue(':correo',            $d['correo']);
        $st->bindValue(':numero_orden',      $d['numero_orden']);
        $st->bindValue(':telefono',          $d['telefono']);
        $st->bindValue(':producto',          $d['producto']);
        $st->bindValue(':motivo_devolucion', $d['motivo_devolucion']);
        $st->bindValue(':fecha_compra',      $d['fecha_compra']);
        $st->bindValue(':fecha_devolucion',  $d['fecha_devolucion']);
        $st->bindValue(':observaciones',     $d['observaciones']);
        $st->bindValue(':estado',            $d['estado']);

        $st->execute();

        return (int) $this->pdo->lastInsertId();
    }

    /* ============================================================
       ACTUALIZAR
       ============================================================ */
    public function actualizar(int $id, array $d): void
    {
        $sql = "UPDATE devoluciones
                SET 
                    nombre_cliente    = :nombre_cliente,
                    correo            = :correo,
                    numero_orden      = :numero_orden,
                    telefono          = :telefono,
                    producto          = :producto,
                    motivo_devolucion = :motivo_devolucion,
                    fecha_compra      = :fecha_compra,
                    fecha_devolucion  = :fecha_devolucion,
                    observaciones     = :observaciones,
                    estado            = :estado
                WHERE id = :id";

        $st = $this->pdo->prepare($sql);

        $st->bindValue(':id',               $id, PDO::PARAM_INT);
        $st->bindValue(':nombre_cliente',   $d['nombre_cliente']);
        $st->bindValue(':correo',           $d['correo']);
        $st->bindValue(':numero_orden',     $d['numero_orden']);
        $st->bindValue(':telefono',         $d['telefono']);
        $st->bindValue(':producto',         $d['producto']);
        $st->bindValue(':motivo_devolucion',$d['motivo_devolucion']);
        $st->bindValue(':fecha_compra',     $d['fecha_compra']);
        $st->bindValue(':fecha_devolucion', $d['fecha_devolucion']);
        $st->bindValue(':observaciones',    $d['observaciones']);
        $st->bindValue(':estado',           $d['estado']);

        $st->execute();
    }

    /* ============================================================
       ELIMINAR
       ============================================================ */
    public function eliminar(int $id): void
    {
        $sql = "DELETE FROM devoluciones WHERE id = :id";
        $st  = $this->pdo->prepare($sql);
        $st->bindValue(':id', $id, PDO::PARAM_INT);
        $st->execute();
    }

    /* ============================================================
       KPIs / Reportes (los que ya tenías)
       ============================================================ */

    // TOP: Productos con más devoluciones (unidades)
    public function topProductosDevueltos(int $limit = 10): array
    {
        // Asumiendo:
        // devoluciones (id, producto, motivo, fecha_devolucion, ...)
        // productos (id, nombre, img)

        $sql = "SELECT 
                    p.id,
                    p.nombre,
                    p.img,
                    COUNT(d.id) AS devoluciones
                FROM devoluciones d
                JOIN productos p ON p.nombre = d.producto
                GROUP BY p.id, p.nombre, p.img
                ORDER BY devoluciones DESC
                LIMIT :lim";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();

        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // TOP: Clientes que más devuelven (mes actual)
    public function topClientesMesActual(int $limit = 10): array
    {
        // devoluciones (id, nombre_cliente, fecha_devolucion)

        $sql = "SELECT 
                    d.nombre_cliente AS cliente,
                    COUNT(*) AS total
                FROM devoluciones d
                WHERE YEAR(d.fecha_devolucion) = YEAR(CURDATE())
                  AND MONTH(d.fecha_devolucion) = MONTH(CURDATE())
                GROUP BY d.nombre_cliente
                ORDER BY total DESC
                LIMIT :lim";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();

        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // KPI: total de devoluciones del mes
    public function totalDevolucionesMes(): int
    {
        $sql = "SELECT COUNT(*)
                FROM devoluciones
                WHERE YEAR(fecha_devolucion) = YEAR(CURDATE())
                  AND MONTH(fecha_devolucion) = MONTH(CURDATE())";

        return (int) $this->pdo->query($sql)->fetchColumn();
    }
}
