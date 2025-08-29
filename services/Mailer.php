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

        // Mapeo de claves según tu env.php
        $host   = $c['host']        ?? 'smtp.gmail.com';
        $port   = isset($c['port']) ? (int)$c['port'] : 587;
        $secure = strtolower((string)($c['secure'] ?? 'tls')); // 'tls' o 'ssl'
        $user   = $c['user']        ?? ($c['username'] ?? '');
        $pass   = $c['pass']        ?? ($c['password'] ?? '');
        $fromE  = $c['from_email']  ?? ($user ?: '');
        $fromN  = $c['from_name']   ?? 'Hieribal';

        // App password: sin espacios
        if (is_string($pass)) {
            $pass = str_replace(' ', '', $pass);
        }

        // Si no hay remitente válido, no enviamos pero tampoco rompemos
        if (!filter_var($fromE, FILTER_VALIDATE_EMAIL)) {
            error_log('[Mailer] Remitente inválido; configura mail.from_email o mail.username');
            return;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user ?: $fromE;
            $mail->Password   = $pass;

            if ($secure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // 465
                if ($port === 587) { $port = 465; }
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 587
            }
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($fromE, $fromN);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = strip_tags($html);

            $mail->send();
            error_log('[Mailer] Enviado a ' . $toEmail);
        } catch (Exception $e) {
            error_log('[Mailer] ' . $e->getMessage());
        }
    }
}
