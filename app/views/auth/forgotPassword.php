<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/auth/forgotPassword.css">
    <title>Mot de passe oublié - Mr Nathan English</title>
</head>

<body>
    <div class="header-layout" style="width: 100%;">
        <?php require_once '../app/views/layouts/header.php'; ?>
    </div>
    <main>
        <section class="forgot-password-page">
            <div class="forgot-password-container">
                <h1>Vous avez oublié votre mot de passe ?</h1>
                <p>Pas de panique ! Entrez l'adresse e-mail associée à votre compte, et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>

                <div class="form-forgot-password">
                    <form action="" method="POST" id="forgotPasswordForm">
                        <!-- Message d'erreur général -->
                        <div class="error-message" data-for="general"></div>

                        <!-- Champ email -->
                        <div class="input-group">
                            <label for="email">Adresse e-mail</label>
                            <input
                                type="text"
                                id="email"
                                name="find-email"
                                placeholder="exemple@domaine.com">
                            <div class="error-message" data-for="email"></div>
                        </div>

                        <!-- Bouton de soumission -->
                        <button type="submit" id="btn-submit" class="btn-submit">
                            <span class="btn-text">Retrouver mon compte</span>
                            <span class="spinner"></span>
                        </button>

                        <!-- Lien de retour -->
                        <div class="back-link">
                            <a href="./login">Retour à la connexion</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <!-- Conteneur pour les toasts -->
    <div id="toast-container"></div>

    <script src="../public/js/main.js"></script>
    <script src="../public/js/forgotPassword.js"></script>
</body>

</html>