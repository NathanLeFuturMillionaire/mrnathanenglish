<?php

namespace App\Core;

use App\Controllers\AdminController;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\ResetPasswordController;
use App\controllers\UserController;
use App\Models\DraftRepository;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
                break;

            case 'dashboard':
                session_start();
                $adminController = new AdminController();
                if (isset($_SESSION["user"]["id"])) {
                    $adminController->dashboard();
                }
                break;

            case 'courses':
                session_start();

                // Sécurité renforcée : utilisateur connecté ET admin/formateur
                if (!isset($_SESSION['user'])) {
                    header("Location: ./login");
                    exit;
                }

                if (!($_SESSION['user']['is_admin'] ?? false)) {
                    // Si ce n'est pas un admin, redirection vers 404 ou tableau de bord étudiant
                    header("Location: ./profile");
                    exit;
                }

                // Instanciation du controller
                $adminController = new AdminController();

                // Appel de la méthode qui affiche la liste des cours du formateur
                $adminController->listCourses();
                break;

            case 'courses/create':
                session_start();

                if (!isset($_SESSION['user']) || !($_SESSION['user']['is_admin'] ?? false)) {
                    header("Location: ./login");
                    exit;
                }

                // Nouvelles instanciations
                $draftRepository = new DraftRepository();
                $adminController = new AdminController();

                // Détection AJAX
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

                if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjax) {
                    $adminController->ajaxCreateCourse();
                    exit;
                }

            case 'courses/create':
                // session_start();

                // Sécurité : utilisateur connecté et admin/formateur
                if (!isset($_SESSION['user'])) {
                    header("Location: ./login");
                    exit;
                }

                if (!($_SESSION['user']['is_admin'] ?? false)) {
                    header("Location: ./404");
                    exit;
                }

                $adminController = new AdminController();
                $trainerId = (int)$_SESSION['user']['id'];

                // === VÉRIFICATION : L'utilisateur a-t-il déjà un brouillon ? ===
                $existingDraft = $draftRepository->findByTrainer($trainerId);

                // Si aucun brouillon n'existe → on en crée un vide automatiquement
                if (!$existingDraft) {
                    // Création d'un brouillon vide avec structure minimale
                    $emptyDraftData = [
                        'course_infos' => [
                            'title_course'       => '',
                            'description_course' => '',
                            'language_taught'    => '',
                            'learner_level'      => '',
                            'time_course'        => null,
                            'validation_period' => null,
                            'price_course'       => 0,
                            'is_free'            => 0,
                            'publish_now'        => 0,
                            'profile_picture'    => null
                        ],
                        'modules' => []
                    ];

                    $jsonData = json_encode($emptyDraftData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                    $draftRepository->create($trainerId, $jsonData);
                } else {
                    // Création d'un brouillon vide avec structure minimale
                    $emptyDraftData = [
                        'course_infos' => [
                            'title_course'       => '',
                            'description_course' => '',
                            'language_taught'    => '',
                            'learner_level'      => '',
                            'time_course'        => null,
                            'validation_period' => null,
                            'price_course'       => 0,
                            'is_free'            => 0,
                            'publish_now'        => 0,
                            'profile_picture'    => null
                        ],
                        'modules' => []
                    ];
                    $jsonData = json_encode($emptyDraftData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                    $draftRepository->create($trainerId, $jsonData);
                }

                // Affichage de la page de création (avec ou sans brouillon existant)
                $adminController->createCourse();
                break;

                // default:
                //     http_response_code(404);
                //     require __DIR__ . '/../views/errors/404.php';
                //     break;
        }
    }
}
