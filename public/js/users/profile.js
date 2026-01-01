document.addEventListener("DOMContentLoaded", function () {
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

document.addEventListener("DOMContentLoaded", () => {
    
    const menuItems = document.querySelectorAll(".menu-item");
    const sections = document.querySelectorAll(".content-section");
    const spinner = document.getElementById("loading-spinner");
    const errorMessage = document.getElementById("error-message");
    
    menuItems.forEach(item => {
        item.addEventListener("click", async e => {
            e.preventDefault();
            
            // Réinitialiser état
            menuItems.forEach(i => i.classList.remove("active"));
            item.classList.add("active");
            sections.forEach(s => s.classList.remove("active"));
            errorMessage.classList.add("hidden");

            // Afficher le spinner
            spinner.classList.remove("hidden");

            try {
                // Simuler un chargement (connexion)
                await simulateLoading();

                // Cacher le spinner
                spinner.classList.add("hidden");

                // Afficher la section correspondante
                const target = item.getAttribute("data-target");
                const section = document.getElementById(target);
                section.classList.add("active");

            } catch (error) {
                spinner.classList.add("hidden");
                errorMessage.textContent = error; // Afficher le message d'erreur dynamique
                errorMessage.classList.remove("hidden");
            }
        });
    });
});

/**
 * Fonction qui simule un chargement avec "connexion"
 * Elle résout toujours sans erreur pour assurer un fonctionnement à 100%
*/
function simulateLoading() {
    return new Promise((resolve) => {
        // Durée du chargement entre 0.8s et 2s
        const duration = 800 + Math.random() * 1200;

        setTimeout(() => {
            resolve();
        }, duration);
    });
}

// lucide.createIcons();
document.addEventListener("DOMContentLoaded", () => {
    const menuLinks = document.querySelectorAll(".profile-menu .menu-links a");
    const sections = document.querySelectorAll(".profile-content > .profile-section");

    // Masque toutes les sections
    function hideAllSections() {
        sections.forEach(section => {
            section.style.display = "none";
        });
    }

    // Active le lien du menu
    function setActiveLink(clickedLink) {
        menuLinks.forEach(link => link.classList.remove("active"));
        clickedLink.classList.add("active");
    }

    // Affiche la section par défaut (la première)
    hideAllSections();
    if (sections.length > 0) {
        sections[0].style.display = "block";
    }

    // Gestion du clic sur chaque lien du menu
    menuLinks.forEach(link => {
        link.addEventListener("click", (e) => {
            e.preventDefault();

            const targetId = link.getAttribute("href").replace("#", "");
            const targetSection = document.getElementById(targetId);

            if (targetSection) {
                hideAllSections();          // Masque toutes les sections
                targetSection.style.display = "block"; // Affiche la section sélectionnée
                setActiveLink(link);        // Active le lien
            }
        });
    });
});
