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
            u.id,
            u.fullname,
            u.email,
            u.username,
            u.is_confirmed,
            u.created_at,

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

    public function getLoginHistory(int $userId, int $limit = 10): array
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
}
