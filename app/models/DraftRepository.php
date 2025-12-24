<?php

namespace App\Models;

use PDO;
use PDOException;
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
     *
     * @param int    $trainerId  ID du formateur
     * @param string $draftData  Données du brouillon au format JSON
     * @return int|false         ID du brouillon créé ou false en cas d'échec
     */
    public function create(int $trainerId, string $draftData)
    {
        $query = "
            INSERT INTO draft (trainer_id, draft_data)
            VALUES (:trainer_id, :draft_data)
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':trainer_id', $trainerId, PDO::PARAM_INT);
            $stmt->bindParam(':draft_data', $draftData, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log('Erreur lors de la création du brouillon : ' . $e->getMessage());
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
     * Met à jour un brouillon existant
     *
     * @param int    $draftId    ID du brouillon
     * @param string $draftData  Nouvelles données au format JSON
     * @return bool              True si succès, false sinon
     */
    public function update(int $draftId, string $draftData): bool
    {
        $query = "
            UPDATE draft
            SET draft_data = :draft_data,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':draft_data', $draftData, PDO::PARAM_STR);
            $stmt->bindParam(':id', $draftId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la mise à jour du brouillon : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère le brouillon actif du formateur
     *
     * @param int $trainerId ID du formateur
     * @return array|false   Tableau associatif du brouillon ou false si aucun
     */
    public function findByTrainer(int $trainerId)
    {
        $query = "
            SELECT id, draft_data, created_at, updated_at
            FROM draft
            WHERE trainer_id = :trainer_id
            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':trainer_id', $trainerId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération du brouillon : ' . $e->getMessage());
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
        $query = "DELETE FROM draft WHERE trainer_id = :trainer_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':trainer_id', $trainerId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la suppression du brouillon : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un brouillon par son ID (utile pour nettoyage manuel)
     *
     * @param int $draftId ID du brouillon
     * @return bool
     */
    public function deleteById(int $draftId): bool
    {
        $query = "DELETE FROM draft WHERE id = :id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $draftId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la suppression du brouillon par ID : ' . $e->getMessage());
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
     * Récupère TOUS les brouillons d'un formateur (pour la liste)
     */
    public function findAllByTrainer(int $trainerId): array
    {
        $query = "
        SELECT id, draft_data, created_at, updated_at
        FROM draft
        WHERE trainer_id = :trainer_id
        ORDER BY updated_at DESC
    ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':trainer_id', $trainerId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur findAllByTrainer : ' . $e->getMessage());
            return [];
        }
    }
}
