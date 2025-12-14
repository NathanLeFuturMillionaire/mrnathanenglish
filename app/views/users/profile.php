<?php
// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user']['id'])) {
    header('Location: ./login');
    exit();
}

// Vérifie si le compte est confirmé
if ($_SESSION['user']['is_confirmed'] != 1) {
    header('Location: ./noconfirmed');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/users/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>OpenDoorsClass - <?= htmlspecialchars($_SESSION["user"]["username"]); ?></title>
</head>

<body>
    <?php require_once '../app/views/layouts/header.php'; ?>

    <div class="profile-layout">

        <!-- ===== MENU GAUCHE ===== -->
        <aside class="profile-menu">
            <div class="menu-header">
                <?php
                // On définit une variable pour l'image de profil
                $avatar = '../public/uploads/profiles/default.png'; // avatar par défaut
                if (isset($_SESSION["user"]["profile_picture"]) && !empty($_SESSION["user"]["profile_picture"])) {
                    $avatar = '../public/uploads/profiles/' . $_SESSION["user"]["profile_picture"];
                } elseif (isset($_SESSION["user"]["profile"]["profile_picture"]) && !empty($_SESSION["user"]["profile"]["profile_picture"])) {
                    $avatar = '../public/uploads/profiles/' . $_SESSION["user"]["profile"]["profile_picture"];
                }
                ?>
                <img src="<?= $avatar; ?>" alt="Avatar" class="avatar">
                <h2><?= htmlspecialchars($_SESSION["user"]["username"]); ?></h2>
                <h5 style="margin-top: -15px;color: #1dbf73;"><?= $_SESSION["user"]["english_level"]; ?></h5>
            </div>

            <nav class="menu-links">
                <a href="#infos" class="active"><i class="fa-solid fa-user"></i> Mon profil</a>
                <a href="#progression"><i class="fa-solid fa-chart-line"></i> Progression</a>
                <a href="#badges"><i class="fa-solid fa-award"></i> Badges</a>
                <a href="#objectifs"><i class="fa-solid fa-bullseye"></i> Objectifs</a>
                <a href="#settings"><i class="fa-solid fa-gear"></i> Paramètres</a>
                <a href="#subscription"><i class="fa-solid fa-credit-card"></i> Abonnement</a>
            </nav>
        </aside>

        <!-- ===== CONTENU DROITE ===== -->
        <main class="profile-content">

            <!-- ===== MON PROFIL ===== -->
            <section id="infos" class="profile-section">
                <h1 class="section-title">Profil</h1>

                <div class="card">
                    <h2 class="card-title">Informations personnelles</h2>
                    <div class="info-list">
                        <div class="info-item">
                            <label>Nom d'utilisateur :</label>
                            <p><?= htmlspecialchars($_SESSION["user"]["username"] ?? ($_SESSION["user"]["profile"]["username"] ?? "Non renseigné")); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Nom complet :</label>
                            <p><?= htmlspecialchars($_SESSION["user"]["fullname"] ?? ($_SESSION["user"]["profile"]["fullname"] ?? "Non renseigné")); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Email :</label>
                            <p><?= htmlspecialchars($_SESSION["user"]["email"] ?? ($_SESSION["user"]["profile"]["email"] ?? "Non renseigné")); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Numéro :</label>
                            <p><?= htmlspecialchars($_SESSION["user"]["phone_number"] ?? ($_SESSION["user"]["profile"]["phone_number"] ?? "Non renseigné")); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Pays :</label>
                            <p><?= htmlspecialchars($_SESSION["user"]["country"] ?? ($_SESSION["user"]["profile"]["country"] ?? "Non renseigné")); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Bio :</label>
                            <p><?= nl2br(htmlspecialchars($_SESSION["user"]["bio"] ?? ($_SESSION["user"]["profile"]["bio"] ?? "Aucune biographie."))); ?></p>
                        </div>
                    </div>

                </div>

                <div class="card">
                    <h2 class="card-title">Informations du compte</h2>
                    <!-- <?php var_dump($_SESSION); ?> -->
                    <div class="info-list">
                        <div class="info-item">
                            <label>État du compte :</label>
                            <p class="status <?= (!empty($_SESSION["user"]["is_confirmed"]) ? "success" : "danger"); ?>">
                                <?= (!empty($_SESSION["user"]["is_confirmed"]) ? "Confirmé" : (!empty($_SESSION["user"]["profile"]["is_confirmed"]) ? "Confirmé" : "Non confirmé")); ?>
                            </p>
                        </div>
                        <div class="info-item">
                            <label>Date de création :</label>
                            <p>
                                <?php
                                $createdAt = $_SESSION['user']['created_at'] ?? null;

                                if ($createdAt && !in_array($createdAt, ['0000-00-00 00:00:00', '0000-00-00'])) {
                                    try {
                                        $date = new DateTime($createdAt);

                                        $day   = $date->format('j');
                                        $month = $date->format('F');
                                        $year  = $date->format('Y');
                                        $hour  = $date->format('H');
                                        $min   = $date->format('i');

                                        // Traduction des mois
                                        $monthsFr = [
                                            'January'   => 'Janvier',
                                            'February'  => 'Février',
                                            'March'     => 'Mars',
                                            'April'     => 'Avril',
                                            'May'       => 'Mai',
                                            'June'      => 'Juin',
                                            'July'      => 'Juillet',
                                            'August'    => 'Août',
                                            'September' => 'Septembre',
                                            'October'   => 'Octobre',
                                            'November'  => 'Novembre',
                                            'December'  => 'Décembre'
                                        ];

                                        $monthFr = $monthsFr[$month] ?? $month;
                                        $dayDisplay = ($day == 1) ? '1er' : $day;

                                        echo "Le {$dayDisplay} {$monthFr} {$year} à {$hour}h{$min}";
                                    } catch (Exception $e) {
                                        echo "Date invalide";
                                    }
                                } else {
                                    echo "Non disponible";
                                }
                                ?>
                            </p>
                        </div>

                        <div class="info-item">
                            <label>Type d'abonnement :</label>
                            <p><?= htmlspecialchars($_SESSION["user"]["subscription_type"] ?? ($_SESSION["user"]["profile"]["subscription_type"] ?? "Gratuit")); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Prochain renouvellement :</label>
                            <p><?= htmlspecialchars($_SESSION["user"]["renew_date"] ?? ($_SESSION["user"]["profile"]["renew_date"] ?? "Aucun")); ?></p>
                        </div>
                    </div>

                </div>
            </section>

            <!-- ===== PROGRESSION ===== -->
            <section id="progression" class="profile-section" style="display:none;">
                <h1 class="section-title">Progression des cours</h1>
                <div class="card">
                    <div class="progress-item">
                        <p>Cours Anglais Niveau 1</p>
                        <div class="progress-bar">
                            <div class="progress" style="width:45%;"></div>
                        </div>
                        <small>45% complété</small>
                    </div>
                    <div class="progress-item">
                        <p>Cours HTML & CSS</p>
                        <div class="progress-bar">
                            <div class="progress" style="width:70%;"></div>
                        </div>
                        <small>70% complété</small>
                    </div>
                </div>
            </section>

            <!-- ===== BADGES ===== -->
            <section id="badges" class="profile-section" style="display:none;">
                <h1 class="section-title">Badges & Récompenses</h1>
                <div class="card">
                    <span class="badge">Débutant</span>
                    <span class="badge">Top Student</span>
                    <span class="badge">Streak 7 jours</span>
                </div>
            </section>

            <!-- ===== OBJECTIFS ===== -->
            <section id="objectifs" class="profile-section" style="display:none;">
                <h1 class="section-title">Objectifs d’apprentissage</h1>
                <div class="card">
                    <div class="progress-item">
                        <p>Objectif semaine : 3 chapitres</p>
                        <div class="progress-bar">
                            <div class="progress" style="width:60%;"></div>
                        </div>
                        <small>60% atteint</small>
                    </div>
                </div>
            </section>

            <!-- ===== PARAMÈTRES ===== -->
            <section id="settings" class="profile-section" style="display:none;">
                <h1 class="section-title">Paramètres du compte</h1>
                <div class="card">
                    <button class="btn-setting"><i class="fa-solid fa-lock"></i> Changer mot de passe</button>
                    <button class="btn-setting logout"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</button>
                </div>
            </section>

            <!-- ===== ABONNEMENT ===== -->
            <section id="subscription" class="profile-section" style="display:none;">
                <h1 class="section-title">Abonnement & Paiements</h1>
                <div class="card">
                    <div class="info-item"><label>Plan actuel :</label>
                        <p>Trimestriel - Anglais</p>
                    </div>
                    <div class="info-item"><label>Expiration :</label>
                        <p>15/12/2025</p>
                    </div>
                    <button class="btn-setting"><i class="fa-solid fa-credit-card"></i> Renouveler / Upgrade</button>
                </div>
            </section>

        </main>
    </div>

    <script src="./js/main.js"></script>
    <script src="./js/users/profile.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</body>

</html>