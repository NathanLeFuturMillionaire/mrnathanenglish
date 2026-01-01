// public/js/reset-password.js
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("resetPasswordForm");
    const btnSubmit = document.getElementById("btn-submit");
    const spinner = btnSubmit.querySelector(".spinner");

    // Récupérer le token depuis l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get("token");

    if (!token) {
        // console.error("❌ [ResetPassword] Token manquant dans l'URL");
        document.querySelector('[data-for="general"]').textContent = "Lien invalide ou manquant.";
        return;
    }

    const tokenInput = document.getElementById("token-input");
    if (tokenInput) {
        tokenInput.value = token;
        // console.log("✅ [ResetPassword] Token chargé dans le formulaire :", token);
    } else {
        // console.error("❌ [ResetPassword] Champ hidden 'token-input' introuvable dans le DOM");
    }

    // Fonctions d'erreur
    function showError(field, message) {
        const errorEl = document.querySelector(`[data-for="${field}"]`);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.opacity = "1";
        }
        // console.warn(`[ResetPassword] Erreur champ '${field}' : ${message}`);
    }

    function clearError(field) {
        const errorEl = document.querySelector(`[data-for="${field}"]`);
        if (errorEl) {
            errorEl.textContent = "";
            errorEl.style.opacity = "0";
        }
    }

    // Effacer les erreurs au fur et à mesure
    document.getElementById("new-password").addEventListener("input", () => clearError("new-password"));
    document.getElementById("confirm-password").addEventListener("input", () => clearError("confirm-password"));

    // Toast
    function showToast(message, type = "success") {
        let container = document.getElementById("toast-container");
        if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            document.body.appendChild(container);
        }

        const toast = document.createElement("div");
        toast.className = `toast ${type}`;
        toast.textContent = message;
        container.appendChild(toast);

        setTimeout(() => toast.classList.add("show"), 100);
        setTimeout(() => {
            toast.classList.remove("show");
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // Soumission
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        // console.log("🚀 [ResetPassword] Soumission du formulaire");

        // Réinitialiser les erreurs
        document.querySelectorAll(".error-message").forEach(el => {
            el.textContent = "";
            el.style.opacity = "0";
        });

        const newPassword = document.getElementById("new-password").value.trim();
        const confirmPassword = document.getElementById("confirm-password").value.trim();

        let hasError = false;

        if (!newPassword) {
            showError("new-password", "Veuillez entrer un nouveau mot de passe.");
            hasError = true;
        } else if (newPassword.length < 8) {
            showError("new-password", "Le mot de passe doit faire au moins 8 caractères.");
            hasError = true;
        }

        if (!confirmPassword) {
            showError("confirm-password", "Veuillez confirmer le mot de passe.");
            hasError = true;
        } else if (newPassword !== confirmPassword) {
            showError("confirm-password", "Les mots de passe ne correspondent pas.");
            hasError = true;
        }

        if (hasError) {
            // console.warn("⚠️ [ResetPassword] Validation échouée – formulaire non envoyé");
            return;
        }

        // Spinner
        spinner.style.display = "inline-block";
        btnSubmit.disabled = true;
        // console.log("⏳ [ResetPassword] Envoi de la requête au serveur...");

        const formData = new FormData(form);

        fetch("./reset-password", {
            method: "POST",
            body: formData
        })
        .then(response => {
            // console.log(`📡 [ResetPassword] Réponse HTTP : ${response.status} ${response.statusText}`);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status} - ${response.statusText}`);
            }
            return response.text(); // On lit d'abord en texte pour voir le brut
        })
        .then(text => {
            // console.log("📄 [ResetPassword] Réponse brute du serveur :", text);

            let data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                // console.error("❌ [ResetPassword] Impossible de parser le JSON :", parseError);
                throw new Error("Réponse du serveur non JSON");
            }

            spinner.style.display = "none";
            btnSubmit.disabled = false;

            if (data.success) {
                // console.log("✅ [ResetPassword] Succès !", data.message);
                showToast("Mot de passe réinitialisé avec succès !", "success");
                setTimeout(() => location.href = "./login", 2000);
            } else {
                // console.warn("⚠️ [ResetPassword] Échec serveur :", data);
                const msg = data.message || "Une erreur est survenue.";
                document.querySelector('[data-for="general"]').textContent = msg;
                showToast(msg, "error");
            }
        })
        .catch(err => {
            // console.error("💥 [ResetPassword] Erreur critique :", err.message);
            spinner.style.display = "none";
            btnSubmit.disabled = false;
            document.querySelector('[data-for="general"]').textContent = "Erreur de connexion au serveur.";
            showToast("Erreur réseau ou serveur. Réessayez.", "error");
        });
    });
});