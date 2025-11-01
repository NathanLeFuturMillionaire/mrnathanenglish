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
        <!-- The banner -->
        <div class="banner"></div>
        <div class="information-section">
            <div class="profil-picture">

            </div>
            <div class="profile-detail">
                <div class="edit-courses-detail">
                    <a href=""></a>
                    
                </div>
            </div>
        </div>
    </main>

    <script src="./js/main.js"></script>
    <script src="./js/users/profile.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

</body>

</html>