<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../public/css/auth/forgotPassword.min.css">

    <title>Mot de passe oublié - OpenDoorsClass</title>
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
                    <form action="" method="POST" id="forgotPasswordForm" novalidate>

                        <div class="error-message" data-for="general"></div>

                        <div class="input-group">
                            <label for="email">Adresse e-mail</label>
                            <input
                                type="email"
                                id="email"
                                name="find-email"
                                placeholder="exemple@domaine.com"
                                autocomplete="email"
                                required>
                            <div class="error-message" data-for="email"></div>
                        </div>

                        <button type="submit" id="btn-submit" class="btn-submit">
                            <span class="btn-text">Retrouver mon compte</span>
                            <span class="spinner"></span>
                        </button>

                        <div class="back-link">
                            <a href="./login">Retour à la connexion</a>
                        </div>

                    </form>
                </div>
            </div>
        </section>
    </main>

    <div id="toast-container"></div>

    <script src="../public/js/header.min.js" defer></script>
    <script src="../public/js/forgotPassword.min.js" defer></script>
</body>

</html>