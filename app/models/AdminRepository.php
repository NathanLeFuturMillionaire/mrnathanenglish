<?php

namespace App\Models;

use PDO;

class AdminRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Vérifie si un admin existe via son username
     */
    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT 
                    a.id,
                    a.user_id,
                    a.username,
                    a.email,
                    a.role,
                    a.is_active,
                    a.created_at
                FROM admins a
                WHERE a.username = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        return $admin ?: null;
    }

    /**
     * Vérifie si un admin est actif
     */
    public function isActive(string $username): bool
    {
        $sql = "SELECT id FROM admins 
                WHERE username = ? AND is_active = 1
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Récupérer un admin par user_id
     */
    public function findByUserId(int $userId): ?array
    {
        $sql = "SELECT * FROM admins WHERE user_id = ? LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        return $admin ?: null;
    }

    /**
     * Créer un admin
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO admins (
                    user_id,
                    username,
                    email,
                    role,
                    is_active,
                    created_at
                ) VALUES (
                    :user_id,
                    :username,
                    :email,
                    :role,
                    1,
                    NOW()
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':user_id'  => $data['user_id'],
            ':username' => $data['username'],
            ':email'    => $data['email'],
            ':role'     => $data['role'] ?? 'admin'
        ]);
    }

    /**
     * Désactiver un admin
     */
    public function disable(int $adminId): bool
    {
        $sql = "UPDATE admins SET is_active = 0 WHERE id = ?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$adminId]);
    }

    /**
     * Mettre à jour la dernière connexion
     */
    public function updateLastLogin(int $adminId): void
    {
        $sql = "UPDATE admins SET last_login = NOW() WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$adminId]);
    }
}
