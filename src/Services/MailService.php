<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private PHPMailer $mailer;
    private array $config;

    public function __construct(array $smtpConfig)
    {
        $this->config = $smtpConfig;
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    private function configure(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host       = $this->config['smtp_host'] ?? '';
        $this->mailer->Port       = (int)($this->config['smtp_port'] ?? 587);
        $this->mailer->Username   = $this->config['smtp_usuario'] ?? '';
        $this->mailer->Password   = $this->config['smtp_password'] ?? '';
        $this->mailer->CharSet    = PHPMailer::CHARSET_UTF8;

        $cifrado = strtolower($this->config['smtp_cifrado'] ?? 'tls');

        if ($cifrado === 'ssl') {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mailer->SMTPAuth   = true;
        } elseif ($cifrado === 'tls') {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->SMTPAuth   = true;
        } else {
            $this->mailer->SMTPSecure = '';
            $this->mailer->SMTPAuth   = false;
        }

        $fromEmail  = $this->config['smtp_from_email'] ?? $this->config['smtp_usuario'] ?? '';
        $fromNombre = $this->config['smtp_from_nombre'] ?? 'ISO 27001 Platform';

        $this->mailer->setFrom($fromEmail, $fromNombre);
    }

    /**
     * Envia un correo.
     *
     * @param string|array $para     Email destino o array ['email' => 'nombre']
     * @param string       $asunto
     * @param string       $cuerpo   HTML permitido
     * @param string       $texto    Texto plano alternativo (opcional)
     */
    public function enviar($para, string $asunto, string $cuerpo, string $texto = ''): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            if (is_array($para)) {
                foreach ($para as $email => $nombre) {
                    $this->mailer->addAddress($email, $nombre);
                }
            } else {
                $this->mailer->addAddress($para);
            }

            $this->mailer->isHTML(true);
            $this->mailer->Subject = $asunto;
            $this->mailer->Body    = $cuerpo;
            $this->mailer->AltBody = $texto ?: strip_tags($cuerpo);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Prueba la conexion SMTP sin enviar correo.
     * Devuelve array ['success' => bool, 'error' => string]
     */
    public function testConexion(): array
    {
        try {
            $this->mailer->SMTPDebug  = SMTP::DEBUG_OFF;
            $this->mailer->Timeout    = 10;

            $resultado = $this->mailer->smtpConnect();

            if ($resultado) {
                $this->mailer->smtpClose();
                return ['success' => true, 'error' => ''];
            }

            return ['success' => false, 'error' => 'No se pudo establecer conexion con el servidor SMTP.'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtiene el ultimo error del mailer.
     */
    public function getError(): string
    {
        return $this->mailer->ErrorInfo;
    }
}
