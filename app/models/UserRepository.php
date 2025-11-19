<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class UserRepository
{
    private Database $connection;
    private PDO $db;

    public function __construct(Database $connection)
    {
        $this->connection = $connection;
        // On récupère la connexion PDO une fois pour toutes
        $this->db = $this->connection->connect();
    }

    /**
     * Met à jour le mot de passe de l'utilisateur
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            return $stmt->execute([$hashedPassword, $userId]);
        } catch (\Exception $e) {
            error_log("Erreur lors de la mise à jour du mot de passe (user ID $userId) : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un utilisateur par son token de réinitialisation
     */
    public function getUserByResetToken(string $token): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE reset_token = ? LIMIT 1");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            return $user ?: null; // Retourne null si aucun utilisateur trouvé
        } catch (\Exception $e) {
            error_log("Erreur getUserByResetToken : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Supprime le token de réinitialisation après utilisation
     */
    public function clearResetToken(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
            return $stmt->execute([$userId]);
        } catch (\Exception $e) {
            error_log("Erreur clearResetToken (user ID $userId) : " . $e->getMessage());
            return false;
        }
    }
}