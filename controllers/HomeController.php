<?php

namespace Controllers;

use Core\Controller;
use Models\Producto;

final class HomeController extends Controller
{
    /** Home público */
    public function index(): void
    {
        $base = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');

        $this->render(
            'home/index',
            [
                'extra_css' => [$base . '/assets/css/home.css'],
            ],
            'Inicio'
        );
    }

    public function pagoInstrucciones(): void
    {
        $base   = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');
        $metodo = $_GET['metodo'] ?? '';
        $total  = isset($_GET['total']) ? (float)$_GET['total'] : 0.0;

        $this->render(
            'home/pago_instrucciones',
            [
                'metodo' => $metodo,
                'total'  => $total,
                // si creaste un CSS para esta vista, descomenta:
                // 'extra_css' => [$base . '/assets/css/pago_instrucciones.css'],
            ],
            'Detalles de pago'
        );
    }


    /** Panel con productos (carrito visual) */
    public function dashboard(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['cliente'])) {
            $this->redirect('/?r=login');
            return;
        }

        // Cargar productos desde el modelo
        $productoModel = new Producto($this->config);
        $productos = $productoModel->todos(); // ESTA ES LA FUNCIÓN QUE CREAMOS

        $base = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');

        $this->render(
            'home/carrito_Compra',
            [
                'cliente'   => $_SESSION['cliente'],
                'productos' => $productos,
                'extra_css' => [
                    $base . '/assets/css/home.css',
                    $base . '/assets/css/carritohomepage.css'
                ],
                'extra_js'  => [
                    $base . '/assets/js/carrito.js'
                ],
            ],
            'Mi carrito'
        );
    }
}
