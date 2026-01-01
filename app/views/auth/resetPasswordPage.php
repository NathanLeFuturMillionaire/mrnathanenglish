<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe - OpenDoorsClass</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/auth/resetPasswordPage.css">
</head>
<body>

    <?php require_once '../app/views/layouts/header.php'; ?>

    <main>
        <section class="reset-password-page">
            <div class="reset-password-container">

                <!-- Colonne gauche -->
                <div class="reset-left">
                    <a href="./forgot-password" class="back-arrow">←</a>

                    <?php if (!empty($error)): ?>
                        <!-- Token invalide ou expiré -->
                        <div class="error-block">
                            <h2>OpenDoorsClass</h2>
                            <h3>Token invalide ou expiré</h3>
                            <p><?= htmlspecialchars($error) ?></p>
                            <a href="./forgot-password" class="btn-primary">Demander un nouveau lien</a>
                        </div>

                    <?php else: ?>
                        <!-- Formulaire de réinitialisation -->
                        <div class="reset-form-wrapper">
                            <h2>OpenDoorsClass</h2>
                            <h3>Réinitialiser votre mot de passe</h3>
                            <p>Choisissez un nouveau mot de passe sécurisé.</p>

                            <!-- Message d'erreur général -->
                            <div class="error-message general-error" data-for="general"></div>

                            <form id="resetPasswordForm" method="post">
                                <!-- Token caché (récupéré par JS depuis l'URL) -->
                                <input type="hidden" name="token" id="token-input" value="<?= htmlspecialchars($token ?? '') ?>">

                                <div class="input-group">
                                    <label for="new-password">Nouveau mot de passe</label>
                                    <input 
                                        type="password" 
                                        id="new-password" 
                                        name="new-password" 
                                        placeholder="Au moins 8 caractères"
                                    >
                                    <div class="error-message" data-for="new-password"></div>
                                </div>

                                <div class="input-group">
                                    <label for="confirm-password">Confirmer le mot de passe</label>
                                    <input 
                                        type="password" 
                                        id="confirm-password" 
                                        name="confirm-password" 
                                        placeholder="Répétez le mot de passe"
                                    >
                                    <div class="error-message" data-for="confirm-password"></div>
                                </div>

                                <button type="submit" id="btn-submit" class="btn-submit">
                                    <span class="btn-text">Réinitialiser mon mot de passe</span>
                                    <span class="spinner"></span>
                                </button>
                            </form>

                            <p class="login-link">
                                <a href="./login">← Retour à la connexion</a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Colonne droite (image décorative) -->
                <div class="reset-right"></div>
            </div>
        </section>
    </main>

    <!-- Toast container -->
    <div id="toast-container"></div>

    <script src="./js/main.js"></script>
    <script src="./js/resetPassword.js"></script>
</body>
</html>