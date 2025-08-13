document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('confirmForm');
    const codeInput = document.getElementById('code');
    const messageDiv = document.getElementById('message');
    const resendLink = document.getElementById('resend-link');
    const resendMessage = document.getElementById('resend-message');

    // --- Validation et envoi du formulaire de confirmation ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        messageDiv.textContent = '';
        
        const code = codeInput.value.trim();
        const email = form.querySelector('input[name="email"]').value;

        // Validation rapide côté client
        if (code.length !== 6 || !/^\d{6}$/.test(code)) {
            messageDiv.style.color = 'red';
            messageDiv.textContent = 'Veuillez entrer un code valide à 6 chiffres.';
            return;
        }

        const data = new FormData();
        data.append('code', code);
        data.append('email', email);

        try {
            const response = await fetch('./confirm', {
                method: 'POST',
                body: data
            });

            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

            const result = await response.json();

            if (result.success) {
                messageDiv.style.color = 'green';
                messageDiv.textContent = result.message || 'Compte confirmé avec succès !';
                setTimeout(() => window.location.href = './login', 2000);
            } else {
                messageDiv.style.color = 'red';
                messageDiv.textContent = result.message || 'Code invalide, veuillez réessayer.';
            }
        } catch (error) {
            console.error('Erreur JS/fetch:', error);
            messageDiv.style.color = 'red';
            messageDiv.textContent = 'Erreur serveur, veuillez réessayer plus tard.';
        }
    });

    codeInput.addEventListener('input', () => {
        messageDiv.textContent = '';
    });

    // --- Renvoyer un nouveau code ---
    resendLink.addEventListener('click', async (e) => {
        e.preventDefault();
        resendMessage.style.display = 'block';
        resendMessage.style.color = 'black';
        resendMessage.textContent = 'Envoi en cours...';

        const email = form.querySelector('input[name="email"]').value;

        try {
            const response = await fetch(`./resend-code?email=${encodeURIComponent(email)}`, {
                method: 'GET'
            });

            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

            const result = await response.json();

            if (result.success) {
                resendMessage.style.color = 'green';
                resendMessage.textContent = 'Un nouveau code a été envoyé à votre adresse e-mail.';
                // Disparaît après 5 secondes
                setTimeout(() => {
                    resendMessage.style.display = 'none';
                }, 5000);
            } else {
                resendMessage.style.color = 'red';
                resendMessage.textContent = result.error || 'Erreur lors de l’envoi du code.';
            }
        } catch (error) {
            console.error('Erreur JS/fetch (resend):', error);
            resendMessage.style.color = 'red';
            resendMessage.textContent = 'Erreur serveur, veuillez réessayer plus tard.';
        }
    });
});
