document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('btn-submit');

    const fields = [
        { id: 'email', type: 'email', message: 'Adresse e-mail invalide.' },
        { id: 'password', min: 8, message: 'Le mot de passe doit contenir au moins 8 caractères.' }
    ];

    function showError(id, message) {
        const input = document.getElementById(id);
        const errorElement = input.closest('.form-login').querySelector(`.error-message[data-for="${id}"]`);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.opacity = '1';
        }
        // Animation shake
        input.classList.add('shake');
        setTimeout(() => input.classList.remove('shake'), 500);
    }

    function clearError(id) {
        const errorElement = document.querySelector(`.error-message[data-for="${id}"]`);
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.opacity = '0';
        }
    }

    // Efface l'erreur dès que l'utilisateur tape
    fields.forEach(field => {
        const input = document.getElementById(field.id);
        if (input) {
            input.addEventListener('input', () => {
                clearError(field.id);
            });
        }
    });

    // Effacer les erreurs générales
    function clearGeneralError() {
        const generalErrorElement = document.querySelector('.error-message[data-for="general"]');
        if (generalErrorElement) {
            generalErrorElement.textContent = '';
            generalErrorElement.style.opacity = '0';
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btn.classList.add('loading'); // Afficher le spinner de chargement

        let isValid = true;

        // Réinitialiser toutes les erreurs
        fields.forEach(field => clearError(field.id));
        clearGeneralError(); // Effacer les erreurs générales

        fields.forEach(field => {
            let value = document.getElementById(field.id).value.trim();

            // Vérification longueur minimale
            if (field.min && value.length < field.min) {
                showError(field.id, field.message);
                isValid = false;
            }

            // Vérification email
            if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                showError(field.id, field.message);
                isValid = false;
            }
        });

        // Soumettre ou afficher erreurs
        if (isValid) {
            // Effectuer un fetch vers la même page
            const formData = new FormData(form); // Créer un FormData avec toutes les données du formulaire
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convertir la réponse en JSON
            .then(data => {
                btn.classList.remove('loading'); // Cacher le spinner de chargement

                if (data.success) {
                    // Connexion réussie
                    window.location.href = '/dashboard'; // Rediriger vers le tableau de bord ou une autre page
                } else {
                    // Afficher les erreurs dans les champs correspondants
                    if (data.errors) {
                        for (const [field, message] of Object.entries(data.errors)) {
                            showError(field, message); // Afficher l'erreur pour chaque champ
                        }
                    } else {
                        // Afficher une erreur générale
                        showError('general', data.message || 'Une erreur est survenue.');
                    }
                }
            })
            .catch(error => {
                btn.classList.remove('loading');
                showError('general', 'Une erreur est survenue. Veuillez réessayer.');
            });
        } else {
            btn.classList.remove('loading');
        }
    });
});
