<?php
namespace Controllers;

use Core\Controller;
use Models\Carrito;

class CarritoAdminController extends Controller
{
    public function guardar()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['cliente']['id'])) {
            echo json_encode(['success' => false, 'msg' => 'Debe iniciar sesión']);
            return;
        }

        $clienteId = $_SESSION['cliente']['id'];

        $data = json_decode(file_get_contents("php://input"), true);

        $carrito = new Carrito($this->config);
        $ok = $carrito->guardar($data, $clienteId);

        echo json_encode(['success' => $ok]);
    }

    public function listar()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['cliente']['id'])) {
            echo json_encode([]);
            return;
        }

        $clienteId = $_SESSION['cliente']['id'];

        $carrito = new Carrito($this->config);
        $items = $carrito->listar($clienteId);

        echo json_encode($items);
    }
}
