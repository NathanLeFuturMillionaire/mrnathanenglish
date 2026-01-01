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
}
