<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Core\Database;

class AuthRepository
{
  protected PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  /**
   * Récupère un utilisateur complet par son ID
   */
  public function findUserWithProfileById(int $userId): ?array
  {
    $sql = "
            SELECT 
                u.id,
                u.fullname,
                u.username,
                u.email,
                u.is_confirmed,
                u.created_at AS user_created_at,
                p.profile_picture,
                p.birth_date,
                p.country,
                p.english_level,
                p.phone_number,
                p.bio
            FROM users u
            INNER JOIN user_profiles p ON u.id = p.user_id
            WHERE u.id = ?
        ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$userId]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
  }

  /**
   * Récupère un utilisateur avec profil + tokens via remember token
   */
  public function findByRememberToken(string $token): ?array
  {
    $sql = "
          SELECT 
              u.id AS user_id,
              u.fullname,
              u.username,
              u.email,
              u.password,
              u.is_confirmed,
              u.created_at AS user_created_at,

              p.id AS profile_id,
              p.profile_picture,
              p.birth_date,
              p.country,
              p.english_level,
              p.phone_number,
              p.bio,
              p.updated_at AS profile_updated_at,

              t.id AS token_id,
              t.token,
              t.expires_at,
              t.created_at AS token_created_at,
              t.ip_address,
              t.device,
              t.browser
          FROM users u
          INNER JOIN user_profiles p ON u.id = p.user_id
          INNER JOIN user_remember_tokens t ON u.id = t.user_id
          WHERE t.token = :token
      ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
      return null;
    }

    // --- Utilisateur ---
    $user = [
      'id'           => $rows[0]['user_id'],
      'fullname'     => $rows[0]['fullname'],
      'username'     => $rows[0]['username'],
      'email'        => $rows[0]['email'],
      'password'     => $rows[0]['password'],
      'is_confirmed' => $rows[0]['is_confirmed'],
      'created_at'   => $rows[0]['user_created_at'],
      'profile'      => null,
      'tokens'       => []
    ];

    // --- Profil ---
    if ($rows[0]['profile_id']) {
      $user['profile'] = [
        'id'              => $rows[0]['profile_id'],
        'profile_picture' => $rows[0]['profile_picture'],
        'birth_date'      => $rows[0]['birth_date'],
        'country'         => $rows[0]['country'],
        'english_level'   => $rows[0]['english_level'],
        'phone_number'    => $rows[0]['phone_number'],
        'bio'             => $rows[0]['bio'],
        'updated_at'      => $rows[0]['profile_updated_at']
      ];
    }

    // --- Tokens ---
    foreach ($rows as $row) {
      if ($row['token_id']) {
        $user['tokens'][] = [
          'id'         => $row['token_id'],
          'token'      => $row['token'],
          'expires_at' => $row['expires_at'],
          'created_at' => $row['token_created_at'],
          'ip_address' => $row['ip_address'],
          'device'     => $row['device'],
          'browser'    => $row['browser'],
        ];
      }
    }

    return $user;
  }
}
