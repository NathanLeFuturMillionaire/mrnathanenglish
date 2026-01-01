<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\CourseRepository;
use App\Models\DraftRepository;
use Exception;

class AdminController
{

    private DraftRepository $draftRepository;
    protected $db;
    protected $courseRepository;

    public function __construct()
    {

        // Sécurité : utilisateur connecté
        if (!isset($_SESSION['user'])) {
            header('Location: ./login');
            exit;
        }

        // Sécurité : admin uniquement
        if (!($_SESSION['user']['is_admin'] ?? false)) {
            header('Location: ./404');
            exit;
        }

        // Connexion à la base
        $database = new Database();
        $this->db = $database->connect();

        // Repository
        $this->courseRepository = new CourseRepository($this->db);

        // DraftRepository
        $this->draftRepository = new DraftRepository($this->db);
    }


    /**
     * Dashboard admin
     */
    public function dashboard(): void
    {
        require __DIR__ . '/../views/admins/dashboard.php';
    }

    /**
     * Page de création de cours
     */
    public function createCourse(): void
    {
        $draft = $this->draftRepository->findByTrainer($_SESSION['user']['id']);

        // Récupération sécurisée des données du brouillon (injectées par le contrôleur)
        $draftData = $draft ?? []; // Tableau contenant toutes les colonnes de la table draft

        // Valeurs par défaut pour éviter les notices PHP et assurer une expérience fluide
        $title            = htmlspecialchars($draftData['title_course'] ?? '');
        $description      = htmlspecialchars($draftData['description_course'] ?? '');
        $language         = htmlspecialchars($draftData['language_taught'] ?? '');
        $level            = htmlspecialchars($draftData['learner_level'] ?? '');
        $duration         = htmlspecialchars($draftData['time_course'] ?? '');
        $validationPeriod = htmlspecialchars($draftData['validation_period'] ?? '');
        $price            = htmlspecialchars($draftData['price_course'] ?? '0');
        $is_free          = !empty($draftData['is_free']) ? (int)$draftData['is_free'] : 1; // 1 = gratuit par défaut
        $profilePicture   = $draftData['profile_picture'] ?? null;
        $draftId          = $draftData['id'] ?? null;

        $modules = []; // futur autosave step 2

        require __DIR__ . '/../views/admins/courses/createCourse.php';
    }

    /**
     * AJAX : Étape 1 du wizard – Sauvegarde des informations générales dans le brouillon (table draft)
     * Le cours n'est PAS créé dans la table courses à ce stade
     */

