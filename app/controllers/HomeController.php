<?php
namespace App\Controllers;

class HomeController {
    public function index() {
        // On peut préparer des données à envoyer à la vue ici si besoin

        // Inclure la vue
        require_once __DIR__ . '/../views/home/index.php';
    }
}
