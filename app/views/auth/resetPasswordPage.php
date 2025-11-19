<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/auth/resetPasswordPage.css">
    <title>Réinitialisation du mot de passe - Mr Nathan English</title>
</head>

<body>
    <div class="header-layout" style="width: 100%;">
        <?php require_once '../app/views/layouts/header.php'; ?>
    </div>
    <main>
        <section class="reset-password-page">
            <div class="reset-password-container">
                <!-- Colonne gauche -->
                <div class="reset-left">
                    <!-- Flèche retour -->
                    <a href="javascript:window.history.back();" class="back-arrow">
                        &#8592;
                    </a>

                    <?php if ($error): ?>
                        <!-- Message d'erreur si token invalide -->
                        <div class="error-container">
                            <h2>OpenDoorsClass</h2>
                            <h2>Erreur de réinitialisation</h2>
                            <p><?= htmlspecialchars($error) ?></p>
                            <a href="./forgot-password" class="back-link">Demander un nouveau lien</a>
                        </div>
                    <?php else: ?>
                        <!-- Formulaire standard si pas de token valide -->
                        <form action="/reset-password" method="POST" class="form-reset-password" id="resetPasswordForm">
                            <h2>OpenDoorsClass</h2>
                            <h2>Réinitialiser votre mot de passe</h2>
                            <p>Entrez votre nouveau mot de passe pour réinitialiser votre compte.</p>

                            <div class="error-message" data-for="general"></div>

                            <label for="new-password">Nouveau mot de passe</label>
                            <input type="password" id="new-password" name="new_password" placeholder="Nouveau mot de passe" required>
                            <div class="error-message" data-for="new-password"></div>

                            <label for="confirm-password">Confirmer le mot de passe</label>
                            <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
                            <div class="error-message" data-for="confirm-password"></div>

                            <div class="form-bottom-line">
                                <a href="/login" class="forgot-password-link">Retour à la connexion</a>
                            </div>

                            <button type="submit" id="btn-submit" class="form-submit">
                                <span class="btn-text">Réinitialiser</span>
                                <span class="spinner"></span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Colonne droite : image -->
                <div class="login-right"></div>
            </div>
        </section>
    </main>

    <div id="toast-container"></div>

    <script src="z/js/main.js"></script>
    <script src="/js/reset-password.js"></script>
</body>

</html>