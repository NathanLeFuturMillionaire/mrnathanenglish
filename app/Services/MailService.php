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
        $this->mailer->Password   = 'vdqzewccgpvfswgj';
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
    public function sendTwoFactorCode(string $email, string $name, string $code): bool
    {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'misterntkofficiel2.0@gmail.com';
            $mail->Password   = 'vdqzewccgpvfswgj';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('misterntkofficiel2.0@gmail.com', 'OpenDoorsClass');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Votre code de connexion — OpenDoorsClass';
            $mail->Body    = "
            <div style='font-family:Montserrat,sans-serif;max-width:480px;margin:0 auto;padding:32px;background:#fff;border-radius:12px;'>
                <h2 style='color:#0d2b5e;margin-bottom:8px;'>Vérification de connexion</h2>
                <p style='color:#555;'>Bonjour <strong>{$name}</strong>,</p>
                <p style='color:#555;'>Voici votre code de connexion à usage unique :</p>
                <div style='text-align:center;margin:28px 0;'>
                    <span style='font-size:36px;font-weight:800;letter-spacing:12px;color:#0d2b5e;background:#e8f2fc;padding:16px 28px;border-radius:12px;'>
                        {$code}
                    </span>
                </div>
                <p style='color:#888;font-size:13px;'>Ce code expire dans <strong>10 minutes</strong>.</p>
                <p style='color:#888;font-size:13px;'>Si vous n'êtes pas à l'origine de cette connexion, ignorez cet e-mail.</p>
                <hr style='border:none;border-top:1px solid #eee;margin:24px 0;'>
                <p style='color:#bbb;font-size:12px;text-align:center;'>OpenDoorsClass — Formation en ligne</p>
            </div>
        ";

            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log('[sendTwoFactorCode] ' . $e->getMessage());
            return false;
        }
    }
}
