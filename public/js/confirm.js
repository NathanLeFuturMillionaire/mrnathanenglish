document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('confirmForm');
  const codeInput = document.getElementById('code');
  const errorCode = document.getElementById('error-code');
  const messageDiv = document.getElementById('message');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Reset errors and message
    errorCode.textContent = '';
    messageDiv.textContent = '';

    const code = codeInput.value.trim();

    // Simple validation
    if (code.length !== 6 || !/^\d{6}$/.test(code)) {
      errorCode.textContent = 'Veuillez entrer un code valide à 6 chiffres.';
      return;
    }

    // Prépare les données à envoyer
    const formData = new FormData(form);

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        messageDiv.style.color = 'green';
        messageDiv.textContent = 'Compte confirmé avec succès ! Redirection en cours...';

        // Rediriger par exemple après 2 secondes
        setTimeout(() => {
          window.location.href = './login';
        }, 2000);

      } else {
        messageDiv.style.color = 'red';
        messageDiv.textContent = result.message || 'Code invalide, veuillez réessayer.';
      }

    } catch (error) {
      messageDiv.style.color = 'red';
      messageDiv.textContent = 'Erreur serveur, veuillez réessayer plus tard.';
      console.error(error);
    }
  });

  // Efface l'erreur dès que l'utilisateur tape
  codeInput.addEventListener('input', () => {
    errorCode.textContent = '';
    messageDiv.textContent = '';
  });
});
