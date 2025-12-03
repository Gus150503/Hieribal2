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
