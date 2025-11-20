<?php

namespace App\controllers;

use App\Core\Database;
use App\Services\MailService;

use DateTime;
use Exception;
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

    public function loginAsUser()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_as_user_id'])) {
            $userId = (int) $_POST['login_as_user_id'];

            // On récupère l'utilisateur dans la base
            $sql = "SELECT u.id, u.fullname, u.username AS username, u.email, u.password, u.confirmation_code, u.is_confirmed AS is_confirmed, u.reset_link, u.reset_token, u.reset_expires_at, u.created_at AS user_created_at, p.id AS profile_id, p.user_id, p.profile_picture, p.birth_date, p.country, p.english_level, p.phone_number, p.bio, p.updated_at AS profile_updated_at FROM users u INNER JOIN user_profiles p ON u.id = p.user_id WHERE u.id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                session_start();
                // Création de la session
                $_SESSION['user'] = [
                    'id'            => $user['id'],
                    'is_confirmed'  => $user['is_confirmed'],
                    'username'      => $user['username'],
                    'english_level' => $user['english_level'],
                    'fullname'      => $user['fullname'],
                    'email'         => $user['email'],
                    'created_at'    => $user['created_at'],
                    'profile'       => [
                        'profile_picture' => $user['profile_picture'],
                        'birth_date'      => $user['birth_date'],
                        'phone_number'    => $user['phone_number'],
                        'bio'             => $user['bio'],
                        'country'         => $user['country'],
                    ],
                ];

                // Redirection vers le tableau de bord
                header("Location: ./");
                exit;
            } else {
                // Utilisateur non trouvé
                $_SESSION['error'] = "Utilisateur introuvable.";
                header("Location: ./login");
                exit;
            }
        } else {
            // Si pas de POST, retour au login
            header("Location: ./login");
            exit;
        }
    }


    /**
     * Récupère toutes les informations d’un utilisateur (user, profile, tokens)
     *
     * @param int $userId
     * @return array|null
     */
    public function getUserWithDetails(string $token): ?array
    {
        $sql = "
            SELECT 
                u.id AS user_id,
                u.fullname,
                u.username,
                u.email,
                u.password,
                u.confirmation_code,
                u.is_confirmed AS is_confirmed,
                u.reset_link,
                u.reset_token,
                u.reset_expires_at,
                u.created_at AS user_created_at,

                p.id AS profile_id,
                p.profile_picture,
                p.birth_date,
                p.country,
                p.english_level,
                p.phone_number,
                p.bio,
                p.updated_at AS profile_updated_at,

                t.id AS token_id,
                t.token,
                t.expires_at AS token_expires_at,
                t.created_at AS token_created_at,
                t.ip_address,
                t.device,
                t.browser

            FROM users u
            INNER JOIN user_profiles p ON u.id = p.user_id
            INNER JOIN user_remember_tokens t ON u.id = t.user_id
            WHERE t.token = :token
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':token', $token, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        if (!$rows) {
            return null; // aucun utilisateur trouvé
        }

        // --- Structuration du résultat ---
        $user = [
            'id' => $rows[0]['user_id'],
            'fullname' => $rows[0]['fullname'],
            'username' => $rows[0]['username'],
            'email' => $rows[0]['email'],
            'password' => $rows[0]['password'],
            'confirmation_code' => $rows[0]['confirmation_code'],
            'is_confirmed' => $rows[0]['is_confirmed'],
            'reset_link' => $rows[0]['reset_link'],
            'reset_token' => $rows[0]['reset_token'],
            'reset_expires_at' => $rows[0]['reset_expires_at'],
            'created_at' => $rows[0]['user_created_at'],
            'profile' => null,
            'tokens' => []
        ];

        // Profil (s’il existe)
        if ($rows[0]['profile_id']) {
            $user['profile'] = [
                'id' => $rows[0]['profile_id'],
                'profile_picture' => $rows[0]['profile_picture'],
                'birth_date' => $rows[0]['birth_date'],
                'country' => $rows[0]['country'],
                'english_level' => $rows[0]['english_level'],
                'phone_number' => $rows[0]['phone_number'],
                'bio' => $rows[0]['bio'],
                'updated_at' => $rows[0]['profile_updated_at']
            ];
        }

        // Tokens (il peut y en avoir plusieurs)
        foreach ($rows as $row) {
            if ($row['token_id']) {
                $user['token'][] = [
                    'id' => $row['token_id'],
                    'tokens' => $row['token'],
                    'expires_at' => $row['token_expires_at'],
                    'created_at' => $row['token_created_at'],
                    'ip_address' => $row['ip_address'],
                    'device' => $row['device'],
                    'browser' => $row['browser'],
                ];
            }
        }

        return $user;
        require __DIR__ . './../views/login.php';
    }

    // Affiche le formulaire de connexion
    public function login()
    {
        $errors = [];
        $old = [];  // Valeurs précédentes
        require_once __DIR__ . '/../views/auth/login.php';
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

    public function loginPost()
    {
        header('Content-Type: application/json');

        try {
            // 1) Récupérer les données du formulaire
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] === 'on';

            // 2) Validation rapide
            if (empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Email ou mot de passe manquant.']);
                return;
            }

            // 3) Vérifier si l'utilisateur existe dans la base de données 
            $stmt = $this->db->prepare("
                    SELECT 
                        u.id,
                        u.email,
                        u.username,
                        u.fullname,
                        u.password,
                        u.is_confirmed,
                        u.created_at,
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

            // 4) Vérifier si le mot de passe est correct
            if (!password_verify($password, $user['password'])) {
                echo json_encode(['success' => false, 'message' => 'Email ou mot de passe invalide.']);
                return;
            }

            // 5) Vérifier si l'utilisateur est confirmé
            if ($user['is_confirmed'] !== 1) {
                echo json_encode(['success' => false, 'message' => 'Votre compte n\'est pas confirmé.']);
                return;
            }

            // 6) Configurer la session
            // Vérifier si une session existe déjà
            if (session_status() === PHP_SESSION_NONE) {
                // Aucune session active : démarrer une nouvelle session
                session_start();
            }

            // Régénérer l'ID de session pour la sécurité
            session_regenerate_id(true);

            // Vérifie si la case "Se souvenir de moi" est cochée
            if ($rememberMe) {
                $token = bin2hex(random_bytes(32));
                $expiresAt = time() + (30 * 24 * 60 * 60); // 30 jours

                // Définir le cookie côté client
                setcookie('remember_me_token', $token, $expiresAt, '/', '', false, true);

                // Vérifie si un token existe déjà pour cet utilisateur
                $stmt = $this->db->prepare("SELECT id FROM user_remember_tokens WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $existing = $stmt->fetch();

                if ($existing) {
                    // Mettre à jour le token existant
                    $stmt = $this->db->prepare("UPDATE user_remember_tokens 
                                    SET token = ?, expires_at = ?, ip_address = ?, device = ?, browser = ?, created_at = NOW() 
                                    WHERE user_id = ?");
                    $stmt->execute([
                        $token,
                        date('Y-m-d H:i:s', $expiresAt),
                        $_SERVER['REMOTE_ADDR'] ?? null,
                        $_SERVER['HTTP_USER_AGENT'] ?? null, // tu peux parser pour isoler device/browser si tu veux
                        $_SERVER['HTTP_USER_AGENT'] ?? null,
                        $user['id']
                    ]);
                } else {
                    // Insérer un nouveau token
                    $stmt = $this->db->prepare("INSERT INTO user_remember_tokens (user_id, token, expires_at, ip_address, device, browser, created_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([
                        $user['id'],
                        $token,
                        date('Y-m-d H:i:s', $expiresAt),
                        $_SERVER['REMOTE_ADDR'] ?? null,
                        $_SERVER['HTTP_USER_AGENT'] ?? null,
                        $_SERVER['HTTP_USER_AGENT'] ?? null
                    ]);
                }
            } else {
                // Si l'utilisateur ne coche pas la case, supprimer le cookie + token en base
                if (isset($_COOKIE['remember_me_token'])) {
                    setcookie('remember_me_token', '', time() - 3600, '/', '', false, true);

                    $stmt = $this->db->prepare("DELETE FROM user_remember_tokens WHERE user_id = ?");
                    $stmt->execute([$user['id']]);
                }
            }


            // Enregistrer les données de l'utilisateur dans la session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'is_confirmed' => $user['is_confirmed'],
                'username' => $user['username'],
                'fullname' => $user['fullname'],
                'phone_number' => $user["phone_number"],
                'country' => $user["country"],
                'bio' => $user["bio"],
                'created_at' => $user["created_at"],
            ];

            // 7) Récupérer le profil de l'utilisateur
            $profileStmt = $this->db->prepare("SELECT profile_picture, english_level FROM user_profiles WHERE user_id = ?");
            $profileStmt->execute([$user['id']]);
            $profile = $profileStmt->fetch(\PDO::FETCH_ASSOC);

            $_SESSION['user']['profile_picture'] = $profile['profile_picture'] ?? 'default.png';
            $_SESSION['user']['english_level'] = $profile['english_level'] ?? null;

            // 8) Répondre avec une réponse JSON réussie
            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie.',
                'user' => $_SESSION['user']
            ]);
        } catch (\Exception $e) {
            error_log('Erreur lors de la connexion: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la connexion.'
            ]);
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
            SELECT u.id, u.email, u.username, u.fullname, u.is_confirmed AS is_confirmed, p.profile_picture
            FROM users u
            LEFT JOIN user_profiles p ON u.id = p.user_id
            WHERE u.id = ?
        ");
            $userData->execute([$user['id']]);
            $fullUser = $userData->fetch(\PDO::FETCH_ASSOC);

            // Mettre à jour la session
            $_SESSION['user'] = [
                'id' => $fullUser['id'],
                'email' => $fullUser['email'],
                'username' => $fullUser['username'],
                'fullname' => $fullUser['fullname'],
                'is_confirmed' => (int)$fullUser['is_confirmed'],
                'profile_picture' => $fullUser['profile_picture'] ?? 'default.png'
            ];

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
        $authController = new AuthController;
        $userIP = $authController->getPublicIP();  // Récupère l'IP publique de l'utilisateur (même en local)

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
            admin_name,
            admin_ip,
            admin_role
        FROM admins
        WHERE admin_name = :admin_name
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':admin_name', $adminName, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null; // Aucun admin trouvé
        }

        // Structuration du résultat
        $admin = [
            'id' => $row['id'],
            'admin_name' => $row['admin_name'],
            'admin_ip' => $row['admin_ip'],
            'admin_role' => $row['admin_role']
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
}
