<?php
namespace Models;

use PDO;

class Producto
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ================== LISTAR ==================
    public function listar(string $q, int $page, int $per): array
    {
        $off  = max(0, ($page - 1) * $per);
        $per  = max(1, min(100, $per));
        $like = "%{$q}%";

        // Nota: en MySQL, bindear LIMIT a veces falla si no hay emulación de prepares.
        // Interpolamos enteros casteados para seguridad.
        $sql = "SELECT *
                FROM productos
                WHERE (nombre LIKE :like OR categoria LIKE :like OR marca LIKE :like OR descripcion LIKE :like)
                ORDER BY id DESC
                LIMIT {$off}, {$per}";
        $st = $this->db->prepare($sql);
        $st->execute([':like' => $like]);
        $items = $st->fetchAll(PDO::FETCH_ASSOC);

        $sql2 = "SELECT COUNT(*) FROM productos
                 WHERE (nombre LIKE :like OR categoria LIKE :like OR marca LIKE :like OR descripcion LIKE :like)";
        $st2 = $this->db->prepare($sql2);
        $st2->execute([':like' => $like]);
        $total = (int)$st2->fetchColumn();

        return ['items' => $items, 'page' => $page, 'per' => $per, 'total' => $total];
    }

    // ================== OBTENER ==================
    public function obtener(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM productos WHERE id=:id");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ================== CREAR ==================
    public function crear(array $d): int
    {
        $sql = "INSERT INTO productos
                (nombre,categoria,marca,presentacion,unidad,descripcion,lote,f_vencimiento,
                 precio_compra,precio_venta,iva,codigo_sku,ubicacion,estado,imagen,creado)
                VALUES
                (:nombre,:categoria,:marca,:presentacion,:unidad,:descripcion,:lote,:f_vencimiento,
                 :precio_compra,:precio_venta,:iva,:codigo_sku,:ubicacion,:estado,:imagen,NOW())";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':nombre'         => $d['nombre'],
            ':categoria'      => $d['categoria'],
            ':marca'          => $d['marca'],
            ':presentacion'   => $d['presentacion'],
            ':unidad'         => $d['unidad'],
            ':descripcion'    => $d['descripcion'],
            ':lote'           => $d['lote'],
            ':f_vencimiento'  => $d['f_vencimiento'],
            ':precio_compra'  => $d['precio_compra'],
            ':precio_venta'   => $d['precio_venta'],
            ':iva'            => $d['iva'],
            ':codigo_sku'     => $d['codigo_sku'],
            ':ubicacion'      => $d['ubicacion'],
            ':estado'         => $d['estado'] ?? 'activo',
            ':imagen'         => $d['imagen'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    // ================== ACTUALIZAR ==================
    public function actualizar(int $id, array $d): void
    {
        $sql = "UPDATE productos SET
                nombre=:nombre,categoria=:categoria,marca=:marca,
                presentacion=:presentacion,unidad=:unidad,descripcion=:descripcion,
                lote=:lote,f_vencimiento=:f_vencimiento,precio_compra=:precio_compra,
                precio_venta=:precio_venta,iva=:iva,codigo_sku=:codigo_sku,
                ubicacion=:ubicacion,estado=:estado,imagen=:imagen
                WHERE id=:id";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':nombre'         => $d['nombre'],
            ':categoria'      => $d['categoria'],
            ':marca'          => $d['marca'],
            ':presentacion'   => $d['presentacion'],
            ':unidad'         => $d['unidad'],
            ':descripcion'    => $d['descripcion'],
            ':lote'           => $d['lote'],
            ':f_vencimiento'  => $d['f_vencimiento'],
            ':precio_compra'  => $d['precio_compra'],
            ':precio_venta'   => $d['precio_venta'],
            ':iva'            => $d['iva'],
            ':codigo_sku'     => $d['codigo_sku'],
            ':ubicacion'      => $d['ubicacion'],
            ':estado'         => $d['estado'],
            ':imagen'         => $d['imagen'],
            ':id'             => $id,
        ]);
    }

    // ================== ELIMINAR ==================
    public function eliminar(int $id): void
    {
        $st = $this->db->prepare("DELETE FROM productos WHERE id=?");
        $st->execute([$id]);
    }

    // ================== TOGGLE ESTADO ==================
    public function toggleEstado(int $id): array
    {
        $st = $this->db->prepare("SELECT id, estado FROM productos WHERE id=? LIMIT 1");
        $st->execute([$id]);
        $p = $st->fetch(PDO::FETCH_ASSOC);
        if (!$p) throw new \Exception('Producto no encontrado');

        $nuevoEstado = (strcasecmp($p['estado'], 'activo') === 0) ? 'inactivo' : 'activo';

        $this->db->prepare("UPDATE productos SET estado=? WHERE id=?")
                 ->execute([$nuevoEstado, $id]);

        return ['estado' => $nuevoEstado];
    }
}
