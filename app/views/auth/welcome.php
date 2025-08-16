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
        <div class="birthdate-choice">
            <h2>Entrez votre date de naissance</h2>
            <p>Knowing your birthday helps us personalize your learning journey.</p>
            <div class="square-birthdate">
                <form action="" method="POST">
                    <label for="birthdate" class="birthdate-label">
                        Sélectionnez votre date de naissance
                    </label>
                    <input type="date" id="birthdate" name="birthdate" required>
                </form>
            </div>
            <div class="next">
                <button type="submit" class="btn-submit">Suivant</button>
            </div>
        </div>
        <div class="phone-number">
            <h2>Entrez votre numéro de téléphone</h2>
            <p>Cela peut nous aider à vous contacter en privé pour prendre des nouvelles.</p>
            <div class="square-phone">
                <img id="flag" class="flag" src="" alt="Drapeau">
                <span id="dial-code" class="dial-code"></span>
                <input type="tel" id="phone" placeholder="Votre numéro">
            </div>
            <div class="next">
                <!-- <button type="button" class="btn-prev">Précédent</button> -->
                <button type="submit" class="btn-submit">Suivant</button>
            </div>
        </div>

        <div class="english-level-section">
            <h2>Quel est votre niveau en anglais ?</h2>
            <div class="level-options">

                <label class="level-option">
                    <input type="radio" name="english_level" value="beginner">
                    <span class="custom-radio"></span>
                    <div class="level-content">
                        <strong>Débutant</strong>
                        <p>Vous comprenez quelques mots et phrases simples mais avez besoin d’aide pour communiquer.</p>
                    </div>
                </label>

                <label class="level-option">
                    <input type="radio" name="english_level" value="intermediate">
                    <span class="custom-radio"></span>
                    <div class="level-content">
                        <strong>Intermédiaire</strong>
                        <p>Vous pouvez tenir une conversation simple et comprendre l’essentiel d’un texte ou d’un dialogue.</p>
                    </div>
                </label>

                <label class="level-option">
                    <input type="radio" name="english_level" value="advanced">
                    <span class="custom-radio"></span>
                    <div class="level-content">
                        <strong>Avancé</strong>
                        <p>Vous vous exprimez couramment et comprenez des textes complexes avec aisance.</p>
                    </div>
                </label>
                <div class="next">
                    <!-- <button type="button" class="btn-prev">Précédent</button> -->
                    <button type="submit" class="btn-submit">Suivant</button>
                </div>
            </div>
        </div>

        <div class="bio-section">
            <h2>Parlez-nous de vous</h2>
            <p>Racontez-nous un peu qui vous êtes, vos centres d’intérêt, vos objectifs ou tout ce que vous souhaitez partager.</p>

            <div class="bio-input">
                <textarea id="bio" placeholder="Écrivez votre bio ici..." rows="5"></textarea>
                <div class="next">
                    <!-- <button type="button" class="btn-prev">Précédent</button> -->
                    <button type="submit" class="btn-submit">Suivant</button>
                </div>
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