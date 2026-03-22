document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("registerForm");
  const btn = document.getElementById("btn-submit");

  const fields = [
    {
      id: "fullname",
      min: 3,
      message: "Le nom complet doit contenir au moins 3 caractères.",
    },
    { id: "email", type: "email", message: "Adresse e-mail invalide." },
    {
      id: "username",
      min: 3,
      message: "Le nom d'utilisateur doit contenir au moins 3 caractères.",
    },
    {
      id: "password",
      min: 8,
      message: "Le mot de passe doit contenir au moins 8 caractères.",
    },
    {
      id: "confirm_password",
      match: "password",
      message: "Les mots de passe ne correspondent pas.",
    },
  ];

  // ===== SHOW / CLEAR ERROR =====
  function showError(id, message) {
    const input = document.getElementById(id);
    const err = document.getElementById("error-" + id);
    if (err) {
      err.textContent = message;
      err.style.opacity = "1";
    }
    if (input) {
      input.classList.add("shake");
      setTimeout(() => input.classList.remove("shake"), 500);
    }
  }

  function clearError(id) {
    const err = document.getElementById("error-" + id);
    if (err) {
      err.textContent = "";
      err.style.opacity = "0";
    }
    document.getElementById(id)?.classList.remove("shake");
  }

  function clearAllErrors() {
    fields.forEach((f) => clearError(f.id));
    clearError("terms");
    const general = document.getElementById("error-general");
    if (general) {
      general.textContent = "";
      general.style.display = "none";
    }
  }

  // Efface l'erreur à la saisie
  fields.forEach(({ id }) => {
    document
      .getElementById(id)
      ?.addEventListener("input", () => clearError(id));
  });

  document
    .querySelector('input[name="terms"]')
    ?.addEventListener("change", () => clearError("terms"));

  // ===== VALIDATION FRONT =====
  function validateFront() {
    let valid = true;

    fields.forEach((field) => {
      const input = document.getElementById(field.id);
      if (!input) return;
      const value = input.value.trim();

      if (field.min && value.length < field.min) {
        showError(field.id, field.message);
        valid = false;
      }

      if (field.type === "email" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        showError(field.id, field.message);
        valid = false;
      }

      if (field.match) {
        const matchValue = document.getElementById(field.match)?.value ?? "";
        if (value !== matchValue) {
          showError(field.id, field.message);
          valid = false;
        }
      }
    });

    // Terms
    const terms = document.querySelector('input[name="terms"]');
    if (terms && !terms.checked) {
      showError("terms", "Vous devez accepter les conditions d'utilisation.");
      valid = false;
    }

    return valid;
  }

  // ===== SOUMISSION AJAX =====
  form.addEventListener("submit", async function (e) {
    e.preventDefault();
    clearAllErrors();

    if (!validateFront()) {
      return;
    }

    // Loading state
    const btnText = btn.querySelector(".btn-text");
    const spinner = btn.querySelector(".spinner");
    btn.disabled = true;
    btn.classList.add("loading");
    if (btnText) btnText.style.display = "none";
    if (spinner) spinner.style.display = "inline-block";

    try {
      const res = await fetch(form.action || window.location.href, {
        method: "POST",
        body: new URLSearchParams(new FormData(form)),
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const data = await res.json();

      if (data.success) {
        // Redirection vers la page de confirmation
        window.location.href = data.redirect;
        return;
      }

      // Affiche les erreurs retournées par le serveur
      if (data.errors) {
        Object.entries(data.errors).forEach(([field, message]) => {
          if (field === "general") {
            // Erreur générale
            let general = document.getElementById("error-general");
            if (!general) {
              general = document.createElement("small");
              general.id = "error-general";
              general.className = "error";
              general.style.display = "block";
              general.style.marginBottom = "10px";
              form.prepend(general);
            }
            general.textContent = message;
            general.style.display = "block";
          } else {
            showError(field, message);
          }
        });
      }
    } catch {
      showError("fullname", "Erreur réseau. Veuillez réessayer.");
    } finally {
      btn.disabled = false;
      btn.classList.remove("loading");
      if (btnText) btnText.style.display = "inline";
      if (spinner) spinner.style.display = "none";
    }
  });
});
