    document.addEventListener("DOMContentLoaded", function() {
        const content = document.querySelector(".right-content h1");
        const hours = new Date().getHours();
        let greeting;

        if (hours >= 5 && hours < 12) {
            greeting = "Bonjour";
        } else if (hours >= 12 && hours < 18) {
            greeting = "Bonne après-midi";
        } else {
            greeting = "Bonsoir";
        }

        // Récupère le nom d'utilisateur existant
        const username = content.textContent.match(/, (.+?) 👋/)?.[1] || "";

        // Met à jour le texte complet
        content.textContent = `${greeting}, ${username} 👋`;
    });