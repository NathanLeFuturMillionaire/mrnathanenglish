<?php

namespace App\Core;

use App\Controllers\AdminController;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\ResetPasswordController;
use App\controllers\UserController;
use App\Models\DraftRepository;
use App\Models\CourseRepository;
use App\Controllers\CourseController;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Router
{
    public function direct($url, $method)
    {
        $urlParts = explode('/', trim($url, '/'));

        // Reconstruction propre de la route (1 ou 2 segments)
        $segment0 = $urlParts[0] ?? '';
        $segment1 = $urlParts[1] ?? '';
        $segment2 = $urlParts[2] ?? null;
        $segment3 = $urlParts[3] ?? null;
        $segment4 = $urlParts[4] ?? null;

        // Route = "courses/view" ou juste "courses"
        $route = $segment1 !== '' ? $segment0 . '/' . $segment1 : $segment0;

        // ID dans l'URL (3ème segment)
        if ($segment2 !== null && $segment2 !== '') {
            $_GET['id'] = (int) $segment2;
        }
        switch ($route) {
            case '':
                // Page d'accueil
                $controller = new HomeController();
                $controller->index();
                break;

            case 'register':
                $controller = new AuthController();
                if ($method === 'POST') {
                    $controller->register();
                } else {
                    $controller->registerPage();
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
                    $controller->showLogin();
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


            case 'courses/view':
                session_start();

                // Sécurité : utilisateur connecté
                if (!isset($_SESSION['user']['id'])) {
                    header('Location: ./login');
                    exit;
                }

                $courseController = new CourseController();
                $courseController->viewCourse();
                break;
            // default:
            //     http_response_code(404);
            //     require __DIR__ . '/../views/errors/404.php';
            //     break;

            case 'courses/lesson':
                session_start();

                if (!isset($_SESSION['user']['id'])) {
                    header('Location: ./login');
                    exit;
                }

                // $urlParts[2] = id_course, [3] = moduleIndex, [4] = lessonIndex
                $_GET['id']            = isset($urlParts[2]) ? (int) $urlParts[2] : null;
                $_GET['module_index']  = isset($urlParts[3]) ? (int) $urlParts[3] : 0;
                $_GET['lesson_index']  = isset($urlParts[4]) ? (int) $urlParts[4] : 0;

                $courseController = new CourseController();
                $courseController->viewLesson();
                break;

            case 'profile/update':
                session_start();

                if (!isset($_SESSION['user']['id'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
                    exit;
                }

                $userController = new UserController();
                $userController->updateProfile();
                break;

            case 'profile/delete-login':
                session_start();

                if (!isset($_SESSION['user']['id'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false]);
                    exit;
                }

                $userController = new UserController();
                $userController->deleteLogin();
                break;

            case 'profile/login-history':
                session_start();

                if (!isset($_SESSION['user']['id'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false]);
                    exit;
                }

                $userController = new UserController();
                $userController->loginHistory();
                break;

            case 'profile/toggle-2fa':
                session_start();
                $userController = new UserController();
                $userController->toggleTwoFactor();
                break;

            case 'auth/verify-2fa':
                session_start();
                $authController = new AuthController();
                $authController->verifyTwoFactor();
                break;

            case 'verify-2fa':
                session_start();
                if ($method === 'POST') {
                    $authController = new AuthController();
                    $authController->verifyTwoFactor();
                } else {
                    require __DIR__ . '/../views/auth/verify2fa.php';
                }
                break;

            case 'auth/resend-2fa':
                session_start();
                $authController = new AuthController();
                $authController->resendTwoFactorCode();
                break;

            case 'auth/check-2fa-lock':
                session_start();
                $authController = new AuthController();
                $authController->checkTwoFactorLock();
                break;

            case 'noconfirmed':
                $authController = new AuthController();
                $authController->noConfirmed();
                break;

            case 'profile/generate-totp':
                session_start();
                $userController = new UserController();
                $userController->generateTotp();
                break;

            case 'profile/activate-totp':
                session_start();
                $userController = new UserController();
                $userController->activateTotp();
                break;

            case 'profile/disable-totp':
                session_start();
                $userController = new UserController();
                $userController->disableTotp();
                break;

            case 'auth/verify-totp':
                session_start();
                if ($method === 'POST') {
                    $authController = new AuthController();
                    $authController->verifyTotp();
                } else {
                    require __DIR__ . '/../views/auth/verifyTotp.php';
                }
                break;
            case 'profile/change-password-verify':
                session_start();
                $userController = new UserController();
                $userController->changePasswordVerify();
                break;

            case 'profile/change-password':
                session_start();
                $userController = new UserController();
                $userController->changePassword();
                break;

            case 'profile/change-password-start':
                session_start();
                $userController = new UserController();
                $userController->changePasswordStart();
                break;

            case 'profile/trusted-devices':
                session_start();
                $userController = new UserController();
                $userController->trustedDevices();
                break;

            case 'profile/revoke-device':
                session_start();
                $userController = new UserController();
                $userController->revokeTrustedDevice();
                break;

            case 'profile/revoke-all-devices':
                session_start();
                $userController = new UserController();
                $userController->revokeAllTrustedDevices();
                break;

            case 'profile/get-notifications':
                session_start();
                $userController = new UserController();
                $userController->getNotifications();
                break;

            case 'profile/update-notification':
                session_start();
                $userController = new UserController();
                $userController->updateNotification();
                break;

            case 'profile/delete-account-start':
                session_start();
                $userController = new UserController();
                $userController->deleteAccountStart();
                break;

            case 'profile/delete-account-verify':
                session_start();
                $userController = new UserController();
                $userController->deleteAccountVerify();
                break;

            case 'profile/delete-account':
                session_start();
                $userController = new UserController();
                $userController->deleteAccount();
                break;
        }
    }
}
