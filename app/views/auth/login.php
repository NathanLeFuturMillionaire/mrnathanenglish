<?php
session_start();
require_once '../app/controllers/AuthController.php';

use App\controllers\AuthController;

$auth = new AuthController();
$userFromCookie = null;

// Vérifier si un cookie existe
if (!empty($_COOKIE['remember_me_token'])) {
    $token = $_COOKIE['remember_me_token'];
    $userFromCookie = $auth->getUserWithDetails($token);
}

function formatDateFr($date)
{
    if (empty($date)) return '';

    $formatter = new \IntlDateFormatter(
        'fr_FR',                        // Locale français
        \IntlDateFormatter::LONG,       // Format complet (ex: 5 septembre 2025)
        \IntlDateFormatter::NONE,       // Pas d'heure
        'Europe/Paris',                 // Fuseau horaire
        \IntlDateFormatter::GREGORIAN,  // Calendrier
        "'le' d MMMM yyyy"              // Pattern personnalisé
    );

    return $formatter->format(new \DateTime($date));
}

if(isset($_POST["userLoginSubmit"])) {
    // $loginAsUser = $auth->loginAsUser();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/auth/login.css">
    <title>Connexion - Mr Nathan English</title>
</head>

<body>
    <?php require_once '../app/views/layouts/header.php'; ?>
    <main>
        <section class="login-page">
            <div class="login-container">
                <!-- Colonne gauche -->
                <div class="login-left login-left-cookie">
                    <a href="javascript:window.history.back();" class="back-arrow">&#8592;</a>

                    <?php if ($userFromCookie): ?>
                        <!-- Infos de l'utilisateur depuis le cookie -->
                        <div class="user-remember-info">
                            <h2>OpenDoorsClass</h2>
                            <h2>Bon retour !</h2>
                            <p>Nous ne vous avons pas oublié <em><?= $userFromCookie["fullname"]; ?></em>, reconnectez-vous et continuez votre apprentissage de l'anglais.</p>

                            <?php if (!empty($userFromCookie['profile']['profile_picture'])): ?>
                                <?php if ($userFromCookie['profile']['profile_picture'] !== NULL): ?>
                                    <img src="../public/uploads/profiles/<?= htmlspecialchars($userFromCookie['profile']['profile_picture']) ?>" alt="Photo de profil" width="120" style="border-radius:50%;margin-bottom:15px;">
                                <?php else: ?>
                                    <img src="../public/uploads/profiles/defaut.png" alt="Photo de profile" width="120" style="border-radius:50%;margin-bottom:15px;">
                                <?php endif; ?>
                            <?php endif; ?>
                            <!-- Le nom de l'utilisateur -->
                            <h4 style="margin-top: -20px; margin-bottom: 20px;"><?= htmlspecialchars($userFromCookie["fullname"]); ?></h4>
                            <p>Né(e) <?= formatDateFr($userFromCookie['profile']['birth_date']) ?></p>
                            <p><?= htmlspecialchars($userFromCookie['profile']['phone_number'] ?? 'Numéro inconnu') ?></p>
                            <a href="./endtoken" class="not-me-link" style="margin-top: -30px;">Ce n'est pas moi</a>

                            <form action="./persistLogin" method="POST" style="margin-top:15px;">
                                <input type="hidden" name="login_as_user_id" value="<?= $userFromCookie['id'] ?>">
                                <button type="submit" class="btn-login-as-user" name="userLoginSubmit">
                                    Se connecter en tant que <?= htmlspecialchars($userFromCookie['fullname']) ?>
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- Formulaire classique -->
                        <form action="" method="POST" class="form-login" id="loginForm">
                            <h2>OpenDoorsClass</h2>
                            <h2>Se connecter</h2>
                            <p>Connectez-vous à votre compte pour accéder à vos cours d'anglais.</p>

                            <div class="error-message" data-for="general"></div>

                            <label for="email">Adresse e-mail</label>
                            <input type="text" id="email" name="email"
                                placeholder="exemple@gmail.com"
                                value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                            <div class="error-message" data-for="email"></div>

                            <label for="password">Mot de passe</label>
                            <input type="password" id="password" name="password" placeholder="Votre mot de passe">
                            <div class="error-message" data-for="password"></div>

                            <div class="checkbox-container">
                                <input type="checkbox" id="remember-me" name="remember_me">
                                <label for="remember-me" class="checkbox-label">Ne pas me déconnecter</label>
                            </div>

                            <div class="form-bottom-line">
                                <a href="./forgot-password" class="forgot-password-link">Mot de passe oublié ?</a>
                                <a href="./register" class="register-link">Créez un compte</a>
                            </div>

                            <button type="submit" id="btn-submit" class="btn-submit">
                                <span class="btn-text">Se connecter</span>
                                <span class="spinner"></span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Colonne droite -->
                <div class="login-right"></div>
            </div>
        </section>
    </main>

    <script src="js/main.js"></script>
    <script src="js/login.js"></script>
</body>

</html>