<?php
namespace Controllers;

use Core\Controller;
use Models\Usuario;
use Models\Cliente;
use Models\Producto;
use Models\Venta;

final class AdminDashboardController extends Controller
{
    private Usuario $usuarios;
    private Cliente $clientes;
    private Producto $productos;
    private Venta $ventas;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->usuarios  = new Usuario($config);
        $this->clientes  = new Cliente($config);
        $this->productos = new Producto($config);
        $this->ventas    = new Venta($config);
    }

// controllers/AdminDashboardController.php
public function index(): void
{
    if (empty($_SESSION['admin'])) {
        $_SESSION['admin_error'] = 'Inicia sesión para continuar.';
        $this->redirect('/?r=admin_login');
    }

    [$lowLabels,  $lowValues]  = $this->productos->porAcabarse(10);
    [$needLabels, $needValues] = $this->productos->porPedir(10);
    [$tcLabels,   $tcValues]   = $this->ventas->topClientes(10, 'compras');

    $this->render(
        'admin/dashboard',
        [
            'esAdmin'         => true, // <—— IMPORTANTE

            // KPIs
            'admin'           => $_SESSION['admin'],
            'totalEmpleados'  => (int) $this->usuarios->totalPorRol('Empleado'),
            'totalClientes'   => (int) $this->clientes->totalActivos(), // o ->total()
            'totalProductos'  => (int) $this->productos->totalActivos(),
            'totalVentasMes'  => (int) $this->ventas->totalDelMes(),

            // Carruseles
            'invDestacados'   => [],
            'topVendidos'     => [],
            'agotados'        => [],
            'aniversario1Año' => [],

            // Gráficos
            'lowStockLabels'   => $lowLabels,
            'lowStockValues'   => $lowValues,
            'toOrderLabels'    => $needLabels,
            'toOrderValues'    => $needValues,
            'topClientsLabels' => $tcLabels,
            'topClientsValues' => $tcValues,

            // JS extra
            'extra_js' => [
                'https://cdn.jsdelivr.net/npm/chart.js',
                $this->config['app']['base_url'] . '/assets/js/admin-dashboard.js',
            ],
        ],
        'Dashboard'
    );
}
}