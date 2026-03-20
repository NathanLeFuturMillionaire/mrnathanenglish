// ===== ÉLÉMENTS =====
const otpInputs = document.querySelectorAll(".otp-input");
const hiddenInput = document.getElementById("otp_code");
const submitBtn = document.getElementById("btn-verify");
const otpError = document.getElementById("otp-error");
const attemptsInfo = document.getElementById("attempts-info");
const attemptsCount = document.getElementById("attempts-count");
const form = document.getElementById("verify2faForm");
const lockScreen = document.getElementById("lock-screen");
const lockTimer = document.getElementById("lock-timer");
const resendLink = document.getElementById("resend-2fa");
const resendMsg = document.getElementById("resend-message");
const verifyDivider = document.getElementById("verify-divider");

let attemptsLeft = 5;
let countdownInterval = null;

// ===== INITIALISATION =====
submitBtn.disabled = true;
submitBtn.style.opacity = "0.5";
submitBtn.style.cursor = "not-allowed";

otpInputs.forEach((input, index) => {
  if (index !== 0) {
    input.setAttribute("disabled", true);
    input.style.opacity = "0.4";
    input.style.cursor = "not-allowed";
  }
});

// ===== HELPERS OTP =====
function enableInput(index) {
  const input = otpInputs[index];
  input.removeAttribute("disabled");
  input.style.opacity = "1";
  input.style.cursor = "text";
  input.focus();
}

function disableInput(index) {
  const input = otpInputs[index];
  input.setAttribute("disabled", true);
  input.value = "";
  input.style.opacity = "0.4";
  input.style.cursor = "not-allowed";
  input.classList.remove("is-filled", "is-error", "is-valid");
}

function syncHidden() {
  hiddenInput.value = [...otpInputs].map((i) => i.value).join("");
}

function isComplete() {
  return [...otpInputs].every((i) => i.value !== "");
}

function updateBtn() {
  const complete = isComplete();
  submitBtn.disabled = !complete;
  submitBtn.style.opacity = complete ? "1" : "0.5";
  submitBtn.style.cursor = complete ? "pointer" : "not-allowed";
}

function clearOtpError() {
  otpError.textContent = "";
  otpInputs.forEach((inp) => inp.classList.remove("is-error"));
}

function resetOtpInputs() {
  otpInputs.forEach((inp, i) => {
    inp.value = "";
    inp.classList.remove("is-filled", "is-error", "is-valid");
    if (i !== 0) disableInput(i);
    else {
      inp.removeAttribute("disabled");
      inp.style.opacity = "1";
      inp.style.cursor = "text";
    }
  });
  syncHidden();
  updateBtn();
  otpInputs[0].focus();
}

// ===== TENTATIVES =====
function updateAttemptsDisplay() {
  if (!attemptsCount || !attemptsInfo) return;

  attemptsCount.textContent = attemptsLeft;
  attemptsInfo.style.display = "flex";
  attemptsInfo.className = "attempts-info";

  if (attemptsLeft <= 1) attemptsInfo.classList.add("is-danger");
  else if (attemptsLeft <= 3) attemptsInfo.classList.add("is-warning");

  // Animation sur le chiffre
  attemptsCount.style.transition = "transform 0.2s ease";
  attemptsCount.style.transform = "scale(1.4)";
  setTimeout(() => {
    attemptsCount.style.transform = "scale(1)";
  }, 200);

  // ===== MESSAGE D'AVERTISSEMENT À 3 TENTATIVES =====
  const warningMsg = document.getElementById("lock-warning");
  if (warningMsg) {
    if (attemptsLeft <= 3) {
      warningMsg.style.display = "flex";
      warningMsg.style.opacity = "0";
      warningMsg.style.transform = "translateY(-4px)";
      // Transition d'apparition
      requestAnimationFrame(() => {
        warningMsg.style.transition = "opacity 0.3s ease, transform 0.3s ease";
        warningMsg.style.opacity = "1";
        warningMsg.style.transform = "translateY(0)";
      });
    } else {
      warningMsg.style.display = "none";
    }
  }
}
// ===== LOCK SCREEN =====
function showLockScreen(secondsLeft) {
  if (form) form.style.display = "none";
  if (attemptsInfo) attemptsInfo.style.display = "none";
  if (lockScreen) lockScreen.style.display = "block";
  clearOtpError();
  startCountdown(secondsLeft);
}

