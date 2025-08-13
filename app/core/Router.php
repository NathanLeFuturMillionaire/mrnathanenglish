<?php

namespace App\Core;

use App\Controllers\HomeController;
use App\Controllers\AuthController;

class Router
{
    public function direct($url, $method)
    {
        switch ($url) {
            case '':
                // Page d'accueil
                $controller = new HomeController();
                $controller->index();
                break;

            case 'register':
                $controller = new AuthController();
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
                break;

            case 'resend-code':
                $controller = new AuthController();
                // GET pour ton JS actuel
                if ($method === 'GET') {
                    $controller->resendCode();
                } else {
                    echo json_encode(["success" => false, "error" => "Méthode non autorisée"]);
                }
                break;

            default:
                // Page 404
                http_response_code(404);
                echo "<h1>404 - Page non trouvée</h1>";
                break;
        }
    }
}
