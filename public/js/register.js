document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');
    const btn = document.getElementById('btn-submit');

    const fields = [
        { id: 'fullname', min: 3, message: 'Le nom complet doit contenir au moins 3 caractères.' },
        { id: 'email', type: 'email', message: 'Adresse e-mail invalide.' },
        { id: 'username', min: 4, message: 'Le nom d’utilisateur doit contenir au moins 4 caractères.' },
        { id: 'password', min: 8, message: 'Le mot de passe doit contenir au moins 8 caractères.' },
        { id: 'confirm_password', match: 'password', message: 'Les mots de passe ne correspondent pas.' }
    ];

    function showError(id, message) {
        const input = document.getElementById(id);
        const errorElement = document.getElementById(`error-${id}`);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.opacity = '1';
        }
        // Animation shake
        input.classList.add('shake');
        setTimeout(() => input.classList.remove('shake'), 500);
    }

    function clearError(id) {
        const errorElement = document.getElementById(`error-${id}`);
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

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btn.classList.add('loading');

        let isValid = true;

        // Réinitialiser toutes les erreurs
        fields.forEach(field => clearError(field.id));

        fields.forEach(field => {
            let value = document.getElementById(field.id).value.trim();

            // Vérif longueur minimale
            if (field.min && value.length < field.min) {
                showError(field.id, field.message);
                isValid = false;
            }

            // Vérif email
            if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                showError(field.id, field.message);
                isValid = false;
            }

            // Vérif correspondance des mots de passe
            if (field.match && value !== document.getElementById(field.match).value) {
                showError(field.id, field.message);
                isValid = false;
            }
        });

        // Soumettre ou afficher erreurs
        if (isValid) {
            form.submit();
        } else {
            btn.classList.remove('loading');
        }
    });

    // Checkbox terms clear error on change
    const termsCheckbox = document.querySelector('input[name="terms"]');
    if (termsCheckbox) {
        termsCheckbox.addEventListener('change', () => {
            const errorTerms = document.getElementById('error-terms');
            if (errorTerms) {
                errorTerms.textContent = '';
                errorTerms.style.opacity = '0';
            }
        });
    }

});