function hideLockScreen() {
  if (form) form.style.display = "block";
  if (attemptsInfo) attemptsInfo.style.display = "none";
  if (lockScreen) lockScreen.style.display = "none";
  if (lockTimer) lockTimer.classList.remove("is-ending");

  const warningMsg = document.getElementById("lock-warning");
  if (warningMsg) warningMsg.style.display = "none"; // ← ajout

  attemptsLeft = 5;
  resetOtpInputs();
}

function startCountdown(seconds) {
  clearInterval(countdownInterval);
  let remaining = seconds;

  function tick() {
    const min = Math.floor(remaining / 60);
    const sec = remaining % 60;
    lockTimer.textContent = `${String(min).padStart(2, "0")}:${String(sec).padStart(2, "0")}`;

    if (remaining <= 60) lockTimer.classList.add("is-ending");

    if (remaining <= 0) {
      clearInterval(countdownInterval);
      hideLockScreen();
    }

    remaining--;
  }

  tick();
  countdownInterval = setInterval(tick, 1000);
}

// ===== ÉCOUTEURS OTP =====
otpInputs.forEach((input, index) => {
  input.addEventListener("input", (e) => {
    const val = e.target.value.replace(/\D/g, "");
    input.value = val ? val[val.length - 1] : "";

    if (input.value) {
      input.classList.add("is-filled");
      if (index < otpInputs.length - 1) enableInput(index + 1);
    } else {
      input.classList.remove("is-filled");
    }

    syncHidden();
    updateBtn();
    clearOtpError();
  });

  input.addEventListener("keydown", (e) => {
    if (e.key === "Backspace") {
      if (input.value) {
        input.value = "";
        input.classList.remove("is-filled");
        syncHidden();
        updateBtn();
      } else if (index > 0) {
        disableInput(index);
        enableInput(index - 1);
        otpInputs[index - 1].value = "";
        otpInputs[index - 1].classList.remove("is-filled");
        syncHidden();
        updateBtn();
      }
    }
  });

  input.addEventListener("paste", (e) => {
    e.preventDefault();
    const pasted = e.clipboardData
      .getData("text")
      .replace(/\D/g, "")
      .slice(0, 6);
    if (!pasted) return;

    otpInputs.forEach((inp, i) => {
      if (i !== 0) disableInput(i);
      inp.value = "";
      inp.classList.remove("is-filled");
    });

    [...pasted].forEach((char, i) => {
      if (i < otpInputs.length) {
        if (i !== 0) enableInput(i);
        otpInputs[i].value = char;
        otpInputs[i].classList.add("is-filled");
      }
    });

    if (pasted.length < otpInputs.length) {
      enableInput(pasted.length);
    } else {
      otpInputs[otpInputs.length - 1].focus();
    }

    syncHidden();
    updateBtn();
    clearOtpError();
  });

  input.addEventListener("focus", () => {
    if (!input.disabled) input.select();
  });
});

