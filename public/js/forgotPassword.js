document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('forgotPasswordForm');
    const btn = document.getElementById('btn-submit');

    const fields = [
        { id: 'email', type: 'email', message: 'Adresse e-mail invalide.' }
    ];

    function showError(id, message) {
        const input = document.getElementById(id);
        const errorElement = input ? input.closest('.input-group').querySelector(`.error-message[data-for="${id}"]`) : document.querySelector(`.error-message[data-for="${id}"]`);
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

    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);

        // Afficher le toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Masquer et supprimer le toast après 3 secondes
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300); // Attendre la fin de la transition
        }, 3000);
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
            if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                showError(field.id, field.message);
                isValid = false;
            }
        });

        if (isValid) {
            const formData = new FormData(form);
            fetch('./forgot-password', {
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
                    console.log('Réponse brute du serveur:', text); // Log pour débogage
                    try {
                        const data = JSON.parse(text);
                        btn.classList.remove('loading');
                        if (data.success) {
                            showToast(data.message || 'Nous avons envoyé un lien de réinitialisation dans votre email.', 'success');
                        } else {
                            if (data.errors) {
                                for (const [field, message] of Object.entries(data.errors)) {
                                    showError(field, message);
                                }
                            } else if (data.message) {
                                showToast(data.message, 'error');
                            } else {
                                showToast('Une erreur est survenue.', 'error');
                            }
                        }
                    } catch (e) {
                        console.error('Erreur de parsing JSON:', e, 'Réponse brute:', text);
                        btn.classList.remove('loading');
                        showToast('Erreur de communication avec le serveur.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur fetch:', error);
                    btn.classList.remove('loading');
                    showToast('Une erreur est survenue. Veuillez réessayer.', 'error');
                });
        } else {
            btn.classList.remove('loading');
        }
    });
});