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
  <link rel="stylesheet" href="../public/css/auth/verify2fa.min.css">
  <title>OpenDoorsClass — Vérification</title>
</head>

<body>

  <div class="bg-letters" aria-hidden="true">
    <span>O</span><span>P</span><span>E</span><span>N</span>
    <span>D</span><span>O</span><span>O</span><span>R</span>
    <span>S</span><span>C</span><span>L</span><span>A</span><span>S</span>
  </div>

  <header>
    <h1>OpenDoorsClass</h1>
  </header>

  <div class="verify-container">

    <!-- ===== ICÔNE + TITRE (toujours visibles) ===== -->
    <div class="verify-icon" id="verify-icon">
      <i class="fas fa-shield-halved"></i>
    </div>

    <h1 id="verify-title">Vérification en deux étapes</h1>

    <p class="verify-desc" id="verify-desc">
      Un code de vérification a été envoyé à votre adresse e-mail.
      Saisissez-le ci-dessous pour continuer votre formation d'anglais sur OpenDoorsClass.
    </p>

    <!-- ===== FORMULAIRE ===== -->
    <form id="verify2faForm" action="./auth/verify-2fa" method="POST" novalidate>

      <div class="otp-wrapper">
        <input class="otp-input" type="text" inputmode="numeric" maxlength="1" pattern="\d" autocomplete="off" data-index="0">
        <input class="otp-input" type="text" inputmode="numeric" maxlength="1" pattern="\d" autocomplete="off" data-index="1">
        <input class="otp-input" type="text" inputmode="numeric" maxlength="1" pattern="\d" autocomplete="off" data-index="2">
        <span class="otp-separator">—</span>
        <input class="otp-input" type="text" inputmode="numeric" maxlength="1" pattern="\d" autocomplete="off" data-index="3">
        <input class="otp-input" type="text" inputmode="numeric" maxlength="1" pattern="\d" autocomplete="off" data-index="4">
        <input class="otp-input" type="text" inputmode="numeric" maxlength="1" pattern="\d" autocomplete="off" data-index="5">
      </div>

      <input type="hidden" id="otp_code" name="otp_code">

      <small id="otp-error" style="color:#f5365c;font-size:0.82rem;min-height:18px;display:block;text-align:center;margin-bottom:8px;"></small>
      <!-- Tentatives restantes -->
      <div id="attempts-info" class="attempts-info" style="display:none;">
        <i class="fas fa-shield-exclamation"></i>
        Vous avez <strong id="attempts-count"></strong> tentative(s).
      </div>
      <div id="lock-warning" class="lock-warning" style="display:none;">
        <i class="fas fa-triangle-exclamation"></i>
        <span>Attention - OpenDoorsClass bloquera votre compte temporairement si vous ratez toutes les tentatives restantes.</span>
      </div>

      <!-- Navigateur de confiance -->
      <label class="trust-device">
        <input type="checkbox" id="trust-device" name="trust_device" value="1">
        <span class="trust-device__checkmark">
          <i class="fas fa-check"></i>
        </span>
        <div class="trust-device__text">
          <span class="trust-device__label">Se souvenir de ce navigateur</span>
          <span class="trust-device__hint">Ne plus demander de code pendant 30 jours sur cet appareil</span>
        </div>
      </label>

      <button type="submit" class="btn-verify" id="btn-verify" disabled>
        <span class="btn-text">
          <i class="fas fa-arrow-right-to-bracket"></i>
          Accéder à mon compte
        </span>
        <span class="btn-spinner" style="display:none;">
          <i class="fas fa-spinner fa-spin"></i>
        </span>
      </button>

    </form>

    <!-- ===== LOCK SCREEN (remplace le form) ===== -->
    <div id="lock-screen" class="lock-screen" style="display:none;">
      <div class="lock-screen__icon">
        <i class="fas fa-lock"></i>
      </div>
      <h2 class="lock-screen__title">Compte temporairement bloqué</h2>
      <p class="lock-screen__desc">
        Vous avez effectué trop de tentatives incorrectes.<br>
        Vous pourrez réessayer dans :
      </p>
      <div class="lock-screen__timer" id="lock-timer">10:00</div>
      <p class="lock-screen__hint">
        <!-- <i class="fas fa-envelope"></i> -->
        Un nouveau code vous sera automatiquement envoyé à l'expiration du délai.
      </p>
    </div>

    <!-- ===== RENVOI CODE (sous le form ET le lock screen) ===== -->
    <div class="verify-divider" id="verify-divider">ou</div>

    <a href="#" class="resend-link" id="resend-2fa">
      <i class="fas fa-rotate-right"></i>
      Renvoyer un nouveau code
    </a>
    <!-- <span id="resend-message">
      <i class="fas fa-circle-check" style="margin-right:5px;"></i>
      Un nouveau code vient d'être envoyé.
    </span> -->

  </div>

  <footer class="verify-footer">
    <p>&copy; <?= date('Y') ?> OpenDoorsClass. Tous droits réservés.</p>
  </footer>

  <script src="js/auth/verify2fa.min.js" defer></script>

</body>

</html>