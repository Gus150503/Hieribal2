<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Models\UsuarioAdmin;
use Services\ServicioCorreo;

final class AdminUsuariosController extends Controller
{
    private UsuarioAdmin $Usuario;
    private ServicioCorreo $correo;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->Usuario = new UsuarioAdmin($config);
        $this->correo  = new ServicioCorreo($config); // 👈 usamos ServicioCorreo
    }

    /* ============================
       Helpers comunes JSON y sesión
       ============================ */

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function ok(array $extra = [], int $status = 200): void
    {
        $this->json(['ok' => true] + $extra, $status);
    }

    private function fail(string $msg, int $status = 400, array $extra = []): void
    {
        $this->json(['ok' => false, 'msg' => $msg] + $extra, $status);
    }

    private function ensureAdmin(array $rolesPermitidos = ['Admin']): void
    {
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            session_start();
        }

        // ¿Existe sesión admin?
        $user = $_SESSION['admin'] ?? null;

        if (!$user) {
            $_SESSION['admin_error'] = 'Inicia sesión para continuar.';
            $base = rtrim($this->config['app']['base_url'] ?? '', '/');
            header("Location: {$base}/?r=admin_login");
            exit;
        }

        // Rol del usuario en minúscula para comparar
        $rol = strtolower($user['rol'] ?? '');
        $rolesPermitidos = array_map('strtolower', $rolesPermitidos);

        // ❌ Si el rol NO está permitido → redirigir
        if (!in_array($rol, $rolesPermitidos, true)) {
            $_SESSION['admin_error'] = 'No tienes permisos para acceder a este módulo.';
            $base = rtrim($this->config['app']['base_url'] ?? '', '/');
            header("Location: {$base}/?r=dashboard"); 
            exit;
        }
    }


    /* ============================
       Vistas
       ============================ */

    public function index(): void
    {
        $this->ensureAdmin(['Admin']); 

        $this->render('admin/usuarios/index', [
            'page_title' => 'Usuarios',
            'esAdmin'    => true,
            'extra_css'  => [$this->config['app']['base_url'] . '/assets/css/admin_usuarios.css?v=6'],
            'extra_js'   => [$this->config['app']['base_url'] . '/assets/js/admin_usuarios.js?v=6'],
        ]);
    }

    /* ============================
       API CRUD
       ============================ */
    public function api(): void
    {
         $this->ensureAdmin(['Admin']); 
        header('Content-Type: application/json; charset=utf-8');

        try {
            $m      = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_REQUEST['action'] ?? '';

            /* LIST */
            if ($m === 'GET' && $action === 'list') {
                try {
                    $q    = trim((string)($_GET['q'] ?? ''));
                    $page = max(1, (int)($_GET['page'] ?? 1));
                    $per  = max(1, min(50, (int)($_GET['per'] ?? 10)));

                    $data = $this->Usuario->listar($q, $page, $per);
                    $this->json($data);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            /* GET ONE */
            if ($m === 'GET' && $action === 'get') {
                try {
                    $id = (int)($_GET['id'] ?? 0);
                    if ($id <= 0) { $this->fail('ID inválido'); return; }
                    $row = $this->Usuario->obtener($id);
                    $this->json(['data' => $row]);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            /* CREATE */
            if ($m === 'POST' && $action === 'create') {
                try {
                    $d  = $this->sanitize($_POST, true);
                    $id = $this->Usuario->crear($d);

                    // correo de verificación
                    try {
                        $u = $this->Usuario->getById($id);
                        $this->sendVerificationEmail($u);
                    } catch (\Throwable $mailEx) {
                        // no rompemos la creación si email falla
                    }

                    $this->ok(['id' => $id], 201);
                } catch (\Throwable $ex) {
                    $this->fail($this->friendlyDbError($ex), 500);
                }
                return;
            }

            /* UPDATE */
            if ($m === 'POST' && $action === 'update') {
                try {
                    $id = (int)($_POST['id_usuario'] ?? 0);
                    if ($id <= 0) { $this->fail('ID inválido'); return; }

                    $d  = $this->sanitize($_POST, false);

                    // ¿cambió correo?
                    $uOld = $this->Usuario->getById($id);
                    $emailChanged = $uOld && !empty($d['correo'])
                        && strcasecmp($d['correo'], $uOld['correo'] ?? '') !== 0;
                    if ($emailChanged) { $d['reset_verif'] = 1; }

                    $this->Usuario->actualizar($id, $d);

                    if ($emailChanged) {
                        try {
                            $u = $this->Usuario->getById($id);
                            $this->sendVerificationEmail($u);
                        } catch (\Throwable $mailEx) {
                            // ignorar fallo de correo en update
                        }
                    }

                    $this->ok();
                } catch (\Throwable $ex) {
                    $this->fail($this->friendlyDbError($ex), 500);
                }
                return;
            }

            /* DELETE */
            if ($m === 'POST' && $action === 'delete') {
                try {
                    $id = (int)($_POST['id_usuario'] ?? 0);
                    if ($id <= 0) { $this->fail('ID inválido'); return; }
                    $this->Usuario->eliminar($id);
                    $this->ok();
                } catch (\Throwable $ex) {
                    $this->fail($this->friendlyDbError($ex), 500);
                }
                return;
            }

            /* TOGGLE ACTIVO/INACTIVO (rota password al desactivar) */
            if ($m === 'POST' && $action === 'toggle') {
                try {
                    $id = (int)($_POST['id_usuario'] ?? 0);
                    if ($id <= 0) { $this->fail('ID inválido'); return; }

                    // Debe devolver ['estado'=>'Activo'|'Inactivo','rotated'=>bool]
                    $res = $this->Usuario->toggleEstado($id);
                    $msg = ($res['estado'] === 'Activo')
                        ? 'Usuario activado.'
                        : 'Usuario desactivado. La contraseña fue rotada por seguridad.';

                    $this->ok([
                        'estado'  => $res['estado'],
                        'rotated' => (bool)($res['rotated'] ?? false),
                        'msg'     => $msg
                    ]);
                } catch (\Throwable $e) {
                    $this->fail($this->friendlyDbError($e), 500);
                }
                return;
            }

            $this->fail('Acción no válida', 400);
        } catch (\Throwable $e) {
            $this->fail($e->getMessage(), 500);
        }
    }

    /* ============================
       Verificación de correo
       ============================ */

    public function verifyEmail(): void
    {
        if (session_status() !== \PHP_SESSION_ACTIVE) session_start();

        $token = $_GET['token'] ?? '';
        $ok    = false;
        try {
            $ok = $token ? $this->Usuario->setEmailVerifiedByToken($token) : false;
        } catch (\Throwable $e) {
            // ignora; ok=false
        }

        $_SESSION['flash_public'] = [
            'type' => $ok ? 'success' : 'danger',
            'msg'  => $ok ? 'Correo verificado correctamente. Ya puedes ingresar.'
                          : 'Token inválido o vencido.',
        ];

        $base = rtrim($this->config['app']['base_url'] ?? '', '/');
        header("Location: {$base}/?r=home"); // o ?r=login
        exit;
    }

    public function resendVerification(): void
    {
           $this->ensureAdmin(['Admin']); 
        if (session_status() !== \PHP_SESSION_ACTIVE) session_start();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash'] = ['type'=>'danger','msg'=>'ID inválido'];
            $this->redirect('/?r=admin_usuarios');
        }

        try {
            $u = $this->Usuario->getById($id);
            if (!$u) {
                $_SESSION['flash'] = ['type'=>'danger','msg'=>'Usuario no encontrado'];
                $this->redirect('/?r=admin_usuarios');
            }

            // regenera si no existe o venció
            if (empty($u['correo_verificacion_token']) ||
                (!empty($u['correo_verificacion_expira']) && strtotime($u['correo_verificacion_expira']) <= time())) {
                $u = $this->Usuario->resetVerificationToken($id);
            }

            $this->sendVerificationEmail($u);

            $_SESSION['flash'] = [
                'type'=>'success',
                'msg'=>'Correo de verificación reenviado (si el SMTP está configurado).'
            ];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = ['type'=>'danger','msg'=>'No se pudo reenviar la verificación.'];
        }

        $this->redirect('/?r=admin_usuarios');
    }

    /* ============================
       Utilidades privadas
       ============================ */

    private function sendVerificationEmail(array $u): void
    {
        if (empty($u['correo']) || empty($u['correo_verificacion_token'])) return;

        $base = rtrim($this->config['app']['base_url'] ?? '', '/');
        $link = $base . '/?r=admin_usuarios_verify_email&token=' . $u['correo_verificacion_token'];

        $nombre = trim(($u['nombres'] ?? '') . ' ' . ($u['apellidos'] ?? '')) ?: ($u['usuario'] ?? 'Usuario');

        // 👉 AHORA usamos ServicioCorreo
        try {
            $this->correo->enviarVerificacion($u['correo'], $nombre, $link);
        } catch (\Throwable $e) {
            // opcional: loguear error
            // error_log('Error enviando verificación: ' . $e->getMessage());
        }
    }

    private function sanitize(array $in, bool $creating): array
    {
        $nameRe = '/^[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,60}$/u';
        $userRe = '/^[A-Za-z0-9._-]{3,30}$/';

        $rolIn    = strtolower(trim($in['rol'] ?? 'empleado'));
        $estadoIn = strtolower(trim($in['estado'] ?? 'activo'));

        $rolOk    = in_array($rolIn, ['admin','empleado','cajero'], true) ? $rolIn : 'empleado';
        $estadoOk = in_array($estadoIn, ['activo','inactivo'], true) ? $estadoIn : 'activo';

        $rolLabel    = ['admin'=>'Admin','empleado'=>'Empleado','cajero'=>'Cajero'][$rolOk];
        $estadoLabel = ($estadoOk === 'inactivo') ? 'Inactivo' : 'Activo';

        $nombres   = trim($in['nombres'] ?? '');
        $apellidos = trim($in['apellidos'] ?? '');
        $usuario   = trim($in['usuario'] ?? '');
        $correo    = trim($in['correo'] ?? '');
        $password  = trim($in['password'] ?? '');

        if (!preg_match($nameRe, $nombres))   throw new \Exception('Nombres inválidos');
        if (!preg_match($nameRe, $apellidos)) throw new \Exception('Apellidos inválidos');
        if (!preg_match($userRe, $usuario))   throw new \Exception('Usuario inválido');
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) throw new \Exception('Correo inválido');
        if ($creating && strlen($password) < 8) throw new \Exception('Password muy corto (mín. 8)');

        return [
            'usuario'   => $usuario,
            'password'  => $password,
            'rol'       => $rolLabel,
            'nombres'   => $nombres,
            'apellidos' => $apellidos,
            'correo'    => $correo,
            'estado'    => $estadoLabel,
        ];
    }

    private function friendlyDbError(\Throwable $e): string
    {
        if ($e instanceof \PDOException && $e->getCode() === '23000') {
            $msg = $e->getMessage();
            if (stripos($msg, 'usuario') !== false) return 'Ese usuario ya existe.';
            if (stripos($msg, 'correo')  !== false) return 'Ese correo ya está registrado.';
            if (stripos($msg, 'duplicate') !== false || stripos($msg, '1062') !== false) {
                return 'Datos duplicados (usuario o correo).';
            }
        }
        return $e->getMessage();
    }
}
