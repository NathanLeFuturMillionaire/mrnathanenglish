<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class UserRepository
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Met à jour le mot de passe de l'utilisateur via son token de réinitialisation
     * 
     * @param string $token Le token de réinitialisation
     * @param string $newPassword Le nouveau mot de passe en clair
     * @return bool true si succès, false sinon
     */
    public function updatePasswordByResetToken(string $token, string $newPassword): bool
    {
        try {
            // Vérifier si le token est valide et non expiré
            $stmt = $this->db->prepare("
                SELECT * 
                FROM users 
                WHERE reset_token = ? 
                  AND reset_expires_at > NOW()
                LIMIT 1
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return false; // Token invalide ou expiré
            }

            // Hasher le nouveau mot de passe
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Mettre à jour le mot de passe
            $updateStmt = $this->db->prepare("
                UPDATE users 
                SET 
                    password = ?,
                    reset_token = NULL,
                    reset_expires_at = NULL
                WHERE id = ?
            ");

            $success = $updateStmt->execute([$hashedPassword, $user['id']]);

            return $success;
        } catch (Exception $e) {
            error_log('Erreur updatePasswordByResetToken : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère l'utilisateur à partir du token de réinitialisation
     * @param string $token
     * @return array ['success' => bool, 'data' => array|null, 'message' => string]
     */
    public function getUserByResetToken(string $token): array
    {
        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token manquant.'
            ];
        }

        try {
            $stmt = $this->db->prepare("
            SELECT id, fullname, email, username, is_confirmed
            FROM users 
            WHERE reset_token = ? 
              AND reset_expires_at > NOW()
            LIMIT 1
        ");
            $stmt->execute([$token]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user) {
                return [
                    'success' => true,
                    'data'    => $user
                ];
            }

            return [
                'success' => false,
                'message' => 'Token invalide ou expiré.'
            ];
        } catch (\Exception $e) {
            error_log('Erreur getUserByResetToken : ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur serveur lors de la vérification du token.'
            ];
        }
    }
    /**
     * Récupère un utilisateur avec son profil et toutes ses informations d'abonnement
     *
     * @param int $userId
     * @return array|null Tableau associatif contenant toutes les données ou null si non trouvé
     */
    public function getUserWithProfileAndSubscriptionsById(int $userId): ?array
    {
        $sql = "SELECT 
            u.*,

            p.profile_picture,
            p.birth_date,
            p.phone_number,
            p.bio,
            p.country,
            p.english_level,
            p.native_language,

            s.id AS subscription_id,
            s.type AS subscription_type,
            s.amount,
            s.currency,
            s.pawa_pay_deposit_id,
            s.pawa_pay_status,
            s.pawa_pay_correlation_id,
            s.pawa_pay_mobile_number,
            s.pawa_pay_country_code,
            s.pawa_pay_operator,
            s.pawa_pay_response_raw,
            s.billing_period,
            s.start_date,
            s.end_date,
            s.next_billing_date,
            s.status AS subscription_status,
            s.canceled_at,
            s.ended_at,
            s.created_at AS subscription_created_at,
            s.updated_at AS subscription_updated_at
        FROM users u
        LEFT JOIN user_profiles p ON u.id = p.user_id
        LEFT JOIN subscriptions s ON u.id = s.id_user
        WHERE u.id = ?
        ORDER BY s.start_date DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!$rows) {
                return null;
            }

            return $rows; // Retourne un tableau indexé avec toutes les lignes (1 ligne utilisateur + autant de lignes que d'abonnements)
        } catch (\Exception $e) {
            error_log('Erreur getUserWithProfileAndSubscriptionsById : ' . $e->getMessage());
            return null;
        }
    }

    public function updateUser(int $userId, array $data): bool
    {
        // ===== TABLE users =====
        $stmtUser = $this->db->prepare("
            UPDATE users
            SET
                username   = :username,
                fullname   = :fullname,
                email      = :email
            WHERE id = :id
        ");

        $stmtUser->bindValue(':username', $data['username']);
        $stmtUser->bindValue(':fullname', $data['fullname']);
        $stmtUser->bindValue(':email',    $data['email']);
        $stmtUser->bindValue(':id',       $userId, \PDO::PARAM_INT);
        $stmtUser->execute();

        // ===== TABLE user_profiles =====
        // Vérifie si le profil existe déjà
        $stmtCheck = $this->db->prepare("
            SELECT id FROM user_profiles WHERE user_id = :user_id LIMIT 1
        ");
        $stmtCheck->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmtCheck->execute();
        $profileExists = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

        if ($profileExists) {
            // UPDATE
            $stmtProfile = $this->db->prepare("
                UPDATE user_profiles
                SET
                    phone_number    = :phone_number,
                    country         = :country,
                    bio             = :bio,
                    profile_picture = :profile_picture,
                    birth_date      = :birth_date,
                    english_level   = :english_level,
                    native_language = :native_language,
                    updated_at      = NOW()
                WHERE user_id = :user_id
            ");
        } else {
            // INSERT si le profil n'existe pas encore
            $stmtProfile = $this->db->prepare("
                INSERT INTO user_profiles
                (user_id, phone_number, country, bio, profile_picture, birth_date, english_level, native_language, updated_at)
                VALUES
                (:user_id, :phone_number, :country, :bio, :profile_picture, :birth_date, :english_level, :native_language, NOW())
                ");
        }

        $stmtProfile->bindValue(':user_id',         $userId, \PDO::PARAM_INT);
        $stmtProfile->bindValue(':phone_number',    $data['phone_number']);
        $stmtProfile->bindValue(':country',         $data['country']);
        $stmtProfile->bindValue(':bio',             $data['bio']);
        $stmtProfile->bindValue(':profile_picture', $data['profile_picture']);
        $stmtProfile->bindValue(':birth_date',    $data['birth_date']);
        $stmtProfile->bindValue(':english_level', $data['english_level']);
        $stmtProfile->bindValue(':native_language', $data['native_language'] ?: null);

        return $stmtProfile->execute();
    }

    public function findByUsername(string $username): array|false
    {
        $stmt = $this->db->prepare("SELECT id, username FROM users WHERE username = :username LIMIT 1");
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: false;
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT id, email FROM users WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: false;
    }

    public function logLogin(int $userId): bool
    {
        $stmt = $this->db->prepare("
        INSERT INTO user_login_history (user_id, ip_address, user_agent)
        VALUES (:user_id, :ip_address, :user_agent)
    ");

        $stmt->bindValue(':user_id',    $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':ip_address', $this->getIpAddress());
        $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? null);

        return $stmt->execute();
    }

    public function getLoginHistory(int $userId, int $limit = 4): array
    {
        $stmt = $this->db->prepare("
        SELECT
            id,
            ip_address,
            user_agent,
            created_at
        FROM user_login_history
        WHERE user_id = :user_id
        ORDER BY created_at DESC
        LIMIT :limit
    ");

        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit',   $limit,  \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countLoginHistory(int $userId): int
    {
        $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM user_login_history WHERE user_id = :user_id
    ");
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    private function getIpAddress(): string
    {
        // Gère les proxys et load balancers
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    public function deleteLoginEntry(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare("
        DELETE FROM user_login_history
        WHERE id = :id AND user_id = :user_id
    ");
        $stmt->bindValue(':id',      $id,     \PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Active ou désactive le 2FA
    public function toggleTwoFactor(int $userId, bool $enabled): bool
    {
        $stmt = $this->db->prepare("
        UPDATE users
        SET two_factor_enabled = :enabled
        WHERE id = :id
    ");
        $stmt->bindValue(':enabled', $enabled ? 1 : 0, \PDO::PARAM_INT);
        $stmt->bindValue(':id',      $userId,           \PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Génère et sauvegarde un code 2FA (expire dans 10 minutes)
    public function saveTwoFactorCode(int $userId): string
    {
        $code      = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Utilise la même source de temps que MySQL pour éviter les décalages
        $stmt = $this->db->prepare("
            UPDATE users
            SET two_factor_code    = :code,
                two_factor_expires = DATE_ADD(NOW(), INTERVAL 10 MINUTE)
            WHERE id = :id
        ");
        $stmt->bindValue(':code', $code);
        $stmt->bindValue(':id',   $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return $code;
    }

    // Vérifie le code 2FA
    public function verifyTwoFactorCode(int $userId, string $code): array
    {
        // Vérifie si le compte est verrouillé
        $stmtLock = $this->db->prepare("
            SELECT two_factor_attempts, two_factor_locked_until
            FROM users
            WHERE id = :id
        ");
        $stmtLock->bindValue(':id', $userId, \PDO::PARAM_INT);
        $stmtLock->execute();
        $lockData = $stmtLock->fetch(\PDO::FETCH_ASSOC);

        // Compte verrouillé
        if (!empty($lockData['two_factor_locked_until'])) {
            $lockedUntil = new \DateTime($lockData['two_factor_locked_until']);
            $now         = new \DateTime();

            if ($now < $lockedUntil) {
                $secondsLeft = $now->diff($lockedUntil)->s + ($now->diff($lockedUntil)->i * 60);
                return [
                    'success'      => false,
                    'locked'       => true,
                    'seconds_left' => $secondsLeft,
                    'message'      => 'Compte temporairement bloqué.',
                ];
            } else {
                // Verrou expiré — réinitialise
                $this->resetTwoFactorAttempts($userId);
            }
        }

        // Vérifie le code
        $stmt = $this->db->prepare("
            SELECT id FROM users
            WHERE id              = :id
              AND two_factor_code = :code
              AND two_factor_expires > NOW()
        ");
        $stmt->bindValue(':id',   $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':code', $code);
        $stmt->execute();

        if (!$stmt->fetch()) {
            // Incrémente les tentatives
            $attempts = (int) ($lockData['two_factor_attempts'] ?? 0) + 1;

            if ($attempts >= 5) {
                // Verrouille pendant 10 minutes
                $lockStmt = $this->db->prepare("
                    UPDATE users
                    SET two_factor_attempts     = :attempts,
                        two_factor_locked_until = DATE_ADD(NOW(), INTERVAL 10 MINUTE)
                    WHERE id = :id
                ");
                $lockStmt->bindValue(':attempts', $attempts, \PDO::PARAM_INT);
                $lockStmt->bindValue(':id',       $userId,   \PDO::PARAM_INT);
                $lockStmt->execute();

                return [
                    'success'      => false,
                    'locked'       => true,
                    'seconds_left' => 600,
                    'message'      => 'Trop de tentatives. Réessayez dans 10 minutes.',
                ];
            }

            // Met à jour le compteur
            $incrStmt = $this->db->prepare("
                UPDATE users SET two_factor_attempts = :attempts WHERE id = :id
            ");
            $incrStmt->bindValue(':attempts', $attempts, \PDO::PARAM_INT);
            $incrStmt->bindValue(':id',       $userId,   \PDO::PARAM_INT);
            $incrStmt->execute();

            return [
                'success'          => false,
                'locked'           => false,
                'attempts_left'    => 5 - $attempts,
                'message'          => 'Code incorrect.',
            ];
        }

        // Code valide — réinitialise tout
        $this->resetTwoFactorAttempts($userId);

        $clean = $this->db->prepare("
            UPDATE users
            SET two_factor_code = NULL, two_factor_expires = NULL
            WHERE id = :id
        ");
        $clean->bindValue(':id', $userId, \PDO::PARAM_INT);
        $clean->execute();

        return ['success' => true];
    }

    private function resetTwoFactorAttempts(int $userId): void
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET two_factor_attempts     = 0,
                two_factor_locked_until = NULL
            WHERE id = :id
        ");
        $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
    }

    // Vérifie si le 2FA est activé pour un utilisateur
    public function hasTwoFactorEnabled(int $userId): bool
    {
        $stmt = $this->db->prepare("
        SELECT two_factor_enabled FROM users WHERE id = :id
    ");
        $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (bool) ($row['two_factor_enabled'] ?? false);
    }

    // Enregistre un navigateur de confiance
    public function saveTrustedDevice(int $userId, string $userAgent): string
    {
        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        // Détecte le nom lisible du navigateur/OS
        $browser = match (true) {
            str_contains($userAgent, 'Edg')     => 'Edge',
            str_contains($userAgent, 'Chrome')  => 'Chrome',
            str_contains($userAgent, 'Firefox') => 'Firefox',
            str_contains($userAgent, 'Safari')  => 'Safari',
            str_contains($userAgent, 'Opera')  => 'Opera',
            default                             => 'Navigateur'
        };

        $os = match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'iPhone')  => 'iPhone',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'Mac')     => 'macOS',
            str_contains($userAgent, 'Linux')   => 'Linux',
            default                             => 'OS inconnu'
        };

        $stmt = $this->db->prepare("
        INSERT INTO user_trusted_devices
            (user_id, token, user_agent, ip_address, name, expires_at)
        VALUES
            (:user_id, :token, :ua, :ip, :name, :expires)
    ");

        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':token',   $token);
        $stmt->bindValue(':ua',      $userAgent);
        $stmt->bindValue(':ip',      $this->getIpAddress());
        $stmt->bindValue(':name',    $browser . ' sur ' . $os);
        $stmt->bindValue(':expires', $expiresAt);
        $stmt->execute();

        return $token;
    }

    // Vérifie si le navigateur est de confiance
    public function isTrustedDevice(int $userId, string $token): bool
    {
        $stmt = $this->db->prepare("
        SELECT id FROM user_trusted_devices
        WHERE user_id  = :user_id
          AND token    = :token
          AND expires_at > NOW()
    ");
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':token',   $token);
        $stmt->execute();
        return (bool) $stmt->fetch();
    }

    // Supprime un appareil de confiance
    public function deleteTrustedDevice(int $userId, string $token): bool
    {
        $stmt = $this->db->prepare("
        DELETE FROM user_trusted_devices
        WHERE user_id = :user_id AND token = :token
    ");
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':token',   $token);
        return $stmt->execute();
    }

    // Liste les appareils de confiance
    public function getTrustedDevices(int $userId): array
    {
        $stmt = $this->db->prepare("
        SELECT id, name, ip_address, created_at, expires_at
        FROM user_trusted_devices
        WHERE user_id = :user_id AND expires_at > NOW()
        ORDER BY created_at DESC
    ");
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    /**
     * Sauvegarde le secret TOTP et active Google Authenticator pour l'utilisateur.
     *
     * @param  int    $userId Identifiant de l'utilisateur
     * @param  string $secret Secret TOTP généré par la librairie OTPHP
     * @return bool          True si la mise à jour a réussi
     */
    public function saveTotpSecret(int $userId, string $secret): bool
    {
        $stmt = $this->db->prepare("
        UPDATE users
        SET totp_secret  = :secret,
            totp_enabled = 1
        WHERE id = :id
    ");
        $stmt->bindValue(':secret', $secret);
        $stmt->bindValue(':id',     $userId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Désactive Google Authenticator et supprime le secret TOTP de l'utilisateur.
     *
     * @param  int  $userId Identifiant de l'utilisateur
     * @return bool         True si la mise à jour a réussi
     */
    public function disableTotp(int $userId): bool
    {
        $stmt = $this->db->prepare("
        UPDATE users
        SET totp_secret  = NULL,
            totp_enabled = 0
        WHERE id = :id
    ");
        $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Récupère le secret TOTP d'un utilisateur si Google Authenticator est activé.
     *
     * @param  int         $userId Identifiant de l'utilisateur
     * @return string|null         Le secret TOTP ou null si non configuré
     */
    public function getTotpSecret(int $userId): ?string
    {
        $stmt = $this->db->prepare("
        SELECT totp_secret FROM users
        WHERE id = :id AND totp_enabled = 1
    ");
        $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['totp_secret'] ?? null;
    }
}
