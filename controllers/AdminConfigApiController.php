<?php
declare(strict_types=1);

namespace Controllers;

final class AdminConfigApiController
{
    private string $file;

    public function __construct()
    {
        // carpeta de storage
        $dir = \dirname(__DIR__) . '/storage';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $this->file = $dir . '/ui_config.json';
        if (!is_file($this->file)) {
            @file_put_contents($this->file, json_encode([
                'empresa_nombre'       => '',
                'empresa_ruc'          => '',
                'empresa_direccion'    => '',
                'correo_host'          => '',
                'correo_puerto'        => 587,
                'correo_seguridad'     => 'tls',
                'correo_usuario'       => '',
                'correo_from'          => '',
                'correo_activo'        => 0,
                'ui_tema'              => 'light',
                'ui_color_principal'   => '#198754',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }

    public function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $action = $_GET['action'] ?? 'get';
        try {
            switch ($action) {
                case 'get':
                    $data = json_decode((string)@file_get_contents($this->file), true) ?: [];
                    echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
                    return;

                case 'update':
                    // items[clave]=valor …
                    $items = $_POST['items'] ?? [];
                    if (!is_array($items)) $items = [];

                    $cur = json_decode((string)@file_get_contents($this->file), true) ?: [];
                    $new = array_merge($cur, $items);

                    @file_put_contents($this->file, json_encode($new, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                    echo json_encode(['ok' => true, 'msg' => 'Guardado'], JSON_UNESCAPED_UNICODE);
                    return;

                default:
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'msg' => 'Acción no válida'], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }
}
