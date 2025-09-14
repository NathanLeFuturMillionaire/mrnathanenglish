<?php
namespace App\Controllers;

class HomeController {
    public function index() {
        // On peut préparer des données à envoyer à la vue ici si besoin

        // Inclure la vue
        require_once __DIR__ . '/../views/home/index.php';
    }

    public function logout() {
        session_start();

        if(isset($_SESSION)) {
            // On arrête la session et on redirige vers la page d'acceuil
            session_destroy();
            header("Location: ./");
        }
    }
}
