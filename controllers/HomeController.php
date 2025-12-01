<?php
namespace Controllers;

use Core\Controller;

final class HomeController extends Controller
{
    /** Home público (no requiere sesión) */
    public function index(): void
    {
        $base = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');

        $this->render(
            'home/index',
            [
                'extra_css' => [$base . '/assets/css/home.css'],
                // 'extra_js'  => [$base . '/assets/js/home.js'],
            ],
            'Inicio'
        );
    }

    public function dashboard(): void
    {
        // Asegúrate de que la sesión esté iniciada en algún punto global
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['cliente'])) {
            $this->redirect('/?r=login');
            return;
        }

        $base = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');

        $this->render(
            'home/carrito_Compra',
            [
                'cliente'   => $_SESSION['cliente'] ?? null,
                'extra_css' => [
                    $base . '/assets/css/home.css',            // estilos del header
                    $base . '/assets/css/carritohomepage.css' // estilos del carrito
                ],
                'extra_js'  => [$base . '/assets/js/carrito.js'],
            ],
            'Mi carrito'
        );
    }
}
