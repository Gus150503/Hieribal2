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

    /** Dashboard privado (solo logueados) */
    public function dashboard(): void
    {
        if (empty($_SESSION['cliente'])) {
            $this->redirect('/?r=login');
            return;
        }
$this->render(
    'home/carrito_Compra',     // ← EXACTO ASÍ
    [ 'cliente' => $_SESSION['cliente'] ?? null ],
    'Panel'
);

    }
}
