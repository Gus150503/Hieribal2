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
        [$lowLabels,  $lowValues]  = $this->productos->porAcabarse(10);           // [labels, values]
        [$needLabels, $needValues] = $this->productos->porPedir(10);              // [labels, values]
        [$tcLabels,   $tcValues]   = $this->ventas->topClientes(10, 'compras');   // [labels, values]

        // ===== Carruseles =====
        $invDestacados     = $this->productos->destacados(10);
        $topVendidos       = $this->ventas->topProductos(10);
        $agotados          = $this->productos->agotados(10);
        $aniversario1Anio  = $this->usuarios->conAnioAntiguedad(10); // usa fecha_creacion

        // Render (vista = contenido puro; layout aporta sidebar/estructura)
        $this->render(
            'admin/dashboard',
            [
                'esAdmin'            => true,

                // === NUEVO: variables que espera el layout ===
                'bodyClass'   => 'admin-layout',                    // activa tema del panel
                'pageStyles'  => [
                    '/assets/css/dashboard.css', 
                    '/assets/css/sidebar.css'                      // <-- agregado aquí
                ],     
                'pageScripts' => [                                 // scripts que debe inyectar el layout
                    'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
                    $this->config['app']['base_url'] . '/assets/js/admin.js',
                ],

                // === Mantengo lo tuyo tal cual ===
                'admin'              => $_SESSION['admin'],

                // KPIs
                'totalEmpleados'     => (int) $this->usuarios->totalPorRol('Empleado'),
                'totalClientes'      => (int) $this->clientes->totalActivos(),
                'totalProductos'     => (int) $this->productos->totalActivos(),
                'totalVentasMes'     => (int) $this->ventas->totalDelMes(),

                // Carruseles
                'invDestacados'      => $invDestacados,
                'topVendidos'        => $topVendidos,
                'agotados'           => $agotados,
                'aniversario1Anio'   => $aniversario1Anio,

                // 👉 Alias por si tu vista usa la variable con "ñ"
                'aniversario1Año'    => $aniversario1Anio,

                // Charts
                'lowStockLabels'     => $lowLabels,
                'lowStockValues'     => $lowValues,
                'toOrderLabels'      => $needLabels,
                'toOrderValues'      => $needValues,
                'topClientsLabels'   => $tcLabels,
                'topClientsValues'   => $tcValues,

                // === Mantengo tu clave original (por compatibilidad) ===
                // Si tu layout nuevo ya usa pageScripts, evita duplicar carga en la plantilla.
                'extra_js' => [
                    'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
                    $this->config['app']['base_url'] . '/assets/js/admin.js',
                ],
            ],
            'Dashboard'
        );
    }
}
