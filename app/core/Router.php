<?php

namespace App\Core;

use App\Controllers\HomeController;
use App\controllers\AuthController;

class Router
{
    // Exemple dans Router.php (méthode direct)
    public function direct($url, $method)
    {
        switch ($url) {
            case '':
                // URL vide = page d'accueil
                $controller = new HomeController();
                $controller->index();  // méthode qui affiche la home
                break;

            case 'register':
                $controller = new \App\Controllers\AuthController();
                if ($method === 'POST') {
                    $controller->registerPost();
                } else {
                    $controller->register();
                }
                break;

            case 'confirm':
                $controller = new AuthController();
                if ($method === 'POST') {
                    $controller->confirmPost();
                } else {
                    $controller->confirm();
                }

            default:
                // Page 404 ou page d'accueil
                // echo "Page non trouvée.";
                // break;
        }
    }
}
