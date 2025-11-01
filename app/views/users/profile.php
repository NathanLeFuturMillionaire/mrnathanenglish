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
    <title>OpenDoorsClass - <?= htmlspecialchars($_SESSION["user"]["username"]); ?></title>
</head>

<body>
    <div class="header-layout" style="width: 100%;">
        <?php require_once '../app/views/layouts/header.php'; ?>
    </div>

    <main>
        <!-- Menu latéral gauche -->
        <div class="left-menu">
            <ul>
                <li class="menu-item active" data-target="espace">
                    <a href="#">
                        <i data-lucide="user"></i>
                        <span>Mon espace</span>
                    </a>
                </li>
                <li class="menu-item" data-target="cours">
                    <a href="#">
                        <i data-lucide="book"></i>
                        <span>Cours</span>
                    </a>
                </li>
                <li class="menu-item" data-target="parametres">
                    <a href="#">
                        <i data-lucide="settings"></i>
                        <span>Paramètres</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Contenu principal -->
        <div class="right-content">
            <!-- Zone de chargement -->
            <div id="loading-spinner" class="spinner hidden"></div>

            <!-- Zone de message d'erreur -->
            <div id="error-message" class="error hidden">
                ❌ Connexion impossible. Veuillez vérifier votre internet et réessayer.
            </div>

            <div id="espace" class="content-section active">
                <h1>Bienvenue, <?= htmlspecialchars($_SESSION["user"]["username"]); ?> 👋</h1>
                <p>Votre profil est bien confirmé ✅</p>
            </div>

            <div id="cours" class="content-section">
                <h1>Mes cours 🎓</h1>
                <p>Accédez à vos formations et suivez votre progression ici.</p>
            </div>

            <div id="parametres" class="content-section">
                <h1>Paramètres ⚙️</h1>
                <p>Gérez vos informations personnelles et vos préférences.</p>
            </div>
        </div>

    </main>


    <script src="./js/main.js"></script>
    <script src="./js/users/profile.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

</body>

</html>