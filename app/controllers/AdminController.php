<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\CourseRepository;
use App\Models\DraftRepository;

class AdminController
{
    protected $db;
    protected $courseRepository;
    protected $draftRepository;

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
        // Récupère les données temporaires si elles existent
        $tempData = $_SESSION['temp_course_creation'] ?? [];

        require __DIR__ . '/../views/admins/courses/createCourse.php';
    }

    /**
     * AJAX : Étape 1 du wizard – Sauvegarde des informations générales dans le brouillon (table draft)
     * Le cours n'est PAS créé dans la table courses à ce stade
     */
    public function ajaxCreateCourse(): void
    {
        header('Content-Type: application/json');

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$isAjax) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé ou méthode invalide.']);
            exit;
        }

        // ID du formateur connecté
        $trainerId = $_SESSION['user']['id'];

        // Récupération des données du formulaire
        $title            = trim($_POST['title_course'] ?? '');
        $description      = trim($_POST['description_course'] ?? '');
        $language         = $_POST['language_taught'] ?? '';
        $level            = $_POST['learner_level'] ?? '';
        $duration         = !empty($_POST['time_course']) ? (int)$_POST['time_course'] : null;
        $validationPeriod = !empty($_POST['validation_period']) ? (int)$_POST['validation_period'] : null;
        $price            = (float)($_POST['price_course'] ?? 0);
        $is_free          = isset($_POST['is_free']) ? 1 : 0;
        $publish_now      = isset($_POST['publish_now']) ? 1 : 0;

        // Validation
        $errors = [];
        if ($title === '') $errors[] = "Le titre du cours est obligatoire.";
        if ($description === '') $errors[] = "La description du cours est obligatoire.";
        if ($language === '') $errors[] = "La langue enseignée est obligatoire.";
        if ($level === '') $errors[] = "Le niveau du cours est obligatoire.";
        if ($validationPeriod === null) $errors[] = "La période de validation est obligatoire.";
        if ($price < 0) $errors[] = "Le prix ne peut pas être négatif.";

        // Gestion de l'image de couverture
        $newImagePath = null;
        $oldImagePath = null;

        // Récupérer l'ancien chemin d'image depuis le brouillon existant (si mise à jour)
        $existingDraft = $this->draftRepository->findByTrainer($trainerId);
        if ($existingDraft) {
            $existingData = json_decode($existingDraft['draft_data'], true);
            $oldImagePath = $existingData['course_infos']['profile_picture'] ?? null;
        }

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/courses/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                $errors[] = "Impossible de créer le répertoire d'upload.";
            } else {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed)) {
                    $errors[] = "Format d'image non autorisé.";
                } elseif ($_FILES['profile_picture']['size'] > 5 * 1024 * 1024) {
                    $errors[] = "L'image ne doit pas dépasser 5 Mo.";
                } else {
                    $fileName = uniqid('course_draft_') . '.' . $ext;
                    $filePath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $filePath)) {
                        $newImagePath = '/uploads/courses/' . $fileName;

                        // Supprimer l'ancienne image si elle existe
                        if ($oldImagePath && $oldImagePath !== $newImagePath && file_exists(__DIR__ . '/../../public' . $oldImagePath)) {
                            @unlink(__DIR__ . '/../../public' . $oldImagePath);
                        }
                    } else {
                        $errors[] = "Échec de l'enregistrement de l'image.";
                    }
                }
            }
        } else {
            // Pas de nouvelle image : on conserve l'ancienne si elle existe
            $newImagePath = $oldImagePath;
            if (!$newImagePath && empty($existingDraft)) {
                $errors[] = "L'image de couverture est obligatoire pour commencer.";
            }
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Construction des données du brouillon
        $draftData = [
            'course_infos' => [
                'title_course'       => $title,
                'description_course' => $description,
                'language_taught'    => $language,
                'learner_level'      => $level,
                'time_course'        => $duration,
                'validation_period' => $validationPeriod,
                'price_course'       => $price,
                'is_free'            => $is_free,
                'publish_now'        => $publish_now,
                'profile_picture'    => $newImagePath
            ],
            'modules' => [] // Sera rempli à l'étape 2
        ];

        $jsonData = json_encode($draftData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        try {
            if ($existingDraft) {
                // Mise à jour du brouillon existant
                $success = $this->draftRepository->update($existingDraft['id'], $jsonData);
                $message = "Brouillon mis à jour. Passez au contenu du cours.";
            } else {
                // Création d'un nouveau brouillon
                $draftId = $this->draftRepository->create($trainerId, $jsonData);
                $success = $draftId !== false;
                $message = "Brouillon créé. Vous pouvez maintenant construire le contenu du cours.";
            }

            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => $message
                ]);
            } else {
                throw new \Exception("Échec de la sauvegarde du brouillon.");
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde du brouillon : ' . $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * Page réservée à l'admin/formateur : Liste de ses cours publiés + brouillons
     */
    public function listCourses(): void
    {
        $trainerId = (int)$_SESSION['user']['id'];

        // 1. Récupération des cours publiés du formateur
        $publishedCourses = $this->courseRepository->findByTrainerPublished($trainerId);

        // 2. Récupération de TOUS les brouillons du formateur
        $draftCourses = [];
        $drafts = $this->draftRepository->findAllByTrainer($trainerId);

        if ($drafts && is_array($drafts)) {
            foreach ($drafts as $draft) {
                if (!is_array($draft) || !isset($draft['draft_data'])) {
                    continue; // Sécurité
                }

                $data = json_decode($draft['draft_data'], true);
                if (!is_array($data)) {
                    continue;
                }

                $courseInfos = $data['course_infos'] ?? [];

                $draftCourses[] = [
                    'draft_id'        => $draft['id'],
                    'title_course'    => $courseInfos['title_course'] ?? 'Sans titre',
                    'profile_picture' => $courseInfos['profile_picture'] ?? '/assets/img/default-course.jpg',
                    'updated_at'      => $draft['updated_at'] ?? $draft['created_at'] ?? 'Inconnue',
                    'is_draft'        => true
                ];
            }
        }

        // Fusion : brouillons en haut, puis cours publiés
        $allCourses = array_merge($draftCourses, $publishedCourses);

        // Passage des données à la vue
        require __DIR__ . '/../views/admins/courses/listCourses.php';
    }
}
