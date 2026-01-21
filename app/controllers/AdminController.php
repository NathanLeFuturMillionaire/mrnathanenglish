<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\CourseRepository;
use App\Models\DraftRepository;
use Exception;
use Throwable;

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

    public function createCourse(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $trainerId = (int) $_SESSION['user']['id'];

        // ======================
        // ID brouillon depuis URL
        // ======================
        $draftId = isset($_GET['id']) ? (int) $_GET['id'] : null;

        // Si aucun brouillon → on le crée
        if (!$draftId) {
            $draftId = $this->draftRepository->createEmptyDraft($trainerId);

            if (!$draftId) {
                http_response_code(500);
                exit('Impossible de créer le brouillon');
            }

            header('Location: ../courses/create?id=' . $draftId);
            exit;
        }

        $draft = $this->draftRepository->findByIdAndTrainer($draftId, $trainerId);

        if (!$draft) {
            http_response_code(404);
            exit('Brouillon introuvable');
        }

        // ======================
        // CONTEXTE VUE
        // ======================
        $entityType = 'draft';
        $entityId   = $draftId;
        $isEditMode = false;

        // Champs
        $title            = $draft['title_course'] ?? '';
        $description      = $draft['description_course'] ?? '';
        $language         = $draft['language_taught'] ?? '';
        $level            = $draft['learner_level'] ?? '';
        $duration         = $draft['time_course'] ?? '';
        $validationPeriod = $draft['validation_period'] ?? '';
        $price            = $draft['price_course'] ?? '';
        $is_free          = $draft['is_free'] ?? 1;
        $profilePicture   = $draft['profile_picture'] ?? null;

        require __DIR__ . '/../views/admins/courses/createCourse.php';
    }




    /**
     * AJAX : Étape 1 du wizard – Sauvegarde auto dans la table draft
     * Le brouillon DOIT déjà exister (draft_id obligatoire)
     */
    public function ajaxCreateCourse(): void
    {
        header('Content-Type: application/json');

        /* =====================================================
           SÉCURITÉ AJAX
        ===================================================== */
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
        ) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Requête invalide']);
            exit;
        }

        /* =====================================================
           SÉCURITÉ UTILISATEUR
        ===================================================== */
        if (empty($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Non authentifié']);
            exit;
        }

        $trainerId = (int) $_SESSION['user']['id'];

        /* =====================================================
           DÉTECTION CONTEXTE : DRAFT OU COURSE
        ===================================================== */
        $draftId  = isset($_POST['draft_id'])  ? (int) $_POST['draft_id']  : 0;
        $courseId = isset($_POST['course_id']) ? (int) $_POST['course_id'] : 0;

        $entityType = null;
        $entityId   = null;
        $entityData = null;
        $isEditMode = true;


        if ($draftId > 0) {
            $entityType = 'draft';
            $entityId   = $draftId;
            $entityData = $this->draftRepository->findByIdAndTrainer($draftId, $trainerId);
        } elseif ($courseId > 0) {
            $entityType = 'course';
            $entityId   = $courseId;
            $entityData = $this->courseRepository->findByIdAndTrainer($courseId, $trainerId);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Identifiant manquant']);
            exit;
        }

        if (!$entityData) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Ressource introuvable']);
            exit;
        }

        /* =====================================================
           DONNÉES FORMULAIRE
        ===================================================== */
        $title            = trim($_POST['title_course'] ?? '');
        $description      = trim($_POST['description_course'] ?? '');
        $language         = trim($_POST['language_taught'] ?? '');
        $level            = trim($_POST['learner_level'] ?? '');
        $duration         = !empty($_POST['time_course']) ? (int) $_POST['time_course'] : null;
        $validationPeriod = !empty($_POST['validation_period']) ? (int) $_POST['validation_period'] : null;
        $price            = isset($_POST['price_course']) ? (float) $_POST['price_course'] : 0;
        $isFree           = isset($_POST['is_free']) && (int) $_POST['is_free'] === 1 ? 1 : 0;

        /* =====================================================
           VALIDATION
        ===================================================== */
        $errors = [];

        if ($title === '')            $errors[] = 'Le titre est obligatoire';
        if ($description === '')      $errors[] = 'La description est obligatoire';
        if ($language === '')         $errors[] = 'La langue est obligatoire';
        if ($level === '')            $errors[] = 'Le niveau est obligatoire';
        if ($validationPeriod === null) $errors[] = 'La période est obligatoire';
        if ($price < 0)               $errors[] = 'Prix invalide';

        /* =====================================================
           IMAGE DE COUVERTURE
        ===================================================== */
        $oldImagePath = $entityData['profile_picture'] ?? null;
        $newImagePath = $oldImagePath;

        if (!empty($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {

            $uploadDir = __DIR__ . '/../../public/uploads/courses/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed, true)) {
                $errors[] = 'Format image invalide';
            } elseif ($_FILES['profile_picture']['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Image trop lourde (5 Mo max)';
            } else {
                $fileName = uniqid('course_', true) . '.' . $ext;
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $filePath)) {
                    $newImagePath = '/uploads/courses/' . $fileName;

                    if ($oldImagePath && file_exists(__DIR__ . '/../../public' . $oldImagePath)) {
                        @unlink(__DIR__ . '/../../public' . $oldImagePath);
                    }
                } else {
                    $errors[] = 'Upload image échoué';
                }
            }
        }

        if (!$newImagePath) {
            $errors[] = 'Image de couverture obligatoire';
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        /* =====================================================
           DONNÉES À SAUVEGARDER
        ===================================================== */
        $data = [
            'title_course'       => $title,
            'description_course' => $description,
            'profile_picture'    => $newImagePath,
            'time_course'        => $duration,
            'validation_period'  => $validationPeriod,
            'price_course'       => $price,
            'language_taught'    => $language,
            'learner_level'      => $level,
            'is_free'            => $isFree,
            'updated_at'         => date('Y-m-d H:i:s')
        ];

        if (!empty($_POST['content_data'])) {
            $data['content_data'] = $_POST['content_data'];
        }

        /* =====================================================
           SAUVEGARDE
        ===================================================== */
        if ($entityType === 'draft') {
            $this->draftRepository->updateDraft($entityId, $data);
            echo json_encode(['success' => true, 'draft_id' => $entityId]);
            exit;
        }

        if ($entityType === 'course') {
            $this->courseRepository->updateCourse($entityId, $data);
            echo json_encode(['success' => true, 'course_id' => $entityId]);
            exit;
        }
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

    /**
     * AJAX : Publication finale du cours
     */
    public function publishCourse(): void
    {
        header('Content-Type: application/json');

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        ) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }

        $trainerId = (int) ($_SESSION['user']['id'] ?? 0);
        $draftId   = (int) ($_POST['draft_id'] ?? 0);

        if ($trainerId <= 0 || $draftId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
            exit;
        }

        // 1️⃣ Récupération du brouillon
        $draft = $this->draftRepository->findByIdAndTrainer($draftId, $trainerId);

        if (!$draft) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Brouillon introuvable']);
            exit;
        }

        // 2️⃣ Vérification du contenu pédagogique
        $content = json_decode($draft['content_data'], true);

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            empty($content['modules'])
        ) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Le contenu pédagogique est incomplet'
            ]);
            exit;
        }

        // Vérification fine modules / leçons
        foreach ($content['modules'] as $module) {
            if (empty($module['title']) || empty($module['lessons'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Chaque module doit avoir un titre et au moins une leçon'
                ]);
                exit;
            }

            foreach ($module['lessons'] as $lesson) {
                if (empty($lesson['title']) || empty($lesson['content'])) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Chaque leçon doit avoir un titre et un contenu'
                    ]);
                    exit;
                }
            }
        }

        // 3️⃣ Création du cours officiel
        $courseId = $this->courseRepository->createFromDraft($draft);

        if (!$courseId) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Création du cours échouée']);
            exit;
        }

        // 4️⃣ Suppression du brouillon
        $this->draftRepository->deleteDraft($draftId, $trainerId);

        // 5️⃣ Succès
        echo json_encode([
            'success'   => true,
            'course_id' => $courseId,
            'message'  => 'Cours publié avec succès'
        ]);
        exit;
    }

    public function ajaxUpdateCourse(): void
    {
        header('Content-Type: application/json');

        /* ======================
           Sécurité AJAX
        ====================== */
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
        ) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée']);
            exit;
        }

        /* ======================
           Sécurité utilisateur
        ====================== */
        if (empty($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']);
            exit;
        }

        $trainerId = (int) $_SESSION['user']['id'];
        $courseId  = (int) ($_POST['course_id'] ?? 0);

        if ($courseId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Course ID manquant']);
            exit;
        }

        $courseRepo = new CourseRepository();

        /* ======================
           Vérification propriété du cours
        ====================== */
        $course = $courseRepo->findByIdAndTrainer($courseId, $trainerId);
        if (!$course) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Cours introuvable ou non autorisé']);
            exit;
        }

        /* ======================
           Données générales du formulaire
        ====================== */
        $data = [
            'title_course'       => trim($_POST['title_course'] ?? ''),
            'description_course' => trim($_POST['description_course'] ?? ''),
            'language_taught'    => trim($_POST['language_taught'] ?? ''),
            'learner_level'      => trim($_POST['learner_level'] ?? ''),
            'time_course'        => !empty($_POST['time_course']) ? (int) $_POST['time_course'] : null,
            'validation_period'  => !empty($_POST['validation_period']) ? (int) $_POST['validation_period'] : null,
            'price_course'       => (float) ($_POST['price_course'] ?? 0),
            'is_free'            => isset($_POST['is_free']) && (int) $_POST['is_free'] === 1 ? 1 : 0,
            'profile_picture'    => $course['profile_picture']
        ];

        /* ======================
           Upload image (optionnel)
        ====================== */
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            // ======================
            // Gestion image de couverture
            // ======================
            $oldImagePath = $draft['profile_picture'] ?? null;
            $newImagePath = $oldImagePath;

            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {

                $uploadDir = __DIR__ . '/../../public/uploads/courses/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $originalName = $_FILES['profile_picture']['name'];
                $tmpName = $_FILES['profile_picture']['tmp_name'];
                $size = $_FILES['profile_picture']['size'];

                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                if (!in_array($extension, $allowedExtensions, true)) {
                    $errors[] = 'Format d’image non autorisé (JPG, PNG, WEBP).';
                } elseif ($size > 5 * 1024 * 1024) {
                    $errors[] = 'Image trop lourde (max 5 Mo).';
                } else {
                    $fileName = uniqid('course_', true) . '.' . $extension;
                    $filePath = $uploadDir . $fileName;

                    if (move_uploaded_file($tmpName, $filePath)) {
                        $newImagePath = '/uploads/courses/' . $fileName;

                        // Suppression ancienne image si existante
                        if ($oldImagePath) {
                            $oldFullPath = __DIR__ . '/../../public' . $oldImagePath;
                            if (file_exists($oldFullPath)) {
                                @unlink($oldFullPath);
                            }
                        }
                    } else {
                        $errors[] = 'Échec de l’upload de l’image.';
                    }
                }
            }

            // Image obligatoire si aucune image n’existe encore
            if (!$newImagePath) {
                $errors[] = 'L’image de couverture est obligatoire.';
            }

            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'errors'  => $errors
                ]);
                exit;
            }
        }

        /* ======================
           Contenu pédagogique (JSON)
        ====================== */
        $contentRaw = $_POST['content_data'] ?? '';

        if ($contentRaw === '') {
            echo json_encode(['success' => false, 'message' => 'Contenu pédagogique manquant']);
            exit;
        }

        // Vérification JSON
        $contentArray = json_decode($contentRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($contentArray['modules'])) {
            echo json_encode(['success' => false, 'message' => 'Contenu pédagogique invalide']);
            exit;
        }

        // Encodage propre
        $data['content_data'] = json_encode($contentArray, JSON_UNESCAPED_UNICODE);

        /* ======================
           Mise à jour finale
        ====================== */
        try {
            if (!$courseRepo->updateCourse($courseId, $data)) {
                throw new Exception('Échec update cours');
            }

            echo json_encode(['success' => true]);
            exit;
        } catch (Throwable $e) {
            error_log($e->getMessage());

            echo json_encode([
                'success' => false,
                'message' => 'Échec de la mise à jour du cours'
            ]);
            exit;
        }
    }

    public function editCourse(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $trainerId = (int) $_SESSION['user']['id'];
        $courseId  = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($courseId <= 0) {
            http_response_code(400);
            exit('ID cours invalide');
        }

        $course = $this->courseRepository->findByIdAndTrainer($courseId, $trainerId);

        if (!$course) {
            http_response_code(404);
            exit('Ressource introuvable');
        }

        // ======================
        // CONTEXTE VUE
        // ======================
        $entityType = 'course';
        $entityId   = $courseId;
        $isEditMode = true;

        // Champs
        $title            = $course['title_course'];
        $description      = $course['description_course'];
        $language         = $course['language_taught'];
        $level            = $course['learner_level'];
        $duration         = $course['time_course'];
        $validationPeriod = $course['validation_period'];
        $price            = $course['price_course'];
        $is_free          = $course['is_free'];
        $profilePicture   = $course['profile_picture'];

        $courseData = $course;
        $draft = null;

        require __DIR__ . '/../views/admins/courses/createCourse.php';
    }

    /**
     * AJAX : Auto-save du contenu pédagogique
     * pour un cours déjà publié (édition)
     */
    public function autoSaveCourseContent(): void
    {
        header('Content-Type: application/json');

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
        ) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
            exit;
        }

        if (empty($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié.']);
            exit;
        }

        $trainerId   = (int) $_SESSION['user']['id'];
        $courseId    = (int) ($_POST['course_id'] ?? 0);
        $contentData = $_POST['content_data'] ?? null;

        if ($courseId <= 0 || !$contentData) {
            echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
            exit;
        }

        // Vérification propriété du cours
        $course = $this->courseRepository->findByIdAndTrainer($courseId, $trainerId);

        if (!$course) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Cours introuvable.']);
            exit;
        }

        try {
            $this->courseRepository->updateCourseContent($courseId, $contentData);

            echo json_encode([
                'success'   => true,
                'message'   => 'Contenu du cours sauvegardé automatiquement.',
                'course_id' => $courseId
            ]);
            exit;
        } catch (Throwable $e) {
            error_log($e->getMessage());

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur lors de la sauvegarde.'
            ]);
            exit;
        }
    }
}
