<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\UserRepository;
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

            echo json_encode([
                'success' => true,
                'message' => 'Profil mis à jour avec succès.',
                'user'    => [
                    'username'        => $username,
                    'fullname'        => $fullname,
                    'email'           => $email,
                    'phone_number'    => $phoneNumber,
                    'country'         => $country,
                    'bio'             => $bio,
                    'profile_picture' => $profilePicture,
                    'birth_date'      => $birthDate,
                    'english_level'   => $englishLevel,
                ]
            ]);
            exit;
        } catch (Throwable $e) {
            error_log('[updateProfile] ' . $e->getMessage());

            // Débogage
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(), // ← affiche l'erreur réelle
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ]);
            exit;

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
}
