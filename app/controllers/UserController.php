<?php

namespace App\controllers;

use App\Models\UserRepository;
use App\Core\Database;

class UserController
{
    public function user($userId)
    {
        $userRepository = new UserRepository();
        $user = $userRepository->getUserWithDetails($userId);

        require __DIR__ . '/../views/users/profile.php';

    }
}