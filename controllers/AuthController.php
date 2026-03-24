<?php

namespace Controllers;

use Core\Controller;
use Models\Cliente;
use Google\Client as GoogleClient;
use Google\Service\Oauth2 as GoogleOauth2;
use Services\ServicioCorreo;

final class AuthController extends Controller
{
    private Cliente $clientes;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->clientes = new Cliente($config);
    }

    /* ================= Helpers comunes ================= */

    protected function isAjax(): bool
    {
        return (
            isset($_SERVER['HTTP_ACCEPT']) &&
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
        ) || (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ================= LOGIN ================= */

    /** Muestra formulario de login */
    public function loginForm(): void
    {
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        $msg = $_SESSION['msg'] ?? null;
        unset($_SESSION['msg']);

        $this->render(
            'auth/login',
            ['error' => $error, 'msg' => $msg, 'full' => true],
            'Login'
        );
    }

    /** Inicia flujo OAuth con Google */
    public function googleStart(): void
    {
        $g = $this->config['google'];

        $client = new GoogleClient();
        $client->setClientId(trim($g['client_id']));
        $client->setClientSecret(trim($g['client_secret']));
        $client->setRedirectUri(trim($g['redirect_uri']));
        $client->setAccessType('offline');
        $client->setPrompt('select_account');
        $client->addScope(['email', 'profile']);

        header('Location: ' . $client->createAuthUrl());
        exit;
    }

    /** Callback de Google OAuth */
    public function googleCallback(): void
    {
        $g = $this->config['google'];

        $client = new GoogleClient();
        $client->setClientId(trim($g['client_id']));
        $client->setClientSecret(trim($g['client_secret']));
        $client->setRedirectUri(trim($g['redirect_uri']));

        if (empty($_GET['code'])) {
            $_SESSION['error'] = 'Error en autenticación con Google.';
            $this->redirect('/?r=login');
        }

        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (!empty($token['error'])) {
            $_SESSION['error'] = 'No se pudo obtener token de Google.';
            $this->redirect('/?r=login');
        }

        $client->setAccessToken($token);

        $oauth2 = new GoogleOauth2($client);
        $me = $oauth2->userinfo->get();

        $email = $me->email ?? '';
        $name  = $me->name ?? '';

        if ($email === '') {
            $_SESSION['error'] = 'Google no devolvió un correo válido.';
            $this->redirect('/?r=login');
        }

        // Buscar/crear cuenta mínima para Google
        $c = $this->clientes->buscarPorCorreo($email);
        if (!$c) {
            $this->clientes->crearDesdeGoogle($name, $email);
            $c = $this->clientes->buscarPorCorreo($email);

            if (!$c) {
                $_SESSION['error'] = 'No se pudo crear el perfil con Google.';
                $this->redirect('/?r=login');
            }
        }

        session_regenerate_id(true);

        $_SESSION['cliente'] = [
            'id_cliente' => (int)($c['id_cliente'] ?? 0),
            'nombres'    => trim((string)($c['nombres'] ?? '')) !== '' ? $c['nombres'] : $name,
            'correo'     => $c['correo'] ?? '',
            'cedula'     => $c['cedula'] ?? '',
            'apellidos'  => $c['apellidos'] ?? '',
            'telefono'   => $c['telefono'] ?? '',

            'falta_cedula'    => empty(trim((string)($c['cedula'] ?? ''))),
            'falta_apellidos' => empty(trim((string)($c['apellidos'] ?? ''))),
            'falta_telefono'  => empty(trim((string)($c['telefono'] ?? ''))),
        ];

        $_SESSION['force_profile'] = (
            $_SESSION['cliente']['falta_cedula'] ||
            $_SESSION['cliente']['falta_apellidos'] ||
            $_SESSION['cliente']['falta_telefono']
        );

        $this->redirect('/?r=home');
    }

    /** Procesa login tradicional */
    public function login(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/?r=login');
        }

        unset($_SESSION['cliente'], $_SESSION['force_profile']);

        $email = trim((string)$this->post('correo'));
        $pass  = (string)$this->post('password');

        if ($email === '' || $pass === '') {
            $_SESSION['error'] = 'Correo y contraseña son obligatorios.';
            $this->redirect('/?r=login');
        }

        $cliente = $this->clientes->verificarPassword($email, $pass);
        if ($cliente === false) {
            $_SESSION['error'] = 'Correo o contraseña incorrectos.';
            $this->redirect('/?r=login');
        }

        if ((int)($cliente['verificado'] ?? 1) !== 1) {
            $_SESSION['error'] = '⚠️ Debes verificar tu cuenta desde tu correo.';
            $this->redirect('/?r=login');
        }

        session_regenerate_id(true);

        $_SESSION['cliente'] = [
            'id_cliente' => (int)($cliente['id_cliente'] ?? 0),
            'nombres'    => $cliente['nombres'] ?? '',
            'correo'     => $cliente['correo'] ?? '',
            'cedula'     => $cliente['cedula'] ?? '',
            'apellidos'  => $cliente['apellidos'] ?? '',
            'telefono'   => $cliente['telefono'] ?? '',

            'falta_cedula'    => empty(trim((string)($cliente['cedula'] ?? ''))),
            'falta_apellidos' => empty(trim((string)($cliente['apellidos'] ?? ''))),
            'falta_telefono'  => empty(trim((string)($cliente['telefono'] ?? ''))),
        ];

        $_SESSION['force_profile'] = (
            $_SESSION['cliente']['falta_cedula'] ||
            $_SESSION['cliente']['falta_apellidos'] ||
            $_SESSION['cliente']['falta_telefono']
        );

        $this->redirect('/?r=home');
    }

    /** Logout */
    public function logout(): void
    {
        unset($_SESSION['cliente'], $_SESSION['force_profile']);
        session_regenerate_id(true);
        $this->redirect('/?r=login');
    }

    /* ================= REGISTRO ================= */

    /** Endpoint AJAX: verifica existencia de correo/cedula */
    public function checkField(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $type   = $_GET['type'] ?? '';
        $value  = $_GET['value'] ?? '';
        $exists = false;

        if ($type === 'correo' && $this->clientes->correoExiste($value)) {
            $exists = true;
        }

        if ($type === 'cedula' && $this->clientes->cedulaExiste($value)) {
            $exists = true;
        }

        echo json_encode(['exists' => $exists], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Muestra formulario de registro */
    public function registroForm(): void
    {
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        $this->render(
            'auth/registro',
            [
                'error'      => $error,
                'full'       => true,
                'carga_swal' => true,
                'extra_js'   => [],
            ],
            'Registro'
        );
    }

    /** Procesa registro (con verificación por email) */
    public function registrar(): void
    {
        if (!$this->isPost()) {
            if ($this->isAjax()) {
                $this->json(['ok' => false, 'msg' => 'Método inválido'], 405);
            }
            $this->redirect('/?r=register');
        }

        $data = [
            'cedula'    => trim((string)$this->post('cedula')),
            'nombres'   => trim((string)$this->post('nombres')),
            'apellidos' => trim((string)$this->post('apellidos')),
            'telefono'  => trim((string)$this->post('telefono')),
            'correo'    => trim((string)$this->post('correo')),
            'password'  => (string)$this->post('password'),
        ];

        $data['cedula'] = preg_replace('/\D/', '', $data['cedula'] ?? '');

        if ($data['cedula'] === '' || $data['nombres'] === '' || $data['correo'] === '' || $data['password'] === '') {
            if ($this->isAjax()) {
                $this->json(['ok' => false, 'msg' => 'Completa los campos obligatorios.'], 400);
            }
            $_SESSION['error'] = 'Completa los campos obligatorios.';
            $this->redirect('/?r=register');
        }

        if (!preg_match('/^(\d{8}|\d{10})$/', $data['cedula'])) {
            if ($this->isAjax()) {
                $this->json(['ok' => false, 'msg' => 'La cédula debe tener 8 o 10 dígitos.'], 400);
            }
            $_SESSION['error'] = 'La cédula debe tener 8 o 10 dígitos.';
            $this->redirect('/?r=register');
        }

        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            if ($this->isAjax()) {
                $this->json(['ok' => false, 'msg' => 'Correo inválido.'], 400);
            }
            $_SESSION['error'] = 'Correo inválido.';
            $this->redirect('/?r=register');
        }

        if ($this->clientes->correoExiste($data['correo'])) {
            if ($this->isAjax()) {
                $this->json(['ok' => false, 'msg' => 'El correo ya está registrado.'], 409);
            }
            $_SESSION['error'] = 'El correo ya está registrado.';
            $this->redirect('/?r=register');
        }

        if ($this->clientes->cedulaExiste($data['cedula'])) {
            if ($this->isAjax()) {
                $this->json(['ok' => false, 'msg' => 'La cédula ya está registrada.'], 409);
            }
            $_SESSION['error'] = 'La cédula ya está registrada.';
            $this->redirect('/?r=register');
        }

        $token = bin2hex(random_bytes(32));
        $this->clientes->crearConVerificacion($data, $token);

        $base = rtrim($this->config['app']['base_url'], '/');
        $link = $base . '/?r=verify&token=' . urlencode($token);

        $mailer = new ServicioCorreo($this->config);
        $mailer->enviarVerificacion($data['correo'], $data['nombres'], $link);

        if ($this->isAjax()) {
            $this->json(['ok' => true]);
        }

        $_SESSION['msg'] = '✅ Perfil creado con éxito. Te enviamos un correo para activar tu cuenta.';
        $this->redirect('/?r=login');
    }

    /** Verifica cuenta por token */
    public function verify(): void
    {
        $token = $_GET['token'] ?? '';

        if ($token === '') {
            $_SESSION['error'] = 'Enlace inválido.';
            $this->redirect('/?r=login');
        }

        if ($this->clientes->marcarVerificadoPorToken($token)) {
            $_SESSION['msg'] = '✅ Tu cuenta ha sido verificada. Ya puedes iniciar sesión.';
        } else {
            $_SESSION['error'] = 'El enlace no es válido o ya fue utilizado.';
        }

        $this->redirect('/?r=login');
    }

    /* ================= PERFIL OBLIGATORIO (GOOGLE) ================= */

    /**
     * Endpoint AJAX para guardar cédula + apellidos + teléfono
     * Ruta sugerida: ?r=completar_perfil
     */
    public function completarPerfil(): void
    {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->json(['ok' => false, 'msg' => 'Método no permitido.'], 405);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['cliente']['id_cliente'])) {
            $this->json(['ok' => false, 'msg' => 'Sesión no válida.'], 401);
        }

        $idCliente = (int) $_SESSION['cliente']['id_cliente'];

        $faltaCedula = !empty($_SESSION['cliente']['falta_cedula']);
        $faltaApe    = !empty($_SESSION['cliente']['falta_apellidos']);
        $faltaTel    = !empty($_SESSION['cliente']['falta_telefono']);

        $cedulaPost    = trim((string) ($this->post('cedula') ?? ''));
        $apellidosPost = trim((string) ($this->post('apellidos') ?? ''));
        $telefonoPost  = trim((string) ($this->post('telefono') ?? ''));

        $cedula = $faltaCedula
            ? $cedulaPost
            : trim((string) ($_SESSION['cliente']['cedula'] ?? ''));

        $apellidos = $faltaApe
            ? $apellidosPost
            : trim((string) ($_SESSION['cliente']['apellidos'] ?? ''));

        $telefono = $faltaTel
            ? $telefonoPost
            : trim((string) ($_SESSION['cliente']['telefono'] ?? ''));

        if ($faltaCedula) {
            if ($cedula === '') {
                $this->json(['ok' => false, 'msg' => 'Ingresa tu cédula.'], 400);
            }

            if (!preg_match('/^\d+$/', $cedula)) {
                $this->json(['ok' => false, 'msg' => 'La cédula solo debe contener números.'], 400);
            }

            if (!preg_match('/^(\d{8}|\d{10})$/', $cedula)) {
                $this->json(['ok' => false, 'msg' => 'La cédula debe tener 8 o 10 dígitos.'], 400);
            }

            if ($this->clientes->cedulaExisteEnOtro($cedula, $idCliente)) {
                $this->json(['ok' => false, 'msg' => 'La cédula ya está registrada en otro usuario.'], 409);
            }
        }

        if ($faltaApe) {
            if ($apellidos === '' || mb_strlen($apellidos) < 2) {
                $this->json(['ok' => false, 'msg' => 'Ingresa tus apellidos (mínimo 2 letras).'], 400);
            }

            if (!preg_match('/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]+$/u', $apellidos)) {
                $this->json(['ok' => false, 'msg' => 'Apellidos inválidos. Solo letras y espacios.'], 400);
            }

            $apellidos = preg_replace('/\s{2,}/', ' ', $apellidos);
        }

        if ($faltaTel) {
            if ($telefono === '') {
                $this->json(['ok' => false, 'msg' => 'Ingresa tu teléfono.'], 400);
            }

            if (!preg_match('/^\d+$/', $telefono)) {
                $this->json(['ok' => false, 'msg' => 'El teléfono solo debe contener números.'], 400);
            }

            if (!preg_match('/^\d{7,15}$/', $telefono)) {
                $this->json(['ok' => false, 'msg' => 'El teléfono debe tener entre 7 y 15 dígitos.'], 400);
            }
        }

        $ok = $this->clientes->actualizarPerfilObligatorio($idCliente, $cedula, $apellidos, $telefono);

        if (!$ok) {
            $this->json(['ok' => false, 'msg' => 'No se pudo guardar. Intenta de nuevo.'], 500);
        }

        $_SESSION['cliente']['cedula'] = $cedula;
        $_SESSION['cliente']['apellidos'] = $apellidos;
        $_SESSION['cliente']['telefono'] = $telefono;

        $_SESSION['cliente']['falta_cedula'] = empty(trim($cedula));
        $_SESSION['cliente']['falta_apellidos'] = empty(trim($apellidos));
        $_SESSION['cliente']['falta_telefono'] = empty(trim($telefono));

        $_SESSION['force_profile'] = (
            $_SESSION['cliente']['falta_cedula'] ||
            $_SESSION['cliente']['falta_apellidos'] ||
            $_SESSION['cliente']['falta_telefono']
        );

        $this->json(['ok' => true, 'msg' => 'Perfil actualizado']);
    }

    /**
     * Endpoint antiguo: solo cédula
     */
    public function completarCedula(): void
    {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->json(['ok' => false, 'msg' => 'Método no permitido.'], 405);
        }

        if (empty($_SESSION['cliente']['id_cliente'])) {
            $this->json(['ok' => false, 'msg' => 'Sesión no válida.'], 401);
        }

        $idCliente = (int)$_SESSION['cliente']['id_cliente'];

        $raw = (string)$this->post('cedula');
        $cedula = preg_replace('/\D/', '', $raw ?? '');

        if (!preg_match('/^(\d{8}|\d{10})$/', $cedula)) {
            $this->json(['ok' => false, 'msg' => 'La cédula debe tener 8 o 10 dígitos.'], 400);
        }

        if ($this->clientes->cedulaExisteEnOtro($cedula, $idCliente)) {
            $this->json(['ok' => false, 'msg' => 'La cédula ya está registrada en otro usuario.'], 409);
        }

        if (!$this->clientes->actualizarCedula($idCliente, $cedula)) {
            $this->json(['ok' => false, 'msg' => 'No se pudo guardar la cédula.'], 500);
        }

        $_SESSION['cliente']['cedula'] = $cedula;
        $_SESSION['cliente']['falta_cedula'] = false;

        $_SESSION['force_profile'] = (
            $_SESSION['cliente']['falta_cedula'] ||
            ($_SESSION['cliente']['falta_apellidos'] ?? false) ||
            ($_SESSION['cliente']['falta_telefono'] ?? false)
        );

        $this->json(['ok' => true]);
    }

    /* ================= RECUPERACIÓN DE CONTRASEÑA ================= */

    /** Formulario: "olvidé mi contraseña" */
    public function forgotForm(): void
    {
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        $msg = $_SESSION['msg'] ?? null;
        unset($_SESSION['msg']);

        $this->render(
            'auth/forgot',
            ['error' => $error, 'msg' => $msg, 'full' => true],
            'Recuperar contraseña'
        );
    }

    /** Procesa solicitud: genera token + expiración y envía correo */
    public function forgot(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/?r=forgot');
        }

        $correo = trim((string)$this->post('correo'));
        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Ingresa un correo válido.';
            $this->redirect('/?r=forgot');
        }

        $token  = bin2hex(random_bytes(32));
        $expira = new \DateTime('+1 hour');

        $this->clientes->setTokenRecuperacion($correo, $token, $expira);

        $base = rtrim($this->config['app']['base_url'], '/');
        $link = $base . '/?r=reset&token=' . urlencode($token);

        $mailer = new ServicioCorreo($this->config);
        $mailer->enviarRecuperacion($correo, $link);

        $_SESSION['msg'] = 'Si el correo existe, te enviamos un enlace para restablecer tu contraseña.';
        $this->redirect('/?r=forgot');
    }

    /** Formulario: nueva contraseña (con token) */
    public function resetForm(): void
    {
        $token = $_GET['token'] ?? '';

        if ($token === '') {
            $this->redirect('/?r=forgot');
        }

        $cliente = $this->clientes->buscarPorTokenRecuperacion($token);
        if (!$cliente) {
            $_SESSION['error'] = 'El enlace no es válido o ya fue utilizado.';
            $this->redirect('/?r=forgot');
        }

        if (!empty($cliente['recuperacion_expira']) && new \DateTime() > new \DateTime($cliente['recuperacion_expira'])) {
            $_SESSION['error'] = 'El enlace de recuperación ha expirado.';
            $this->redirect('/?r=forgot');
        }

        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        $this->render(
            'auth/reset',
            ['token' => $token, 'error' => $error, 'full' => true],
            'Restablecer contraseña'
        );
    }

    /** Procesa reseteo: guarda nueva contraseña e invalida token */
    public function reset(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/?r=forgot');
        }

        $token = (string)$this->post('token');
        $pass1 = (string)$this->post('password');
        $pass2 = (string)$this->post('password2');

        if ($token === '') {
            $_SESSION['error'] = 'Token inválido.';
            $this->redirect('/?r=forgot');
        }

        if ($pass1 === '' || strlen($pass1) < 8) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 8 caracteres.';
            $this->redirect('/?r=reset&token=' . urlencode($token));
        }

        if ($pass1 !== $pass2) {
            $_SESSION['error'] = 'Las contraseñas no coinciden.';
            $this->redirect('/?r=reset&token=' . urlencode($token));
        }

        $cliente = $this->clientes->buscarPorTokenRecuperacion($token);
        if (!$cliente) {
            $_SESSION['error'] = 'El enlace no es válido o ya fue utilizado.';
            $this->redirect('/?r=forgot');
        }

        $ok = $this->clientes->actualizarPasswordPorToken($token, $pass1);

        if ($ok) {
            $_SESSION['cliente'] = [
                'id_cliente' => (int)($cliente['id_cliente'] ?? 0),
                'nombres'    => $cliente['nombres'] ?? '',
                'correo'     => $cliente['correo'] ?? '',
                'cedula'     => $cliente['cedula'] ?? '',
                'apellidos'  => $cliente['apellidos'] ?? '',
                'telefono'   => $cliente['telefono'] ?? '',

                'falta_cedula'    => empty(trim((string)($cliente['cedula'] ?? ''))),
                'falta_apellidos' => empty(trim((string)($cliente['apellidos'] ?? ''))),
                'falta_telefono'  => empty(trim((string)($cliente['telefono'] ?? ''))),
            ];

            $_SESSION['force_profile'] = (
                $_SESSION['cliente']['falta_cedula'] ||
                $_SESSION['cliente']['falta_apellidos'] ||
                $_SESSION['cliente']['falta_telefono']
            );

            $this->redirect('/?r=home');
        } else {
            $_SESSION['error'] = 'No se pudo actualizar la contraseña.';
            $this->redirect('/?r=reset&token=' . urlencode($token));
        }
    }
}