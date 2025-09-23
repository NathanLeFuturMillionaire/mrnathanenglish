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
                <!-- Colonne gauche : formulaire -->
                <div class="login-left">
                    <!-- Flèche retour -->
                    <a href="javascript:window.history.back();" class="back-arrow">
                        &#8592;
                    </a>
                    <form action="" method="POST" class="form-login" id="loginForm">
                        <h2>OpenDoorsClass</h2>
                        <h2>Se connecter</h2>
                        <p>Connectez-vous à votre compte pour accéder à vos cours d'anglais.</p>

                        <!-- Message d'erreur général -->
                        <div class="error-message" data-for="general"></div>

                        <!-- Pour email -->
                        <label for="email">Adresse e-mail</label>
                        <input
                            type="text"
                            id="email"
                            name="email"
                            placeholder="exemple@gmail.com"
                            value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                        <div class="error-message" data-for="email"></div>

                        <!-- Mot de passe -->
                        <label for="password">Mot de passe</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Votre mot de passe">
                        <div class="error-message" data-for="password"></div>

                        <!-- Case à cocher "Ne pas me déconnecter" -->
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
                </div>

                <!-- Colonne droite : image -->
                <div class="login-right"></div>
            </div>
        </section>
    </main>

    <script src="js/main.js"></script>
    <script src="js/login.js"></script>
</body>

</html>