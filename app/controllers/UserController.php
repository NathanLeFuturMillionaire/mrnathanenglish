<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\userRepository;

class UserController
{
    protected $db;
    protected $userRepository;
    protected $errors = [];

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();

        $this->userRepository = new userRepository(new Database);
    }

    public function user($userId)
    {
        $userId = (int) $userId;

        // Récupération des données via le model
        $user = $this->userRepository->getUserWithProfileById($userId);

        if (!$user) {
            header("Location: ./404");
            exit;
        }

        // Données accessibles dans la vue
        $id           = $user['id'];
        $fullname     = $user['fullname'];
        $username     = $user['username'];
        $email        = $user['email'];
        $created_at   = $user['created_at'];
        $is_confirmed = $user['is_confirmed'];

        $profile = [
            'profile_picture' => $user['profile_picture'],
            'birth_date'      => $user['birth_date'],
            'phone_number'    => $user['phone_number'],
            'bio'             => $user['bio'],
            'country'         => $user['country'],
        ];

        require __DIR__ . '/../views/users/profile.php';
    }
}
