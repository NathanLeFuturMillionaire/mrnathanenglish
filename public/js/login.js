document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("loginForm");
  const btn = document.getElementById("btn-submit");

  const fields = [
    { id: "email", type: "email", message: "Adresse e-mail invalide." },
    {
      id: "password",
      min: 8,
      message: "Le mot de passe doit contenir au moins 8 caractères.",
    },
  ];

  function showError(id, message) {
    const input = document.getElementById(id);
    const errorElement = input
      ? input
          .closest(".form-login")
          ?.querySelector(`.error-message[data-for="${id}"]`)
      : document.querySelector(`.error-message[data-for="${id}"]`);

    if (errorElement) {
      errorElement.textContent = message;
      errorElement.style.opacity = "1";
    }

    if (input) {
      input.classList.add("shake");
      setTimeout(() => input.classList.remove("shake"), 500);
    }
  }

  function clearError(id) {
    const errorElement = document.querySelector(
      `.error-message[data-for="${id}"]`,
    );
    if (errorElement) {
      errorElement.textContent = "";
      errorElement.style.opacity = "0";
    }
  }

  fields.forEach(({ id }) => {
    document
      .getElementById(id)
      ?.addEventListener("input", () => clearError(id));
  });

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    btn.classList.add("loading");

    let isValid = true;

    fields.forEach(({ id }) => clearError(id));
    clearError("general");

    fields.forEach((field) => {
      const value = document.getElementById(field.id)?.value.trim() ?? "";

      if (field.min && value.length < field.min) {
        showError(field.id, field.message);
        isValid = false;
      }

      if (field.type === "email" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        showError(field.id, field.message);
        isValid = false;
      }
    });

    if (!isValid) {
      btn.classList.remove("loading");
      return;
    }

    fetch("./login", {
      method: "POST",
      body: new FormData(form),
    })
      .then((response) => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
      })
      .then((data) => {
        btn.classList.remove("loading");

        // ===== REDIRECTION 2FA =====
        if (data.success && data.requires_2fa) {
          window.location.href = "./verify-2fa";
          return;
        }

        // ===== CONNEXION RÉUSSIE =====
        if (data.success) {
          window.location.href = "./";
          return;
        }

        // ===== COMPTE NON CONFIRMÉ =====
        if (!data.success && data.not_confirmed) {
          window.location.href = "./noconfirmed";
          return;
        }

        // ===== ERREURS =====
        if (data.errors) {
          Object.entries(data.errors).forEach(([field, message]) => {
            showError(field, message);
          });
        } else {
          showError("general", data.message ?? "Une erreur est survenue.");
        }
      })
      .catch((error) => {
        console.error("Erreur fetch:", error);
        btn.classList.remove("loading");
        showError("general", "Une erreur est survenue. Veuillez réessayer.");
      });
  });
});