// ===== SOUMISSION =====
form?.addEventListener("submit", async (e) => {
  e.preventDefault();
  if (!isComplete()) return;

  const btnText = submitBtn.querySelector(".btn-text");
  const spinner = submitBtn.querySelector(".btn-spinner");
  const trustDevice = document.getElementById("trust-device").checked;

  // Loading
  submitBtn.disabled = true;
  btnText.style.display = "none";
  spinner.style.display = "flex";
  clearOtpError();

  try {
    const res = await fetch("./auth/verify-2fa", {
      method: "POST",
      body: new URLSearchParams({
        otp_code: hiddenInput.value,
        trust_device: trustDevice ? "1" : "0",
      }),
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });

    const data = await res.json();

    // ===== SUCCÈS =====
    if (data.success) {
      // Double auth — enchaîne vers TOTP
      if (data.requires_totp) {
        window.location.href = "./auth/verify-totp";
        return;
      }

      // Connexion réussie normale
      submitBtn.style.background = "linear-gradient(135deg, #00c48c, #00e6a8)";
      btnText.innerHTML = '<i class="fas fa-check"></i> Connexion réussie !';
      btnText.style.display = "flex";
      spinner.style.display = "none";
      setTimeout(() => {
        window.location.href = data.redirect ?? "../";
      }, 800);
      return;
    }

    // ===== COMPTE VERROUILLÉ =====
    if (data.locked) {
      showLockScreen(data.seconds_left ?? 600);
      btnText.style.display = "flex";
      spinner.style.display = "none";
      return;
    }

    // ===== CODE INCORRECT =====
    attemptsLeft = data.attempts_left ?? Math.max(attemptsLeft - 1, 0);
    updateAttemptsDisplay();

    otpError.textContent = data.message ?? "Code incorrect.";
    otpInputs.forEach((inp) => {
      if (!inp.disabled) inp.classList.add("is-error");
    });
    setTimeout(() => {
      otpInputs.forEach((inp) => inp.classList.remove("is-error"));
    }, 600);

    // Réinitialise les cases pour ressaisir
    resetOtpInputs();

    submitBtn.disabled = false;
    btnText.style.display = "flex";
    spinner.style.display = "none";
    updateBtn();
  } catch {
    otpError.textContent = "Erreur réseau. Veuillez réessayer.";
    submitBtn.disabled = false;
    btnText.style.display = "flex";
    spinner.style.display = "none";
    updateBtn();
  }
});

// ===== RENVOI CODE =====
resendLink?.addEventListener("click", async (e) => {
  e.preventDefault();

  // ===== LOADING STATE =====
  resendLink.style.pointerEvents = "none";
  resendLink.innerHTML = `
        <i class="fas fa-spinner fa-spin" style="font-size:12px;"></i>
        Envoi en cours...
    `;
  resendLink.style.opacity = "0.7";

  if (resendMsg) resendMsg.style.display = "none";

  try {
    const res = await fetch("./auth/resend-2fa", {
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });
    const data = await res.json();

    if (data.success) {
      // Feedback succès sur le bouton
      resendLink.innerHTML = `
                <i class="fas fa-circle-check" style="font-size:12px;"></i>
                Code envoyé !
            `;
      resendLink.style.color = "#00c48c";
      resendLink.style.opacity = "1";

      // Affiche le message
      // if (resendMsg) {
      //   resendMsg.style.display = "flex";
      //   resendMsg.style.opacity = "0";
      //   resendMsg.style.transition = "opacity 0.3s ease";
      //   requestAnimationFrame(() => {
      //     resendMsg.style.opacity = "1";
      //   });
      // }

      resetOtpInputs();
      clearOtpError();

      // Remet le lien à son état initial après 4s
      setTimeout(() => {
        if (resendMsg) {
          resendMsg.style.opacity = "0";
          setTimeout(() => {
            resendMsg.style.display = "none";
          }, 300);
        }

        resendLink.innerHTML = `<i class="fas fa-rotate-right"></i> Renvoyer un nouveau code`;
        resendLink.style.color = "";
        resendLink.style.opacity = "1";
        resendLink.style.pointerEvents = "auto";
      }, 4000);
    } else {
      // Erreur — remet le lien
      resendLink.innerHTML = `<i class="fas fa-rotate-right"></i> Renvoyer un nouveau code`;
      resendLink.style.opacity = "1";
      resendLink.style.pointerEvents = "auto";
    }
  } catch {
    resendLink.innerHTML = `<i class="fas fa-rotate-right"></i> Renvoyer un nouveau code`;
    resendLink.style.opacity = "1";
    resendLink.style.pointerEvents = "auto";
  }
});

// ===== VÉRIFICATION VERROU AU CHARGEMENT =====
document.addEventListener("DOMContentLoaded", () => {
  updateAttemptsDisplay();

  fetch("./auth/check-2fa-lock", {
    headers: { "X-Requested-With": "XMLHttpRequest" },
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.locked && data.seconds_left > 0) {
        showLockScreen(data.seconds_left);
      }
    })
    .catch(() => {});
});
