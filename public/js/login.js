document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('btn-submit');

    const fields = [
        { id: 'email', type: 'email', message: 'Adresse e-mail invalide.' },
        { id: 'password', min: 8, message: 'Le mot de passe doit contenir au moins 8 caractères.' }
    ];

    function showError(id, message) {
        const input = document.getElementById(id);
        const errorElement = input ? input.closest('.form-login').querySelector(`.error-message[data-for="${id}"]`) : document.querySelector(`.error-message[data-for="${id}"]`);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.opacity = '1';
        }
        if (input) {
            input.classList.add('shake');
            setTimeout(() => input.classList.remove('shake'), 500);
        }
    }

    function clearError(id) {
        const errorElement = document.querySelector(`.error-message[data-for="${id}"]`);
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.opacity = '0';
        }
    }

    fields.forEach(field => {
        const input = document.getElementById(field.id);
        if (input) {
            input.addEventListener('input', () => {
                clearError(field.id);
            });
        }
    });

    function clearGeneralError() {
        clearError('general');
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btn.classList.add('loading');

        let isValid = true;

        fields.forEach(field => clearError(field.id));
        clearGeneralError();

        fields.forEach(field => {
            let value = document.getElementById(field.id).value.trim();
            if (field.min && value.length < field.min) {
                showError(field.id, field.message);
                isValid = false;
            }
            if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                showError(field.id, field.message);
                isValid = false;
            }
        });

        if (isValid) {
            const formData = new FormData(form);
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status} ${response.statusText}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    btn.classList.remove('loading');
                    if (data.success) {
                        window.location.href = './';
                    } else {
                        if (data.errors) {
                            for (const [field, message] of Object.entries(data.errors)) {
                                showError(field, message);
                            }
                        } else if (data.message) {
                            showError('general', data.message);
                        } else {
                            showError('general', 'Une erreur est survenue.');
                        }
                    }
                } catch (e) {
                    console.error('Erreur de parsing JSON:', e, 'Réponse brute:', text);
                    btn.classList.remove('loading');
                    showError('general', 'Erreur de communication avec le serveur.');
                }
            })
            .catch(error => {
                console.error('Erreur fetch:', error);
                btn.classList.remove('loading');
                showError('general', 'Une erreur est survenue. Veuillez réessayer.');
            });
        } else {
            btn.classList.remove('loading');
        }
    });
});