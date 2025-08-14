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

                // --- Mise à jour dynamique du header ---
                if (result.user && result.user.confirmed === 1) {
                    updateHeaderAfterConfirmation(result.user);
                }

                // Redirection vers la page de bienvenue après un petit délai
                setTimeout(() => window.location.href = './welcome', 1500);

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
                setTimeout(() => resendMessage.style.display = 'none', 5000);
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

    /**
     * Met à jour le header pour un utilisateur confirmé
     */
    function updateHeaderAfterConfirmation(user) {
        const navMenu = document.querySelector('.nav-menu ul');
        if (!navMenu) return;

        // Nettoyer le menu actuel
        navMenu.innerHTML = `
            <li><a href="./">Accueil</a></li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Examens ▾</a>
                <ul class="dropdown-content">
                    <li><a href="./toefl">TOEFL</a></li>
                    <li><a href="./ielts">IELTS</a></li>
                    <li><a href="./cambridge">Cambridge English</a></li>
                    <li><a href="./toeic">TOEIC</a></li>
                    <li><a href="./pte">PTE Academic</a></li>
                </ul>
            </li>
            <li><a href="/courses">Cours</a></li>
            <li class="profile-menu">
                <a href="./profile">
                    <img src="./uploads/profiles/${user.profile_picture}" 
                         alt="Photo de profil" 
                         class="profile-picture"
                         style="width:35px; height:35px; border-radius:50%; object-fit:cover;">
                </a>
            </li>
        `;
    }
});
