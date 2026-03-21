<?php

namespace App\Controllers;

use App\Core\Database;
use App\Helpers\LanguageHelper;
use App\Models\UserRepository;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;


use Throwable;

class UserController
{
    protected $db;
    protected UserRepository $userRepository;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $database             = new Database();
        $this->db             = $database->connect();
        $this->userRepository = new UserRepository($this->db);
    }

    // ======================
    // AFFICHAGE DU PROFIL
    // ======================
    public function user(int $userId): void
    {
        $userId = (int) $userId;

        $rows = $this->userRepository->getUserWithProfileAndSubscriptionsById($userId);

        if (!$rows || !isset($rows[0]['id'])) {
            http_response_code(404);
            exit('Utilisateur introuvable');
        }

        $user = $this->buildUser($rows[0]);
        $subscriptions = $this->buildSubscriptions($rows);
        $languages     = LanguageHelper::getAllLanguages();


        $_SESSION['user'] = array_merge($_SESSION['user'], $user);

        $totalLogins  = $this->userRepository->countLoginHistory($userId);
        $loginHistory  = $this->userRepository->getLoginHistory($userId, 4);
        $loginHistory  = $this->buildLoginHistory($loginHistory);
        $completion = $this->buildProfileCompletion($user);

        $notifSettings = $this->userRepository->getNotificationSettings($userId);

        require __DIR__ . '/../views/users/profile.php';
    }


    // ======================
    // MISE À JOUR DU PROFIL
    // ======================
    public function updateProfile(): void
    {
        header('Content-Type: application/json');

        // Sécurité AJAX
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
        ) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée']);
            exit;
        }

        if (empty($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']);
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];

        // ======================
        // VALIDATION
        // ======================
        $errors = [];

        $username    = trim($_POST['username']    ?? '');
        $fullname    = trim($_POST['fullname']    ?? '');
        $email       = trim($_POST['email']       ?? '');
        $phoneNumber = trim($_POST['phone_number'] ?? '');
        $country     = trim($_POST['country']     ?? '');
        $bio         = trim($_POST['bio']         ?? '');
        $birthDate    = trim($_POST['birth_date']     ?? '');
        $englishLevel = trim($_POST['english_level']  ?? '');
        $nativeLanguage = trim($_POST['native_language'] ?? '');

        if (empty($username)) {
            $errors['username'] = 'Le nom d\'utilisateur est obligatoire.';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
        }

        if (empty($email)) {
            $errors['email'] = 'L\'adresse e-mail est obligatoire.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'adresse e-mail n\'est pas valide.';
        }

        // Vérifie si le username est déjà pris par un autre utilisateur
        if (empty($errors['username'])) {
            $existingUser = $this->userRepository->findByUsername($username);
            if ($existingUser && (int) $existingUser['id'] !== $userId) {
                $errors['username'] = 'Ce nom d\'utilisateur est déjà utilisé.';
            }
        }

        // Vérifie si l'email est déjà pris par un autre utilisateur
        if (empty($errors['email'])) {
            $existingEmail = $this->userRepository->findByEmail($email);
            if ($existingEmail && (int) $existingEmail['id'] !== $userId) {
                $errors['email'] = 'Cette adresse e-mail est déjà utilisée.';
            }
        }

        if (!empty($nativeLanguage) && !array_key_exists($nativeLanguage, LanguageHelper::getFlatList())) {
            $errors['native_language'] = 'Langue non reconnue.';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // ======================
        // UPLOAD IMAGE
        // ======================
        $profilePicture = $_SESSION['user']['profile_picture'] ?? null;

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $originalName      = $_FILES['profile_picture']['name'];
            $tmpName           = $_FILES['profile_picture']['tmp_name'];
            $size              = $_FILES['profile_picture']['size'];
            $extension         = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions, true)) {
                echo json_encode(['success' => false, 'message' => 'Format d\'image non autorisé (JPG, PNG, WebP).']);
                exit;
            }

            if ($size > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Image trop lourde (max 5 Mo).']);
                exit;
            }

            $uploadDir = __DIR__ . '/../../public/uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = uniqid('profile_', true) . '.' . $extension;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $filePath)) {
                // Supprime l'ancienne image si ce n'est pas la photo par défaut
                if ($profilePicture && $profilePicture !== 'default.png') {
                    $oldPath = $uploadDir . $profilePicture;
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $profilePicture = $fileName;
            } else {
                echo json_encode(['success' => false, 'message' => 'Échec de l\'upload de l\'image.']);
                exit;
            }
        }

        // ======================
        // MISE À JOUR EN BASE
        // ======================
        $data = [
            'username'        => $username,
            'fullname'        => $fullname,
            'email'           => $email,
            'phone_number'    => $phoneNumber,
            'country'         => $country,
            'bio'             => $bio,
            'profile_picture' => $profilePicture,
            'birth_date'      => $birthDate    ?: null,
            'english_level'   => $englishLevel ?: null,
            'native_language' => $nativeLanguage,
        ];

        try {
            $updated = $this->userRepository->updateUser($userId, $data);

            if (!$updated) {
                throw new \Exception('Échec de la mise à jour en base de données.');
            }

            // Mise à jour de la session
            $_SESSION['user']['username']        = $username;
            $_SESSION['user']['fullname']        = $fullname;
            $_SESSION['user']['email']           = $email;
            $_SESSION['user']['phone_number']    = $phoneNumber;
            $_SESSION['user']['country']         = $country;
            $_SESSION['user']['bio']             = $bio;
            $_SESSION['user']['profile_picture'] = $profilePicture;
            $_SESSION['user']['profile']['birth_date']    = $birthDate;
            $_SESSION['user']['profile']['english_level'] = $englishLevel;
            $_SESSION['user']['profile']['native_language'] = $nativeLanguage;

            // Recalcule la complétion avec les nouvelles données
            $updatedUser = [
                'username'        => $username,
                'fullname'        => $fullname,
                'email'           => $email,
                'phone_number'    => $phoneNumber,
                'country'         => $country,
                'bio'             => $bio,
                'profile_picture' => $profilePicture,
                'profile'         => [
                    'birth_date'      => $birthDate,
                    'english_level'   => $englishLevel,
                    'native_language' => $nativeLanguage,
                ]
            ];

            $completion = $this->buildProfileCompletion($updatedUser);

            echo json_encode([
                'success'    => true,
                'message'    => 'Profil mis à jour avec succès.',
                'completion' => $completion,
                'user'       => [
                    'username'             => $username,
                    'fullname'             => $fullname,
                    'email'                => $email,
                    'phone_number'         => $phoneNumber,
                    'country'              => $country,
                    'bio'                  => $bio,
                    'profile_picture'      => $profilePicture,
                    'birth_date'           => $birthDate,
                    'english_level'        => $englishLevel,
                    'native_language'      => $nativeLanguage,
                    'native_language_label' => $nativeLanguage
                        ? LanguageHelper::getLabel($nativeLanguage)
                        : 'Non renseigné',
                ]
            ]);
            exit;
        } catch (Throwable $e) {
            error_log('[updateProfile] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Une erreur est survenue. Veuillez réessayer.']);
            exit;
        }
    }

    // ======================
    // HELPERS PRIVÉS
    // ======================
    private function buildUser(array $row): array
    {
        $monthsFr = [
            'January'   => 'Janvier',
            'February' => 'Février',
            'March'     => 'Mars',
            'April'    => 'Avril',
            'May'       => 'Mai',
            'June'     => 'Juin',
            'July'      => 'Juillet',
            'August'   => 'Août',
            'September' => 'Septembre',
            'October'  => 'Octobre',
            'November'  => 'Novembre',
            'December' => 'Décembre'
        ];

        $levelLabels = [
            'beginner'     => 'Débutant',
            'intermediate' => 'Intermédiaire',
            'advanced'     => 'Avancé',
        ];

        // Formatage date de création
        $createdAtFormatted = 'Non disponible';
        $createdAt = $row['created_at'] ?? null;
        if ($createdAt && !in_array($createdAt, ['0000-00-00 00:00:00', '0000-00-00'])) {
            try {
                $date       = new \DateTime($createdAt);
                $day        = $date->format('j');
                $monthFr    = $monthsFr[$date->format('F')] ?? $date->format('F');
                $createdAtFormatted = ($day == 1 ? '1er' : $day) . ' ' . $monthFr . ' ' . $date->format('Y') . ' à ' . $date->format('H') . 'h' . $date->format('i');
            } catch (\Exception $e) {
                $createdAtFormatted = 'Date invalide';
            }
        }

        // Formatage date de naissance
        $birthDateFormatted = 'Non renseigné';
        $birthDate = $row['birth_date'] ?? null;
        if ($birthDate && $birthDate !== '0000-00-00') {
            try {
                $date       = new \DateTime($birthDate);
                $day        = $date->format('j');
                $monthFr    = $monthsFr[$date->format('F')] ?? $date->format('F');
                $birthDateFormatted = ($day == 1 ? '1er' : $day) . ' ' . $monthFr . ' ' . $date->format('Y');
            } catch (\Exception $e) {
                $birthDateFormatted = 'Non renseigné';
            }
        }

        // Niveau d'anglais
        $englishLevel      = $row['english_level'] ?? '';
        $englishLevelLabel = $levelLabels[$englishLevel] ?? 'Non renseigné';
        // Affichage du label de la langue maternelle
        $nativeLanguage      = $row['native_language'] ?? '';
        $nativeLanguageLabel = $nativeLanguage ? LanguageHelper::getLabel($nativeLanguage) : 'Non renseigné';

        return [
            'id'                    => $row['id'],
            'fullname'              => $row['fullname']        ?? '',
            'username'              => $row['username']        ?? '',
            'email'                 => $row['email']           ?? '',
            'phone_number'          => $row['phone_number']    ?? '',
            'bio'                   => $row['bio']             ?? '',
            'country'               => $row['country']         ?? '',
            'english_level'         => $englishLevel,
            'english_level_label'   => $englishLevelLabel,
            'profile_picture'       => $row['profile_picture'] ?? 'default.png',
            'two_factor_enabled' => (bool) ($row['two_factor_enabled'] ?? false),
            'totp_enabled'       => (bool) ($row['totp_enabled']       ?? false),
            'is_confirmed'          => $row['is_confirmed'],
            'created_at'            => $row['created_at'],
            'created_at_formatted'  => $createdAtFormatted,
            'birth_date'            => $birthDate,
            'birth_date_formatted'  => $birthDateFormatted,
            'profile'               => [
                'profile_picture'  => $row['profile_picture'] ?? 'default.png',
                'birth_date'       => $birthDate,
                'birth_date_formatted' => $birthDateFormatted,
                'phone_number'     => $row['phone_number']    ?? '',
                'bio'              => $row['bio']             ?? '',
                'country'          => $row['country']         ?? '',
                'english_level'    => $englishLevel,
                'english_level_label' => $englishLevelLabel,
                'native_language'       => $nativeLanguage,
                'native_language_label' => $nativeLanguageLabel,
            ]
        ];
    }
    private function buildSubscriptions(array $rows): array
    {
        $subscriptions = [];

        foreach ($rows as $row) {
            if (empty($row['subscription_id'])) continue;

            // Formatage du prochain renouvellement
            $nextBillingFormatted = 'Aucun abonnement actif';
            if (!empty($row['next_billing_date'])) {
                try {
                    $formatter            = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
                    $nextBillingFormatted = $formatter->format(new \DateTime($row['next_billing_date']));
                } catch (\Exception $e) {
                }
            }

            // Calcul des jours restants
            $daysMessage = 'Aucun abonnement actif';
            $statusClass = 'none';
            if (!empty($row['start_date']) && !empty($row['end_date'])) {
                $endDate = new \DateTime($row['end_date']);
                $today   = new \DateTime();

                if ($today > $endDate) {
                    $daysMessage = 'Expiré';
                    $statusClass = 'expired';
                } else {
                    $d           = $today->diff($endDate)->days;
                    $daysMessage = $d === 0 ? 'Arrive à terme aujourd\'hui' : ($d === 1 ? '1 jour restant' : "$d jours restants");
                    $statusClass = $d <= 7 ? 'warning' : 'active';
                }
            }

            $subscriptions[] = [
                'id'                      => $row['subscription_id'],
                'type'                    => $row['subscription_type'],
                'amount'                  => $row['amount'],
                'currency'                => $row['currency'],
                'billing_period'          => $row['billing_period'],
                'start_date'              => $row['start_date'],
                'end_date'                => $row['end_date'],
                'next_billing_date'       => $row['next_billing_date'],
                'next_billing_formatted'  => $nextBillingFormatted,
                'status'                  => $row['subscription_status'],
                'status_class'            => $statusClass,
                'days_message'            => $daysMessage,
                'days_remaining'          => (int) ($row['days_remaining']       ?? 0),
                'status_display'          => $row['subscription_status_display'] ?? 'Unknown',
                'canceled_at'             => $row['canceled_at'],
                'ended_at'                => $row['ended_at'],
                'failed_payment_count'    => (int) ($row['failed_payment_count'] ?? 0),
                'created_at'              => $row['subscription_created_at'],
                'updated_at'              => $row['subscription_updated_at'],
                'pawa_pay_deposit_id'     => $row['pawa_pay_deposit_id'],
                'pawa_pay_status'         => $row['pawa_pay_status'],
                'pawa_pay_correlation_id' => $row['pawa_pay_correlation_id'],
                'pawa_pay_mobile_number'  => $row['pawa_pay_mobile_number'],
                'pawa_pay_country_code'   => $row['pawa_pay_country_code'],
                'pawa_pay_operator'       => $row['pawa_pay_operator'],
            ];
        }

        return $subscriptions;
    }

    private function buildLoginHistory(array $rows): array
    {
        $monthsFr = [
            'January' => 'Jan',
            'February' => 'Fév',
            'March' => 'Mar',
            'April' => 'Avr',
            'May' => 'Mai',
            'June' => 'Jun',
            'July' => 'Jul',
            'August' => 'Aoû',
            'September' => 'Sep',
            'October' => 'Oct',
            'November' => 'Nov',
            'December' => 'Déc'
        ];

        return array_map(function (array $row) use ($monthsFr) {
            $date      = new \DateTime($row['created_at']);
            $day       = $date->format('j');
            $monthFr   = $monthsFr[$date->format('F')] ?? $date->format('F');
            $formatted = ($day == 1 ? '1er' : $day) . ' ' . $monthFr . ' ' . $date->format('Y') . ' à ' . $date->format('H\hi');

            // Parse le user agent simplement
            $ua      = $row['user_agent'] ?? '';
            $browser = match (true) {
                str_contains($ua, 'Edg')     => 'Edge',
                str_contains($ua, 'Chrome')  => 'Chrome',
                str_contains($ua, 'Firefox') => 'Firefox',
                str_contains($ua, 'Safari')  => 'Safari',
                str_contains($ua, 'Opera')   => 'Opera',
                default                      => 'Navigateur inconnu',
            };

            $os = match (true) {
                str_contains($ua, 'Windows') => 'Windows',
                str_contains($ua, 'iPhone')  => 'iPhone',
                str_contains($ua, 'iPad')    => 'iPad',
                str_contains($ua, 'Android') => 'Android',
                str_contains($ua, 'Mac')     => 'macOS',
                str_contains($ua, 'Linux')   => 'Linux',
                default                      => 'OS inconnu',
            };

            $device = match (true) {
                str_contains($ua, 'Mobile') || str_contains($ua, 'iPhone') || str_contains($ua, 'Android') => 'mobile',
                str_contains($ua, 'iPad')   => 'tablette',
                default                     => 'desktop',
            };

            return [
                'id'         => $row['id'],
                'ip_address' => $row['ip_address'],
                'browser'    => $browser,
                'os'         => $os,
                'device'     => $device,
                'created_at' => $formatted,
                'is_current' => false, // on marquera la dernière plus tard si besoin
            ];
        }, $rows);
    }
    private function buildProfileCompletion(array $user): array
    {
        $fields = [
            'username'        => ['label' => 'Nom d\'utilisateur',   'value' => $user['username']                          ?? ''],
            'fullname'        => ['label' => 'Nom complet',          'value' => $user['fullname']                          ?? ''],
            'email'           => ['label' => 'Adresse e-mail',       'value' => $user['email']                             ?? ''],
            'phone_number'    => ['label' => 'Numéro de téléphone',  'value' => $user['phone_number']                      ?? ''],
            'country'         => ['label' => 'Pays',                 'value' => $user['country']                           ?? ''],
            'bio'             => ['label' => 'Biographie',           'value' => $user['bio']                               ?? ''],
            'profile_picture' => ['label' => 'Photo de profil',      'value' => ($user['profile_picture'] ?? '') !== 'default.png' ? $user['profile_picture'] : ''],
            'birth_date'      => ['label' => 'Date de naissance',    'value' => $user['profile']['birth_date']             ?? ''],
            'english_level'   => ['label' => 'Niveau d\'anglais',    'value' => $user['profile']['english_level']          ?? ''],
            'native_language' => ['label' => 'Langue maternelle',    'value' => $user['profile']['native_language']        ?? ''],
        ];

        $total     = count($fields);
        $filled    = 0;
        $missing   = [];

        foreach ($fields as $key => $field) {
            if (!empty($field['value'])) {
                $filled++;
            } else {
                $missing[] = $field['label'];
            }
        }

        $percentage = (int) round(($filled / $total) * 100);

        return [
            'percentage' => $percentage,
            'filled'     => $filled,
            'total'      => $total,
            'missing'    => $missing,
            'color'      => match (true) {
                $percentage >= 80 => 'success',
                $percentage >= 50 => 'warning',
                default           => 'danger',
            },
        ];
    }
    public function loginHistory(): void
    {
        header('Content-Type: application/json');

        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || empty($_SESSION['user']['id'])) {
            http_response_code(403);
            echo json_encode(['success' => false]);
            exit;
        }

        $userId  = (int) $_SESSION['user']['id'];
        $history = $this->userRepository->getLoginHistory($userId, 100);
        $history = $this->buildLoginHistory($history);

        echo json_encode(['success' => true, 'logins' => $history]);
        exit;
    }
    public function deleteLogin(): void
    {
        header('Content-Type: application/json');

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        ) {
            http_response_code(403);
            echo json_encode(['success' => false]);
            exit;
        }

        if (empty($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false]);
            exit;
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $userId = (int) $_SESSION['user']['id'];

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            exit;
        }

        $deleted = $this->userRepository->deleteLoginEntry($id, $userId);

        echo json_encode(['success' => $deleted]);
        exit;
    }

    public function toggleTwoFactor(): void
    {
        header('Content-Type: application/json');

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        ) {
            http_response_code(403);
            echo json_encode(['success' => false]);
            exit;
        }

        if (empty($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false]);
            exit;
        }

        $userId  = (int) $_SESSION['user']['id'];
        $enabled = filter_var($_POST['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $this->userRepository->toggleTwoFactor($userId, $enabled);

        $_SESSION['user']['two_factor_enabled'] = $enabled;

        echo json_encode([
            'success' => true,
            'enabled' => $enabled,
            'message' => $enabled
                ? 'Authentification à deux facteurs activée.'
                : 'Authentification à deux facteurs désactivée.',
        ]);
        exit;
    }


    /**
     * Génère un secret TOTP et retourne le QR code à scanner.
     * Le secret est stocké temporairement en session jusqu'à validation.
     * Utilise bacon/bacon-qr-code pour générer le QR code en SVG base64.
     *
     * @return void
     */
    public function generateTotp(): void
    {
        header('Content-Type: application/json');

        if (empty($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
            exit;
        }

        $username = $_SESSION['user']['email'] ?? 'user';

        $google2fa = new Google2FA();
        $secret    = $google2fa->generateSecretKey();

        $_SESSION['totp_pending_secret'] = $secret;

        $otpUrl = $google2fa->getQRCodeUrl('OpenDoorsClass', $username, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg    = $writer->writeString($otpUrl);
        $qrUrl  = 'data:image/svg+xml;base64,' . base64_encode($svg);

        echo json_encode([
            'success' => true,
            'qr_url'  => $qrUrl,
            'secret'  => $secret,
        ]);
        exit;
    }

    /**
     * Vérifie le code TOTP saisi par l'utilisateur et active Google Authenticator.
     * Le secret temporaire en session est sauvegardé en base si le code est valide.
     * Utilise pragmarx/google2fa avec une tolérance de ±2 minutes.
     *
     * @return void
     */
    public function activateTotp(): void
    {
        header('Content-Type: application/json');

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            empty($_SESSION['user']['id'])
        ) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée.']);
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];
        $code   = preg_replace('/\D/', '', trim($_POST['code'] ?? ''));
        $secret = $_SESSION['totp_pending_secret'] ?? trim($_POST['secret'] ?? '');

        if (!$secret || strlen($code) !== 6) {
            echo json_encode(['success' => false, 'message' => 'Données invalides.']);
            exit;
        }

        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($secret, $code, 4)) {
            echo json_encode(['success' => false, 'message' => 'Code incorrect. Vérifiez votre application.']);
            exit;
        }

        $this->userRepository->saveTotpSecret($userId, $secret);
        unset($_SESSION['totp_pending_secret']);
        $_SESSION['user']['totp_enabled'] = true;

        echo json_encode([
            'success' => true,
            'message' => 'Google Authenticator activé avec succès.',
        ]);
        exit;
    }

    /**
     * Désactive Google Authenticator après vérification du code actuel.
     * Le secret est supprimé de la base uniquement si le code fourni est valide.
     * Récupère le secret sans condition sur totp_enabled pour éviter les blocages.
     *
     * @return void
     */
    public function disableTotp(): void
    {
        header('Content-Type: application/json');

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            empty($_SESSION['user']['id'])
        ) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée.']);
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];
        $code   = preg_replace('/\D/', '', trim($_POST['code'] ?? ''));

        if (strlen($code) !== 6) {
            echo json_encode(['success' => false, 'message' => 'Code invalide.']);
            exit;
        }

        // Récupère le secret sans condition sur totp_enabled
        $stmt = $this->db->prepare("SELECT totp_secret FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row    = $stmt->fetch(\PDO::FETCH_ASSOC);
        $secret = $row['totp_secret'] ?? null;

        if (!$secret) {
            echo json_encode(['success' => false, 'message' => 'TOTP non configuré.']);
            exit;
        }

        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($secret, $code, 4)) {
            echo json_encode(['success' => false, 'message' => 'Code incorrect. Impossible de désactiver.']);
            exit;
        }

        $this->userRepository->disableTotp($userId);
        $_SESSION['user']['totp_enabled'] = false;

        echo json_encode([
            'success' => true,
            'message' => 'Google Authenticator désactivé avec succès.',
        ]);
        exit;
    }

    /**
     * Lance le processus de changement de mot de passe.
     * Envoie un code 2FA si activé, vérifie le TOTP si activé.
     * Retourne le statut de vérification nécessaire.
     *
     * @return void
     */
    public function changePasswordStart(): void
    {
        header('Content-Type: application/json');

        if (empty($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false]);
            exit;
        }

        $userId  = (int) $_SESSION['user']['id'];
        $has2fa  = !empty($_SESSION['user']['two_factor_enabled']);
        $hasTotp = !empty($_SESSION['user']['totp_enabled']);

        // Envoie le code 2FA si activé
        if ($has2fa) {
            $code = $this->userRepository->saveTwoFactorCode($userId);

            $stmt = $this->db->prepare("SELECT email, fullname FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            $mailService = new \App\Services\MailService();
            $mailService->sendTwoFactorCode($user['email'], $user['fullname'], $code);
        }

        echo json_encode([
            'success'  => true,
            'has_2fa'  => $has2fa,
            'has_totp' => $hasTotp,
        ]);
        exit;
    }

    /**
     * Vérifie le code 2FA et/ou TOTP pour le changement de mot de passe.
     * Stocke un token de vérification en session si tout est valide.
     *
     * @return void
     */
    public function changePasswordVerify(): void
    {
        header('Content-Type: application/json');

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            empty($_SESSION['user']['id'])
        ) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée.']);
            exit;
        }

        $userId   = (int) $_SESSION['user']['id'];
        $step     = trim($_POST['step']     ?? '');
        $code     = preg_replace('/\D/', '', trim($_POST['code'] ?? ''));
        $has2fa   = !empty($_SESSION['user']['two_factor_enabled']);
        $hasTotp  = !empty($_SESSION['user']['totp_enabled']);

        if (strlen($code) !== 6) {
            echo json_encode(['success' => false, 'message' => 'Code invalide.']);
            exit;
        }

        // ===== VÉRIFICATION 2FA EMAIL =====
        if ($step === '2fa') {
            $result = $this->userRepository->verifyTwoFactorCode($userId, $code);

            if (!$result['success']) {
                echo json_encode([
                    'success' => false,
                    'message' => $result['message'],
                    'locked'  => $result['locked']       ?? false,
                    'seconds' => $result['seconds_left'] ?? null,
                ]);
                exit;
            }

            // Si TOTP aussi activé → étape suivante
            if ($hasTotp) {
                echo json_encode(['success' => true, 'next' => 'totp']);
                exit;
            }

            // Sinon → peut changer le mot de passe
            $_SESSION['pwd_change_verified'] = true;
            echo json_encode(['success' => true, 'next' => 'form']);
            exit;
        }

        // ===== VÉRIFICATION TOTP =====
        if ($step === 'totp') {
            $stmt = $this->db->prepare("SELECT totp_secret FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $row    = $stmt->fetch(\PDO::FETCH_ASSOC);
            $secret = $row['totp_secret'] ?? null;

            if (!$secret) {
                echo json_encode(['success' => false, 'message' => 'TOTP non configuré.']);
                exit;
            }

            $google2fa = new \PragmaRX\Google2FA\Google2FA();

            if (!$google2fa->verifyKey($secret, $code, 4)) {
                echo json_encode(['success' => false, 'message' => 'Code incorrect. Vérifiez votre application.']);
                exit;
            }

            $_SESSION['pwd_change_verified'] = true;
            echo json_encode(['success' => true, 'next' => 'form']);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Étape inconnue.']);
        exit;
    }

    /**
     * Change le mot de passe de l'utilisateur connecté.
     * Nécessite une vérification préalable via changePasswordVerify().
     * Vérifie le mot de passe actuel avant de le remplacer.
     *
     * @return void
     */
    public function changePassword(): void
    {
        header('Content-Type: application/json');

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            empty($_SESSION['user']['id'])
        ) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée.']);
            exit;
        }

        // Vérifie que la vérification 2FA/TOTP a bien été faite
        if (empty($_SESSION['pwd_change_verified'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Vérification de sécurité requise.']);
            exit;
        }

        $userId      = (int) $_SESSION['user']['id'];
        $current     = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password']     ?? '';
        $confirm     = $_POST['confirm_password'] ?? '';

        // Validations
        if (empty($current) || empty($newPassword) || empty($confirm)) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
            exit;
        }

        if (strlen($newPassword) < 8) {
            echo json_encode(['success' => false, 'field' => 'new', 'message' => 'Le mot de passe doit contenir au moins 8 caractères.']);
            exit;
        }

        if ($newPassword !== $confirm) {
            echo json_encode(['success' => false, 'field' => 'confirm', 'message' => 'Les mots de passe ne correspondent pas.']);
            exit;
        }

        // Vérifie le mot de passe actuel
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!password_verify($current, $row['password'])) {
            echo json_encode(['success' => false, 'field' => 'current', 'message' => 'Mot de passe actuel incorrect.']);
            exit;
        }

        if (password_verify($newPassword, $row['password'])) {
            echo json_encode(['success' => false, 'field' => 'new', 'message' => 'Le nouveau mot de passe doit être différent de l\'ancien.']);
            exit;
        }

        // Met à jour le mot de passe
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$hashed, $userId]);

        // ===== DÉCONNEXION DE TOUS LES APPAREILS =====
        $logoutAll = !empty($_POST['logout_all']) && $_POST['logout_all'] === '1';

        if ($logoutAll) {
            // Supprime tous les tokens remember me
            $stmt = $this->db->prepare("DELETE FROM user_remember_tokens WHERE user_id = ?");
            $stmt->execute([$userId]);

            // Supprime tous les appareils de confiance
            $stmt = $this->db->prepare("DELETE FROM user_trusted_devices WHERE user_id = ?");
            $stmt->execute([$userId]);

            // Supprime le cookie remember me côté client
            setcookie('remember_me_token', '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            // Supprime les cookies trusted_device
            foreach ($_COOKIE as $name => $value) {
                if (str_starts_with($name, 'trusted_device_')) {
                    setcookie($name, '', [
                        'expires'  => time() - 3600,
                        'path'     => '/',
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                }
            }
        }

        // Nettoie le token de vérification
        unset($_SESSION['pwd_change_verified']);

        echo json_encode([
            'success' => true,
            'message' => 'Mot de passe modifié avec succès.',
            'logout_all' => $logoutAll,
        ]);
        exit;
    }

    /**
     * Retourne la liste des appareils de confiance de l'utilisateur.
     * Formate les dates et parse le user agent pour un affichage lisible.
     *
     * @return void
     */
    public function trustedDevices(): void
    {
        header('Content-Type: application/json');

        if (empty($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false]);
            exit;
        }

        $userId  = (int) $_SESSION['user']['id'];
        $devices = $this->userRepository->getTrustedDevices($userId);

        // Formate les données pour l'affichage
        $formatted = array_map(function (array $device) {
            $date     = new \DateTime($device['created_at']);
            $monthsFr = ['January' => 'Jan', 'February' => 'Fév', 'March' => 'Mar', 'April' => 'Avr', 'May' => 'Mai', 'June' => 'Jun', 'July' => 'Jul', 'August' => 'Aoû', 'September' => 'Sep', 'October' => 'Oct', 'November' => 'Nov', 'December' => 'Déc'];
            $day      = $date->format('j');
            $monthFr  = $monthsFr[$date->format('F')] ?? $date->format('F');
            $formatted = ($day == 1 ? '1er' : $day) . ' ' . $monthFr . ' ' . $date->format('Y');

            $expires     = new \DateTime($device['expires_at']);
            $now         = new \DateTime();
            $daysLeft    = (int) $now->diff($expires)->days;
            $isExpired   = $now > $expires;

            return [
                'id'         => $device['id'],
                'name'       => $device['name']       ?? 'Appareil inconnu',
                'ip_address' => $device['ip_address'] ?? '—',
                'created_at' => $formatted,
                'days_left'  => $isExpired ? 0 : $daysLeft,
                'is_expired' => $isExpired,
                'is_current' => $this->isCurrentDevice($device),
            ];
        }, $devices);

        echo json_encode(['success' => true, 'devices' => $formatted]);
        exit;
    }

    /**
     * Vérifie si l'appareil correspond à la session actuelle.
     *
     * @param  array $device Données de l'appareil
     * @return bool
     */
    private function isCurrentDevice(array $device): bool
    {
        $currentUa = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $currentIp = $_SERVER['REMOTE_ADDR']     ?? '';
        return $device['ip_address'] === $currentIp
            && str_contains($device['user_agent'] ?? '', substr($currentUa, 0, 30));
    }

    /**
     * Révoque un appareil de confiance spécifique.
     *
     * @return void
     */
    public function revokeTrustedDevice(): void
    {
        header('Content-Type: application/json');

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            empty($_SESSION['user']['id'])
        ) {
            http_response_code(403);
            echo json_encode(['success' => false]);
            exit;
        }

        $userId   = (int) $_SESSION['user']['id'];
        $deviceId = (int) ($_POST['device_id'] ?? 0);

        if ($deviceId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalide.']);
            exit;
        }

        $stmt = $this->db->prepare("
        DELETE FROM user_trusted_devices
        WHERE id = :id AND user_id = :user_id
    ");
        $stmt->bindValue(':id',      $deviceId, \PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId,   \PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Révoque tous les appareils de confiance de l'utilisateur.
     *
     * @return void
     */
    public function revokeAllTrustedDevices(): void
    {
        header('Content-Type: application/json');

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            empty($_SESSION['user']['id'])
        ) {
            http_response_code(403);
            echo json_encode(['success' => false]);
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];

        $stmt = $this->db->prepare("DELETE FROM user_trusted_devices WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Supprime les cookies trusted_device côté client
        foreach ($_COOKIE as $name => $value) {
            if (str_starts_with($name, 'trusted_device_')) {
                setcookie($name, '', [
                    'expires'  => time() - 3600,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
        }

        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Récupère les préférences de notifications de l'utilisateur connecté.
     *
     * @return void
     */
    public function getNotifications(): void
    {
        header('Content-Type: application/json');

        if (empty($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false]);
            exit;
        }

        $settings = $this->userRepository->getNotificationSettings((int) $_SESSION['user']['id']);

        echo json_encode(['success' => true, 'settings' => $settings]);
        exit;
    }

    /**
     * Met à jour une préférence de notification.
     * Reçoit le nom du paramètre et sa valeur booléenne.
     *
     * @return void
     */
    public function updateNotification(): void
    {
        header('Content-Type: application/json');

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            empty($_SESSION['user']['id'])
        ) {
            http_response_code(403);
            echo json_encode(['success' => false]);
            exit;
        }

        $userId  = (int) $_SESSION['user']['id'];
        $setting = trim($_POST['setting'] ?? '');
        $value   = filter_var($_POST['value'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (empty($setting)) {
            echo json_encode(['success' => false, 'message' => 'Paramètre manquant.']);
            exit;
        }

        $updated = $this->userRepository->updateNotificationSetting($userId, $setting, $value);

        echo json_encode([
            'success' => $updated,
            'message' => $updated ? 'Préférence mise à jour.' : 'Paramètre invalide.',
        ]);
        exit;
    }
}
