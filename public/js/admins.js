const prices = {
    1: 3000,
    2: 6000,
    3: 9000,
    4: 12000,
    5: 15000,
    6: 18000,
    7: 21000,
    8: 24000,
    9: 27000,
    10: 30000,
    11: 33000,
    12: 36000
};

document.getElementById('duree').addEventListener('input', function () {
    const dureeValue = parseInt(this.value);
    const dateDebutInput = document.getElementById('dateDebut');
    const dateFinInput = document.getElementById('dateFin');
    const prixInput = document.getElementById('prix');

    if (dureeValue && dureeValue > 0 && dureeValue <= 12) {
        // Définir la date de début à aujourd'hui
        const today = new Date().toISOString().split('T')[0];
        dateDebutInput.value = today;

        // Calculer la date de fin
        const startDate = new Date(today);
        startDate.setMonth(startDate.getMonth() + dureeValue);
        const endDate = startDate.toISOString().split('T')[0];
        dateFinInput.value = endDate;

        // Définir le prix
        const price = prices[dureeValue] || 0;
        prixInput.value = price > 0 ? price + ' F CFA' : '';
    } else {
        // Réinitialiser si valeur invalide
        dateDebutInput.value = '';
        dateFinInput.value = '';
        prixInput.value = '';
    }
});

// Si la date de début change manuellement, recalculer la fin si durée définie
document.getElementById('dateDebut').addEventListener('change', function () {
    const dureeValue = parseInt(document.getElementById('duree').value);
    const dateFinInput = document.getElementById('dateFin');
    if (dureeValue && this.value) {
        const startDate = new Date(this.value);
        startDate.setMonth(startDate.getMonth() + dureeValue);
        const endDate = startDate.toISOString().split('T')[0];
        dateFinInput.value = endDate;
    }
});
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('adminForm');
    const btn = document.querySelector('.btn-submit');

    if (!form || !btn) return;

    // Ajouter les divs d'erreur si elles n'existent pas
    const fields = [
        { id: 'nom', type: 'text', required: true, message: 'Le nom est requis.' },
        { id: 'email', type: 'email', required: true, message: 'Email valide requis.' },
        { id: 'telephone', type: 'tel', required: true, message: 'Numéro de téléphone valide requis.' },
        { id: 'duree', type: 'number', required: true, min: 1, max: 12, message: 'Durée entre 1 et 12 mois requise.' }
    ];

    fields.forEach(field => {
        const input = document.getElementById(field.id);
        if (input && !input.nextElementSibling.classList.contains('error-message')) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.setAttribute('data-for', field.id);
            input.parentNode.insertBefore(errorDiv, input.nextSibling);
        }
    });

    function showError(id, message) {
        const input = document.getElementById(id);
        const errorElement = document.querySelector(`.error-message[data-for="${id}"]`);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.opacity = '1';
            errorElement.classList.add('visible');
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
            errorElement.classList.remove('visible');
        }
    }

    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 1000;';
        document.body.appendChild(container);
        return container;
    }

    // Clear errors on input
    fields.forEach(field => {
        const input = document.getElementById(field.id);
        if (input) {
            input.addEventListener('input', () => clearError(field.id));
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btn.classList.add('loading');

        let isValid = true;

        fields.forEach(field => clearError(field.id));

        // Validation nom
        const nom = document.getElementById('nom').value.trim();
        if (!nom) {
            showError('nom', fields[0].message);
            isValid = false;
        }

        // Validation email
        const email = document.getElementById('email').value.trim();
        if (!email) {
            showError('email', fields[1].message);
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('email', 'Format email invalide.');
            isValid = false;
        }

        // Validation téléphone
        const telephone = document.getElementById('telephone').value.trim();
        if (!telephone) {
            showError('telephone', fields[2].message);
            isValid = false;
        } else if (!/^\+?[1-9]\d{1,14}$/.test(telephone.replace(/\s/g, ''))) {
            showError('telephone', 'Format téléphone invalide (ex. : +1234567890).');
            isValid = false;
        }

        // Validation durée
        const duree = parseInt(document.getElementById('duree').value);
        if (!duree || duree < 1 || duree > 12) {
            showError('duree', fields[3].message);
            isValid = false;
        }

        if (isValid) {
            // Calculer le prix (ex. : 10€/mois * durée)
            const prix = duree * 10; // Ajuste selon ta logique
            const prixInput = document.getElementById('prix');
            if (prixInput) prixInput.value = prix;

            // Définir les dates
            const today = new Date().toISOString().split('T')[0];
            const dateDebutInput = document.getElementById('dateDebut');
            if (dateDebutInput) dateDebutInput.value = today;

            const dateFin = new Date();
            dateFin.setMonth(dateFin.getMonth() + duree);
            const dateFinInput = document.getElementById('dateFin');
            if (dateFinInput) dateFinInput.value = dateFin.toISOString().split('T')[0];

            // Créer FormData pour l'AJAX
            const formData = new FormData(form);

            // AJAX POST vers /admins/add (ajuste l'URL selon ton routeur)
            fetch('./admins', { // Ajuste l'URL selon ton routeur
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status} ${response.statusText}`);
                }
                return response.text(); // Utilise text() pour déboguer, puis parse JSON
            })
            .then(text => {
                console.log('Réponse brute:', text); // Pour déboguer
                try {
                    const data = JSON.parse(text);
                    btn.classList.remove('loading');
                    if (data.success) {
                        showToast(data.message || 'Membre premium ajouté avec succès !', 'success');
                        setTimeout(() => {
                            form.reset();
                            window.location.reload(); // Ou redirige vers une liste
                        }, 2000);
                    } else {
                        showToast(data.message || 'Erreur lors de l\'ajout.', 'error');
                    }
                } catch (e) {
                    console.error('Erreur de parsing JSON:', e, 'Réponse brute:', text);
                    btn.classList.remove('loading');
                    showToast('Réponse invalide du serveur. Vérifiez la console.', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur fetch:', error);
                btn.classList.remove('loading');
                showToast('Une erreur est survenue. Veuillez réessayer.', 'error');
            });
        } else {
            btn.classList.remove('loading');
            showToast('Veuillez corriger les erreurs.', 'error');
        }
    });
});