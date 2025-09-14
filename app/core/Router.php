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
                if ($method === 'GET') {
                    $controller->resendCode();
                } else {
                    echo json_encode([
                        "success" => false,
                        "error"   => "Méthode non autorisée"
                    ]);
                }
                break;

            case 'welcome':
                $controller = new AuthController();
                $controller->welcome();
                break;

            case 'logout':
                $controller = new HomeController();
                $controller->logout();
                break;


            case 'login':
                $controller = new AuthController();
                if ($method === "POST") {
                    $controller->loginPost();
                } else {
                    $controller->login();
                }

            // default:
            //     http_response_code(404);
            //     require __DIR__ . '/../views/errors/404.php';
            //     break;
        }
    }
}
