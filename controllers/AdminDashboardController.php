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

    public function index(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['admin'])) {
        $_SESSION['admin_error'] = 'Inicia sesión para continuar.';
        $this->redirect('/?r=admin_login');
    }


    
    // ===== Charts =====
    [$lowLabels,  $lowValues]  = $this->productos->porAcabarse(10);
    [$needLabels, $needValues] = $this->productos->porPedir(10);
    [$tcLabels,   $tcValues]   = $this->ventas->topClientes(10, 'compras');

    // ===== Carruseles =====
    $invDestacados    = $this->productos->destacados(10);
    $topVendidos      = $this->ventas->topProductos(10);
    $agotados         = $this->productos->agotados(10);
    $aniversario1Anio = $this->usuarios->conAnioAntiguedad(10);

    $this->render('admin/dashboard', [
        'titulo'            => 'Dashboard',   // 👈 IMPORTANTE (va dentro de $data)
        'esAdmin'           => true,

        // Tus variables (se mantienen tal cual)
        'admin'             => $_SESSION['admin'],
        'totalEmpleados'    => (int) $this->usuarios->totalPorRol('Empleado'),
        'totalClientes'     => (int) $this->clientes->totalActivos(),
        'totalProductos'    => (int) $this->productos->totalActivos(),
        'totalVentasMes'    => (int) $this->ventas->totalDelMes(),

        'invDestacados'     => $invDestacados,
        'topVendidos'       => $topVendidos,
        'agotados'          => $agotados,
        'aniversario1Anio'  => $aniversario1Anio,
        'aniversario1Año'   => $aniversario1Anio, // alias

        'lowStockLabels'    => $lowLabels,
        'lowStockValues'    => $lowValues,
        'toOrderLabels'     => $needLabels,
        'toOrderValues'     => $needValues,
        'topClientsLabels'  => $tcLabels,
        'topClientsValues'  => $tcValues,

        // Si tu plantilla usa estos:
        'extra_js' => [
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
            $this->config['app']['base_url'] . '/assets/js/admin.js',
        ],
    ]);
}

public function inventario(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['admin'])) { $_SESSION['admin_error'] = 'Inicia sesión para continuar.'; $this->redirect('/?r=admin_login'); }
    $this->render('admin/inventario/index', ['titulo' => 'Inventario', 'esAdmin' => true]);
}

public function productos(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['admin'])) { $_SESSION['admin_error'] = 'Inicia sesión para continuar.'; $this->redirect('/?r=admin_login'); }
    $this->render('admin/productos/index', ['titulo' => 'Productos', 'esAdmin' => true]);
}

public function configuracion(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['admin'])) { $_SESSION['admin_error'] = 'Inicia sesión para continuar.'; $this->redirect('/?r=admin_login'); }
    $this->render('admin/configuracion/index', ['titulo' => 'Configuración', 'esAdmin' => true]);
}


}
