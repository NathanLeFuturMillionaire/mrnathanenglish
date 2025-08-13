<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href="../public/css/style.css"> -->
    <link rel="stylesheet" href="../public/css/auth/confirm.css">
    <link rel="stylesheet" href="../public/css/style.css">
    <title>Vérification - Mr Nathan English</title>
</head>

<body>
    <header>
        <h1>Mr Nathan English</h1>
    </header>
    <div class="confirmation-container">
        <h1>Confirmez votre compte</h1>

        <p class="description">
            Un code de confirmation a été envoyé à
            <strong class="recipient-email"><?= htmlspecialchars($email ?? '') ?></strong>.
            Saisissez le code à 6 chiffres ci-dessous pour activer votre compte.
        </p>

        <form id="confirmForm" action="./confirm" method="POST" novalidate>
            <!-- Email transporté en POST -->
            <input type="hidden" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>">

            <label for="code">Code de confirmation</label>
            <input
                type="text"
                id="code"
                name="code"
                placeholder="6 chiffres"
                inputmode="numeric"
                autocomplete="one-time-code"
                pattern="\d{6}"
                maxlength="6"
                required />
            <small id="error-code" class="error" aria-live="polite"></small>

            <div id="message" class="form-message" aria-live="polite"></div>

            <button type="submit" class="btn-confirm">
                <span class="btn-text">Confirmer</span>
            </button>
        </form>

        <p class="resend-wrapper">
            <!-- Lien fonctionnel même sans JS (GET) + data-email pour JS -->
            <a
                id="resend-link"
                class="resend-link"
                href="./resend-code?email=<?= urlencode($email ?? '') ?>"
                data-email="<?= htmlspecialchars($email ?? '') ?>">
                Renvoyer un nouveau code
            </a>
            <span id="resend-message" class="resend-message" hidden>
                Un nouveau code vient d’être envoyé.
            </span>
        </p>

        <noscript>
            <p class="noscript-hint">
                JavaScript est désactivé : le lien ci-dessus rechargera la page pour renvoyer le code.
            </p>
        </noscript>
    </div>


    <script src="js/main.js"></script>
    <script src="js/confirm.js"></script>

    <!-- Footer -->
    <footer class="confirmation-footer">
        <p>&copy; <?= date('Y') ?> Mr Nathan English. Tous droits réservés.</p>
    </footer>
</body>

</html>