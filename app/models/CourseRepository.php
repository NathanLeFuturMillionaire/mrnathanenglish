<?php

namespace App\Models;

use PDO;

class CourseRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Créer un nouveau cours
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO courses (
            title_course,
            description_course,
            profile_picture,
            time_course,
            validation_period,
            teacher_course,
            price_course,
            language_taught,
            learner_level,
            status_course,
            course_rate,
            is_free,
            course_date
        ) VALUES (
            :title,
            :description,
            :picture,
            :time,
            :period,
            :teacher,
            :price,
            :language,
            :level,
            :status_course,
            :rate,
            :is_free,
            NOW()
        )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':title'       => $data['title_course'],
            ':description' => $data['description_course'] ?? null,
            ':picture'     => $data['profile_picture'] ?? null,
            ':time'        => $data['time_course'] ?? null,
            ':period'      => $data['validation_period'] ?? null,
            ':teacher'     => $data['teacher_course'] ?? null,
            ':price'       => $data['price_course'],
            ':language'    => $data['language_taught'],
            ':level'       => $data['learner_level'],
            ':status_course' => $data['status_course'] ?? null,
            ':rate'        => $data['course_rate'] ?? 0,
            ':is_free'        => $data['is_free'] ?? 0,
        ]);
    }

    /**
     * Récupère tous les cours avec tous leurs champs
     * @return array Tableau associatif de tous les cours
     */
    public function findAll(): array
    {
        $query = "
            SELECT 
                id,
                title_course,
                description_course,
                profile_picture,
                time_course,
                price_course,
                language_taught,
                learner_level,
                teacher_course,
                is_free,
                status_course,
                validation_period,
                course_date
            FROM courses
            ORDER BY course_date DESC
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur SQL dans CourseRepository::findAll() : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Met à jour un cours existant
     * @param int $id ID du cours à mettre à jour
     * @param array $data Tableau des données à mettre à jour
     * @param string|null $newImagePath Chemin de la nouvelle image (si upload)
     * @return bool True si succès, false sinon
     */
    public function update(int $id, array $data, ?string $newImagePath = null): bool
    {
        // Récupérer l'ancien cours pour supprimer l'ancienne image si nécessaire
        $oldCourse = $this->findById($id);
        if (!$oldCourse) {
            return false;
        }

        // Préparer les champs à mettre à jour
        $fields = [];
        $params = [];
        $allowedFields = [
            'title_course',
            'description_course',
            'time_course',
            'price_course',
            'language_taught',
            'learner_level',
            'teacher_course',
            'is_free',
            'status_course',
            'validation_period'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        // Gestion de l'image
        if ($newImagePath) {
            $fields[] = "profile_picture = :profile_picture";
            $params[':profile_picture'] = $newImagePath;

            // Supprimer l'ancienne image si elle existe et n'est pas par défaut
            $oldImage = $oldCourse['profile_picture'];
            if ($oldImage && file_exists(__DIR__ . '/../../public' . $oldImage)) {
                unlink(__DIR__ . '/../../public' . $oldImage);
            }
        }

        // Si rien à mettre à jour
        if (empty($fields)) {
            return true;
        }

        $query = "UPDATE courses SET " . implode(', ', $fields) . " WHERE id = :id";
        $params[':id'] = $id;

        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            error_log('Erreur lors de la mise à jour du cours ID ' . $id . ' : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un cours par ID
     */
    public function findById(int $id)
    {
        $query = "SELECT * FROM courses WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    /**
     * Supprimer un cours
     */
    public function delete(int $courseId): bool
    {
        $sql = "DELETE FROM course WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$courseId]);
    }

    /**
     * Récupère les cours publiés d'un formateur spécifique
     */
    public function findByTrainerPublished(int $trainerId): array
    {
        $query = "
        SELECT 
            id,
            title_course,
            description_course,
            profile_picture,
            price_course,
            is_free,
            language_taught,
            learner_level,
            status_course,
            course_date
        FROM courses
        WHERE id_trainer = :trainer_id
          AND status_course = 'published'
        ORDER BY course_date DESC
    ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':trainer_id', $trainerId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
