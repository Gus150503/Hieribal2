<?php
namespace Controllers;

use Core\Controller;
use Models\UsuarioAdmin;
use Services\Mailer;

final class AdminUsuariosController extends Controller
{
    private UsuarioAdmin $Usuario;
    private Mailer $mailer;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->Usuario = new UsuarioAdmin($config);
        $this->mailer  = new Mailer($config);
    }

    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['admin'])) {
            $_SESSION['admin_error'] = 'Inicia sesión para continuar.';
            header('Location: /?r=admin_login'); exit;
        }
            $this->render('admin/usuarios/index', [
            'page_title' => 'Usuarios',
            'esAdmin'    => true,
            'extra_css'  => [$this->config['app']['base_url'] . '/assets/css/admin_usuarios.css?v=5'],
            'extra_js'   => [$this->config['app']['base_url'] . '/assets/js/admin_usuarios.js?v=5'],
            ]);
    }

    /** ==== API CRUD ==== */
    public function api(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $m      = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_REQUEST['action'] ?? '';

            /* LIST */
            if ($m === 'GET' && $action === 'list') {
                $q    = trim($_GET['q'] ?? '');
                $page = max(1, (int)($_GET['page'] ?? 1));
                $per  = max(1, min(50, (int)($_GET['per'] ?? 10)));
                echo json_encode($this->Usuario->listar($q, $page, $per));
                return;
            }

            /* GET ONE */
            if ($m === 'GET' && $action === 'get') {
                $id = (int)($_GET['id'] ?? 0);
                echo json_encode(['data' => $this->Usuario->obtener($id)]);
                return;
            }

            /* CREATE */
            if ($m === 'POST' && $action === 'create') {
                try {
                    $d  = $this->sanitize($_POST, true);
                    $id = $this->Usuario->crear($d);

                    // correo de verificación
                    $u = $this->Usuario->getById($id);
                    $this->sendVerificationEmail($u);

                    echo json_encode(['ok' => true, 'id' => $id]);
                } catch (\Throwable $ex) {
                    echo json_encode(['ok' => false, 'msg' => $this->friendlyDbError($ex)]);
                }
                return;
            }

            /* UPDATE */
            if ($m === 'POST' && $action === 'update') {
                try {
                    $id = (int)($_POST['id_usuario'] ?? 0);
                    $d  = $this->sanitize($_POST, false);

                    // ¿cambió correo?
                    $uOld = $this->Usuario->getById($id);
                    $emailChanged = $uOld && !empty($d['correo'])
                        && strcasecmp($d['correo'], $uOld['correo'] ?? '') !== 0;
                    if ($emailChanged) { $d['reset_verif'] = 1; }

                    $this->Usuario->actualizar($id, $d);

                    if ($emailChanged) {
                        $u = $this->Usuario->getById($id);
                        $this->sendVerificationEmail($u);
                    }

                    echo json_encode(['ok' => true]);
                } catch (\Throwable $ex) {
                    echo json_encode(['ok' => false, 'msg' => $this->friendlyDbError($ex)]);
                }
                return;
            }

            /* DELETE */
            if ($m === 'POST' && $action === 'delete') {
                $id = (int)($_POST['id_usuario'] ?? 0);
                $this->Usuario->eliminar($id);
                echo json_encode(['ok' => true]);
                return;
            }

            /* TOGGLE ACTIVO/INACTIVO (rota password al desactivar) */
            if ($m === 'POST' && $action === 'toggle') {
                $id = (int)($_POST['id_usuario'] ?? 0);
                if ($id <= 0) { echo json_encode(['ok' => false, 'msg' => 'ID inválido']); return; }

                try {
                    // Debe devolver ['estado'=>'Activo'|'Inactivo','rotated'=>bool]
                    $res = $this->Usuario->toggleEstado($id);
                    $msg = ($res['estado'] === 'Activo')
                        ? 'Usuario activado.'
                        : 'Usuario desactivado. La contraseña fue rotada por seguridad.';

                    echo json_encode([
                        'ok'      => true,
                        'estado'  => $res['estado'],
                        'rotated' => (bool)($res['rotated'] ?? false),
                        'msg'     => $msg
                    ]);
                } catch (\Throwable $e) {
                    http_response_code(500);
                    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
                }
                return;
            }

            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Acción no válida']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    /** ==== Verificación de correo por token ==== */
    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';
        $ok    = $token ? $this->Usuario->setEmailVerifiedByToken($token) : false;
        $msg   = $ok ? 'Correo verificado correctamente.' : 'Token inválido o vencido.';
        $this->render('admin/usuarios/verify_result', [
            'titulo'  => 'Verificación de correo',
            'esAdmin' => true,
            'msg'     => $msg
        ]);
    }

    /** ==== Reenviar verificación ==== */
    public function resendVerification(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['admin'])) {
            $_SESSION['admin_error'] = 'Inicia sesión para continuar.';
            $this->redirect('/?r=admin_login');
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { $_SESSION['flash'] = ['type'=>'danger','msg'=>'ID inválido']; $this->redirect('/?r=admin_usuarios'); }

        $u = $this->Usuario->getById($id);
        if (!$u) { $_SESSION['flash'] = ['type'=>'danger','msg'=>'Usuario no encontrado']; $this->redirect('/?r=admin_usuarios'); }

        // regenera si no existe o venció
        if (empty($u['correo_verificacion_token']) ||
            (!empty($u['correo_verificacion_expira']) && strtotime($u['correo_verificacion_expira']) <= time())) {
            $u = $this->Usuario->resetVerificationToken($id);
        }

        $this->sendVerificationEmail($u);

        $_SESSION['flash'] = ['type'=>'success','msg'=>'Correo de verificación reenviado (si el SMTP está configurado).'];
        $this->redirect('/?r=admin_usuarios');
    }

    /** ==== Util: enviar correo de verificación ==== */
    private function sendVerificationEmail(array $u): void
    {
        if (empty($u['correo']) || empty($u['correo_verificacion_token'])) return;
        $base = rtrim($this->config['app']['base_url'] ?? '', '/');
        $link = $base . '/?r=admin_usuarios_verify_email&token=' . $u['correo_verificacion_token'];
        $html = "<p>Hola {$u['nombres']} {$u['apellidos']},</p>
                 <p>Confirma tu correo haciendo clic aquí:</p>
                 <p><a href=\"{$link}\">Verificar correo</a></p>";
        $this->mailer->send($u['correo'], $u['nombres'] ?: ($u['usuario'] ?? 'Usuario'), 'Verifica tu correo', $html);
    }

    /** ==== Sanitización y validación de entrada ==== */
    private function sanitize(array $in, bool $creating): array
    {
        $nameRe = '/^[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,60}$/u';
        $userRe = '/^[A-Za-z0-9._-]{3,30}$/';

        $rolIn    = strtolower(trim($in['rol'] ?? 'empleado'));
        $estadoIn = strtolower(trim($in['estado'] ?? 'activo'));

        // Normaliza valores a la forma que usas en BD (Title Case)
        $rolOk    = in_array($rolIn, ['admin','empleado'], true) ? $rolIn : 'empleado';
        $estadoOk = in_array($estadoIn, ['activo','inactivo'], true) ? $estadoIn : 'activo';

        $nombres   = trim($in['nombres']   ?? '');
        $apellidos = trim($in['apellidos'] ?? '');
        $usuario   = trim($in['usuario']   ?? '');
        $correo    = trim($in['correo']    ?? '');
        $password  = trim($in['password']  ?? '');

        if (!preg_match($nameRe, $nombres))    throw new \Exception('Nombres inválidos.');
        if (!preg_match($nameRe, $apellidos))  throw new \Exception('Apellidos inválidos.');
        if (!preg_match($userRe, $usuario))    throw new \Exception('Usuario inválido (3-30, letras/números . _ -).');
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) throw new \Exception('Correo inválido.');

        // Password mínimo 8 en creación; en update sólo si viene (no obligatorio)
        if ($creating) {
            if (strlen($password) < 8) throw new \Exception('La contraseña debe tener al menos 8 caracteres.');
        } elseif ($password !== '' && strlen($password) < 8) {
            throw new \Exception('La contraseña debe tener al menos 8 caracteres.');
        }

        return [
            'usuario'   => $usuario,
            'password'  => $password, // en update, si viene vacío el modelo lo ignora
            'rol'       => ($rolOk === 'admin') ? 'Admin' : 'Empleado',
            'nombres'   => $nombres,
            'apellidos' => $apellidos,
            'correo'    => $correo,
            'estado'    => ($estadoOk === 'inactivo') ? 'Inactivo' : 'Activo',
        ];
    }

    /** ==== Mapea errores de BD a mensajes amigables ==== */
    private function friendlyDbError(\Throwable $e): string
    {
        if ($e instanceof \PDOException && $e->getCode() === '23000') {
            // 23000 = violación de restricción (UNIQUE, FK, etc.)
            $msg = $e->getMessage();
            // Intenta detectar por nombre de índice o columna que aparece en el mensaje
            if (stripos($msg, 'usuario') !== false) return 'Ese usuario ya existe.';
            if (stripos($msg, 'correo')  !== false) return 'Ese correo ya está registrado.';
            if (stripos($msg, 'duplicate') !== false || stripos($msg, '1062') !== false) {
                return 'Datos duplicados (usuario o correo).';
            }
        }
        return $e->getMessage();
    }

    
}
