<?php
if (!isset($_SESSION['user']['id'])) {
    header('Location: ./login');
    exit();
}
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/style.min.css">
    <link rel="stylesheet" href="../public/css/users/profile.min.css">
    <title>OpenDoorsClass — <?= htmlspecialchars($_SESSION["user"]["username"]) ?></title>
</head>

<body>
    <?php require_once '../app/views/layouts/header.php'; ?>

    <div class="profile-layout">

        <!-- ===== SIDEBAR ===== -->
        <aside class="profile-menu">

            <div class="menu-header">
                <?php
                $avatar = '../public/uploads/profiles/default.png';
                if (!empty($_SESSION["user"]["profile_picture"]))
                    $avatar = '../public/uploads/profiles/' . $_SESSION["user"]["profile_picture"];
                elseif (!empty($_SESSION["user"]["profile"]["profile_picture"]))
                    $avatar = '../public/uploads/profiles/' . $_SESSION["user"]["profile"]["profile_picture"];

                $level = $_SESSION["user"]["english_level"] ?? '';
                $levelLabels = [
                    'beginner'     => 'Débutant',
                    'intermediate' => 'Intermédiaire',
                    'advanced'     => 'Avancé',
                ];
                $levelLabel = $levelLabels[$level] ?? ucfirst($level);
                ?>
                <div class="avatar-wrapper">
                    <img src="<?= $avatar ?>" alt="Avatar" class="avatar" loading="lazy" width="88" height="88">
                    <span class="avatar-status"></span>
                </div>
                <h2><?= htmlspecialchars($_SESSION["user"]["username"]) ?></h2>
                <span class="level-badge" data-field="level-badge">
                    <i class="fas fa-graduation-cap"></i>
                    <?= htmlspecialchars($user['profile']['english_level_label'] ?? $levelLabel) ?>
                </span>
            </div>

            <!-- Stats rapides -->
            <div class="menu-stats">
                <div class="menu-stat">
                    <span class="menu-stat__value">0</span>
                    <span class="menu-stat__label">Cours</span>
                </div>
                <div class="menu-stat">
                    <span class="menu-stat__value">0</span>
                    <span class="menu-stat__label">Leçons</span>
                </div>
                <div class="menu-stat">
                    <span class="menu-stat__value">0</span>
                    <span class="menu-stat__label">Jours</span>
                </div>
                <div class="menu-stat">
                    <span class="menu-stat__value">0%</span>
                    <span class="menu-stat__label">Complété</span>
                </div>
            </div>

            <nav class="menu-nav">
                <span class="menu-nav-section">Général</span>
                <div class="menu-links">
                    <a href="#infos" class="active" data-section="infos">
                        <i class="fas fa-user"></i> Mon profil
                    </a>
                    <a href="#courses" data-section="courses">
                        <i class="fas fa-book-open"></i> Mes cours
                    </a>
                    <a href="#progression" data-section="progression">
                        <i class="fas fa-chart-line"></i> Progression
                    </a>
                </div>
                <span class="menu-nav-section">Récompenses</span>
                <div class="menu-links">
                    <a href="#badges" data-section="badges">
                        <i class="fas fa-award"></i> Badges
                    </a>
                    <a href="#objectifs" data-section="objectifs">
                        <i class="fas fa-bullseye"></i> Objectifs
                    </a>
                </div>
                <span class="menu-nav-section">Compte</span>
                <div class="menu-links">
                    <a href="#settings" data-section="settings">
                        <i class="fas fa-gear"></i> Paramètres
                    </a>
                    <a href="#subscription" data-section="subscription">
                        <i class="fas fa-credit-card"></i> Abonnement
                    </a>
                </div>
            </nav>
        </aside>

        <!-- ===== CONTENU ===== -->
        <main class="profile-content">

            <!-- ===== MON PROFIL ===== -->
            <section id="infos" class="profile-section active">

                <div class="section-header">
                    <div>
                        <h1 class="section-title">Mon profil</h1>
                        <p class="section-subtitle">Vos informations personnelles et votre compte</p>
                    </div>
                    <button class="btn-edit-profile" id="btn-edit-toggle">
                        <i class="fas fa-pen"></i> Modifier
                    </button>
                </div>

                <!-- ===== VUE LECTURE ===== -->
                <div id="profile-view">

                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-user"></i>
                                Informations personnelles
                            </h2>
                        </div>
                        <div class="info-list">
                            <!-- Informations personnelles -->
                            <!-- Informations personnelles -->
                            <div class="info-item">
                                <label>Nom d'utilisateur</label>
                                <p data-field="username"><?= htmlspecialchars($user['username'] ?? 'Non renseigné') ?></p>
                            </div>
                            <div class="info-item">
                                <label>Nom complet</label>
                                <p data-field="fullname"><?= htmlspecialchars($user['fullname'] ?? 'Non renseigné') ?></p>
                            </div>
                            <div class="info-item">
                                <label>Adresse e-mail</label>
                                <p data-field="email"><?= htmlspecialchars($user['email'] ?? 'Non renseigné') ?></p>
                            </div>
                            <div class="info-item">
                                <label>Numéro de téléphone</label>
                                <p data-field="phone"><?= htmlspecialchars($user['phone_number'] ?? 'Non renseigné') ?></p>
                            </div>
                            <div class="info-item">
                                <label>Pays</label>
                                <p data-field="country"><?= htmlspecialchars($user['country'] ?? 'Non renseigné') ?></p>
                            </div>
                            <div class="info-item">
                                <label>Date de naissance</label>
                                <p data-field="birth_date"><?= htmlspecialchars($user['birth_date_formatted']) ?></p>
                            </div>
                            <div class="info-item">
                                <label>Niveau d'anglais</label>
                                <p data-field="english_level"><?= htmlspecialchars($user['english_level_label']) ?></p>
                            </div>
                            <div class="info-item">
                                <label>Biographie</label>
                                <p data-field="bio"><?= nl2br(htmlspecialchars($user['bio'] ?? 'Aucune biographie.')) ?></p>
                            </div>

                            <!-- Informations du compte -->
                            <div class="info-item">
                                <label>État du compte</label>
                                <p>
                                    <span class="status <?= !empty($user['is_confirmed']) ? 'success' : 'danger' ?>">
                                        <?= !empty($user['is_confirmed']) ? 'Confirmé' : 'Non confirmé' ?>
                                    </span>
                                </p>
                            </div>
                            <div class="info-item">
                                <label>Membre depuis</label>
                                <p><?= htmlspecialchars($user['created_at_formatted']) ?></p>
                            </div>
                            <div class="info-item">
                                <label>Type d'abonnement</label>
                                <p><?= (!empty($subscriptions[0]["type"]) && $subscriptions[0]["type"] === "premium") ? 'Premium' : 'Accès libre' ?></p>
                            </div>
                            <div class="info-item">
                                <label>Prochain renouvellement</label>
                                <p><?= htmlspecialchars($subscriptions[0]['next_billing_formatted'] ?? 'Aucun abonnement actif') ?></p>
                            </div>

                            <!-- Section abonnement -->
                            <?php foreach ($subscriptions as $sub): ?>
                                <div class="subscription">
                                    <p><strong>Type :</strong> <?= htmlspecialchars($sub['type'] ?? 'Inconnu') ?></p>
                                    <p><strong>Prix :</strong> <?= number_format($sub['amount'] ?? 0, 0, ',', ' ') ?> <?= htmlspecialchars($sub['currency'] ?? 'FCFA') ?></p>
                                    <p>
                                        <strong>Statut :</strong>
                                        <span class="<?= $sub['status_class'] ?>"><?= htmlspecialchars($sub['status'] ?? '') ?></span>
                                        (<?= htmlspecialchars($sub['days_message']) ?>)
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>

                <!-- ===== FORMULAIRE ÉDITION ===== -->
                <div id="profile-edit" style="display:none;">

                    <div class="card edit-card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-pen"></i>
                                Modifier mon profil
                            </h2>
                            <button class="btn-cancel-edit" id="btn-cancel-edit">
                                <i class="fas fa-xmark"></i> Annuler
                            </button>
                        </div>

                        <form id="profile-edit-form" enctype="multipart/form-data">

                            <!-- Photo de profil -->
                            <div class="edit-avatar-section">
                                <div class="edit-avatar-wrapper">
                                    <img
                                        src="<?= $avatar ?>"
                                        alt="Avatar"
                                        id="avatar-preview"
                                        class="edit-avatar-preview"
                                        width="80"
                                        height="80">
                                    <label for="profile_picture" class="edit-avatar-btn" title="Changer la photo">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                    <input
                                        type="file"
                                        id="profile_picture"
                                        name="profile_picture"
                                        accept="image/jpeg,image/png,image/webp"
                                        style="display:none;">
                                </div>
                                <div class="edit-avatar-hint">
                                    <p>Cliquez sur l'icône pour changer votre photo</p>
                                    <p>JPG, PNG ou WebP | max 5 Mo</p>
                                </div>
                            </div>

                            <!-- Champs -->
                            <div class="edit-grid">
                                <div class="edit-field">
                                    <label for="edit-username">Nom d'utilisateur</label>
                                    <input
                                        type="text"
                                        id="edit-username"
                                        name="username"
                                        value="<?= htmlspecialchars($_SESSION["user"]["username"] ?? '') ?>"
                                        placeholder="Votre nom d'utilisateur"
                                        autocomplete="username">
                                    <small class="edit-error" id="err-username"></small>
                                </div>

                                <div class="edit-field">
                                    <label for="edit-fullname">Nom complet</label>
                                    <input
                                        type="text"
                                        id="edit-fullname"
                                        name="fullname"
                                        value="<?= htmlspecialchars($_SESSION["user"]["fullname"] ?? '') ?>"
                                        placeholder="Votre nom complet"
                                        autocomplete="name">
                                    <small class="edit-error" id="err-fullname"></small>
                                </div>

                                <div class="edit-field">
                                    <label for="edit-email">Adresse e-mail</label>
                                    <input
                                        type="email"
                                        id="edit-email"
                                        name="email"
                                        value="<?= htmlspecialchars($_SESSION["user"]["email"] ?? '') ?>"
                                        placeholder="votre@email.com"
                                        autocomplete="email">
                                    <small class="edit-error" id="err-email"></small>
                                </div>

                                <div class="edit-field">
                                    <label for="edit-phone">Numéro de téléphone</label>
                                    <input
                                        type="tel"
                                        id="edit-phone"
                                        name="phone_number"
                                        value="<?= htmlspecialchars($_SESSION["user"]["phone_number"] ?? '') ?>"
                                        placeholder="+241 00 00 00 00"
                                        autocomplete="tel">
                                    <small class="edit-error" id="err-phone"></small>
                                </div>

                                <div class="edit-field">
                                    <label for="edit-country">Pays</label>
                                    <input
                                        type="text"
                                        id="edit-country"
                                        name="country"
                                        value="<?= htmlspecialchars($_SESSION["user"]["country"] ?? '') ?>"
                                        placeholder="Votre pays"
                                        autocomplete="country-name">
                                    <small class="edit-error" id="err-country"></small>
                                </div>

                                <div class="edit-field edit-field--full">
                                    <label for="edit-bio">Biographie</label>
                                    <textarea
                                        id="edit-bio"
                                        name="bio"
                                        rows="4"
                                        placeholder="Parlez-nous de vous..."><?= htmlspecialchars($_SESSION["user"]["bio"] ?? '') ?></textarea>
                                    <small class="edit-error" id="err-bio"></small>
                                </div>
                                <div class="edit-field">
                                    <label for="edit-birthdate">Date de naissance</label>
                                    <input
                                        type="date"
                                        id="edit-birthdate"
                                        name="birth_date"
                                        value="<?= htmlspecialchars($_SESSION["user"]["profile"]["birth_date"] ?? '') ?>">
                                    <small class="edit-error" id="err-birth_date"></small>
                                </div>

                                <div class="edit-field">
                                    <label for="edit-level">Niveau d'anglais</label>
                                    <select id="edit-level" name="english_level">
                                        <option value="">-- Choisir un niveau --</option>
                                        <option value="beginner"
                                            <?= ($_SESSION["user"]["profile"]["english_level"] ?? '') === 'beginner'     ? 'selected' : '' ?>>
                                            Débutant
                                        </option>
                                        <option value="intermediate"
                                            <?= ($_SESSION["user"]["profile"]["english_level"] ?? '') === 'intermediate' ? 'selected' : '' ?>>
                                            Intermédiaire
                                        </option>
                                        <option value="advanced"
                                            <?= ($_SESSION["user"]["profile"]["english_level"] ?? '') === 'advanced'     ? 'selected' : '' ?>>
                                            Avancé
                                        </option>
                                    </select>
                                    <small class="edit-error" id="err-english_level"></small>
                                </div>
                                <small style="color: gray;">Actualisez la page si vous ne constatez aucun changement.</small>
                            </div>

                            <!-- Message global -->
                            <div id="edit-message" class="edit-message" style="display:none;"></div>

                            <!-- Actions -->
                            <div class="edit-actions">
                                <button type="button" class="btn-cancel-edit" id="btn-cancel-edit-2">
                                    <i class="fas fa-xmark"></i> Annuler
                                </button>
                                <button type="submit" class="btn-save" id="btn-save">
                                    <span class="btn-text"><i class="fas fa-check"></i> Enregistrer</span>
                                    <span class="btn-spinner" style="display:none;">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </button>
                            </div>

                        </form>
                    </div>

                </div>

            </section>

            <!-- ===== MES COURS ===== -->
            <section id="courses" class="profile-section">
                <div class="section-header">
                    <div>
                        <h1 class="section-title">Mes cours</h1>
                        <p class="section-subtitle">Les formations que vous suivez</p>
                    </div>
                </div>
                <div class="card">
                    <p style="color:var(--text-muted);font-size:0.9rem;">Aucun cours en cours pour le moment.</p>
                </div>
            </section>

            <!-- ===== PROGRESSION ===== -->
            <section id="progression" class="profile-section">
                <div class="section-header">
                    <div>
                        <h1 class="section-title">Progression</h1>
                        <p class="section-subtitle">Votre avancement dans chaque formation</p>
                    </div>
                </div>
                <div class="card">
                    <div class="progress-item">
                        <div class="progress-item__header">
                            <p>Anglais Niveau 1</p>
                            <small>45%</small>
                        </div>
                        <div class="progress-bar">
                            <div class="progress" style="width:45%"></div>
                        </div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-item__header">
                            <p>Anglais des affaires</p>
                            <small>70%</small>
                        </div>
                        <div class="progress-bar">
                            <div class="progress" style="width:70%"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===== BADGES ===== -->
            <section id="badges" class="profile-section">
                <div class="section-header">
                    <div>
                        <h1 class="section-title">Badges & Récompenses</h1>
                        <p class="section-subtitle">Vos accomplissements</p>
                    </div>
                </div>
                <div class="card">
                    <div class="badge-grid">
                        <span class="badge"><i class="fas fa-star"></i> Débutant</span>
                        <span class="badge"><i class="fas fa-trophy"></i> Top Student</span>
                        <span class="badge"><i class="fas fa-fire"></i> Streak 7 jours</span>
                    </div>
                </div>
            </section>

            <!-- ===== OBJECTIFS ===== -->
            <section id="objectifs" class="profile-section">
                <div class="section-header">
                    <div>
                        <h1 class="section-title">Objectifs</h1>
                        <p class="section-subtitle">Vos objectifs d'apprentissage de la semaine</p>
                    </div>
                </div>
                <div class="card">
                    <div class="progress-item">
                        <div class="progress-item__header">
                            <p>Objectif semaine — 3 chapitres</p>
                            <small>60%</small>
                        </div>
                        <div class="progress-bar">
                            <div class="progress" style="width:60%"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===== PARAMÈTRES ===== -->
            <section id="settings" class="profile-section">
                <div class="section-header">
                    <div>
                        <h1 class="section-title">Paramètres</h1>
                        <p class="section-subtitle">Gérez votre compte</p>
                    </div>
                </div>
                <div class="card">
                    <button class="btn-setting">
                        <i class="fas fa-lock"></i> Changer le mot de passe
                    </button>
                    <button class="btn-setting logout">
                        <i class="fas fa-right-from-bracket"></i> Déconnexion
                    </button>
                </div>
            </section>

            <!-- ===== ABONNEMENT ===== -->
            <section id="subscription" class="profile-section">
                <div class="section-header">
                    <div>
                        <h1 class="section-title">Abonnement</h1>
                        <p class="section-subtitle">Vos paiements et renouvellements</p>
                    </div>
                </div>
                <div class="card">
                    <?php if (!empty($subscriptions)): ?>
                        <?php foreach ($subscriptions as $sub): ?>
                            <div class="subscription">
                                <?php
                                if (!empty($sub['start_date']) && !empty($sub['end_date'])) {
                                    $endDate = new DateTime($sub['end_date']);
                                    $today   = new DateTime();
                                    if ($today > $endDate) {
                                        $daysMessage = "Expiré";
                                        $statusClass = "expired";
                                    } else {
                                        $d           = $today->diff($endDate)->days;
                                        $daysMessage = $d === 0 ? "Arrive à terme aujourd'hui" : ($d === 1 ? "1 jour restant" : "$d jours restants");
                                        $statusClass = $d <= 7 ? "warning" : "active";
                                    }
                                } else {
                                    $daysMessage = "Aucun abonnement actif";
                                    $statusClass = "none";
                                }
                                ?>
                                <p><strong>Type :</strong> <?= htmlspecialchars($sub['type'] ?? 'Inconnu') ?></p>
                                <p><strong>Prix :</strong> <?= number_format($sub['amount'] ?? 0, 0, ',', ' ') ?> <?= htmlspecialchars($sub['currency'] ?? 'FCFA') ?></p>
                                <p><strong>Statut :</strong>
                                    <span class="<?= $statusClass ?>"><?= htmlspecialchars($sub['status'] ?? '') ?></span>
                                    (<?= $daysMessage ?>)
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:var(--text-muted);font-size:0.9rem;">Aucun abonnement actif.</p>
                    <?php endif; ?>
                    <button class="btn-setting" style="margin-top:16px;">
                        <i class="fas fa-credit-card"></i> Renouveler mon abonnement
                    </button>
                </div>
            </section>

        </main>
    </div>

    <script src="./js/header.min.js" defer></script>
    <script src="./js/users/profile.min.js" defer></script>
</body>

</html>