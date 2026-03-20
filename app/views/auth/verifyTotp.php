<?php

if (empty($_SESSION['totp_pending_user_id'])) {
  header('Location: ./login');
  exit();
}
?>
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
  <link rel="stylesheet" href="../../public/css/auth/verify2fa.min.css">
  <title>OpenDoorsClass — Google Authenticator</title>
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

    <div class="verify-icon">
      <i class="fas fa-mobile-screen"></i>
    </div>

    <h1>Vérification Google Authenticator</h1>

    <p class="verify-desc">
      Ouvrez votre application <strong>Google Authenticator</strong> et
      saisissez le code à 6 chiffres pour continuer votre formation en anglais.
    </p>

    <!-- Indicateur de validité 30 secondes -->
    <div class="totp-timer-wrapper" id="totp-timer-wrapper">
      <svg class="totp-timer-ring" viewBox="0 0 36 36" width="48" height="48">
        <circle class="totp-timer-ring__bg" cx="18" cy="18" r="15.9" />
        <circle class="totp-timer-ring__fill" id="totp-ring-fill" cx="18" cy="18" r="15.9" />
      </svg>
      <span class="totp-timer-count" id="totp-timer-count">30</span>
    </div>
    <p class="totp-timer-label" id="totp-timer-label">Code valide pendant <strong id="totp-seconds">30</strong>s</p>

    <form id="verifyTotpForm" action="" method="POST" novalidate>

      <!-- Cases OTP -->
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

    <div class="verify-divider">ou</div>

    <a href="../login" class="resend-link">
      <i class="fas fa-arrow-left"></i>
      Retour à la connexion
    </a>

  </div>

  <footer class="verify-footer">
    <p>&copy; <?= date('Y') ?> OpenDoorsClass. Tous droits réservés.</p>
  </footer>

  <script src="../../public/js/auth/verifyTotp.min.js" defer></script>

</body>

</html>