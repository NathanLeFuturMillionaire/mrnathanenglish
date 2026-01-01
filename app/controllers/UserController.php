<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\UserRepository;

class UserController
{
    protected $db;
    protected $userRepository;
    protected $errors = [];

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();

        $this->userRepository = new UserRepository($this->db);
    }

    /**
     * Affiche le profil complet de l'utilisateur (infos + abonnements)
     */
    public function user(int $userId): void
    {
        // $userId = (int) $userId;
        // var_dump($userId);
        // die;

        $userId = (int) $userId;

        // Récupération des données via le repository
        $rows = $this->userRepository->getUserWithProfileAndSubscriptionsById($userId);

        // Vérification cruciale : si rien n'est retourné
        if (!$rows || !isset($rows[0]['id'])) {
            // header("Location: ./404");
            // exit;
            var_dump($rows);
        }

        // Maintenant on est sûr que $rows[0] existe
        $user = [
            'id'            => $rows[0]['id'],
            'fullname'      => $rows[0]['fullname'],
            'username'      => $rows[0]['username'],
            'email'         => $rows[0]['email'],
            'is_confirmed'  => $rows[0]['is_confirmed'],
            'created_at'    => $rows[0]['created_at'],
            'profile'       => [
                'profile_picture' => $rows[0]['profile_picture'] ?? 'default.png',
                'birth_date'      => $rows[0]['birth_date'],
                'phone_number'    => $rows[0]['phone_number'],
                'bio'             => $rows[0]['bio'] ?? '',
                'country'         => $rows[0]['country'] ?? '',
                'english_level'   => $rows[0]['english_level'] ?? null,
            ]
        ];

        // Récupération des abonnements (toutes les lignes)
        $subscriptions = [];
        foreach ($rows as $row) {
            if (!empty($row['subscription_id'])) {
                $subscriptions[] = [
                    'id'                      => $row['subscription_id'],
                    'type'                    => $row['subscription_type'],
                    'amount'                  => $row['amount'],
                    'currency'                => $row['currency'],
                    'billing_period'          => $row['billing_period'],
                    'pawa_pay_deposit_id'     => $row['pawa_pay_deposit_id'],
                    'pawa_pay_status'         => $row['pawa_pay_status'],
                    'pawa_pay_correlation_id' => $row['pawa_pay_correlation_id'],
                    'pawa_pay_mobile_number'  => $row['pawa_pay_mobile_number'],
                    'pawa_pay_country_code'   => $row['pawa_pay_country_code'],
                    'pawa_pay_operator'       => $row['pawa_pay_operator'],
                    'start_date'              => $row['start_date'],
                    'end_date'                => $row['end_date'],
                    'next_billing_date'       => $row['next_billing_date'],
                    'status'                  => $row['subscription_status'],
                    'days_remaining'          => (int)($row['days_remaining'] ?? 0),
                    'status_display'          => $row['subscription_status_display'] ?? 'Unknown',
                    'canceled_at'             => $row['canceled_at'],
                    'ended_at'                => $row['ended_at'],
                    'failed_payment_count'    => (int)($row['failed_payment_count'] ?? 0),
                    'created_at'              => $row['subscription_created_at'],
                    'updated_at'              => $row['subscription_updated_at'],
                ];
            }
        }

        // Passage à la vue
        extract([
            'user'          => $user,
            'subscriptions' => $subscriptions
        ]);

        require __DIR__ . '/../views/users/profile.php';
    }
}
