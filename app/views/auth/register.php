<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/auth/register.css">

    <title>Inscription - OpenDoorsClass</title>

</head>

<body>
    <main>
        <section class="register-page">
            <div class="register-container">

                <!-- Colonne gauche : formulaire -->
                <div class="register-left">
                    <!-- Flèche retour -->
                    <a href="javascript:window.history.back();" class="back-arrow">
                        &#8592;
                    </a>
                    <form action="" method="POST" class="form-inscription" id="registerForm">
                        <h2>OpenDoorsClass</h2>
                        <h2>Créer un compte</h2>
                        <p>Ouvrez votre compte gratuitement et apprenez l'anglais à votre rythme.</p>

                        <!-- Exemple pour fullname -->
                        <label for="fullname">Nom complet</label>
                        <input
                            type="text"
                            id="fullname"
                            name="fullname"
                            placeholder="Votre nom complet"
                            value="<?= htmlspecialchars($old['fullname'] ?? '') ?>">
                        <small class="error" id="error-fullname"><?= $errors['fullname'] ?? '' ?></small>

                        <!-- Pour email -->
                        <label for="email">Adresse e-mail</label>
                        <input
                            type="text"
                            id="email"
                            name="email"
                            placeholder="exemple@gmail.com"
                            value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                        <small class="error" id="error-email"><?= $errors['email'] ?? '' ?></small>

                        <!-- Pour username -->
                        <label for="username">Nom d’utilisateur</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Choisissez un nom d’utilisateur"
                            value="<?= htmlspecialchars($old['username'] ?? '') ?>">
                        <small class="error" id="error-username"><?= $errors['username'] ?? '' ?></small>

                        <!-- Mot de passe -->
                        <label for="password">Mot de passe</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Votre mot de passe">
                        <small class="error" id="error-password"><?= $errors['password'] ?? '' ?></small>

                        <!-- Confirmation mot de passe -->
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Confirmez votre mot de passe">
                        <small class="error" id="error-confirm_password"><?= $errors['confirm_password'] ?? '' ?></small>

                        <div class="form-bottom-line">
                            <label class="checkbox-container">
                                <input type="checkbox" name="terms" <?= isset($_POST['terms']) ? 'checked' : '' ?>>
                                <span>J’accepte les <a href="/terms" target="_blank">conditions d’utilisation du site.</a></span>
                            </label>
                            <small class="error"><?= $errors['terms'] ?? '' ?></small>

                            <a href="./login" class="login-link">J'ai déjà un compte</a>
                        </div>

                        <button type="submit" id="btn-submit" class="btn-submit">
                            <span class="btn-text">S’inscrire</span>
                            <span class="spinner"></span>
                        </button>
                    </form>

                </div>

                <!-- Colonne droite : image -->
                <div class="register-right"></div>
            </div>
        </section>


    </main>

    <script src="js/main.js"></script>
    <script src="js/register.js"></script>
</body>

</html>