<?php
declare(strict_types=1);

namespace Models;

use PDO;

final class UsuarioProducto
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /** Listar con búsqueda y paginación */
    public function listar(string $q = '', int $page = 1, int $per = 10): array
    {
        $page = max(1, $page);
        $per  = max(1, min(50, $per));
        $off  = ($page - 1) * $per;

        $where  = '';
        $params = [];
        if ($q !== '') {
            $where  = "WHERE nombre LIKE ? OR codigo_sku LIKE ? OR marca LIKE ? OR categoria LIKE ?";
            $like   = "%{$q}%";
            $params = [$like, $like, $like, $like];
        }

        $sql = "SELECT
                  id, nombre, categoria, marca, presentacion,
                  stock_actual, stock_minimo,
                  descripcion, lote, f_vencimiento,
                  precio_compra, precio_venta, iva,
                  codigo_sku, ubicacion, estado, imagen, creado
                FROM productos
                {$where}
                ORDER BY id DESC
                LIMIT ?, ?";

        $st = $this->db->prepare($sql);
        $i = 1;
        foreach ($params as $p) $st->bindValue($i++, $p, PDO::PARAM_STR);
        $st->bindValue($i++, (int)$off, PDO::PARAM_INT);
        $st->bindValue($i++, (int)$per, PDO::PARAM_INT);
        $st->execute();
        $items = $st->fetchAll();

        $st2 = $this->db->prepare("SELECT COUNT(*) FROM productos {$where}");
        $i = 1;
        foreach ($params as $p) $st2->bindValue($i++, $p, PDO::PARAM_STR);
        $st2->execute();
        $total = (int)$st2->fetchColumn();

        return ['items' => $items, 'page' => $page, 'per' => $per, 'total' => $total];
    }

    /** Obtener uno */
    public function obtener(int $id): ?array
    {
        $st = $this->db->prepare("SELECT
                  id, nombre, categoria, marca, presentacion,
                  stock_actual, stock_minimo,
                  descripcion, lote, f_vencimiento,
                  precio_compra, precio_venta, iva,
                  codigo_sku, ubicacion, estado, imagen, creado
                FROM productos
                WHERE id = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /** Crear */
    public function crear(array $d): int
    {
        $sql = "INSERT INTO productos
                (nombre, categoria, marca, presentacion,
                 stock_actual, stock_minimo,
                 descripcion, lote, f_vencimiento,
                 precio_compra, precio_venta, iva,
                 codigo_sku, ubicacion, estado, imagen, creado)
                VALUES
                (:nombre, :categoria, :marca, :presentacion,
                 :stock_actual, :stock_minimo,
                 :descripcion, :lote, :f_vencimiento,
                 :precio_compra, :precio_venta, :iva,
                 :codigo_sku, :ubicacion, :estado, :imagen, NOW())";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':nombre'         => $d['nombre'],
            ':categoria'      => $d['categoria'],
            ':marca'          => $d['marca'],
            ':presentacion'   => $d['presentacion'],
            ':stock_actual'   => $d['stock_actual'],
            ':stock_minimo'   => $d['stock_minimo'],
            ':descripcion'    => $d['descripcion'],
            ':lote'           => $d['lote'],
            ':f_vencimiento'  => $d['f_vencimiento'], // null o 'YYYY-mm-dd'
            ':precio_compra'  => $d['precio_compra'],
            ':precio_venta'   => $d['precio_venta'],
            ':iva'            => $d['iva'],
            ':codigo_sku'     => ($d['codigo_sku'] === '' ? null : $d['codigo_sku']),
            ':ubicacion'      => $d['ubicacion'],
            ':estado'         => $d['estado'], // 'activo' | 'inactivo'
            ':imagen'         => ($d['imagen'] === '' ? null : $d['imagen']),
        ]);
        return (int)$this->db->lastInsertId();
    }

    /** Actualizar */
    public function actualizar(int $id, array $d): void
    {
        $sql = "UPDATE productos SET
                  nombre=:nombre, categoria=:categoria, marca=:marca, presentacion=:presentacion,
                  stock_actual=:stock_actual, stock_minimo=:stock_minimo,
                  descripcion=:descripcion, lote=:lote, f_vencimiento=:f_vencimiento,
                  precio_compra=:precio_compra, precio_venta=:precio_venta, iva=:iva,
                  codigo_sku=:codigo_sku, ubicacion=:ubicacion, estado=:estado, imagen=:imagen
                WHERE id=:id";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':nombre'         => $d['nombre'],
            ':categoria'      => $d['categoria'],
            ':marca'          => $d['marca'],
            ':presentacion'   => $d['presentacion'],
            ':stock_actual'   => $d['stock_actual'],
            ':stock_minimo'   => $d['stock_minimo'],
            ':descripcion'    => $d['descripcion'],
            ':lote'           => $d['lote'],
            ':f_vencimiento'  => $d['f_vencimiento'],
            ':precio_compra'  => $d['precio_compra'],
            ':precio_venta'   => $d['precio_venta'],
            ':iva'            => $d['iva'],
            ':codigo_sku'     => ($d['codigo_sku'] === '' ? null : $d['codigo_sku']),
            ':ubicacion'      => $d['ubicacion'],
            ':estado'         => $d['estado'],
            ':imagen'         => ($d['imagen'] === '' ? null : $d['imagen']),
            ':id'             => $id,
        ]);
    }

    /** Eliminar */
    public function eliminar(int $id): void
    {
        $st = $this->db->prepare("DELETE FROM productos WHERE id=:id");
        $st->execute([':id' => $id]);
    }

    /** Toggle estado activo/inactivo */
    public function toggleEstado(int $id): array
    {
        $row = $this->obtener($id);
        if (!$row) return [];

        $nuevo = (strcasecmp((string)$row['estado'], 'activo') === 0) ? 'inactivo' : 'activo';
        $st = $this->db->prepare("UPDATE productos SET estado=:e WHERE id=:id");
        $st->execute([':e' => $nuevo, ':id' => $id]);

        return ['estado' => $nuevo];
    }

    /** Verifica si el producto está siendo usado en otras tablas */
public function estaEnUso(int $id): bool
{
    // Aquí validamos SOLO carrito, puedes agregar ventas si las tienes
    $st = $this->db->prepare("SELECT COUNT(*) FROM carrito WHERE id_producto = :id");
    $st->execute([':id' => $id]);
    return (int)$st->fetchColumn() > 0;
}

}
