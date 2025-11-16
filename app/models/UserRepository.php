<?php

namespace App\Models;

use App\Core\Database;

class UserRepository
{

    protected $db;
    protected $errors = [];

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    /**
     * Retrieve user details with associated profile and remember token
     * @param int $userId
     * @return array
     */
    public function getUserWithDetails($userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    u.id AS user_id,
                    u.fullname,
                    u.email,
                    u.username,
                    u.is_confirmed,
                    up.profile_picture,
                    up.birthdate,
                    up.phone_number,
                    urt.token,
                    urt.expires_at
                FROM users u
                INNER JOIN user_profiles up ON u.id = up.user_id
                INNER JOIN user_remember_tokens urt ON u.id = urt.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Utilisateur non trouvé.'
                ];
            }

            $data = [
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $result['user_id'],
                        'fullname' => $result['fullname'],
                        'email' => $result['email'],
                        'username' => $result['username'],
                        'confirmed' => $result['is_confirmed']
                    ],
                    'profile' => [
                        'profile_picture' => $result['profile_picture'] ?? 'default.png',
                        'birthdate' => $result['birthdate'],
                        'phone_number' => $result['phone_number']
                    ],
                    'remember_token' => [
                        'token' => $result['token'],
                        'expires_at' => $result['expires_at']
                    ]
                ]
            ];

            // Stockage des informations dans la session si le succès est vrai
            if ($data['success']) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_details'] = $data['data'];
            }

            return $data;
        } catch (\Exception $e) {
            error_log('Erreur lors de la récupération des détails de l\'utilisateur: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des détails.'
            ];
        }
    }
}