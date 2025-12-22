<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\CourseRepository;

class AdminController
{
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
        require __DIR__ . '/../views/admins/courses/createCourse.php';
    }

    /**
     * Traite la création du cours (étape 1) via AJAX
     */
    public function ajaxCreateCourse(): void
    {
        header('Content-Type: application/json');

        // === TEST TEMPORAIRE ===
        // echo json_encode(['test' => 'La méthode AJAX est bien appelée !']);
        // exit;

        // Vérification que c'est bien une requête AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$isAjax) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé ou méthode invalide']);
            exit;
        }

        // Récupération et nettoyage des données
        $title          = trim($_POST['title_course'] ?? '');
        $description    = trim($_POST['description_course'] ?? '');
        $language       = $_POST['language_taught'] ?? '';
        $level          = $_POST['learner_level'] ?? '';
        $duration       = (int)($_POST['time_course'] ?? 0) ?: null;
        $validation_period = (int)($_POST["validation_period"] ?? 0) ?: null;
        $price          = (float)($_POST['price_course'] ?? 0);
        $teacher        = trim($_POST['teacher_course'] ?? '');
        $is_free        = isset($_POST['is_free']) ? 1 : 0;

        // Validation basique
        $errors = [];
        if (empty($title)) $errors[] = "Le titre est obligatoire";
        if (empty($description)) $errors[] = "La description est obligatoire";
        if (empty($language)) $errors[] = "La langue est obligatoire";
        if (empty($level)) $errors[] = "Le niveau est obligatoire";
        if ($price < 0) $errors[] = "Le prix ne peut pas être négatif";
        if (empty($teacher)) $errors[] = "Le professeur est obligatoire";
        if (empty($validation_period)) $errors[] = "Veuillez saisir une durée sur laquelle l'étudiant pourra voir les résultats";

        // Gestion de l'image de couverture
        $profile_picture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/courses/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileName = uniqid('course_') . '.' . pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $filePath = $uploadDir . $fileName;

            // Vérification type + taille (max 5 Mo)
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $errors[] = "Format d'image non autorisé";
            }
            if ($_FILES['profile_picture']['size'] > 5 * 1024 * 1024) {
                $errors[] = "L'image ne doit pas dépasser 5 Mo";
            }

            if (empty($errors) && move_uploaded_file($_FILES['profile_picture']['tmp_name'], $filePath)) {
                $profile_picture = '/uploads/courses/' . $fileName;
            } else {
                $errors[] = "Erreur lors de l'upload de l'image";
            }
        } else {
            $errors[] = "L'image de couverture est obligatoire";
        }

        // S'il y a des erreurs
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Création du cours via le repository
        $courseData = [
            'title_course'       => $title,
            'description_course' => $description,
            'profile_picture'    => $profile_picture,
            'time_course'        => $duration,
            'validation_period'  => $validation_period,
            'price_course'       => $price,
            'language_taught'    => $language,
            'learner_level'      => $level,
            'teacher_course'     => $teacher,
            'is_free'            => $is_free,
            'status_course'      => 'draft', // ou 'published' si tu veux
            'course_date'        => date('Y-m-d H:i:s')
        ];

        $courseId = $this->courseRepository->create($courseData);

        if ($courseId) {
            echo json_encode([
                'success' => true,
                'message' => 'Cours créé avec succès !',
                'course_id' => $courseId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du cours']);
        }
        exit;
    }
}
