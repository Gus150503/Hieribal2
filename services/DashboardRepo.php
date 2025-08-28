<?php
// services/DashboardRepo.php

class DashboardRepo
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function scalar(string $sql, array $params = []): mixed
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchColumn();
    }

    public function kpis(): array
    {
        // Ajusta nombres de tablas/campos a tu esquema real
        // Empleados activos (si están en "usuarios" con rol)
        $totalEmpleados = (int)$this->scalar("
            SELECT COUNT(*) 
            FROM usuarios 
            WHERE rol IN ('empleado','cajero') 
              AND activo = 1 
              AND (eliminado = 0 OR eliminado IS NULL)
        ");

        // Clientes activos
        $totalClientes = (int)$this->scalar("
            SELECT COUNT(*)
            FROM clientes
            WHERE activo = 1 
              AND (eliminado = 0 OR eliminado IS NULL)
        ");

        // Productos (no eliminados)
        $totalProductos = (int)$this->scalar("
            SELECT COUNT(*)
            FROM productos
            WHERE (eliminado = 0 OR eliminado IS NULL)
        ");

        // Ventas del mes (ejemplo)
        $totalVentasMes = (int)$this->scalar("
            SELECT COALESCE(COUNT(*),0)
            FROM ventas
            WHERE estado = 'pagada'
              AND DATE_FORMAT(fecha, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
        ");

        return compact('totalEmpleados','totalClientes','totalProductos','totalVentasMes');
    }
}
