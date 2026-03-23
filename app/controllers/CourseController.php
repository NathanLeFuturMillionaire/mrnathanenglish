<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\CourseRepository;

class CourseController
{
  protected $db;
  protected CourseRepository $courseRepository;

  public function __construct()
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    // Sécurité : utilisateur connecté
    if (!isset($_SESSION['user']['id'])) {
      header('Location: ./login');
      exit;
    }

    // Connexion à la base
    $database = new Database();
    $this->db = $database->connect();

    // Repository
    $this->courseRepository = new CourseRepository($this->db);
  }

  public function viewCourse(): void
  {
    $courseId = isset($_GET['id']) ? (int) $_GET['id'] : null;

    if (!$courseId) {
      http_response_code(400);
      exit('ID du cours manquant');
    }

    $course = $this->courseRepository->getCourseById($courseId);

    if (!$course) {
      http_response_code(404);
      exit('Cours introuvable');
    }

    $lastUpdate = $this->courseRepository->timeAgo(
      $course['updated_at'] !== $course['created_at']
        ? $course['updated_at']
        : $course['created_at']
    );

    $updateLabel = $course['updated_at'] !== $course['created_at'] ? 'Mise à jour' : 'Publié';

    require __DIR__ . '/../views/courses/viewCourse.php';
  }

  public function viewLesson(): void
  {
    $courseId    = isset($_GET['id'])           ? (int) $_GET['id']           : null;
    $moduleIndex = isset($_GET['module_index']) ? (int) $_GET['module_index'] : 0;
    $lessonIndex = isset($_GET['lesson_index']) ? (int) $_GET['lesson_index'] : 0;

    if (!$courseId) {
      http_response_code(400);
      exit('ID du cours manquant');
    }

    $course = $this->courseRepository->getCourseById($courseId);

    if (!$course) {
      http_response_code(404);
      exit('Cours introuvable');
    }

    $modules = $course['content_data']['modules'] ?? [];

    if (!isset($modules[$moduleIndex])) {
      http_response_code(404);
      exit('Module introuvable');
    }

    if (!isset($modules[$moduleIndex]['lessons'][$lessonIndex])) {
      http_response_code(404);
      exit('Leçon introuvable');
    }

    $module = $modules[$moduleIndex];
    $lesson = $modules[$moduleIndex]['lessons'][$lessonIndex];

    require __DIR__ . '/../views/admins/courses/viewLesson.php';
  }
  public function listCourses(): void
  {
    // Filtres
    $level    = $_GET['level']    ?? '';
    $language = $_GET['language'] ?? '';
    $free     = $_GET['free']     ?? '';
    $search   = trim($_GET['search'] ?? '');
    $sort     = $_GET['sort']     ?? 'recent';

    // Construction de la requête
    $where  = ["c.status_course = 'published'"];
    $params = [];

    if ($level) {
      $where[]           = 'c.learner_level = :level';
      $params[':level']  = $level;
    }

    if ($language) {
      $where[]              = 'c.language_taught = :language';
      $params[':language']  = $language;
    }

    if ($free !== '') {
      $where[]        = 'c.is_free = :is_free';
      $params[':is_free'] = (int) $free;
    }

    if ($search) {
      $where[]            = '(c.title_course LIKE :search1 OR c.description_course LIKE :search2)';
      $params[':search1'] = '%' . $search . '%';
      $params[':search2'] = '%' . $search . '%';
    }

    $whereClause = implode(' AND ', $where);

    $orderBy = match ($sort) {
      'price_asc'  => 'c.price_course ASC',
      'price_desc' => 'c.price_course DESC',
      'rating'     => 'c.course_rate DESC',
      'popular'    => 'enrolled_count DESC',
      default      => 'c.created_at DESC',
    };

    $stmt = $this->db->prepare("
        SELECT
            c.*,
            COUNT(DISTINCT sc.id)  AS enrolled_count,
            CASE
                WHEN sc2.id IS NOT NULL THEN 1 ELSE 0
            END                    AS is_enrolled
        FROM courses c
        LEFT JOIN student_courses sc  ON sc.id_course = c.id
        LEFT JOIN student_courses sc2 ON sc2.id_course = c.id
                                     AND sc2.id_student = :user_id
        WHERE {$whereClause}
        GROUP BY c.id
        ORDER BY {$orderBy}
    ");

    $params[':user_id'] = (int) $_SESSION['user']['id'];
    $stmt->execute($params);
    $courses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Parse content_data pour chaque cours
    foreach ($courses as &$course) {
      $content      = json_decode($course['content_data'], true);
      $totalLessons = 0;
      $totalModules = 0;

      if (!empty($content['modules'])) {
        $totalModules = count($content['modules']);
        foreach ($content['modules'] as $module) {
          $lessons = array_filter(
            $module['lessons'] ?? [],
            fn($l) => !empty($l['title'])
          );
          $totalLessons += count($lessons);
        }
      }

      $totalMinutes = (int) $course['time_course'];
      $hours        = floor($totalMinutes / 60);
      $minutes      = $totalMinutes % 60;

      $course['total_modules']  = $totalModules;
      $course['total_lessons']  = $totalLessons;
      $course['duration_label'] = $hours > 0
        ? $hours . 'h' . ($minutes > 0 ? $minutes . 'min' : '')
        : $minutes . 'min';

      $course['outcomes'] = $content['outcomes'] ?? [];
      unset($course['content_data']);
    }
    unset($course);

    // Stats globales pour les filtres
    $totalCourses = count($courses);

    require __DIR__ . '/../views/courses/list.php';
  }
}