    /**
     * AJAX : Étape 1 du wizard – Sauvegarde auto dans la table draft
     */
    public function ajaxCreateCourse(): void
    {
        header('Content-Type: application/json');

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$isAjax) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
            exit;
        }

        $trainerId = $_SESSION['user']['id'];

        // Données formulaire
        $title            = trim($_POST['title_course'] ?? '');
        $description      = trim($_POST['description_course'] ?? '');
        $language         = $_POST['language_taught'] ?? '';
        $level            = $_POST['learner_level'] ?? '';
        $duration         = !empty($_POST['time_course']) ? (int)$_POST['time_course'] : null;
        $validationPeriod = !empty($_POST['validation_period']) ? (int)$_POST['validation_period'] : null;
        $price            = (float)($_POST['price_course'] ?? 0);
        $isFree           = isset($_POST['is_free']) ? 1 : 0;

        // Validation minimale (compatible autosave)
        $errors = [];
        if ($title === '') $errors[] = "Le titre est obligatoire.";
        if ($description === '') $errors[] = "La description est obligatoire.";
        if ($language === '') $errors[] = "La langue est obligatoire.";
        if ($level === '') $errors[] = "Le niveau est obligatoire.";
        if ($validationPeriod === null) $errors[] = "La période de validation est obligatoire.";
        if ($price < 0) $errors[] = "Le prix ne peut pas être négatif.";

        // Brouillon existant ?
        $draft = $this->draftRepository->findByTrainer($trainerId);

        // Image existante
        $oldImagePath = $draft['profile_picture'] ?? null;
        $newImagePath = $oldImagePath;

        // Upload image (si fournie)
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/courses/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $errors[] = "Format d’image non autorisé.";
            } elseif ($_FILES['profile_picture']['size'] > 5 * 1024 * 1024) {
                $errors[] = "Image trop lourde (max 5 Mo).";
            } else {
                $fileName = uniqid('course_') . '.' . $ext;
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $filePath)) {
                    $newImagePath = '/uploads/courses/' . $fileName;

                    if ($oldImagePath && file_exists(__DIR__ . '/../../public' . $oldImagePath)) {
                        @unlink(__DIR__ . '/../../public' . $oldImagePath);
                    }
                } else {
                    $errors[] = "Échec de l’upload de l’image.";
                }
            }
        }

        // Image obligatoire uniquement à la première sauvegarde
        if (!$draft && !$newImagePath) {
            $errors[] = "L’image de couverture est obligatoire.";
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Créer brouillon si inexistant
        if (!$draft) {
            $draftId = $this->draftRepository->createEmptyDraft($trainerId);
            if (!$draftId) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Création du brouillon impossible.']);
                exit;
            }
        } else {
            $draftId = $draft['id'];
        }

        // Données à persister
        $draftData = [
            'title_course'       => $title,
            'description_course' => $description,
            'profile_picture'    => $newImagePath,
            'time_course'        => $duration,
            'validation_period'  => $validationPeriod,
            'price_course'       => $price,
            'language_taught'    => $language,
            'learner_level'      => $level,
            'is_free'            => $isFree
        ];

        // Mise à jour
        if (!$this->draftRepository->updateDraft($draftId, $draftData)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Échec de la sauvegarde.']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'draft_id' => $draftId,
            'message' => 'Brouillon enregistré automatiquement.'
        ]);
        exit;
    }


    /**
     * Page réservée à l'admin / formateur :
     * Liste des cours publiés + brouillons
     */
    public function listCourses(): void
    {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: ./login');
            exit;
        }

        if (
            empty($_SESSION['user']['is_admin']) &&
            empty($_SESSION['user']['is_trainer'])
        ) {
            http_response_code(403);
            exit('Accès interdit');
        }

        // Sécurité minimale : un formateur authentifié
        $trainerId = (int) ($_SESSION['user']['id'] ?? 0);

        if ($trainerId <= 0) {
            http_response_code(403);
            exit('Accès interdit');
        }

        // 1. Cours publiés
        $publishedCourses = $this->courseRepository
            ->findByTrainerPublished($trainerId);

        // 2. Brouillons
        $drafts = $this->draftRepository
            ->findAllByTrainer($trainerId);

        $draftCourses = array_map(
            static function (array $draft): array {
                return [
                    'draft_id'        => $draft['id'],
                    'title_course'    => $draft['title_course'] ?? 'Sans titre',
                    'description_course' => $draft['description_course'] ?? 'Aucune description',
                    'profile_picture' => $draft['profile_picture']
                        ?: '/assets/img/default-course.jpg',
                    'updated_at'      => $draft['updated_at']
                        ?? $draft['created_at'],
                    'is_draft'        => true,
                ];
            },
            $drafts
        );


        // 3. Fusion : brouillons en premier
        $allCourses = array_merge($draftCourses, $publishedCourses);

        // 4. Vue
        require __DIR__ . '/../views/admins/courses/listCourses.php';
    }


    public function autoSaveContent(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
            exit;
        }

        $trainerId = $_SESSION['user']['id'];
        $contentData = $_POST['content_data'] ?? null;

        if (!$contentData) {
            echo json_encode(['success' => false, 'message' => 'Aucun contenu reçu.']);
            exit;
        }

        // Récupérer ou créer le draft
        $draft = $this->draftRepository->findByTrainer($trainerId);

        if (!$draft) {
            echo json_encode(['success' => false, 'message' => 'Aucun brouillon trouvé. Commencez par l\'étape 1.']);
            exit;
        }

        try {
            $this->draftRepository->updateDraft($draft['id'], [
                'content_data' => $contentData,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Contenu du cours sauvegardé automatiquement.',
                'draft_id' => $draft['id']
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
        }
    }

    /**
     * Supprime un brouillon de cours
     */
    public function deleteDraft(): void
    {
        // Session (normalement déjà démarrée dans index.php)
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Auth
        if (empty($_SESSION['user']['id'])) {
            header('Location: /nathan');
            exit;
        }

        // Rôle
        if (
            empty($_SESSION['user']['is_admin']) &&
            empty($_SESSION['user']['is_trainer'])
        ) {
            http_response_code(403);
            exit('Accès interdit');
        }

        // ID depuis l’URL
        $draftId = (int) ($_GET['id'] ?? 0);

        if ($draftId <= 0) {
            http_response_code(400);
            exit('ID de brouillon invalide');
        }

        $trainerId = (int) $_SESSION['user']['id'];

        // Suppression sécurisée
        $deleted = $this->draftRepository->deleteById($draftId, $trainerId);

        if (!$deleted) {
            http_response_code(404);
            exit('Brouillon introuvable ou non autorisé');
        }

        // Redirection propre
        header('Location: ../courses');
        exit;
    }
}
