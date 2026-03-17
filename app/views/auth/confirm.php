<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/auth/confirm.min.css">

    <title>OpenDoorsClass - Vérification</title>
</head>

<body>

    <!-- ===== LETTRES DÉFILANTES ===== -->
    <div class="bg-letters" aria-hidden="true">
        <span>O</span>
        <span>P</span>
        <span>E</span>
        <span>N</span>
        <span>D</span>
        <span>O</span>
        <span>O</span>
        <span>R</span>
        <span>S</span>
        <span>C</span>
        <span>L</span>
        <span>A</span>
        <span>S</span>
    </div>

    <!-- ===== HEADER ===== -->
    <header>
        <h1>OpenDoorsClass</h1>
    </header>

    <!-- ===== CONTENEUR ===== -->
    <div class="confirmation-container">

        <div class="confirmation-icon">
            <i class="fas fa-envelope-open-text"></i>
        </div>

        <h1>Confirmez votre compte</h1>

        <p class="description">
            Un code de confirmation a été envoyé à
            <strong class="recipient-email"><?= htmlspecialchars($email ?? '') ?></strong>.
            Saisissez le code à 6 chiffres ci-dessous pour activer votre compte.
        </p>

        <form id="confirmForm" action="./confirm" method="POST" novalidate>
            <input type="hidden" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>">

            <label for="confirmation_code">Code de confirmation</label>

            <div class="otp-wrapper">
                <input class="otp-input" type="text" inputmode="numeric" maxlength="1" pattern="\d" autocomplete="off" data-index="0">
                <input class="otp-input" type="text" inputmode="numeric" maxlength="1" pattern="\d" autocomplete="off" data-index="1">
                <input class="otp-input" type="text" inputmode="numeric" maxlength="1" pattern="\d" autocomplete="off" data-index="2">
                <span class="otp-separator">—</span>
                <input class="otp-input" type="text" inputmode="numeric" maxlength="1" pattern="\d" autocomplete="off" data-index="3">
                <input class="otp-input" type="text" inputmode="numeric" maxlength="1" pattern="\d" autocomplete="off" data-index="4">
                <input class="otp-input" type="text" inputmode="numeric" maxlength="1" pattern="\d" autocomplete="off" data-index="5">
            </div>

            <!-- Input caché qui regroupe les 6 chiffres pour le POST -->
            <input type="hidden" id="confirmation_code" name="confirmation_code">

            <small id="error-code" aria-live="polite"></small>
            <div id="message" class="form-message" aria-live="polite"></div>

            <button type="submit" class="btn-confirm">
                <span class="btn-text">Confirmer mon compte</span>
            </button>
        </form>

        <div class="divider">ou</div>

        <div class="resend-wrapper">
            <a
                id="resend-link"
                class="resend-link"
                href="./resend-code?email=<?= urlencode($email ?? '') ?>"
                data-email="<?= htmlspecialchars($email ?? '') ?>">
                <i class="fas fa-rotate-right" style="margin-right:6px;font-size:12px;"></i>
                Renvoyer un nouveau code
            </a>
            <span id="resend-message" class="resend-message" hidden>
                Un nouveau code vient d'être envoyé.
            </span>
        </div>

        <noscript>
            <p class="noscript-hint" style="font-size:0.8rem;color:#999;margin-top:12px;">
                JavaScript est désactivé — le lien ci-dessus rechargera la page pour renvoyer le code.
            </p>
        </noscript>

    </div>

    <!-- ===== FOOTER ===== -->
    <footer class="confirmation-footer">
        <p>&copy; <?= date('Y') ?> OpenDoorsClass. Tous droits réservés.</p>
    </footer>

    <script src="js/confirm.min.js" defer></script>

</body>

</html>