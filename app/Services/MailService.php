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
        $this->mailer->Host       = 'smtp.gmail.com';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = 'misterntkofficiel2.0@gmail.com';
        $this->mailer->Password   = 'tqlrzdeuawbjuhkm';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = 587;

        // Expéditeur par défaut
        $this->mailer->setFrom('misterntkofficiel2.0@gmail.com', 'OpenDoorsClass');
    }

    public function sendConfirmationCode($toEmail, $toName, $code)
    {
        try {
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Votre code de confirmation';
            $this->mailer->Body    = "
                <h2>Confirmation de votre compte</h2>
                <p>L'équipe OpenDoorsClass vous salue <strong>{$toName}</strong>,</p>
                <p>Voici votre code de confirmation :</p>
                <h3 style='color:#3c3b6e;'>{$code}</h3>
                <p>Ce code est valable 10 minutes.</p>
                <hr>
                <small>Si vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail.</small>
            ";

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function sendPasswordResetLink($toEmail, $toName, $resetLink)
    {
        try {
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Reinitialisation de votre mot de passe';
            $this->mailer->Body    = "
                <h2>Réinitialisation de mot de passe</h2>
                <p>Bonjour <strong>{$toName}</strong>,</p>
                <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous pour procéder :</p>
                <a href='{$resetLink}' style='display: inline-block; padding: 10px 20px; background-color: #5a57a3; color: #fff; text-decoration: none; border-radius: 5px;'>Réinitialiser mon mot de passe</a>
                <p>Ce lien est valable pendant 1 heure.</p>
                <hr>
                <small>Si vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail.</small>
            ";

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log('Erreur lors de l\'envoi de l\'e-mail de réinitialisation: ' . $e->getMessage());
            return false;
        }
    }
}