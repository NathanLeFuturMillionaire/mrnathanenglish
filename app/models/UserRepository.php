<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class UserRepository
{
    private Database $database;
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->db = $database->connect(); // On récupère la connexion PDO
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

    public function getUserWithProfileById(int $userId): ?array
    {
        $sql = "
            SELECT 
                u.id,
                u.fullname,
                u.username,
                u.email,
                u.is_confirmed,
                u.created_at,
                p.profile_picture,
                p.birth_date,
                p.phone_number,
                p.bio,
                p.country
            FROM users u
            INNER JOIN user_profiles p ON u.id = p.user_id
            WHERE u.id = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }
}
