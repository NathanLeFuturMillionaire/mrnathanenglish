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

document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.querySelector('.hamburger');
  const mobileMenu = document.getElementById('mobile-menu');
  const overlay = document.querySelector('.overlay');
  const dropdownMobiles = document.querySelectorAll('.dropdown-mobile > .dropbtn-mobile');

  // Ouvrir/fermer menu mobile
  hamburger.addEventListener('click', () => {
    const expanded = hamburger.getAttribute('aria-expanded') === 'true';
    hamburger.setAttribute('aria-expanded', String(!expanded));
    mobileMenu.classList.toggle('open');
    overlay.classList.toggle('active');
  });

  // Fermer menu en cliquant sur overlay
  overlay.addEventListener('click', () => {
    hamburger.setAttribute('aria-expanded', 'false');
    mobileMenu.classList.remove('open');
    overlay.classList.remove('active');
  });

  // Toggle dropdown mobile
  dropdownMobiles.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const parent = btn.parentElement;
      parent.classList.toggle('open');
    });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const profileBtn = document.getElementById('profile-btn');
  const dropdownMenu = document.getElementById('dropdown-menu');
  const links = document.querySelectorAll('.dropdown-content li a');

  // Toggle du dropdown
  profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdownMenu.classList.toggle('show');
    profileBtn.classList.toggle('active');
  });

  // Fermer si clic à l’extérieur
  document.addEventListener('click', () => {
    dropdownMenu.classList.remove('show');
    profileBtn.classList.remove('active');
  });

  // Fermer avec la touche Echap
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      dropdownMenu.classList.remove('show');
      profileBtn.classList.remove('active');
    }
  });

  // Effet onde sur chaque lien
  links.forEach(link => {
    link.addEventListener('click', function(e) {
      // Position du clic
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;

      // Création du span "ripple"
      const ripple = document.createElement('span');
      ripple.classList.add('ripple');
      ripple.style.left = `${x}px`;
      ripple.style.top = `${y}px`;

      // Ajout à l'élément cliqué
      this.appendChild(ripple);

      // Suppression après l’animation
      setTimeout(() => {
        ripple.remove();
      }, 600);
    });
  });
});
