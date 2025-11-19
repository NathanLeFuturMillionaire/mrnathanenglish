document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("resetPasswordForm");
    const btnSubmit = document.getElementById("btn-submit");
    const spinner = btnSubmit.querySelector(".spinner");

    form.addEventListener("submit", function(e) {
        e.preventDefault();

        // Récupère les valeurs
        const newPassword = document.getElementById("new-password").value.trim();
        const confirmPassword = document.getElementById("confirm-password").value.trim();
        const token = new URLSearchParams(window.location.search).get("token");

        // Réinitialise les messages d'erreur
        document.querySelectorAll(".error-message").forEach(el => el.textContent = "");

        if (!newPassword || !confirmPassword) {
            if (!newPassword) document.querySelector('[data-for="new-password"]').textContent = "Veuillez entrer le nouveau mot de passe.";
            if (!confirmPassword) document.querySelector('[data-for="confirm-password"]').textContent = "Veuillez confirmer le mot de passe.";
            return;
        }

        if (newPassword !== confirmPassword) {
            document.querySelector('[data-for="confirm-password"]').textContent = "Les mots de passe ne correspondent pas.";
            return;
        }

        // Affiche le spinner
        spinner.style.display = "inline-block";
        btnSubmit.disabled = true;

        // Prépare les données pour AJAX
        const formData = new FormData();
        formData.append("new-password", newPassword);
        formData.append("confirm-password", confirmPassword);
        formData.append("token", token);

        fetch("./reset-password", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            spinner.style.display = "none";
            btnSubmit.disabled = false;

            if (data.success) {
                alert("Mot de passe réinitialisé avec succès !");
                window.location.href = "./login";
            } else {
                if (data.errors) {
                    // Affiche les erreurs pour chaque champ
                    for (const key in data.errors) {
                        const el = document.querySelector(`[data-for="${key}"]`);
                        if (el) el.textContent = data.errors[key];
                    }
                } else if (data.message) {
                    document.querySelector('[data-for="general"]').textContent = data.message;
                }
            }
        })
        .catch(err => {
            spinner.style.display = "none";
            btnSubmit.disabled = false;
            document.querySelector('[data-for="general"]').textContent = "Une erreur est survenue, veuillez réessayer.";
            console.error(err);
        });
    });
});
