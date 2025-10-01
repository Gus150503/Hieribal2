<?php
namespace Controllers;

use Core\Controller;
use Core\Database;
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

        // Todos tus modelos esperan array $config (según el error/reportes)
        $this->usuarios  = new Usuario($config);
        $this->clientes  = new Cliente($config);
        $this->productos = new Producto($config);
        $this->ventas    = new Venta($config);
    }

    private function ensureAdmin(): void
    {
        if (session_status() !== \PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['admin'])) {
            $_SESSION['admin_error'] = 'Inicia sesión para continuar.';
            $this->redirect('/?r=admin_login');
        }
    }

    public function index(): void
    {
        $this->ensureAdmin();

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
            'titulo'            => 'Dashboard',
            'esAdmin'           => true,
            'carga_chartjs'     => true,

            'admin'             => $_SESSION['admin'],

            'totalEmpleados'    => (int) $this->usuarios->totalPorRol('Empleado'),
            'totalClientes'     => (int) $this->clientes->totalActivos(),
            'totalProductos'    => (int) $this->productos->totalActivos(),
            'totalVentasMes'    => (int) $this->ventas->totalDelMes(),

            'invDestacados'     => $invDestacados,
            'topVendidos'       => $topVendidos,
            'agotados'          => $agotados,
            'aniversario1Anio'  => $aniversario1Anio,
            'aniversario1Año'   => $aniversario1Anio, // alias por tilde

            'lowStockLabels'    => $lowLabels,
            'lowStockValues'    => $lowValues,
            'toOrderLabels'     => $needLabels,
            'toOrderValues'     => $needValues,
            'topClientsLabels'  => $tcLabels,
            'topClientsValues'  => $tcValues,
        ], 'Panel');
    }

    public function inventario(): void
    {
        $this->ensureAdmin();
        $this->render('admin/inventario/index', [
            'titulo'  => 'Inventario',
            'esAdmin' => true,
        ], 'Inventario');
    }

    public function productos(): void
    {
        $this->ensureAdmin();
        $this->render('admin/productos/index', [
            'titulo'  => 'Productos',
            'esAdmin' => true,
        ], 'Productos');
    }

    public function configuracion(): void
    {
        $this->ensureAdmin();

        $ui_tema = 'light';
        try {
            // Obtenemos PDO solo aquí, cuando lo necesitamos
            $pdo = Database::get($this->config['db'] ?? []);
            $val = $pdo->query("SELECT valor FROM config WHERE clave='ui_tema' LIMIT 1")->fetchColumn();
            if ($val !== false && $val !== null && $val !== '') $ui_tema = (string)$val;
        } catch (\Throwable $e) {
            // opcional: log
        }

        $base = $this->config['app']['base_url'] ?? '/public';

        $this->render('admin/configuracion/index', [
            'titulo'    => 'Configuración',
            'esAdmin'   => true,
            'extra_css' => [$base . '/assets/css/admin_config.css'],
            'extra_js'  => [$base . '/assets/js/admin_config.js?v=5'],
            'ui_tema'   => $ui_tema,
        ], 'Configuración');
    }
}
