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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" />

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
            <small class="steps" style="font-family: 'Montserrat', sans-serif; font-size: 1.1rem; color: #000;">1 / 5</small>
            <h2>Choisissez votre photo de profil</h2>
            <p>Because using our plateform with a true profile will make you more credible.</p>
            <div class="square-profil">
                <?php
                // Récupère la photo depuis la session
                $profilePic = $_SESSION['user']['profile_picture'] ?? null;
                ?>
                <form action="" method="POST" enctype="multipart/form-data">
                    <label for="profilePic" class="circle-upload"
                        <?php if ($profilePic && $profilePic !== 'default.png'): ?>
                        style="background-image: url('./uploads/profiles/<?= htmlspecialchars($profilePic) ?>'); 
                   background-size: cover; 
                   background-position: center; 
                   border: 3px solid #5a57a3;"
                        <?php endif; ?>>
                        <?php if (!$profilePic || $profilePic === 'default.png') echo 'Cliquez pour choisir'; ?>
                    </label>
                    <input type="file" id="profilePic" name="profilePic" style="display: none;" accept="image/*">
                </form>
            </div>
            <div class="next">
                <button type="submit" class="btn-submit">Suivant</button>
                <a href="">Ignorer cette étape</a>
            </div>
        </div>
        <div class="birthdate-choice">
            <small class="steps" style="font-family: 'Montserrat', sans-serif; font-size: 1.1rem; color: #000;">2 / 5</small>
            <h2>Entrez votre date de naissance</h2>
            <p>Knowing your birthday helps us personalize your learning journey.</p>
            <div class="square-birthdate">
                <form action="" method="POST">
                    <label for="birthdate" class="birthdate-label">
                        Sélectionnez votre date de naissance
                    </label>
                    <input
                        type="date"
                        id="birthdate"
                        name="birthdate"
                        required
                        min="1900-01-01"
                        max="<?php echo date('Y-m-d', strtotime('-10 years')); ?>"
                        value="<?php
                                // Si l'utilisateur a déjà une date en base, la pré-remplir
                                echo isset($_SESSION['user']['birth_date']) && $_SESSION['user']['birth_date'] !== null
                                    ? htmlspecialchars($_SESSION['user']['birth_date'])
                                    : '';
                                ?>">
                </form>
            </div>
            <div class="next">
                <button type="submit" class="btn-submit btn-submit-date">Suivant</button>
            </div>
        </div>
        <div class="phone-number">
            <small class="steps" style="font-family: 'Montserrat', sans-serif; font-size: 1.1rem; color: #000;">3 / 5</small>
            <h2>Entrez votre numéro de téléphone</h2>
            <p>Your phone number could help us to get in touch with you just to check if you're okay.</p>
            <form class="square-phone" method="POST">
                <div class="phone-input-container">
                    <img id="flag" class="flag" src="" alt="Drapeau">
                    <span id="dial-code" class="dial-code"></span>
                    <input value="<?php echo isset($_SESSION["user"]["phone_number"]) && $_SESSION["user"]["phone_number"] !== null ? htmlspecialchars($_SESSION["user"]["phone_number"]) : ''; ?>" type="tel" id="phone" name="phone_number" placeholder="Votre numéro" required>
                </div>
            </form>
            <div class="error-phone" style="color:red; display: none;"></div>

            <div class="next">
                <button type="button" id="submitPhone" class="btn-submit submitPhone" disabled>Suivant</button>
            </div>
        </div>

        <div class="english-level-section">
            <form action="" method="POST" class="form-level">
                <small class="steps" style="font-family: 'Montserrat', sans-serif; font-size: 1.1rem; color: #000;display:block;text-align:center;">4 / 5</small>
                <h2>Quel est votre niveau en anglais ?</h2>
                <div class="level-options">

                    <label class="level-option">
                        <input type="radio" name="english_level" value="beginner" checked>
                        <span class="custom-radio"></span>
                        <div class="level-content">
                            <strong>Débutant</strong>
                            <p>Vous partez de zéro et n'avez aucune base en anglais et vous souhaitez combler cet handicap.</p>
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
                        <button type="button" id="submitLevel" class="btn-submit submitLevel">Suivant</button>
                    </div>
                </div>
            </form>
        </div>


        <div class="bio-section">
            <small class="steps" style="font-family: 'Montserrat', sans-serif; font-size: 1.1rem; color: #000;display:block;text-align:center;">5 / 5</small>
            <h2>Parlez-nous de vous</h2>
            <p>Racontez-nous un peu qui vous êtes, vos centres d'intérêt, vos objectifs ou tout ce que vous souhaitez partager.</p>

            <div class="bio-input">
                <textarea id="bio" placeholder="Écrivez votre bio ici..." rows="5"></textarea>
                <div class="next">
                    <!-- <button type="button" class="btn-prev">Précédent</button> -->
                    <button type="button" id="submitBio" class="btn-submit submitBio" disabled>Suivant</button>
                </div>
            </div>
        </div>


    </div>

    <footer class="confirmation-footer">
        <p>&copy; <?= date('Y') ?> Mr Nathan English. Tous droits réservés.</p>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
    <!-- Lien vers la bibliothèque libphonenumber-js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/libphonenumber-js/1.9.12/libphonenumber-js.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/welcome.js"></script>

</body>

</html>