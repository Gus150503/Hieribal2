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
       LISTAS PARA COMBOS (PRODUCTOS / CLIENTES / PROVEEDORES)
    ============================================================ */
    public function getProductos(): array
    {
        $sql = "SELECT id, nombre 
                FROM productos
                ORDER BY nombre ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getClientes(): array
    {
        $sql = "SELECT
                    id_cliente AS id,
                    nombres,
                    apellidos,
                    correo,
                    telefono
                FROM clientes
                ORDER BY nombres ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getProveedores(): array
    {
        $sql = "SELECT id, empresa
                FROM proveedores
                WHERE estado = 'activo'
                ORDER BY empresa ASC";
        $st = $this->pdo->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /* ============================================================
       LISTAR CON BÚSQUEDA Y PAGINACIÓN
    ============================================================ */
    public function listar(string $q, int $page, int $per): array
    {
        $where  = "";
        $params = [];

        if ($q !== "") {
            $where = "WHERE 
                COALESCE(CONCAT(c.nombres,' ',c.apellidos), pr.empresa, '') LIKE :q
                OR COALESCE(c.correo,'') LIKE :q
                OR d.numero_orden LIKE :q
                OR p.nombre LIKE :q
                OR d.motivo_devolucion LIKE :q";

            $params[':q'] = "%{$q}%";
        }

        $offset = ($page - 1) * $per;

        // -------- TOTAL ----------
        $sqlCount = "
            SELECT COUNT(*)
            FROM devoluciones d
            LEFT JOIN clientes     c  ON c.id_cliente = d.cliente_id
            LEFT JOIN proveedores  pr ON pr.id        = d.proveedor_id
            JOIN productos         p  ON p.id         = d.producto_id
            {$where}
        ";
        $st = $this->pdo->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }
        $st->execute();
        $total = (int) $st->fetchColumn();

        // -------- DATA ----------
 $sqlData = "
    SELECT
        d.id,
        d.numero_orden,
        d.cantidad,
        d.motivo_devolucion,
        d.fecha_compra,
        d.fecha_devolucion,
        d.estado,
        d.observaciones,
        d.origen,

        d.cliente_id,
        c.nombres  AS cliente_nombres,
        c.apellidos AS cliente_apellidos,
        c.correo   AS cliente_correo,
        c.telefono AS cliente_telefono,

        d.proveedor_id,
        pr.empresa AS proveedor_empresa,
        pr.email   AS proveedor_correo,
        pr.telefono AS proveedor_telefono,

        p.id       AS producto_id,
        p.nombre   AS producto_nombre
    FROM devoluciones d
    LEFT JOIN clientes     c  ON c.id_cliente = d.cliente_id   -- 👈 OJO acá
    LEFT JOIN proveedores  pr ON pr.id = d.proveedor_id
    JOIN productos         p  ON p.id  = d.producto_id
    {$where}
    ORDER BY d.fecha_devolucion DESC, d.id DESC
    LIMIT :per OFFSET :off
";


        $st = $this->pdo->prepare($sqlData);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }
        $st->bindValue(':per', $per, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();

        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'data'  => $rows,
            'total' => $total,
            'page'  => $page,
            'per'   => $per,
        ];
    }

    /* ============================================================
       OBTENER UNA DEVOLUCIÓN
    ============================================================ */
    public function obtener(int $id): ?array
{
    $sql = "
        SELECT
            d.*,
            c.nombres   AS cliente_nombres,
            c.apellidos AS cliente_apellidos,
            c.correo    AS cliente_correo,
            c.telefono  AS cliente_telefono,
            pr.empresa  AS proveedor_empresa,
            p.nombre    AS producto_nombre
        FROM devoluciones d
        LEFT JOIN clientes    c  ON c.id_cliente = d.cliente_id
        LEFT JOIN proveedores pr ON pr.id        = d.proveedor_id
        JOIN productos        p  ON p.id         = d.producto_id
        WHERE d.id = :id
        LIMIT 1
    ";
    $st = $this->pdo->prepare($sql);
    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->execute();
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

    /* ============================================================
       CREAR
    ============================================================ */
    public function crear(array $d): int
{
    $sql = "
        INSERT INTO devoluciones (
            cliente_id,
            proveedor_id,
            producto_id,
            cantidad,
            numero_orden,
            motivo_devolucion,
            fecha_compra,
            fecha_devolucion,
            estado,
            observaciones,
            origen
        ) VALUES (
            :cliente_id,
            :proveedor_id,
            :producto_id,
            :cantidad,
            :numero_orden,
            :motivo_devolucion,
            :fecha_compra,
            :fecha_devolucion,
            :estado,
            :observaciones,
            :origen
        )
    ";

    $st = $this->pdo->prepare($sql);

    $clienteId   = $d['cliente_id'];
    $proveedorId = $d['proveedor_id'];

    $st->bindValue(':cliente_id',   $clienteId,
        $clienteId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $st->bindValue(':proveedor_id', $proveedorId,
        $proveedorId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $st->bindValue(':producto_id',  $d['producto_id'], PDO::PARAM_INT);
    $st->bindValue(':cantidad',     $d['cantidad'], PDO::PARAM_INT);
    $st->bindValue(':numero_orden', $d['numero_orden']);
    $st->bindValue(':motivo_devolucion', $d['motivo_devolucion']);
    $st->bindValue(':fecha_compra',      $d['fecha_compra']);
    $st->bindValue(':fecha_devolucion',  $d['fecha_devolucion']);
    $st->bindValue(':estado',            $d['estado']);
    $st->bindValue(':observaciones',     $d['observaciones']);
    $st->bindValue(':origen',            $d['origen']);

    $st->execute();
    return (int)$this->pdo->lastInsertId();
}


    /* ============================================================
       ACTUALIZAR
    ============================================================ */
    public function actualizar(int $id, array $d): void
    {
        $sql = "
            UPDATE devoluciones
            SET
                cliente_id        = :cliente_id,
                proveedor_id      = :proveedor_id,
                producto_id       = :producto_id,
                cantidad          = :cantidad,
                numero_orden      = :numero_orden,
                motivo_devolucion = :motivo_devolucion,
                fecha_compra      = :fecha_compra,
                fecha_devolucion  = :fecha_devolucion,
                estado            = :estado,
                observaciones     = :observaciones,
                origen            = :origen
            WHERE id = :id
        ";
        $st = $this->pdo->prepare($sql);

        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $this->bindClienteProveedor($st, $d);

        $st->bindValue(':producto_id',       (int)$d['producto_id'], PDO::PARAM_INT);
        $st->bindValue(':cantidad',          (int)$d['cantidad'], PDO::PARAM_INT);
        $st->bindValue(':numero_orden',      $d['numero_orden']);
        $st->bindValue(':motivo_devolucion', $d['motivo_devolucion']);
        $st->bindValue(':fecha_compra',      $d['fecha_compra']);
        $st->bindValue(':fecha_devolucion',  $d['fecha_devolucion']);
        $st->bindValue(':estado',            $d['estado']);
        $st->bindValue(':observaciones',     $d['observaciones']);
        $st->bindValue(':origen',            $d['origen']);

        $st->execute();
    }

    private function bindClienteProveedor(\PDOStatement $st, array $d): void
    {
        // cliente
        if (!empty($d['cliente_id'])) {
            $st->bindValue(':cliente_id', (int)$d['cliente_id'], PDO::PARAM_INT);
        } else {
            $st->bindValue(':cliente_id', null, PDO::PARAM_NULL);
        }

        // proveedor
        if (!empty($d['proveedor_id'])) {
            $st->bindValue(':proveedor_id', (int)$d['proveedor_id'], PDO::PARAM_INT);
        } else {
            $st->bindValue(':proveedor_id', null, PDO::PARAM_NULL);
        }
    }

    /* ============================================================
       ELIMINAR
    ============================================================ */
    public function eliminar(int $id): void
    {
        $sql = "DELETE FROM devoluciones WHERE id = :id";
        $st  = $this->pdo->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();
    }

    public function actualizarBasico(int $id, string $fechaDev, string $estado, ?string $obs = null): void
{
    $sql = "
        UPDATE devoluciones
        SET
            fecha_devolucion = :fecha_devolucion,
            estado           = :estado,
            observaciones    = :observaciones
        WHERE id = :id
    ";

    $st = $this->pdo->prepare($sql);
    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->bindValue(':fecha_devolucion', $fechaDev);
    $st->bindValue(':estado', $estado);
    // si viene vacío lo guardamos como NULL
    $st->bindValue(':observaciones', ($obs !== null && $obs !== '') ? $obs : null);
    $st->execute();
}

public function getProductosPorProveedor(int $proveedorId): array
{
    $sql = "
        SELECT DISTINCT p.id, p.nombre
        FROM proveedor_producto pp
        INNER JOIN productos p ON p.id = pp.producto_id
        WHERE pp.proveedor_id = :prov
          AND p.estado = 'activo'
        ORDER BY p.nombre ASC
    ";

    $st = $this->pdo->prepare($sql);
    $st->bindValue(':prov', $proveedorId, PDO::PARAM_INT);
    $st->execute();

    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}



}
