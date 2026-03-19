// ===== OTP =====
const otpInputs = document.querySelectorAll(".otp-input");
const hiddenInput = document.getElementById("confirmation_code");
const submitBtn = document.getElementById("btn-confirm");
const errorCode = document.getElementById("error-code");

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

// ===== HELPERS =====
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

function showError(message) {
  errorCode.textContent = message;
  errorCode.style.opacity = "1";

  otpInputs.forEach((inp) => {
    if (!inp.disabled) inp.classList.add("is-error");
  });

  setTimeout(() => {
    otpInputs.forEach((inp) => inp.classList.remove("is-error"));
  }, 600);
}

function clearError() {
  errorCode.textContent = ""; // vide le texte mais garde le min-height
  errorCode.style.opacity = "0";
  otpInputs.forEach((inp) => inp.classList.remove("is-error"));
}

function clearError() {
  errorCode.textContent = "";
  otpInputs.forEach((inp) => inp.classList.remove("is-error"));
}

function resetAllInputs() {
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
    clearError();
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
    clearError();
  });

  input.addEventListener("focus", () => {
    if (!input.disabled) input.select();
  });
});

// ===== SOUMISSION =====
document
  .getElementById("confirmForm")
  ?.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!isComplete()) return;

    const btnText = submitBtn.querySelector(".btn-text");
    const spinner = submitBtn.querySelector(".btn-spinner");
    const msgBox = document.getElementById("message");

    // Reset
    clearError();
    if (msgBox) {
      msgBox.textContent = "";
      msgBox.style.display = "none";
    }

    // Loading
    submitBtn.disabled = true;
    btnText.style.display = "none";
    spinner.style.display = "flex";

    try {
      const res = await fetch("./confirm", {
        method: "POST",
        body: new FormData(e.target),
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      const data = await res.json();

      if (data.success) {
        submitBtn.style.background =
          "linear-gradient(135deg, #00c48c, #00e6a8)";
        btnText.innerHTML =
          '<i class="fas fa-check" style="margin-right:6px;"></i> Compte confirmé !';
        spinner.style.display = "none";
        // Ne pas changer display de btnText — il reste dans son état naturel

        setTimeout(() => {
          window.location.href = "./";
        }, 1200);
      } else {
        showError(data.message ?? "Code incorrect. Veuillez réessayer.");
        resetAllInputs();

        submitBtn.disabled = false;
        btnText.style.display = "flex";
        spinner.style.display = "none";
        updateBtn();
      }
    } catch {
      showError("Erreur réseau. Veuillez réessayer.");
      submitBtn.disabled = false;
      btnText.style.display = "flex";
      spinner.style.display = "none";
      updateBtn();
    }
  });

// ===== RENVOI CODE =====
document.getElementById("resend-link")?.addEventListener("click", async (e) => {
  e.preventDefault();

  const resendLink = e.currentTarget;
  const resendMsg = document.getElementById("resend-message");
  const email = resendLink.dataset.email;

  // Loading state
  resendLink.style.pointerEvents = "none";
  resendLink.innerHTML = `
        <i class="fas fa-spinner fa-spin" style="font-size:12px;"></i>
        Envoi en cours...
    `;
  resendLink.style.opacity = "0.7";

  if (resendMsg) resendMsg.style.display = "none";

  try {
    const res = await fetch(
      `./resend-code?email=${encodeURIComponent(email)}`,
      {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      },
    );
    const data = await res.json();

    if (data.success) {
      // Feedback succès sur le lien
      resendLink.innerHTML = `<i class="fas fa-circle-check" style="font-size:12px;"></i> Code envoyé !`;
      resendLink.style.color = "#00c48c";
      resendLink.style.opacity = "1";

      // Affiche le message
      if (resendMsg) {
        resendMsg.style.display = "flex";
        resendMsg.style.opacity = "0";
        resendMsg.style.transition = "opacity 0.3s ease";
        requestAnimationFrame(() => {
          resendMsg.style.opacity = "1";
        });
      }

      // Réinitialise les cases sans toucher aux tentatives
      resetAllInputs();
      clearError();

      // Remet le lien à son état initial après 4s
      setTimeout(() => {
        if (resendMsg) {
          resendMsg.style.opacity = "0";
          setTimeout(() => {
            resendMsg.style.display = "none";
          }, 300);
        }
        resendLink.innerHTML = `<i class="fas fa-rotate-right" style="margin-right:6px;font-size:12px;"></i> Renvoyer un nouveau code`;
        resendLink.style.color = "";
        resendLink.style.opacity = "1";
        resendLink.style.pointerEvents = "auto";
      }, 4000);
    } else {
      resendLink.innerHTML = `<i class="fas fa-rotate-right" style="margin-right:6px;font-size:12px;"></i> Renvoyer un nouveau code`;
      resendLink.style.opacity = "1";
      resendLink.style.pointerEvents = "auto";
      showError(data.message ?? "Erreur lors du renvoi du code.");
    }
  } catch {
    resendLink.innerHTML = `<i class="fas fa-rotate-right" style="margin-right:6px;font-size:12px;"></i> Renvoyer un nouveau code`;
    resendLink.style.opacity = "1";
    resendLink.style.pointerEvents = "auto";
    showError("Erreur réseau. Veuillez réessayer.");
  }
});
