<?php
namespace Controllers;

use Core\Controller;
use Models\AdminVenta;

class PedidosWebController extends Controller
{
    public function index() {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $model = new AdminVenta($this->config);

        // Al entrar, los estados '0' pasan a '1' para apagar el punto naranja
        $model->marcarComoVistos();

        $this->render('admin/pedidos_web/index', [
            'pedidos'     => $model->listarPedidosWeb(),
            'totalNuevos' => 0, 
            'esAdmin'     => true,
            'titulo'      => 'Pedidos desde la Web',
            'extra_js'    => ['assets/js/admin_pedidos_web.js'] // Carga el JS externamente
        ]);
    }

    public function despacharAjax() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $model = new AdminVenta($this->config);
            $model->cambiarEstadoEnvio((int)$id, 2); // 2 = Enviado/Despachado
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit; 
    }
}