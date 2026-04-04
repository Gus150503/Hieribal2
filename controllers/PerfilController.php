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

        // 🔐 Validar sesión
        if (empty($_SESSION['cliente'])) {
            $this->redirect('/?r=login');
            return;
        }

        $cliente = $_SESSION['cliente'];

        // 🔥 IMPORTANTE: obtener id correcto
        $idCliente = intval($cliente['id'] ?? $cliente['id_cliente'] ?? 0);

        // ==============================
        // 🔥 CARGAR CONFIG
        // ==============================
        $config = require __DIR__ . '/../config/env.php';

        // ==============================
        // 🔥 MODELO PERFIL
        // ==============================
        $model = new PerfilCliente($config);
        $perfil = $model->obtenerDatos($idCliente);

        // ==============================
        // 🔥 DATOS USUARIO (DINÁMICOS)
        // ==============================
        $usuario = [
            'nombre' => $cliente['nombre'] ?? 'Usuario',
            'email' => $cliente['email'] ?? 'correo@mail.com',
            'telefono' => $cliente['telefono'] ?? '—',
            'fecha_registro' => $cliente['fecha_registro'] ?? date('Y-m-d'),
            'es_pro' => true,

            // 🔥 ahora vienen del modelo
            'total_pedidos' => $perfil['pedidos']['total'] ?? 0,
            'total_gastado' => $perfil['gastado']['gastado'] ?? 0,
            'devoluciones' => $perfil['devoluciones']['total'] ?? 0
        ];

        // ==============================
        // 🎨 RENDER
        // ==============================
        $base = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');

        $this->render(
            'home/perfil',
            [
                'usuario' => $usuario,
                'perfil' => $perfil, // 🔥 CLAVE
                'productosTop' => $perfil['topProductos'] ?? [],
                'extra_css' => [
                    $base . '/assets/css/perfil.css'
                ],
                'carga_chartjs' => true
            ],
            'Mi Perfil'
        );
    }
}