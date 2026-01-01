<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\UserRepository;

class ResetPasswordController
{
    private \PDO $connection;
    private UserRepository $userRepo;

    public function __construct()
    {
        $database = new Database();
        $this->connection = $database->connect(); // ✅ PDO

        $this->userRepo = new UserRepository($this->connection); // ✅ PDO
    }

    /**
     * Traite la soumission du formulaire de réinitialisation
     * Retourne du JSON pour AJAX
     */
    public function reset()
    {
        // Autoriser uniquement les requêtes POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        header('Content-Type: application/json');

        $newPassword     = $_POST['new-password'] ?? '';
        $confirmPassword = $_POST['confirm-password'] ?? '';
        $token           = $_POST['token'] ?? '';

        // Validation des champs
        if (empty($newPassword) || empty($confirmPassword) || empty($token)) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
            return;
        }

        if (strlen($newPassword) < 8) {
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères.']);
            return;
        }

        // Vérifier le token et récupérer l'utilisateur
        $result = $this->userRepo->getUserByResetToken($token);

        if (!$result || !isset($result['success']) || !$result['success']) {
            echo json_encode(['success' => false, 'message' => 'Lien invalide ou expiré.']);
            return;
        }

        $user = $result['data'];

        // Mettre à jour le mot de passe
        $updated = $this->userRepo->updatePasswordByResetToken($token, $newPassword);

        if (!$updated) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du mot de passe.']);
            return;
        }

        // Nettoyer le token (plus réutilisable)
        // $this->userRepo->clearResetToken($user['id']);

        // Succès !
        echo json_encode([
            'success' => true,
            'message' => 'Votre mot de passe a été réinitialisé avec succès ! Redirection vers la connexion...'
        ]);
    }

    public function getUserByResetToken($token)
    {
        try {
            // Vérifier si le token est valide et récupérer toutes les infos de users
            $stmt = $this->connection->prepare("SELECT * FROM users WHERE reset_token = ?");
            $stmt->execute([$token]);
            $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($userData) {
                return [
                    'success' => true,
                    'data' => $userData // Retourne toutes les infos de users comme array
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Token invalide ou expiré.'
                ];
            }
        } catch (\Exception $e) {
            error_log('Erreur lors de la récupération de l\'utilisateur par token: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des détails.'
            ];
        }
    }


    public function resetPasswordPage($token)
    {
        $userDetails = null;
        $error = null;

        // Log pour débogage
        error_log("Token reçu: " . $token);

        // Utiliser la nouvelle méthode pour récupérer toutes les infos de users
        $userDetails = $this->getUserByResetToken($token);

        error_log("User data: " . print_r($userDetails, true)); // Log pour voir ce qui est retourné

        if (!$userDetails || !$userDetails['success']) {
            $error = $userDetails['message'] ?? 'Token invalide ou expiré. Veuillez demander un nouveau lien de réinitialisation.';
        }

        // Passer les variables à la vue
        extract([
            'userDetails' => $userDetails,
            'error' => $error
        ]);

        require __DIR__ . '/../views/auth/resetPasswordPage.php';
    }
}
