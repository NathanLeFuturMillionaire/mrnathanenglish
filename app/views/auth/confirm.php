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
        <p class="description">Un code de confirmation vous a été envoyé par e-mail. Veuillez saisir ce code ci-dessous pour activer votre compte.</p>

        <form id="confirmForm" action="./confirm" method="POST">
            <label for="code">Code de confirmation :</label>
            <!-- Email transporté en POST -->
            <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '') ?>">

            <input type="text" id="code" name="code" placeholder="Entrez votre code" maxlength="6" />
            <small id="error-code" class="error"></small>
            <button type="submit" class="btn-confirm">Confirmer</button>
        </form>

        <div id="message"></div>

        <a href="/resend-code" class="resend-link">Renvoyer un nouveau code</a>
    </div>

    <script src="js/main.js"></script>
    <script src="js/confirm.js"></script>
</body>

</html>