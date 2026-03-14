<?php

namespace Controllers;

use Core\Controller;

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

        $base = rtrim((string)($this->config['app']['base_url'] ?? ''), '/');

        $this->render(
            'home/perfil',
            [
                'cliente' => $cliente,
                'extra_css' => [
                    $base . '/assets/css/perfil.css'
                ]
            ],
            'Mi Perfil'
        );
    }
}