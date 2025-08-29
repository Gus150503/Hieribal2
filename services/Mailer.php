<?php
namespace Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
  private array $cfg;

  public function __construct(array $config) {
    // Usa 'mail' (tu caso) o 'smtp' si existiera
    $this->cfg = $config['mail'] ?? $config['smtp'] ?? [];
  }

  public function send(string $toEmail, string $toName, string $subject, string $html): void {
    $c = $this->cfg;

    // Mapear claves típicas desde tu env.php
    $host  = $c['host']       ?? 'smtp.gmail.com';
    $port  = (int)($c['port'] ?? 587);
    $sec   = strtolower((string)($c['secure'] ?? 'tls'));   // 'tls' o 'ssl'
    $user  = $c['user']       ?? ($c['gustavoalexiscuevas@gmail.com'] ?? '');
    $pass  = $c['pass']       ?? ($c['bhgn jeju ajnu vhtm'] ?? '');Fatal error: Uncaught PHPMailer\PHPMailer\Exception: Invalid address: (From): in C:\xampp\htdocs\Hieribal2\vendor\phpmailer\phpmailer\src\PHPMailer.php:1354 Stack trace: #0 C:\xampp\htdocs\Hieribal2\services\Mailer.php(21): PHPMailer\PHPMailer\PHPMailer->setFrom('', 'Mi Hieribal') #1 C:\xampp\htdocs\Hieribal2\controllers\AdminUsuariosController.php(113): Services\Mailer->send('gustavoalexiscu...', 'gustavo', 'Verifica tu cor...', '<p>Hola gustavo...') #2 C:\xampp\htdocs\Hieribal2\controllers\AdminUsuariosController.php(102): Controllers\AdminUsuariosController->sendVerificationEmail(Array) #3 C:\xampp\htdocs\Hieribal2\public\index.php(85): Controllers\AdminUsuariosController->resendVerification() #4 {main} thrown in C:\xampp\htdocs\Hieribal2\vendor\phpmailer\phpmailer\src\PHPMailer.php on line 1354
    $fromE = $c['from_email'] ?? ($user ?: '');
    $fromN = $c['from_name']  ?? 'Hieribal';

    // App password a veces lo pegan con espacios; quitarlos
    if (is_string($pass)) $pass = str_replace(' ', '', $pass);

    // Si no hay remitente válido, no enviamos (pero no rompemos el CRUD)
    if (!filter_var($fromE, FILTER_VALIDATE_EMAIL)) {
      error_log('[Mailer] Remitente inválido; configura mail.from_email o mail.username');
      return;
    }

    $m = new PHPMailer(true);
    try {
      $m->isSMTP();
      $m->Host       = $host;
      $m->SMTPAuth   = true;
      $m->Username   = $user ?: $fromE;
      $m->Password   = $pass;
      if ($sec === 'ssl') {
        $m->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // 465
        if ($port === 587) $port = 465;
      } else {
        $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 587
      }
      $m->Port       = $port;

      $m->CharSet    = 'UTF-8';
      $m->setFrom($fromE, $fromN);
      $m->addAddress($toEmail, $toName);

      $m->isHTML(true);
      $m->Subject = $subject;
      $m->Body    = $html;
      $m->AltBody = strip_tags($html);

      $m->send();
    } catch (Exception $e) {
      // No detengas el flujo del CRUD por un fallo de email
      error_log('[Mailer] '.$e->getMessage());
    }
  }
}
