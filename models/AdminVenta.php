<?php

namespace Models;

use PDO;

class AdminVenta
{
    private PDO $db;

    public function __construct(array $config)
    {
        $db      = $config['db'] ?? [];
        $host    = $db['host']    ?? '127.0.0.1';
        $name    = $db['name']    ?? 'hieribal';
        $user    = $db['user']    ?? 'root';
        $pass    = $db['pass']    ?? '';
        $charset = $db['charset'] ?? 'utf8mb4';

        $dsn  = "mysql:host={$host};dbname={$name};charset={$charset}";
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->db = new PDO($dsn, $user, $pass, $opts);
    }

    /* ================== LISTAR PEDIDOS WEB ================== */
    
    /**
     * Obtiene todos los pedidos con la información del cliente vinculada.
     */
    public function listarPedidosWeb(): array
    {
        $sql = "
            SELECT 
                c.id_carrito, c.nombre_producto, c.cantidad, c.precio,
                c.subtotal, c.fecha_agregado, c.telefono_envio,
                c.direccion_envio, c.metodo_pago, c.notas, c.estado,
                cli.nombres AS cliente_nombre, cli.correo AS cliente_correo
            FROM carrito c
            INNER JOIN clientes cli ON c.id_cliente = cli.id_cliente
            ORDER BY c.fecha_agregado DESC
        ";
        $st = $this->db->prepare($sql);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /* ================== LÓGICA DE NOTIFICACIONES Y ESTADOS ================== */

    /**
     * Cuenta pedidos en estado 0 (Nuevos). 
     * Usado para el punto naranja del Sidebar.
     */
    public function contarPedidosNuevos(): int
    {
        $st = $this->db->query("SELECT COUNT(*) FROM carrito WHERE estado = 0");
        return (int)$st->fetchColumn();
    }

    /**
     * Cambia pedidos de estado 0 (Nuevo) a 1 (Visto).
     * Se ejecuta al entrar a la sección de Pedidos Web.
     */
    public function marcarComoVistos(): void
    {
        $this->db->query("UPDATE carrito SET estado = 1 WHERE estado = 0");
    }

    /**
     * Cambia el estado de un pedido (Ej: de 1 a 2 para despachar).
     * Usado por la función AJAX del botón verde.
     */
    public function cambiarEstadoEnvio(int $id, int $nuevoEstado): void
    {
        $st = $this->db->prepare("UPDATE carrito SET estado = :est WHERE id_carrito = :id");
        $st->execute([
            ':est' => $nuevoEstado, 
            ':id'  => $id
        ]);
    }
}