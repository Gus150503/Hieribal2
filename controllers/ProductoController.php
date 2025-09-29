<?php
namespace Controllers;

use Core\Controller;
use Models\Producto;

final class ProductoController extends Controller
{
    private Producto $producto;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->producto = new Producto($config);
    }

    /** Página pública: listado de productos activos */
    public function index(): void
    {
        $base = $this->config['app']['base_url'];

        $this->render(
            vista: 'producto/index',
            data: [
                'productos'   => $this->producto->destacados(12), // productos destacados
                'agotados'    => $this->producto->agotados(6),   // agotados
                'porAcabarse' => $this->producto->porAcabarse(), // para chart
                'porPedir'    => $this->producto->porPedir(),   // para chart
                'extra_css'   => [ $base . '/assets/css/producto.css' ],
                'extra_js'    => [ $base . '/assets/js/producto.js' ],
            ],
            titulo: 'Productos'
        );
    }

    /** Dashboard privado de productos */
    public function dashboard(): void
    {
        if (!isset($_SESSION['cliente'])) {
            $this->redirect(to: '/?r=login');
        }

        $this->render(
            vista: 'producto/dashboard',
            data: [
                'totalActivos' => $this->producto->totalActivos(),
                'cliente'      => $_SESSION['cliente'],
            ],
            titulo: 'Gestión de productos'
        );
    }
}
