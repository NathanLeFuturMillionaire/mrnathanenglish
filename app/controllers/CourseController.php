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
}
