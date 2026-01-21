<?php

namespace App\Core;

use App\Controllers\AdminController;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\ResetPasswordController;
use App\controllers\UserController;
use App\Models\DraftRepository;
use App\Models\CourseRepository;

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
                $adminController = new AdminController();
                $adminController->listCourses();
                break;

            case 'courses/create':
                session_start();

                if (!isset($_SESSION['user']) || !($_SESSION['user']['is_admin'] ?? false)) {
                    header('Location: ./login');
                    exit;
                }

                $draftRepository = new DraftRepository();
                $adminController = new AdminController($draftRepository);

                // ======================
                // POST = AJAX AUTOSAVE
                // ======================
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $adminController->ajaxCreateCourse();
                    exit;
                }

                // ======================
                // GET = création initiale du draft
                // ======================
                $adminController->createCourse();
                break;



            case 'courses/auto-save-content':
                session_start();

                if (!isset($_SESSION['user']) || !($_SESSION['user']['is_admin'] ?? false)) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
                    exit;
                }

                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

                if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$isAjax) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
                    exit;
                }

                $draftRepository = new DraftRepository();
                $adminController = new AdminController($draftRepository);
                $adminController->autoSaveContent();
                exit;
                break;

            case 'courses/update-content':
                session_start();

                if (!isset($_SESSION['user']) || !($_SESSION['user']['is_admin'] ?? false)) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
                    exit;
                }

                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

                if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$isAjax) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
                    exit;
                }

                $courseRepository = new CourseRepository();
                $adminController  = new AdminController(null, $courseRepository);
                $adminController->autoSaveCourseContent();
                exit;
                break;


            case 'courses/delete-draft':
                session_start();

                $draftRepository = new DraftRepository();
                $adminController = new AdminController($draftRepository);

                $adminController->deleteDraft();
                break;

            case 'courses/publish':
                session_start();

                $draftRepository  = new DraftRepository();
                $courseRepository = new CourseRepository();

                $adminController = new AdminController(
                    $draftRepository,
                    $courseRepository
                );

                $adminController->publishCourse();
                break;

            case 'courses/edit':
                session_start();

                // Sécurité utilisateur connecté
                if (!isset($_SESSION['user']['id'])) {
                    header('Location: ./login');
                    exit;
                }

                // Sécurité rôle (admin / formateur)
                if (
                    empty($_SESSION['user']['is_admin']) &&
                    empty($_SESSION['user']['is_trainer'])
                ) {
                    http_response_code(403);
                    exit('Accès interdit');
                }

                $courseRepository = new CourseRepository();
                $draftRepository  = new DraftRepository();
                $adminController  = new AdminController($draftRepository, $courseRepository);

                // Détection AJAX
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

                // ======================
                // MISE À JOUR AJAX
                // ======================
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjax) {
                    $adminController->ajaxUpdateCourse();
                    exit;
                }

                // ======================
                // AFFICHAGE PAGE ÉDITION
                // ======================
                $adminController->editCourse();
                break;



                // default:
                //     http_response_code(404);
                //     require __DIR__ . '/../views/errors/404.php';
                //     break;
        }
    }
}
