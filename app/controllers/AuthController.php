<?php

namespace App\controllers;

use App\Core\Database;
use App\Services\MailService;
use App\Models\AdminRepository;
use App\Models\AuthRepository;
use App\Models\UserRepository;
use PragmaRX\Google2FA\Google2FA;
use DateTime;
use Exception;
use PDO;

class AuthController extends Controller
{
    protected $db;
    protected $errors = [];

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Affiche le formulaire d'inscription
    public function registerPage()
    {
        $errors = [];
        $old = [];  // Valeurs précédentes
        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function loginAsUser()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['login_as_user_id'])) {
            $this->redirect('./login');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = (int) $_POST['login_as_user_id'];

        $userModel = new AuthRepository($this->db);
        $user      = $userModel->findUserWithProfileById($userId);

        if (!$user) {
            $_SESSION['error'] = "Utilisateur introuvable.";
            $this->redirect('./login');
        }

        // Récupère le statut 2FA
        $stmt = $this->db->prepare("SELECT two_factor_enabled FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $twoFaRow = $stmt->fetch(\PDO::FETCH_ASSOC);

        // ===== CHECK 2FA =====
        if (!empty($twoFaRow['two_factor_enabled'])) {

            $userRepository = new UserRepository($this->db);

            // Vérifie si le navigateur est de confiance
            $trustedToken = $_COOKIE['trusted_device_' . $userId] ?? null;

            if ($trustedToken && $userRepository->isTrustedDevice($userId, $trustedToken)) {
                // Navigateur de confiance → connexion directe
            } else {
                // Génère et envoie le code 2FA sur l'email de l'utilisateur cible
                $code = $userRepository->saveTwoFactorCode($userId);

                $mailService = new MailService();
                $mailService->sendTwoFactorCode(
                    $user['email'],
                    $user['fullname'] ?? $user['username'],
                    $code
                );

                // Stocke l'ID en attente
                $_SESSION['2fa_pending_user_id'] = $userId;

                // Redirige vers la page de vérification
                $this->redirect('./verify-2fa');
                exit;
            }
        }

        // ===== SESSION COMPLÈTE =====
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'                 => $user['id'],
            'is_confirmed'       => $user['is_confirmed'],
            'username'           => $user['username'],
            'english_level'      => $user['english_level']   ?? null,
            'role'               => 'admin',
            'fullname'           => $user['fullname'],
            'email'              => $user['email'],
            'created_at'         => $user['user_created_at'] ?? null,
            'phone_number'       => $user['phone_number']    ?? '',
            'country'            => $user['country']         ?? '',
            'bio'                => $user['bio']             ?? '',
            'is_admin'           => false,
            'two_factor_enabled' => (bool) ($twoFaRow['two_factor_enabled'] ?? false),
            'profile_picture'    => $user['profile_picture'] ?? 'default.png',
            'profile'            => [
                'profile_picture' => $user['profile_picture'] ?? 'default.png',
                'birth_date'      => $user['birth_date']      ?? null,
                'phone_number'    => $user['phone_number']    ?? '',
                'bio'             => $user['bio']             ?? '',
                'country'         => $user['country']         ?? '',
                'english_level'   => $user['english_level']  ?? null,
                'native_language' => $user['native_language'] ?? null,
            ],
        ];

        $userRepository = new UserRepository($this->db);
        $userRepository->logLogin($userId);

        $this->redirect('./');
    }


    public function rememberLogin()
    {
        if (empty($_COOKIE['remember_token'])) {
            return;
        }

        $token = $_COOKIE['remember_token'];

        $userModel = new AuthRepository($this->db);
        $user = $userModel->findByRememberToken($token);

        if (!$user) {
            return;
        }

        session_start();

        $_SESSION['user'] = [
            'id'            => $user['id'],
            'username'      => $user['username'],
            'fullname'      => $user['fullname'],
            'email'         => $user['email'],
            'english_level' => $user['profile']['english_level'] ?? null,
            'role'          => 'user',
            'is_confirmed'  => $user['is_confirmed'],
            'profile'       => $user['profile']
        ];
    }

    // Affiche le formulaire de connexion
    public function showLogin()
    {
        session_start();

        $userFromCookie = null;

        if (!empty($_COOKIE['remember_me_token'])) {
            $token = $_COOKIE['remember_me_token'];

            $userModel = new AuthRepository($this->db);
            $userFromCookie = $userModel->findByRememberToken($token);
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    public function confirm()
    {
        $email = $_GET['email'] ?? '';
        require_once __DIR__ . '/../views/auth/confirm.php';
    }

    // Traite la soumission du formulaire
    public function register()
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

    public function loginPost()
    {
        header('Content-Type: application/json');

        // Session doit être active avant le check 2FA
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // ===== SESSION =====
        session_regenerate_id(true);

        try {
            $email      = trim($_POST['email']    ?? '');
            $password   = $_POST['password']      ?? '';
            $rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] === 'on';

            if (empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Email ou mot de passe manquant.']);
                return;
            }

            $stmt = $this->db->prepare("
                SELECT
                    u.id,
                    u.email,
                    u.username,
                    u.fullname,
                    u.password,
                    u.is_confirmed,
                    u.created_at,
                    u.two_factor_enabled,
                    u.totp_enabled,
                    up.profile_picture,
                    up.bio,
                    up.country,
                    up.phone_number
                FROM users u
                INNER JOIN user_profiles up ON u.id = up.user_id
                WHERE u.email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Email ou mot de passe invalide.']);
                return;
            }

            if (!password_verify($password, $user['password'])) {
                echo json_encode(['success' => false, 'message' => 'Email ou mot de passe invalide.']);
                return;
            }

            if ($user['is_confirmed'] !== 1) {
                // Met l'email en session pour la page noconfirmed
                $_SESSION['unconfirmed_user'] = [
                    'id'    => $user['id'],
                    'email' => $user['email'],
                ];

                // Envoie UN seul code automatiquement
                $confirmationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                $stmt = $this->db->prepare("UPDATE users SET confirmation_code = ? WHERE id = ?");
                $stmt->execute([$confirmationCode, $user['id']]);

                $mailService = new MailService();
                $mailService->sendConfirmationCode($user['email'], $user['fullname'], $confirmationCode);

                echo json_encode([
                    'success'       => false,
                    'not_confirmed' => true,
                    'message'       => 'Votre compte n\'est pas confirmé. Un code vient d\'être envoyé.',
                ]);
                return;
            }

            // ===== CHECK 2FA + TOTP =====
            $has2fa  = !empty($user['two_factor_enabled']);
            $hasTotp = !empty($user['totp_enabled']);

            $userRepository = new UserRepository($this->db);
            $trustedToken   = $_COOKIE['trusted_device_' . $user['id']] ?? null;
            $isTrusted      = $trustedToken && $userRepository->isTrustedDevice((int) $user['id'], $trustedToken);

            if (!$isTrusted) {

                // ===== CAS 1 : Les deux sont activés → double authentification =====
                // On commence par le 2FA email, le TOTP sera demandé après
                if ($has2fa && $hasTotp) {
                    $code = $userRepository->saveTwoFactorCode((int) $user['id']);

                    $mailService = new MailService();
                    $mailService->sendTwoFactorCode($user['email'], $user['fullname'], $code);

                    $_SESSION['2fa_pending_user_id']    = (int) $user['id'];
                    $_SESSION['2fa_requires_totp_next'] = true; // ← flag pour enchaîner le TOTP

                    echo json_encode([
                        'success'      => true,
                        'requires_2fa' => true,
                        'message'      => 'Un code de vérification a été envoyé à votre adresse e-mail.',
                    ]);
                    exit;
                }

                // ===== CAS 2 : Seulement 2FA email =====
                if ($has2fa) {
                    $code = $userRepository->saveTwoFactorCode((int) $user['id']);

                    $mailService = new MailService();
                    $mailService->sendTwoFactorCode($user['email'], $user['fullname'], $code);

                    $_SESSION['2fa_pending_user_id'] = (int) $user['id'];

                    echo json_encode([
                        'success'      => true,
                        'requires_2fa' => true,
                        'message'      => 'Un code de vérification a été envoyé à votre adresse e-mail.',
                    ]);
                    exit;
                }

                // ===== CAS 3 : Seulement TOTP =====
                if ($hasTotp) {
                    $_SESSION['totp_pending_user_id'] = (int) $user['id'];

                    echo json_encode([
                        'success'       => true,
                        'requires_totp' => true,
                        'message'       => 'Saisissez le code de votre application Google Authenticator.',
                    ]);
                    exit;
                }

                // ===== CAS 4 : Aucun 2FA → connexion directe =====
            }

            // Remember me
            if ($rememberMe) {
                $token     = bin2hex(random_bytes(32));
                $expiresAt = time() + (30 * 24 * 60 * 60);

                setcookie('remember_me_token', $token, $expiresAt, '/', '', false, true);

                $stmt = $this->db->prepare("SELECT id FROM user_remember_tokens WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $stmt = $this->db->prepare("
                        UPDATE user_remember_tokens
                        SET token = ?, expires_at = ?, ip_address = ?, device = ?, browser = ?, created_at = NOW()
                        WHERE user_id = ?
                    ");
                    $stmt->execute([
                        $token,
                        date('Y-m-d H:i:s', $expiresAt),
                        $_SERVER['REMOTE_ADDR']     ?? null,
                        $_SERVER['HTTP_USER_AGENT'] ?? null,
                        $_SERVER['HTTP_USER_AGENT'] ?? null,
                        $user['id']
                    ]);
                } else {
                    $stmt = $this->db->prepare("
                        INSERT INTO user_remember_tokens
                            (user_id, token, expires_at, ip_address, device, browser, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $user['id'],
                        $token,
                        date('Y-m-d H:i:s', $expiresAt),
                        $_SERVER['REMOTE_ADDR']     ?? null,
                        $_SERVER['HTTP_USER_AGENT'] ?? null,
                        $_SERVER['HTTP_USER_AGENT'] ?? null,
                    ]);
                }
            } else {
                if (isset($_COOKIE['remember_me_token'])) {
                    setcookie('remember_me_token', '', time() - 3600, '/', '', false, true);
                    $stmt = $this->db->prepare("DELETE FROM user_remember_tokens WHERE user_id = ?");
                    $stmt->execute([$user['id']]);
                }
            }

            // Session de base
            $_SESSION['user'] = [
                'id'                 => $user['id'],
                'email'              => $user['email'],
                'is_confirmed'       => $user['is_confirmed'],
                'username'           => $user['username'],
                'fullname'           => $user['fullname'],
                'phone_number'       => $user['phone_number']    ?? '',
                'country'            => $user['country']         ?? '',
                'bio'                => $user['bio']             ?? '',
                'created_at'         => $user['created_at'],
                'is_admin'           => false,
                'two_factor_enabled' => (bool) $user['two_factor_enabled'], // ← ajout
                'profile_picture'    => 'default.png',
                'english_level'      => null,
                'profile'            => [
                    'profile_picture' => 'default.png',
                    'birth_date'      => null,
                    'phone_number'    => $user['phone_number'] ?? '',
                    'bio'             => $user['bio']          ?? '',
                    'country'         => $user['country']      ?? '',
                    'english_level'   => null,
                    'native_language' => null,
                ],
            ];

            // Log connexion
            $userRepository = new UserRepository($this->db);
            $userRepository->logLogin((int) $user['id']);

            // Vérifie admin
            $adminRepo = new AdminRepository($this->db);
            $admin     = $adminRepo->findByUserId($user['id']);
            if ($admin && $admin['is_active'] == 1) {
                $_SESSION['user']['is_admin'] = true;
            }

            // Profil complet
            $profileStmt = $this->db->prepare("
                SELECT profile_picture, english_level, native_language,
                       birth_date, phone_number, country, bio
                FROM user_profiles
                WHERE user_id = ?
            ");
            $profileStmt->execute([$user['id']]);
            $profile = $profileStmt->fetch(\PDO::FETCH_ASSOC);

            $_SESSION['user']['profile_picture']              = $profile['profile_picture']  ?? 'default.png';
            $_SESSION['user']['english_level']                = $profile['english_level']    ?? null;
            $_SESSION['user']['profile']['profile_picture']   = $profile['profile_picture']  ?? 'default.png';
            $_SESSION['user']['profile']['birth_date']        = $profile['birth_date']       ?? null;
            $_SESSION['user']['profile']['phone_number']      = $profile['phone_number']     ?? '';
            $_SESSION['user']['profile']['country']           = $profile['country']          ?? '';
            $_SESSION['user']['profile']['bio']               = $profile['bio']              ?? '';
            $_SESSION['user']['profile']['english_level']     = $profile['english_level']    ?? null;
            $_SESSION['user']['profile']['native_language']   = $profile['native_language']  ?? null;

            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie.',
                'user'    => $_SESSION['user']
            ]);
        } catch (\Exception $e) {
            error_log('Erreur lors de la connexion : ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Une erreur est survenue lors de la connexion.']);
        }
    }

    public function confirmPost()
    {
        session_start(); // S'assurer que la session est démarrée
        header('Content-Type: application/json');

        // 1) Email : POST d'abord, GET en fallback
        $email = trim($_POST['email'] ?? ($_GET['email'] ?? ''));

        // 2) Code : accepte "code" ou "confirmation_code"
        $rawCode = $_POST['code'] ?? ($_POST['confirmation_code'] ?? '');
        $code = preg_replace('/\D/', '', trim($rawCode)); // Ne garder que les chiffres

        // 3) Validations rapides
        if (empty($email) || empty($code) || strlen($code) !== 6) {
            echo json_encode(['success' => false, 'message' => 'Code ou email manquant/invalide.']);
            return;
        }

        // 4) Vérif en BDD
        $stmt = $this->db->prepare("
        SELECT id, is_confirmed 
        FROM users 
        WHERE email = ? AND confirmation_code = ?
    ");
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

        try {
            // Commencer une transaction
            $this->db->beginTransaction();

            // 5) Confirme le compte et supprime le code
            $update = $this->db->prepare("
            UPDATE users 
            SET is_confirmed = 1, confirmation_code = NULL 
            WHERE id = ?
        ");
            $update->execute([$user['id']]);

            // 6) Crée un profil vide si inexistant
            $checkProfile = $this->db->prepare("
            SELECT id FROM user_profiles WHERE user_id = ?
        ");
            $checkProfile->execute([$user['id']]);
            if (!$checkProfile->fetch()) {
                $insertProfile = $this->db->prepare("
                INSERT INTO user_profiles (user_id, profile_picture, birth_date, country, phone_number, bio) 
                VALUES (?, 'default.png', NULL, NULL, NULL, NULL)
            ");
                $insertProfile->execute([$user['id']]);
            }

            // 7) Récupère les infos utilisateur avec profil
            $userData = $this->db->prepare("
            SELECT
                u.id,
                u.email,
                u.username,
                u.fullname,
                u.is_confirmed,
                u.created_at,
                p.profile_picture,
                p.english_level,
                p.phone_number,
                p.country,
                p.bio,
                p.birth_date,
                p.native_language
            FROM users u
            LEFT JOIN user_profiles p ON u.id = p.user_id
            WHERE u.id = ?
        ");
            $userData->execute([$user['id']]);
            $fullUser = $userData->fetch(\PDO::FETCH_ASSOC);

            // Mettre à jour la session
            $_SESSION['user'] = [
                'id'              => $fullUser['id'],
                'is_confirmed'    => (int) $fullUser['is_confirmed'],
                'username'        => $fullUser['username']        ?? '',
                'english_level'   => $fullUser['english_level']   ?? null,
                'role'            => 'user', // ← était 'admin' par erreur
                'fullname'        => $fullUser['fullname']         ?? '',
                'email'           => $fullUser['email']            ?? '',
                'created_at'      => $fullUser['created_at']       ?? null,
                'phone_number'    => $fullUser['phone_number']     ?? '',
                'country'         => $fullUser['country']          ?? '',
                'bio'             => $fullUser['bio']              ?? '',
                'profile_picture' => $fullUser['profile_picture']  ?? 'default.png',
                'is_admin'        => false,
                'profile'         => [
                    'profile_picture' => $fullUser['profile_picture'] ?? 'default.png',
                    'birth_date'      => $fullUser['birth_date']      ?? null,
                    'phone_number'    => $fullUser['phone_number']    ?? '',
                    'bio'             => $fullUser['bio']             ?? '',
                    'country'         => $fullUser['country']         ?? '',
                    'english_level'   => $fullUser['english_level']  ?? null,
                    'native_language' => $fullUser['native_language'] ?? null,
                ],
            ];
            // Enregistre la connexion
            $userRepository = new UserRepository($this->db);
            $userRepository->logLogin((int) $fullUser['id']);
            // Valider la transaction
            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Compte confirmé avec succès.',
                'user'    => $_SESSION['user'] // renvoyer aussi au JS
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la confirmation.',
                'error'   => $e->getMessage()
            ]);
        }
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
            $mail->Password = 'vdqzewccgpvfswgj'; // mot de passe app
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('misterntkofficiel2.0@gmail.com', 'OpenDoorsClass');
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

    // Fonction pour obtenir l'IP publique (même en local)
    public function getPublicIP()
    {
        // Utilise l'API ipify pour récupérer l'IP publique
        $publicIP = file_get_contents('https://api.ipify.org');
        return $publicIP;
    }

    // Ajoute un nouveau membre
    public function addMemberPost()
    {
        header('Content-Type: application/json');

        try {
            // Récupérer et valider les données du formulaire
            $username = trim($_POST['nom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phoneNumber = trim($_POST['telephone'] ?? '');
            $duree = intval($_POST['duree'] ?? 0); // Durée en mois

            // Calcul du prix en F CFA (3000 F CFA/mois * durée)
            $price = $duree * 3000;
            $subscriptionStart = $_POST['dateDebut'] ?? date('Y-m-d');
            $subscriptionEnd = $_POST['dateFin'] ?? date('Y-m-d', strtotime("+{$duree} months"));

            // Validation basique
            if (empty($username) || empty($email) || empty($phoneNumber) || $duree < 1 || $duree > 12 || $price <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Données invalides. Vérifiez les champs requis.'
                ]);
                return;
            }

            // Appeler la méthode addNewMember
            $result = $this->addNewMember($username, $email, $phoneNumber, $price, $duree, $subscriptionStart, $subscriptionEnd);

            // Encoder et afficher le résultat en JSON
            echo json_encode($result);
        } catch (\Exception $e) {
            error_log('Erreur lors de l\'ajout du membre premium: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur : ' . $e->getMessage()
            ]);
        }
    }
    // Ta méthode addNewMember reste inchangée, mais retire le header de l'intérieur
    public function addNewMember($username, $email, $phoneNumber, $price, $subscriptionDuration, $subscriptionStart, $subscriptionEnd): array
    {
        try {
            $statement = $this->db->prepare("
            INSERT INTO opendoorsclass_premium_members(
                username, 
                email, 
                phone_number, 
                price, 
                subscription_duration, 
                subscription_start, 
                subscription_end
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

            $success = $statement->execute([
                $username,
                $email,
                $phoneNumber,
                $price,
                $subscriptionDuration,
                $subscriptionStart,
                $subscriptionEnd
            ]);

            if ($success) {
                $memberId = $this->db->lastInsertId();
                return [
                    'success' => true,
                    'message' => 'Membre premium ajouté avec succès ! ID: ' . $memberId,
                    'member_id' => $memberId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'insertion en base.'
                ];
            }
        } catch (\Exception $e) {
            error_log('Erreur lors de l\'ajout du membre premium: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur serveur : ' . $e->getMessage()
            ];
        }
    }



    // Dans App\Controllers\AuthController
    public function welcome()
    {
        session_start();

        // Vérifie si l'utilisateur est connecté et confirmé
        if (!isset($_SESSION['user']) || (int)$_SESSION['user']['is_confirmed'] !== 1) {
            header('Location: ./login');
            exit;
        }

        $user = $_SESSION['user'];

        // --- Détection du pays de l'utilisateur ---
        $userIP = $this->getPublicIP();  // Récupère l'IP publique de l'utilisateur (même en local)

        // Appel à l'API de géolocalisation ipinfo.io
        $geoUrl = "http://ipinfo.io/{$userIP}/json";
        $response = file_get_contents($geoUrl);
        $geoData = json_decode($response, true);

        // Récupère le pays (par défaut 'Inconnu' si l'information n'est pas disponible)
        $userCountry = $geoData['country'] ?? 'Inconnu';

        // --- Traitement upload photo ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profilePic'])) {
            $file = $_FILES['profilePic'];

            if ($file['error'] === 0 && strpos($file['type'], 'image/') === 0) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFileName = 'profile_' . $user['id'] . '_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '../../../public/uploads/profiles/';

                if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

                $filePath = $uploadDir . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    // Met à jour la BDD
                    $stmt = $this->db->prepare("UPDATE user_profiles SET profile_picture = ?, country = ? WHERE user_id = ?");
                    $stmt->execute([$newFileName, $userCountry, $user['id']]);

                    // Met à jour la session
                    $_SESSION['user']['profile_picture'] = $newFileName;
                    $_SESSION['user']['country'] = $userCountry;

                    // Renvoie JSON succès
                    echo json_encode(['success' => true]);
                    exit;

                    // Après avoir déplacé le fichier uploadé
                    $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $filePath);
                    imagecreatefromstring(file_get_contents($filePath));
                    imagewebp(imagecreatefromstring(file_get_contents($filePath)), $webpPath, 82);
                    unlink($filePath); // supprime l'original

                } else {
                    echo json_encode(['success' => false, 'message' => 'Impossible de déplacer le fichier.']);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Fichier invalide.']);
                exit;
            }
        }

        // --- Traitement date de naissance ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['birthdate'])) {
            $birthdate = $_POST['birthdate'];

            // Vérifie le format de la date (YYYY-MM-DD)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
                // Met à jour la BDD
                $stmt = $this->db->prepare("UPDATE user_profiles SET birth_date = ?, country = ? WHERE user_id = ?");
                $stmt->execute([$birthdate, $userCountry, $user['id']]);

                // Met à jour la session
                $_SESSION['user']['birth_date'] = $birthdate;
                $_SESSION['user']['country'] = $userCountry;

                // Renvoie JSON succès
                echo json_encode(['success' => true]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Date invalide.']);
                exit;
            }
        }

        // Traitement du numéro de téléphone
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone_number'])) {
            $phoneNumber = $_POST['phone_number'];

            // Vérifie que le numéro de téléphone a un format valide (par exemple, +1234567890 ou 1234567890)
            if (preg_match('/^\+?\d{8,15}$/', $phoneNumber)) {
                // Met à jour la BDD
                $stmt = $this->db->prepare("UPDATE user_profiles SET phone_number = ? WHERE user_id = ?");
                $stmt->execute([$phoneNumber, $user['id']]);

                // Met à jour la session
                $_SESSION['user']['phone_number'] = $phoneNumber;

                // Renvoie une réponse JSON de succès
                echo json_encode(['success' => true]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Numéro de téléphone invalide.']);
                exit;
            }
        }

        // --- Traitement du niveau d'anglais ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['english_level'])) {
            $englishLevel = $_POST['english_level'];

            // Valider le niveau d'anglais
            $validLevels = ['beginner', 'intermediate', 'advanced'];

            if (in_array($englishLevel, $validLevels)) {
                // Met à jour la BDD
                $stmt = $this->db->prepare("UPDATE user_profiles SET english_level = ? WHERE user_id = ?");
                $stmt->execute([$englishLevel, $user['id']]);

                // Met à jour la session
                $_SESSION['user']['english_level'] = $englishLevel;

                // Renvoie JSON succès
                echo json_encode(['success' => true]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Niveau d\'anglais invalide.']);
                exit;
            }
        }

        // --- Traitement de la biographie ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bio'])) {
            $bio = $_POST['bio'];

            // Valider la biographie (ici on vérifie juste si elle est non vide, tu peux adapter cela)
            if (!empty($bio)) {
                // Met à jour la BDD
                $stmt = $this->db->prepare("UPDATE user_profiles SET bio = ? WHERE user_id = ?");
                $stmt->execute([$bio, $user['id']]);

                // Met à jour la session
                $_SESSION['user']['bio'] = $bio;

                // Renvoie JSON succès
                echo json_encode(['success' => true]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Biographie invalide.']);
                exit;
            }
        }


        // --- Récupération infos utilisateur pour affichage ---
        $stmt = $this->db->prepare("
        SELECT u.id, u.fullname, u.username, u.email, u.is_confirmed is_confirmed, p.profile_picture, p.birth_date, p.country, p.phone_number, p.english_level, p.bio
        FROM users u
        INNER JOIN user_profiles p ON u.id = p.user_id
        WHERE u.id = ?
    ");
        $stmt->execute([$user['id']]);
        $fullUser = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['user'] = [
            'id' => $fullUser['id'],
            'fullname' => $fullUser['fullname'],
            'username' => $fullUser['username'],
            'email' => $fullUser['email'],
            'is_confirmed' => $user['is_confirmed'],
            'profile_picture' => $fullUser['profile_picture'] ?? 'default.png',
            'birth_date' => $fullUser['birth_date'],
            'country' => $fullUser['country'],
            'phone_number' => $fullUser['phone_number'],
            'bio' => $fullUser['bio'],
            'english_level' => $fullUser['english_level'],
        ];

        require_once __DIR__ . '/../views/auth/welcome.php';
    }

    public function forgotPasswordPage()
    {
        require_once __DIR__ . '/../views/auth/forgotPassword.php';
    }

    public function forgotPasswordPost()
    {
        // Désactiver l'affichage des erreurs pour éviter les sorties HTML
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(E_ALL);

        session_start();
        header('Content-Type: application/json');

        try {
            // 1) Récupérer l'email
            $email = trim($_POST['find-email'] ?? '');

            // 2) Validation
            if (empty($email)) {
                echo json_encode(['success' => false, 'message' => 'Veuillez entrer une adresse e-mail.']);
                return;
            }

            // 3) Vérifier si l'utilisateur existe
            $stmt = $this->db->prepare("SELECT id, email, fullname FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Aucun compte n\'est associé à cette adresse e-mail.']);
                return;
            }

            // 4) Générer un token de réinitialisation
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+5 hour'));
            $id = $user["id"];

            // 5) Mettre à jour la table users avec le token et la date d'expiration
            $stmt = $this->db->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE id = ?");
            $stmt->execute([$token, $expiresAt, $id]);

            // 6) Envoyer l'e-mail avec le lien de réinitialisation
            $resetLink = "http://localhost/mrnathanenglish/public/reset-password?token=$token";
            $mailService = new MailService();
            $sent = $mailService->sendPasswordResetLink($email, $user['fullname'], $resetLink);

            if (!$sent) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de l\'e-mail de réinitialisation. Veuillez réessayer.'
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Un lien de réinitialisation a été envoyé à votre adresse e-mail.'
            ]);
        } catch (\Exception $e) {
            error_log('Erreur lors de la demande de réinitialisation: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.'
            ]);
        }
    }

    public function getAdmin(string $adminName): ?array
    {
        $sql = "
        SELECT 
            id,
            username,
            role
        FROM admins
        WHERE username = :username
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', $adminName, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null; // Aucun admin trouvé
        }

        // Structuration du résultat
        $admin = [
            'id' => $row['id'],
            'username' => $row['username'],
            'admin_role' => $row['role']
        ];

        return $admin;
    }

    public function adminPage()
    {
        require __DIR__ . '/../views/auth/admins.php';
    }

    public function members()
    {

        require __DIR__ . '/../views/auth/members.php';
    }

    // Dans AuthController.php
    public function membersPage()
    {
        $members = [];
        $error = null;

        try {
            // Récupérer la liste des membres premium
            $stmt = $this->db->prepare("
            SELECT 
                id, 
                username, 
                email, 
                phone_number, 
                price, 
                subscription_duration, 
                subscription_start, 
                subscription_end
            FROM opendoorsclass_premium_members 
            ORDER BY subscription_end DESC
        ");
            $stmt->execute();
            $members = $stmt->fetchAll();

            // Calculer les jours restants pour chaque membre
            foreach ($members as &$member) {
                try {
                    $endDate = new DateTime($member['subscription_end']);
                    $today = new DateTime();
                    $interval = $endDate->diff($today);
                    $member['days_remaining'] = $interval->days >= 0 ? $interval->days : 0;
                    $member['status'] = $member['days_remaining'] > 0 ? 'Actif' : 'Expiré';
                } catch (Exception $dateError) {
                    // Date invalide : marquer comme expiré sans crash
                    $member['days_remaining'] = 0;
                    $member['status'] = 'Expiré';
                    error_log('Erreur date pour membre ID ' . $member['id'] . ': ' . $dateError->getMessage());
                }
            }
            unset($member); // Libérer la référence

            if (empty($members)) {
                $error = 'Aucun membre premium trouvé.';
            }
        } catch (\Exception $e) {
            error_log('Erreur lors de la récupération des membres: ' . $e->getMessage());
            $error = 'Impossible de charger la liste des membres. Veuillez réessayer plus tard.';
            $members = []; // Tableau vide pour éviter erreurs dans la vue
        }

        // Passer les données à la vue
        extract([
            'members' => $members,
            'error' => $error
        ]);

        require __DIR__ . '/../views/auth/members.php';
    }

    public function verifyTwoFactor(): void
    {
        header('Content-Type: application/json');

        $code        = preg_replace('/\D/', '', trim($_POST['otp_code'] ?? $_POST['otp_code'] ?? ''));
        $trustDevice   = filter_var($_POST['trust_device'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $userId = (int) ($_SESSION['2fa_pending_user_id'] ?? 0);

        if (!$userId || strlen($code) !== 6) {
            echo json_encode(['success' => false, 'message' => 'Code invalide.']);
            exit;
        }

        // Debug avant vérification en base
        $stmt = $this->db->prepare("SELECT two_factor_code, two_factor_expires FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);


        $userRepository = new UserRepository($this->db);

        $result = $userRepository->verifyTwoFactorCode($userId, $code);

        if (!$result['success']) {
            http_response_code(200);
            echo json_encode([
                'success'       => false,
                'locked'        => $result['locked']        ?? false,
                'seconds_left'  => $result['seconds_left']  ?? null,
                'attempts_left' => $result['attempts_left'] ?? null,
                'message'       => $result['message'],
            ]);
            exit;
        }

        if (!$userRepository->verifyTwoFactorCode($userId, $code)) {
            echo json_encode(['success' => false, 'message' => 'Code incorrect ou expiré.']);
            exit;
        }

        // Code email valide — vérifie si TOTP doit suivre
        if (!empty($_SESSION['2fa_requires_totp_next'])) {
            unset($_SESSION['2fa_requires_totp_next']);
            unset($_SESSION['2fa_pending_user_id']);

            // Passe en mode TOTP
            $_SESSION['totp_pending_user_id'] = $userId;

            echo json_encode([
                'success'       => true,
                'requires_totp' => true,
                'message'       => 'Saisissez maintenant le code de votre application Google Authenticator.',
            ]);
            exit;
        }

        // Code valide — récupère l'utilisateur et crée la session complète
        $stmt = $this->db->prepare("
        SELECT
            u.*,
            up.profile_picture,
            up.english_level,
            up.birth_date,
            up.phone_number,
            up.country,
            up.bio,
            up.native_language
        FROM users u
        LEFT JOIN user_profiles up ON up.user_id = u.id
        WHERE u.id = ?
    ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Si l'étudiant veut enregistrer ce navigateur
        if ($trustDevice) {
            $token = $userRepository->saveTrustedDevice(
                $userId,
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            // Cookie valide 30 jours
            setcookie(
                'trusted_device_' . $userId,
                $token,
                time() + (30 * 24 * 60 * 60),
                '/',
                '',
                false,
                true // httpOnly
            );
        }

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
            exit;
        }

        // Nettoie la session temporaire
        unset($_SESSION['2fa_pending_user_id']);

        // Crée la session complète
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'                  => $user['id'],
            'email'               => $user['email'],
            'username'            => $user['username'],
            'fullname'            => $user['fullname'],
            'is_confirmed'        => (int) $user['is_confirmed'],
            'created_at'          => $user['created_at'],
            'phone_number'        => $user['phone_number']    ?? '',
            'country'             => $user['country']         ?? '',
            'bio'                 => $user['bio']             ?? '',
            'profile_picture'     => $user['profile_picture'] ?? 'default.png',
            'english_level'       => $user['english_level']   ?? null,
            'is_admin'            => false,
            'two_factor_enabled'  => (bool) $user['two_factor_enabled'],
            'profile'             => [
                'profile_picture' => $user['profile_picture'] ?? 'default.png',
                'birth_date'      => $user['birth_date']      ?? null,
                'phone_number'    => $user['phone_number']    ?? '',
                'bio'             => $user['bio']             ?? '',
                'country'         => $user['country']         ?? '',
                'english_level'   => $user['english_level']  ?? null,
                'native_language' => $user['native_language'] ?? null,
            ],
        ];

        $userRepository->logLogin($userId);

        echo json_encode([
            'success'  => true,
            'message'  => 'Connexion réussie.',
            'redirect' => './profile',
        ]);

        exit;
    }
    public function resendTwoFactorCode(): void
    {
        header('Content-Type: application/json');

        $userId = (int) ($_SESSION['2fa_pending_user_id'] ?? 0);

        if (!$userId) {
            echo json_encode(['success' => false]);
            exit;
        }

        $userRepository = new UserRepository($this->db);

        // Récupère l'email
        $stmt = $this->db->prepare("SELECT email, fullname FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false]);
            exit;
        }

        $code = $userRepository->saveTwoFactorCode($userId);

        $mailService = new MailService();
        $mailService->sendTwoFactorCode($user['email'], $user['fullname'], $code);

        echo json_encode(['success' => true]);
        exit;
    }

    public function checkTwoFactorLock(): void
    {
        header('Content-Type: application/json');

        $userId = (int) ($_SESSION['2fa_pending_user_id'] ?? 0);

        if (!$userId) {
            echo json_encode(['locked' => false]);
            exit;
        }

        $stmt = $this->db->prepare("
        SELECT two_factor_locked_until, two_factor_attempts
        FROM users
        WHERE id = ?
    ");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!empty($row['two_factor_locked_until'])) {
            $lockedUntil = new \DateTime($row['two_factor_locked_until']);
            $now         = new \DateTime();

            if ($now < $lockedUntil) {
                $diff = $now->diff($lockedUntil);
                $secondsLeft = ($diff->i * 60) + $diff->s;

                echo json_encode([
                    'locked'       => true,
                    'seconds_left' => $secondsLeft,
                ]);
                exit;
            }
        }

        echo json_encode(['locked' => false]);
        exit;
    }

    public function noConfirmed(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Aucune session → login
        if (!isset($_SESSION['user']['id']) && !isset($_SESSION['unconfirmed_user']['id'])) {
            header('Location: ./login');
            exit();
        }

        // Priorité à la session unconfirmed (vient de loginPost)
        if (isset($_SESSION['unconfirmed_user'])) {
            $email = $_SESSION['unconfirmed_user']['email'];
        } else {
            // Utilisateur connecté mais non confirmé
            if (!empty($_SESSION['user']['is_confirmed']) && (int) $_SESSION['user']['is_confirmed'] === 1) {
                header('Location: ./');
                exit();
            }
            $email = $_SESSION['user']['email'] ?? '';
        }

        require __DIR__ . '/../views/auth/noconfirmed.php';
    }

    /**
     * Vérifie le code TOTP lors de la connexion.
     * Crée la session complète si le code est valide.
     * Gère également l'option "Se souvenir de ce navigateur".
     * Récupère le secret directement sans condition sur totp_enabled.
     *
     * @return void
     */
    public function verifyTotp(): void
    {
        header('Content-Type: application/json');

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        ) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée.']);
            exit;
        }

        $userId      = (int) ($_SESSION['totp_pending_user_id'] ?? 0);
        $code        = preg_replace('/\D/', '', trim($_POST['otp_code'] ?? $_POST['code'] ?? ''));
        $trustDevice = filter_var($_POST['trust_device'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$userId || strlen($code) !== 6) {
            echo json_encode(['success' => false, 'message' => 'Code invalide ou session expirée.']);
            exit;
        }

        // Récupère le secret sans condition sur totp_enabled
        $stmt = $this->db->prepare("SELECT totp_secret FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row    = $stmt->fetch(\PDO::FETCH_ASSOC);
        $secret = $row['totp_secret'] ?? null;

        if (!$secret) {
            echo json_encode(['success' => false, 'message' => 'TOTP non configuré pour cet utilisateur.']);
            exit;
        }

        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($secret, $code, 4)) {
            echo json_encode(['success' => false, 'message' => 'Code incorrect ou expiré.']);
            exit;
        }

        $userRepository = new UserRepository($this->db);

        // Enregistre le navigateur de confiance si demandé
        if ($trustDevice) {
            $token = $userRepository->saveTrustedDevice($userId, $_SERVER['HTTP_USER_AGENT'] ?? '');
            setcookie(
                'trusted_device_' . $userId,
                $token,
                time() + (30 * 24 * 60 * 60),
                '/',
                '',
                false,
                true
            );
        }

        // Récupère les données complètes de l'utilisateur
        $stmt = $this->db->prepare("
            SELECT
                u.*,
                up.profile_picture,
                up.english_level,
                up.birth_date,
                up.phone_number,
                up.country,
                up.bio,
                up.native_language
            FROM users u
            LEFT JOIN user_profiles up ON up.user_id = u.id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
            exit;
        }

        // Nettoie la session temporaire
        unset($_SESSION['totp_pending_user_id']);

        // Crée la session complète
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'                 => $user['id'],
            'email'              => $user['email'],
            'username'           => $user['username'],
            'fullname'           => $user['fullname'],
            'is_confirmed'       => (int) $user['is_confirmed'],
            'created_at'         => $user['created_at'],
            'phone_number'       => $user['phone_number']    ?? '',
            'country'            => $user['country']         ?? '',
            'bio'                => $user['bio']             ?? '',
            'profile_picture'    => $user['profile_picture'] ?? 'default.png',
            'english_level'      => $user['english_level']   ?? null,
            'is_admin'           => false,
            'two_factor_enabled' => (bool) ($user['two_factor_enabled'] ?? false),
            'totp_enabled'       => (bool) ($user['totp_enabled']       ?? false),
            'profile'            => [
                'profile_picture' => $user['profile_picture'] ?? 'default.png',
                'birth_date'      => $user['birth_date']      ?? null,
                'phone_number'    => $user['phone_number']    ?? '',
                'bio'             => $user['bio']             ?? '',
                'country'         => $user['country']         ?? '',
                'english_level'   => $user['english_level']  ?? null,
                'native_language' => $user['native_language'] ?? null,
            ],
        ];

        $userRepository->logLogin($userId);

        echo json_encode([
            'success'  => true,
            'message'  => 'Connexion réussie.',
            'redirect' => '../profile',
        ]);
        exit;
    }
}
