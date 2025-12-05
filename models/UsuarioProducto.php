<?php
declare(strict_types=1);

namespace Models;

use PDO;

/**models/UsuarioProducto.php
 *  MODULO PRODUCTOS / Juliana Lugo / Clase modelo para gestión de productos.
 *
 * Encapsula todas las operaciones CRUD sobre la tabla `productos`,
 * incluyendo:
 *  - Listado con búsqueda y paginación
 *  - Obtención de un producto por ID
 *  - Creación de productos
 *  - Actualización de productos
 *  - Eliminación lógica/física de productos
 *  - Cambio de estado (activo / inactivo)
 */
final class UsuarioProducto
{
    /**
     * Conexión PDO a la base de datos.
     *
     * @var PDO
     */
    private PDO $db;

    /**
     * Constructor.
     *
     * Recibe una instancia de PDO (ya configurada) y ajusta
     * los atributos recomendados para manejo de errores y fetch.
     *
     * @param PDO $pdo Conexión PDO inyectada.
     */
    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;

        // Lanzar excepciones en errores SQL
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Obtener resultados como arreglos asociativos
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // Desactivar emulación de prepares para usar prepares nativos
        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * Listar productos con búsqueda y paginación.
     *
     * - Permite filtrar por nombre, código SKU, marca o categoría.
     * - Controla límites razonables de página y tamaño de página.
     *
     * @param string $q    Término de búsqueda (puede ser vacío).
     * @param int    $page Número de página (1 en adelante).
     * @param int    $per  Registros por página (máx. 50).
     *
     * @return array{items: array<int, array>, page: int, per: int, total: int}
     *         Retorna un arreglo con:
     *         - items: listado de productos
     *         - page:  página actual
     *         - per:   cantidad por página
     *         - total: total de registros que cumplen el filtro
     */
    public function listar(string $q = '', int $page = 1, int $per = 10): array
    {
        // Normalizar página y tamaño
        $page = max(1, $page);
        $per  = max(1, min(50, $per));
        $off  = ($page - 1) * $per;

        $where  = '';
        $params = [];

        // Si hay término de búsqueda, construir WHERE dinámico
        if ($q !== '') {
            $where  = "WHERE nombre LIKE ? OR codigo_sku LIKE ? OR marca LIKE ? OR categoria LIKE ?";
            $like   = "%{$q}%";
            $params = [$like, $like, $like, $like];
        }

        // Consulta principal con paginación
        $sql = "SELECT
                  id, nombre, categoria, marca, presentacion, stock_minimo,
                  descripcion, lote, f_vencimiento,
                  precio_compra, precio_venta, iva,
                  codigo_sku, ubicacion, estado, imagen, creado
                FROM productos
                {$where}
                ORDER BY id DESC
                LIMIT ?, ?";

        $st = $this->db->prepare($sql);

        // Enlazar parámetros de búsqueda (si los hay)
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p, PDO::PARAM_STR);
        }

        // Enlazar offset y límite para la paginación
        $st->bindValue($i++, (int) $off, PDO::PARAM_INT);
        $st->bindValue($i++, (int) $per, PDO::PARAM_INT);

        $st->execute();
        $items = $st->fetchAll();

        // Consulta para contar el total de registros (sin LIMIT)
        $st2 = $this->db->prepare("SELECT COUNT(*) FROM productos {$where}");
        $i   = 1;

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
     * Obtener un producto por su ID.
     *
     * @param int $id ID del producto.
     *
     * @return array|null Arreglo asociativo con los datos del producto
     *                    o null si no existe.
     */
    public function obtener(int $id): ?array
    {
        $st = $this->db->prepare(
            "SELECT
                  id, 
                  nombre, 
                  categoria, 
                  marca, 
                  presentacion,
                  stock_minimo,
                  descripcion, 
                  lote, 
                  f_vencimiento,
                  precio_compra, 
                  precio_venta, 
                  iva,
                  codigo_sku, 
                  ubicacion, 
                  estado, 
                  imagen, 
                  creado
                FROM productos
                WHERE id = :id"
        );

        $st->execute([':id' => $id]);
        $row = $st->fetch();

        // Si no hay resultado, devolver null
        return $row ?: null;
    }

    /**
     * Crear un nuevo producto.
     *
     * @param array $d Datos del producto validados desde el controlador.
     *
     * @return int ID del producto recién insertado.
     */
    public function crear(array $d): int
    {
        $sql = "INSERT INTO productos
                (nombre, categoria, marca, presentacion,
                 stock_minimo,descripcion, lote, f_vencimiento,
                 precio_compra, precio_venta, iva,
                 codigo_sku, ubicacion, estado, imagen, creado)
                VALUES
                (:nombre, :categoria, :marca, :presentacion,
                 :stock_minimo,
                 :descripcion, :lote, :f_vencimiento,
                 :precio_compra, :precio_venta, :iva,
                 :codigo_sku, :ubicacion, :estado, :imagen, NOW())";

        $st = $this->db->prepare($sql);

        $st->execute([
            ':nombre'         => $d['nombre'],
            ':categoria'      => $d['categoria'],
            ':marca'          => $d['marca'],
            ':presentacion'   => $d['presentacion'],
            ':stock_minimo'   => $d['stock_minimo'],
            ':descripcion'    => $d['descripcion'],
            ':lote'           => $d['lote'],
            // Puede ser null o 'YYYY-mm-dd'
            ':f_vencimiento'  => $d['f_vencimiento'],
            ':precio_compra'  => $d['precio_compra'],
            ':precio_venta'   => $d['precio_venta'],
            ':iva'            => $d['iva'],
            // Si viene vacío, se almacena NULL
            ':codigo_sku'     => ($d['codigo_sku'] === '' ? null : $d['codigo_sku']),
            ':ubicacion'      => $d['ubicacion'],
            // 'activo' | 'inactivo'
            ':estado'         => $d['estado'],
            // Si viene vacío, se almacena NULL
            ':imagen'         => ($d['imagen'] === '' ? null : $d['imagen']),
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar un producto existente.
     *
     * @param int   $id ID del producto a actualizar.
     * @param array $d  Datos ya validados y normalizados.
     *
     * @return void
     */
    public function actualizar(int $id, array $d): void
    {
        $sql = "UPDATE productos SET
                  nombre=:nombre, categoria=:categoria, marca=:marca, presentacion=:presentacion, stock_minimo=:stock_minimo,
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
            ':stock_minimo'   => $d['stock_minimo'],
            ':descripcion'    => $d['descripcion'],
            ':lote'           => $d['lote'],
            ':f_vencimiento'  => $d['f_vencimiento'],
            ':precio_compra'  => $d['precio_compra'],
            ':precio_venta'   => $d['precio_venta'],
            ':iva'            => $d['iva'],
            // Si viene vacío, se almacena NULL
            ':codigo_sku'     => ($d['codigo_sku'] === '' ? null : $d['codigo_sku']),
            ':ubicacion'      => $d['ubicacion'],
            ':estado'         => $d['estado'],
            // Si viene vacío, se almacena NULL
            ':imagen'         => ($d['imagen'] === '' ? null : $d['imagen']),
            ':id'             => $id,
        ]);
    }

    /**
     * Eliminar un producto por ID.
     *
     * @param int $id ID del producto a eliminar.
     *
     * @return void
     */
    public function eliminar(int $id): void
    {
        $st = $this->db->prepare("DELETE FROM productos WHERE id=:id");
        $st->execute([':id' => $id]);
    }

    /**
     * Alternar el estado del producto entre activo e inactivo.
     *
     * - Si está 'activo' pasa a 'inactivo'.
     * - Si está 'inactivo' pasa a 'activo'.
     *
     * @param int $id ID del producto.
     *
     * @return array Estado nuevo en un arreglo ['estado' => string]
     *               o arreglo vacío si el producto no existe.
     */
    public function toggleEstado(int $id): array
    {
        // Primero obtener el producto
        $row = $this->obtener($id);
        if (!$row) {
            return [];
        }

        // Comparar estado actual (case-insensitive)
        $nuevo = (strcasecmp((string) $row['estado'], 'activo') === 0)
            ? 'inactivo'
            : 'activo';

        // Actualizar estado en BD
        $st = $this->db->prepare("UPDATE productos SET estado=:e WHERE id=:id");
        $st->execute([':e' => $nuevo, ':id' => $id]);

        return ['estado' => $nuevo];
    }
}
