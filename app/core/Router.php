<?php

namespace App\Core;

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\ResetPasswordController;
use App\controllers\UserController;

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

            case "persistLogin":
                $controller = new AuthController();
                $controller->loginAsUser();
                break;

            case 'logout':
                $controller = new HomeController();
                $controller->logout();
                break;

            case 'endtoken':
                $controller = new HomeController();
                $controller->endtoken();
                break;
            case 'forgot-password':
                $controller = new AuthController();
                if ($method === "POST") {
                    $controller->forgotPasswordPost();
                } else {
                    $controller->forgotPasswordPage();
                }
                break;

            case 'login':
                $controller = new AuthController();
                if ($method === "POST") {
                    $controller->loginPost();
                } else {
                    $controller->login();
                }
                break;

            case 'admins':
                $controller = new AuthController();
                if ($method === 'POST') {
                    $controller->addMemberPost(); // Ajout du nouveau membre premium
                } else {
                    $controller->adminPage();
                }
                break;

            case 'reset-password':
                $controller = new ResetPasswordController();
                $token = $_GET['token'] ?? null;

                if ($method === 'POST') {
                    // Traitement de la soumission du formulaire (mise à jour du mot de passe)
                    $controller->reset();
                } else {
                    // Affichage de la page avec le token
                    if ($token && $token !== '') {
                        $controller->resetPasswordPage($token);
                    } else {
                        // Token manquant → erreur ou redirection
                        $controller->resetPasswordPage(null); // ou une page d'erreur dédiée
                    }
                }
                break;

            case 'admins/members':
                $controller = new AuthController();
                if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'delete') {
                    // $controller->deleteMember();
                } else {
                    $controller->membersPage();
                }
                break;

            case 'members/edit':
                $controller = new AuthController();
                // $controller->editMember();
                break;

            case 'profile':
                session_start();
                $userController = new UserController();
                if (isset($_SESSION['user']['id'])) {
                    $userController->user($_SESSION['user']['id']);
                } else {
                    // var_dump($_SESSION);
                    header('Location: ./');
                }

                // default:
                //     http_response_code(404);
                //     require __DIR__ . '/../views/errors/404.php';
                //     break;
        }
    }
}
