<?php

namespace App\Models;

use PDO;
use PDOException;
use Throwable;
use App\Core\Database;

class DraftRepository
{
    private PDO $db;

    public function __construct()
    {
        // Connexion à la base
        $database = new Database();
        $this->db = $database->connect();
    }

    /**
     * Crée un nouveau brouillon pour un formateur
     */
    public function createEmptyDraft(int $trainerId): int|false
    {
        $query = "
        INSERT INTO draft (id_trainer, created_at, updated_at)
        VALUES (:id_trainer, NOW(), NOW())
    ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_trainer', $trainerId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return (int) $this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log('Erreur createEmptyDraft : ' . $e->getMessage());
            return false;
        }
    }


    public function createDraft(int $trainerId)
    {
        $query = "
            INSERT INTO draft (trainer_id)
            VALUES (:trainer_id)
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':trainer_id', $trainerId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log('Erreur lors de la création du brouillon : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour un brouillon existant de manière dynamique et sécurisée.
     * Si seul 'content_data' est fourni, seule cette colonne est mise à jour (idéal pour l'auto-save de l'étape 2).
     *
     * @param int   $draftId ID du brouillon
     * @param array $data    Tableau associatif des données à mettre à jour
     *                       Champs possibles : title_course, description_course, profile_picture,
     *                                       time_course, validation_period, price_course,
     *                                       language_taught, learner_level, is_free,
     *                                       content_data (string JSON)
     * @return bool          true en cas de succès, false sinon
     */
    public function updateDraft(int $draftId, array $data): bool
    {
        // Liste blanche des champs autorisés
        $allowedFields = [
            'title_course',
            'description_course',
            'profile_picture',
            'time_course',
            'validation_period',
            'price_course',
            'language_taught',
            'learner_level',
            'is_free',
            'content_data'
        ];

        // Filtrage : on ne garde que les champs autorisés et présents
        $filteredData = array_intersect_key($data, array_flip($allowedFields));

        // Si rien à mettre à jour, on retourne true (succès silencieux)
        if (empty($filteredData)) {
            return true;
        }

        // Construction dynamique de la clause SET
        $setParts = [];
        $params   = [':id' => $draftId];

        foreach ($filteredData as $field => $value) {
            $setParts[] = "$field = :$field";

            if ($value === null || $value === '') {
                $params[":$field"] = null;
            } elseif ($field === 'content_data') {
                // content_data est une string JSON (déjà encodée côté JS)
                $params[":$field"] = $value;
            } elseif (in_array($field, ['time_course', 'validation_period', 'is_free'])) {
                $params[":$field"] = (int)$value;
            } elseif ($field === 'price_course') {
                $params[":$field"] = (float)$value;
            } else {
                $params[":$field"] = $value;
            }
        }

        // Toujours mettre à jour la date de modification
        $setParts[] = 'updated_at = NOW()';

        $setClause = implode(', ', $setParts);
        $query     = "UPDATE draft SET $setClause WHERE id = :id";

        try {
            $stmt = $this->db->prepare($query);

            foreach ($params as $param => $value) {
                if (is_int($value)) {
                    $stmt->bindValue($param, $value, PDO::PARAM_INT);
                } elseif (is_float($value)) {
                    $stmt->bindValue($param, $value, PDO::PARAM_STR);
                } elseif ($value === null) {
                    $stmt->bindValue($param, null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue($param, $value, PDO::PARAM_STR);
                }
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur updateDraft (ID: ' . $draftId . ') : ' . $e->getMessage());
            error_log('Requête : ' . $query);
            error_log('Paramètres : ' . print_r($params, true));
            return false;
        }
    }




    /**
     * Récupère le brouillon actif du formateur
     *
     * @param int $trainerId ID du formateur
     * @return array|false   Tableau associatif du brouillon ou false si aucun
     */
    public function findByTrainer(int $trainerId): array|false
    {
        $query = "
        SELECT *
        FROM draft
        WHERE id_trainer = :id_trainer
        LIMIT 1
    ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_trainer', $trainerId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (PDOException $e) {
            error_log('Erreur findByTrainer : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un brouillon par son ID
     */
    public function findById(int $id)
    {
        $query = "SELECT id, trainer_id, draft_data FROM draft WHERE id = :id LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    /**
     * Supprime le brouillon du formateur (appelé après publication du cours)
     *
     * @param int $trainerId ID du formateur
     * @return bool          True si supprimé ou aucun brouillon, false sinon
     */
    public function deleteByTrainer(int $trainerId): bool
    {
        $query = "DELETE FROM draft WHERE id_trainer = :id_trainer";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_trainer', $trainerId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur deleteByTrainer : ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Supprime un brouillon appartenant à un formateur
     *
     * @param int $draftId
     * @param int $trainerId
     * @return bool
     */
    public function deleteById(int $draftId, int $trainerId): bool
    {
        $query = "
        DELETE FROM draft
        WHERE id = :id
          AND id_trainer = :id_trainer
        LIMIT 1
    ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $draftId, PDO::PARAM_INT);
            $stmt->bindValue(':id_trainer', $trainerId, PDO::PARAM_INT);
            $stmt->execute();

            // Suppression réelle ou non
            return $stmt->rowCount() === 1;
        } catch (Throwable $e) {
            error_log('Erreur deleteById draft : ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Récupère le brouillon d'un formateur pour un titre donné
     *
     * @param int    $trainerId
     * @param string $title
     * @return array|false
     */
    public function findByTrainerAndTitle(int $trainerId, string $title)
    {
        $query = "
        SELECT id, draft_data
        FROM draft
        WHERE trainer_id = :trainer_id
          AND JSON_EXTRACT(draft_data, '$.course_infos.title_course') = :title
        LIMIT 1
    ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':trainer_id', $trainerId, PDO::PARAM_INT);
            $stmt->bindValue(':title', $title, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (PDOException $e) {
            error_log('Erreur findByTrainerAndTitle : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère tous les brouillons d’un formateur
     */
    public function findAllByTrainer(int $trainerId): array
    {
        $query = "
        SELECT
            id,
            title_course,
            description_course,
            profile_picture,
            time_course,
            validation_period,
            price_course,
            language_taught,
            learner_level,
            is_free,
            content_data,
            created_at,
            updated_at
        FROM draft
        WHERE id_trainer = :id_trainer
        ORDER BY updated_at DESC
    ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id_trainer', $trainerId, PDO::PARAM_INT);
            $stmt->execute();

            $drafts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Décodage JSON propre
            foreach ($drafts as &$draft) {
                $draft['content_data'] = $draft['content_data']
                    ? json_decode($draft['content_data'], true, 512, JSON_THROW_ON_ERROR)
                    : [];
            }

            return $drafts;
        } catch (Throwable $e) {
            error_log('Erreur findAllByTrainer : ' . $e->getMessage());
            return [];
        }
    }
}
