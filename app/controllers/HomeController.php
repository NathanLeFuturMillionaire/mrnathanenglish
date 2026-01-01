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
        // On peut préparer des données à envoyer à la vue ici si besoin

        // Inclure la vue
        require_once __DIR__ . '/../views/home/index.php';
    }

    public function logout()
    {
        session_start();

        if (isset($_SESSION)) {
            // On arrête la session et on redirige vers la page d'acceuil
            session_destroy();
            header("Location: ./");
        }
    }

    public function endtoken()
    {
        // Vérifier si le cookie existe
        if (isset($_COOKIE['remember_me_token'])) {
            $token = $_COOKIE['remember_me_token'];

            // Supprimer le cookie côté navigateur
            setcookie('remember_me_token', '', time() - 3600, '/');
            unset($_COOKIE['remember_me_token']);

            // Si tu veux supprimer/mettre à jour le token dans la base (optionnel)
            $sql = "DELETE FROM user_remember_tokens WHERE token = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$token]);
        }

        // Détruire la session si besoin
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }

        // Redirection vers login ou page souhaitée
        header('Location: ./login');
        exit;
    }
}
