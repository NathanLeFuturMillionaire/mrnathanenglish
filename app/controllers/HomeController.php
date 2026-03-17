<?php

namespace App\Controllers;

use App\Core\Database;

class HomeController
{
    protected $db;
    protected $errors = [];

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function index()
    {
        require_once __DIR__ . '/../views/home/index.php';
    }

    public function logout()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_destroy();
        header("Location: ./");
        exit;
    }

    public function endtoken()
    {
        if (isset($_COOKIE['remember_me_token'])) {
            $token = $_COOKIE['remember_me_token'];
            setcookie('remember_me_token', '', time() - 3600, '/');
            unset($_COOKIE['remember_me_token']);

            $stmt = $this->db->prepare("DELETE FROM user_remember_tokens WHERE token = ?");
            $stmt->execute([$token]);
        }

        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }

        header('Location: ./login');
        exit;
    }
}
