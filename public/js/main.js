document.addEventListener('DOMContentLoaded', function() {
    // Sélection de tous les liens dropdown
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = this.parentElement;
            const isOpen = dropdown.classList.contains('open');

            // Fermer tous les autres dropdowns
            document.querySelectorAll('.dropdown.open').forEach(openDropdown => {
                openDropdown.classList.remove('open');
            });

            // Ouvrir celui cliqué si ce n'était pas déjà ouvert
            if (!isOpen) {
                dropdown.classList.add('open');
            }
        });
    });

    // Fermer dropdown quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown.open').forEach(openDropdown => {
                openDropdown.classList.remove('open');
            });
        }
    });
});


// Sélection du header
const header = document.querySelector("header");

// Écoute l'événement de scroll
window.addEventListener("scroll", () => {
    if (window.scrollY > 50) {
        header.classList.add("scrolled");
    } else {
        header.classList.remove("scrolled");
    }
});
