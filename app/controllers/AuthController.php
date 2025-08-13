<?php

namespace App\controllers;

use App\Core\Database;
use App\Services\MailService;

use PDO;

class AuthController
{
    protected $db;
    protected $errors = [];

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Affiche le formulaire d'inscription
    public function register()
    {
        $errors = [];
        $old = [];  // Valeurs précédentes
        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function confirm()
    {
        $email = $_GET['email'] ?? '';
        require_once __DIR__ . '/../views/auth/confirm.php';
    }

    // Traite la soumission du formulaire
    public function registerPost()
    {
        $errors = [];
        $old = [];

        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $terms = $_POST['terms'] ?? '';

        // Garder valeurs pour réaffichage
        $old['fullname'] = $fullname;
        $old['email'] = $email;
        $old['username'] = $username;

        if (empty($fullname)) {
            $errors['fullname'] = "Le nom complet est requis.";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Adresse e-mail invalide.";
        } else {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $errors['email'] = "Cet e-mail est déjà utilisé.";
            }
        }

        if (empty($username) || strlen($username) < 3) {
            $errors['username'] = "Nom d’utilisateur invalide (min 3 caractères).";
        } else {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $errors['username'] = "Ce nom d’utilisateur est déjà pris.";
            }
        }

        if (strlen($password) < 8) {
            $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères.";
        }

        if ($password !== $confirm_password) {
            $errors['confirm_password'] = "Les mots de passe ne correspondent pas.";
        }

        if (empty($terms)) {
            $errors['terms'] = "Vous devez accepter les conditions d'utilisation.";
        }

        if (!empty($errors)) {
            require_once __DIR__ . '/../views/auth/register.php';
            return;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $confirmation_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $stmt = $this->db->prepare("
            INSERT INTO users (fullname, email, username, password, confirmation_code)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$fullname, $email, $username, $hashed_password, $confirmation_code]);

        // TODO: Envoi mail avec PHPMailer

        $mailService = new MailService();
        $mailSent = $mailService->sendConfirmationCode($email, $fullname, $confirmation_code);

        if ($mailSent) {
            $_SESSION['confirmation_email'] = $email; // Pour l’afficher dans la vue
            header("Location: ./confirm?email=" . urlencode($email));
            exit;
        } else {
            echo "Erreur lors de l'envoi de l'email.";
        }

        header("Location: ./confirm");
        exit;
    }

    public function confirmPost()
    {
        header('Content-Type: application/json');

        // 1) Email : POST d'abord, GET en fallback
        $email = trim($_POST['email'] ?? ($_GET['email'] ?? ''));

        // 2) Code : accepte "code" ou "confirmation_code"
        $rawCode = $_POST['code'] ?? ($_POST['confirmation_code'] ?? '');
        // Ne garder que les chiffres (supprime espaces, etc.)
        $code = preg_replace('/\D/', '', trim($rawCode));

        // 3) Validations rapides
        if (empty($email) || empty($code) || strlen($code) !== 6) {
            echo json_encode(['success' => false, 'message' => 'Code ou email manquant/invalide.']);
            return;
        }

        // 4) Vérif en BDD
        $stmt = $this->db->prepare("SELECT id, is_confirmed FROM users WHERE email = ? AND confirmation_code = ?");
        $stmt->execute([$email, $code]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Le code que vous avez saisi est invalide.']);
            return;
        }

        if ((int)$user['is_confirmed'] === 1) {
            echo json_encode(['success' => true, 'message' => 'Compte déjà confirmé.']);
            return;
        }

        // 5) Confirme le compte (et, si tu veux, invalide le code)
        $update = $this->db->prepare("UPDATE users SET is_confirmed = 1, confirmation_code = NULL WHERE id = ?");
        $update->execute([$user['id']]);

        echo json_encode(['success' => true, 'message' => 'Compte confirmé avec succès.']);
    }

    public function resendCode()
{
    header('Content-Type: application/json');

    // 1) Récupérer email depuis GET
    $email = trim($_GET['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Email invalide.']);
        return;
    }

    // 2) Vérifier si l’utilisateur existe
    $stmt = $this->db->prepare("SELECT id, fullname FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Utilisateur introuvable.']);
        return;
    }

    // 3) Générer un nouveau code à 6 chiffres
    $newCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // 4) Mettre à jour le code en BDD
    $update = $this->db->prepare("UPDATE users SET confirmation_code = ? WHERE id = ?");
    $update->execute([$newCode, $user['id']]);

    // 5) Envoyer le mail via PHPMailer
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'misterntkofficiel2.0@gmail.com'; // ton email
        $mail->Password = 'tqlrzdeuawbjuhkm'; // mot de passe app
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('misterntkofficiel2.0@gmail.com', 'Mr Nathan English');
        $mail->addAddress($email, $user['fullname']);
        $mail->isHTML(true);
        $mail->Subject = 'Votre nouveau code de confirmation';
        $mail->Body = "<p>Bonjour {$user['fullname']},</p>
                       <p>Voici votre nouveau code de confirmation : <b>{$newCode}</b></p>
                       <p>Merci de ne pas partager ce code.</p>";

        $mail->send();

        echo json_encode(['success' => true]);
        return;

    } catch (\PHPMailer\PHPMailer\Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de l’envoi du mail : ' . $mail->ErrorInfo]);
        return;
    }
}

}
