<?php

namespace Controllers;

use Core\Controller;
use Models\Carrito;
use Throwable;

class CarritoAdminController extends Controller
{
    public function guardar()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        try {
            // Obtener ID del cliente desde la sesión
            $clienteId = $_SESSION['cliente']['id_cliente'] ?? $_SESSION['cliente']['id'] ?? $_SESSION['cliente_id'] ?? null;

            if (!$clienteId) {
                echo json_encode(['success' => false, 'msg' => 'Debe iniciar sesión']);
                return;
            }

            // Obtener datos del JSON enviado por el JS
            $raw  = file_get_contents("php://input");
            $data = json_decode($raw, true);

            $items     = $data['items'] ?? [];
            $telefono  = trim($data['telefono']  ?? '');
            $direccion = trim($data['direccion'] ?? '');
            $pago      = trim($data['pago']      ?? '');
            $notas     = trim($data['notas']     ?? '');

            if (!$items || !$telefono || !$direccion || !$pago) {
                echo json_encode(['success' => false, 'msg' => 'Datos de envío incompletos']);
                return;
            }

            $carrito = new Carrito($this->config);
            // El modelo ahora descuenta stock automáticamente al guardar
            $ok = $carrito->guardar($items, (int)$clienteId, $telefono, $direccion, $pago, $notas);

            echo json_encode(['success' => $ok]);
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'msg'     => 'Error al procesar la compra: ' . $e->getMessage()
            ]);
        }
    }

    public function listar()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $clienteId = $_SESSION['cliente']['id_cliente'] ?? $_SESSION['cliente']['id'] ?? null;
        if (!$clienteId) { echo json_encode([]); return; }

        $carrito = new Carrito($this->config);
        echo json_encode($carrito->listar((int)$clienteId));
    }
}