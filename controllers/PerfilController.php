<?php

namespace Controllers;

use Core\Controller;
use Models\PerfilCliente;

class PerfilController extends Controller
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['cliente'])) {
            $this->redirect('/?r=login');
            return;
        }

        $cliente = $_SESSION['cliente'];

        $idCliente = intval($cliente['id'] ?? $cliente['id_cliente'] ?? 0);

        // 🔥 CONFIG
        $config = require __DIR__ . '/../config/env.php';

        // 🔥 MODELO
        $model = new PerfilCliente($config);
        $perfil = $model->obtenerDatos($idCliente);

        // 🔥 DATOS USUARIO
        $usuario = [
            'nombre' => $cliente['nombre'] ?? 'Usuario',
            'email' => $cliente['email'] ?? '',
            'telefono' => $cliente['telefono'] ?? '',
            'fecha_registro' => $cliente['fecha_registro'] ?? date('Y-m-d'),

            'total_pedidos' => $perfil['pedidos']['total'] ?? 0,
            'total_gastado' => $perfil['gastado']['gastado'] ?? 0
        ];

        $base = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');

        $this->render(
            'home/perfil',
            [
                'usuario' => $usuario,
                'perfil' => $perfil,
                'productosTop' => $perfil['topProductos'] ?? [],
                'extra_css' => [$base . '/assets/css/perfil.css'],
                'carga_chartjs' => true
            ],
            'Mi Perfil'
        );
    }
}