<?php
if (!isset($_SESSION['user']) || (int)$_SESSION['user']['confirmed'] !== 1) {
    header('Location: ./login');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bienvenue - Mr Nathan English</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/auth/welcome.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php require "../app/views/layouts/header.php"; ?>

    <div class="container">
        <div class="container-title">
            <h1>Bienvenue à bord, <?= htmlspecialchars($user["username"]); ?> !</h1>
            <p>Préparez-vous à booster vos compétences en anglais et à gravir les échelons grâce à des exercices pratiques et des cours inspirants, adaptés à votre rythme.</p>
            <p><em>Your English journey starts now, let’s make it amazing!</em></p>
        </div>

        <div class="profil-picture-choice">
            <h2>Choisissez votre photo de profil</h2>
            <p>Because using our plateform with a true profile will make you more credible.</p>
            <div class="square-profil">
                <form action="" method="POST">
                    <label for="profilePic" class="circle-upload">
                        Cliquez pour choisir
                    </label>
                    <input type="file" id="profilePic" style="display: none;">
                </form>
            </div>
            <div class="next">
                <button type="submit" class="btn-submit">Suivant</button>
                <a href="">Ignorer cette étape</a>
            </div>
        </div>
    </div>

    <footer class="confirmation-footer">
        <p>&copy; <?= date('Y') ?> Mr Nathan English. Tous droits réservés.</p>
    </footer>

    <script src="js/main.js"></script>
    <script src="js/welcome.js"></script>
</body>
</html>
