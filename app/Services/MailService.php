<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        // Config SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host       = 'smtp.gmail.com'; // Remplace par ton SMTP
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = 'misterntkofficiel2.0@gmail.com'; // Ton email
        $this->mailer->Password   = 'tqlrzdeuawbjuhkm'; // Ton mot de passe / App Password
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = 587;

        // Expéditeur par défaut
        $this->mailer->setFrom('misterntkofficiel2.0@gmail.com', 'Mr Nathan English');
    }

    public function sendConfirmationCode($toEmail, $toName, $code)
    {
        try {
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Votre code de confirmation';
            $this->mailer->Body    = "
                <h2>Confirmation de votre compte</h2>
                <p>Mr Nathan vous salut <strong>{$toName}</strong>,</p>
                <p>Voici votre code de confirmation :</p>
                <h3 style='color:#3c3b6e;'>{$code}</h3>
                <p>Ce code est valable 10 minutes.</p>
                <hr>
                <small>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</small>
            ";

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
