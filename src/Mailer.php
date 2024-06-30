<?php

namespace Fabian\CloudflareUnifiIpImport;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{

    public function __construct()
    {

    }

    /**
     * @throws Exception
     */
    public function sendMail($subject, $message): void
    {
        $from = $_ENV['EMAIL_FROM'];
        $to = $_ENV['EMAIL_TO'];

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->Port = $_ENV['SMTP_PORT'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = $_ENV['SMTP_SECURE'] == "true" ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->setFrom($from, 'Unifi Automations');
        $mail->addAddress($to);
        $mail->isHTML(false);

        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
    }

}