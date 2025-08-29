<?php
namespace Controllers;

use Core\Controller;
use Models\UsuarioAdmin;
use Services\Mailer;

final class AdminUsuariosController extends Controller {
    private UsuarioAdmin $Usuario;
    private Mailer $mailer;

    public function __construct(array $config) {
        parent::__construct($config);
        $this->Usuario = new UsuarioAdmin($config);
        $this->mailer  = new Mailer($config);
    }

    public function index(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['admin'])) { $_SESSION['admin_error']='Inicia sesión para continuar.'; $this->redirect('/?r=admin_login'); }
        $this->render('admin/usuarios/index', ['titulo'=>'Usuarios','esAdmin'=>true]);
    }

    /** API CRUD */
    public function api(): void {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $m = $_SERVER['REQUEST_METHOD'];
            $action = $_REQUEST['action'] ?? '';

            if ($m==='GET' && $action==='list') {
                $q = trim($_GET['q'] ?? ''); $page=max(1,(int)($_GET['page']??1)); $per=max(1,min(50,(int)($_GET['per']??10)));
                echo json_encode($this->Usuario->listar($q,$page,$per)); return;
            }

            if ($m==='GET' && $action==='get') {
                $id = (int)($_GET['id'] ?? 0);
                echo json_encode(['data'=>$this->Usuario->obtener($id)]); return;
            }

            if ($m==='POST' && $action==='create') {
                $d = $this->sanitize($_POST, true);
                $id = $this->Usuario->crear($d);

                // enviar verificación de correo
                $u = $this->Usuario->getById($id);
                $this->sendVerificationEmail($u);

                echo json_encode(['ok'=>true,'id'=>$id]); return;
            }

            if ($m==='POST' && $action==='update') {
                $id = (int)($_POST['id_usuario'] ?? 0);
                $d  = $this->sanitize($_POST, false);

                // ¿cambió el correo?
                $uOld = $this->Usuario->getById($id);
                $emailChanged = $uOld && !empty($d['correo']) && strtolower($d['correo']) !== strtolower($uOld['correo']);
                if ($emailChanged) { $d['reset_verif'] = 1; }

                $this->Usuario->actualizar($id,$d);

                if ($emailChanged) {
                    $u = $this->Usuario->getById($id);
                    $this->sendVerificationEmail($u);
                }

                echo json_encode(['ok'=>true]); return;
            }

            if ($m==='POST' && $action==='delete') {
                $id = (int)($_POST['id_usuario'] ?? 0);
                $this->Usuario->eliminar($id);
                echo json_encode(['ok'=>true]); return;
            }

            if ($m==='POST' && $action==='toggle') {
                $id = (int)($_POST['id_usuario'] ?? 0);
                $estado = $this->Usuario->toggleEstado($id);
                echo json_encode(['ok'=>true,'estado'=>$estado]); return;
            }

            http_response_code(400);
            echo json_encode(['ok'=>false,'msg'=>'Acción no válida']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
        }
    }

    public function verifyEmail(): void {
        $token = $_GET['token'] ?? '';
        $ok = $token ? $this->Usuario->setEmailVerifiedByToken($token) : false;
        $msg = $ok ? 'Correo verificado correctamente.' : 'Token inválido o vencido.';
        // render simple en layout admin (o redirige con flash)
        $this->render('admin/usuarios/verify_result', ['titulo'=>'Verificación de correo','esAdmin'=>true,'msg'=>$msg]);
    }

    public function resendVerification(): void {
        $id = (int)($_GET['id'] ?? 0);
        $u = $this->Usuario->getById($id);
        if ($u) $this->sendVerificationEmail($u);
        $this->redirect('/?r=admin_usuarios');
    }

    private function sendVerificationEmail(array $u): void {
        if (empty($u['correo']) || empty($u['correo_verificacion_token'])) return;
        $base = rtrim($this->config['app']['base_url'] ?? '', '/');
        $link = $base.'/?r=admin_usuarios_verify_email&token='.$u['correo_verificacion_token'];
        $html = "<p>Hola {$u['nombres']} {$u['apellidos']},</p>
                 <p>Confirma tu correo haciendo clic aquí:</p>
                 <p><a href=\"$link\">Verificar correo</a></p>";
        $this->mailer->send($u['correo'], $u['nombres'] ?? $u['usuario'], 'Verifica tu correo', $html);
    }

    private function sanitize(array $in, bool $creating): array {
        $nameRe = '/^[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,60}$/u';
        $userRe = '/^[A-Za-z0-9._-]{3,30}$/';
        $rol    = in_array(($in['rol'] ?? ''), ['admin','empleado'], true) ? $in['rol'] : 'empleado';
        $estado = in_array(($in['estado'] ?? ''), ['activo','inactivo'], true) ? $in['estado'] : 'activo';

        $nombres   = trim($in['nombres'] ?? '');
        $apellidos = trim($in['apellidos'] ?? '');
        $usuario   = trim($in['usuario'] ?? '');
        $correo    = trim($in['correo'] ?? '');
        $password  = trim($in['password'] ?? '');

        if (!preg_match($nameRe, $nombres))   throw new \Exception('Nombres inválidos');
        if (!preg_match($nameRe, $apellidos)) throw new \Exception('Apellidos inválidos');
        if (!preg_match($userRe, $usuario))   throw new \Exception('Usuario inválido');
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) throw new \Exception('Correo inválido');
        if ($creating && strlen($password) < 6) throw new \Exception('Password muy corto');

        return [
            'usuario'   => $usuario,
            'password'  => $password, // si viene vacío en update, el modelo lo ignora
            'rol'       => $rol,
            'nombres'   => $nombres,
            'apellidos' => $apellidos,
            'correo'    => $correo,
            'estado'    => $estado,
        ];
    }
}
