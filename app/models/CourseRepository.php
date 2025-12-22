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
            course_rate,
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
            :rate,
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
            ':rate'        => $data['course_rate'] ?? 0,
        ]);
    }

    /**
     * Récupérer tous les cours
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM course ORDER BY course_date DESC";
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un cours par ID
     */
    public function findById(int $courseId): ?array
    {
        $sql = "SELECT * FROM course WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);

        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        return $course ?: null;
    }

    /**
     * Mettre à jour un cours
     */
    public function update(int $courseId, array $data): bool
    {
        $sql = "UPDATE course SET
            title_course       = :title,
            description_course = :description,
            profile_picture    = :picture,
            time_course        = :time,
            period_course      = :period,
            teacher_course     = :teacher,
            price_course       = :price,
            language_taught    = :language,
            learner_level      = :level
        WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':title'       => $data['title_course'],
            ':description' => $data['description_course'],
            ':picture'     => $data['profile_picture'],
            ':time'        => $data['time_course'],
            ':period'      => $data['period_course'],
            ':teacher'     => $data['teacher_course'],
            ':price'       => $data['price_course'],
            ':language'    => $data['language_taught'],
            ':level'       => $data['learner_level'],
            ':id'          => $courseId
        ]);
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
}
