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
            // ============================
            //  1) OBTENER ID DEL CLIENTE
            // ============================
            $clienteId = null;

            // 👇 ESTA es la que vimos en DEBUG_SESSION
            if (!empty($_SESSION['cliente']['id_cliente'])) {
                $clienteId = (int)$_SESSION['cliente']['id_cliente'];

                // Dejo estos como fallback por si más adelante usas otros
            } elseif (!empty($_SESSION['cliente']['id'])) {
                $clienteId = (int)$_SESSION['cliente']['id'];
            } elseif (!empty($_SESSION['cliente_id'])) {
                $clienteId = (int)$_SESSION['cliente_id'];
            } elseif (!empty($_SESSION['usuario'])) {
                $clienteId = (int)$_SESSION['usuario'];
            }

            if (!$clienteId) {
                echo json_encode(['success' => false, 'msg' => 'Debe iniciar sesión']);
                return;
            }

            // ============================
            //  2) OBTENER CARRITO (JSON)
            // ============================
            $raw  = file_get_contents("php://input");
            $data = json_decode($raw, true);

            // ahora el payload viene como { items: [], telefono, direccion, pago, notas }
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
            $ok      = $carrito->guardar($items, $clienteId, $telefono, $direccion, $pago, $notas);

            echo json_encode(['success' => $ok]);
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'msg'     => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }

    public function pagoInstrucciones(): void
    {
        $base   = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');
        $metodo = $_GET['metodo'] ?? '';
        $total  = isset($_GET['total']) ? (float)$_GET['total'] : 0.0;

        $this->render(
            'home/pago_instrucciones',
            [
                'metodo'   => $metodo,
                'total'    => $total,
                'extra_css' => [$base . '/assets/css/pago_instrucciones.css'],
            ],
            'Detalles de pago'
        );
    }




    public function listar()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $clienteId = null;

        if (!empty($_SESSION['cliente']['id_cliente'])) {
            $clienteId = (int)$_SESSION['cliente']['id_cliente'];
        } elseif (!empty($_SESSION['cliente']['id'])) {
            $clienteId = (int)$_SESSION['cliente']['id'];
        } elseif (!empty($_SESSION['cliente_id'])) {
            $clienteId = (int)$_SESSION['cliente_id'];
        } elseif (!empty($_SESSION['usuario'])) {
            $clienteId = (int)$_SESSION['usuario'];
        }

        if (!$clienteId) {
            echo json_encode([]);
            return;
        }

        $carrito = new Carrito($this->config);
        $items   = $carrito->listar($clienteId);

        echo json_encode($items);
    }
}
