document.addEventListener("DOMContentLoaded", () => {
    const fileInput = document.getElementById("profilePic");
    const uploadLabel = document.querySelector(".circle-upload");
    const submitBtn = document.querySelector(".btn-submit");
    const form = uploadLabel.closest("form");

    // Affiche la prévisualisation et active le bouton
    fileInput.addEventListener("change", (event) => {
        const file = event.target.files[0];
        if (file && file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = (e) => {
                uploadLabel.style.backgroundImage = `url(${e.target.result})`;
                uploadLabel.style.backgroundSize = "cover";
                uploadLabel.style.backgroundPosition = "center";
                uploadLabel.textContent = "";
            };
            reader.readAsDataURL(file);

            // Active le bouton
            submitBtn.disabled = false;
        } else {
            uploadLabel.style.backgroundImage = '';
            uploadLabel.style.border = '2px dashed #bbb';
            uploadLabel.textContent = 'Cliquez pour choisir';
            submitBtn.disabled = true;
        }
    });

    // Upload via AJAX
    submitBtn.addEventListener("click", async (e) => {
        e.preventDefault();
        const file = fileInput.files[0];

        const formData = new FormData();
        formData.append("profilePic", file);

        try {
            const response = await fetch(window.location.href, {
                method: "POST",
                body: formData,
            });

            const data = await response.json();

        } catch (err) {
            console.error(err);
            // alert("Erreur serveur, veuillez réessayer.");
        }
    });

    // Si une photo existe déjà en session, active le bouton
    const existingPic = "<?= $_SESSION['user']['profile_picture'] ?? '' ?>";
    if (existingPic && existingPic !== "default.png") {
        submitBtn.disabled = false;
    }
});


document.addEventListener("DOMContentLoaded", () => {
    const birthdateInput = document.getElementById("birthdate");
    const nextBirthdateBtn = document.querySelector(".btn-submit-date");

    // Désactive par défaut
    nextBirthdateBtn.disabled = true;

    // Calcul âge et validation
    const minYear = 1900;
    const maxYear = new Date().getFullYear() - 10;

    function calculateAge(dateString) {
        const today = new Date();
        const birthDate = new Date(dateString);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) age--;
        return age;
    }

    function isValidYear(dateString) {
        const year = new Date(dateString).getFullYear();
        return year >= minYear && year <= maxYear;
    }

    // Affichage âge
    const ageDisplay = document.createElement("p");
    ageDisplay.style.marginTop = "10px";
    ageDisplay.style.fontWeight = "500";
    ageDisplay.style.color = "#333";
    birthdateInput.parentNode.appendChild(ageDisplay);

    birthdateInput.addEventListener("change", () => {
        const date = birthdateInput.value;
        if (date && isValidYear(date)) {
            const age = calculateAge(date);
            ageDisplay.textContent = `Vous avez ${age} ans.`;
            nextBirthdateBtn.disabled = false;
        } else {
            ageDisplay.textContent = "";
            nextBirthdateBtn.disabled = true;
        }
    });

    // Envoi AJAX
    nextBirthdateBtn.addEventListener("click", async (e) => {
        e.preventDefault();
        const date = birthdateInput.value;
        if (!date || !isValidYear(date)) return;

        const formData = new FormData();
        formData.append("birthdate", date);

        try {
            const response = await fetch(window.location.href, {
                method: "POST",
                body: formData,
            });

            const data = await response.json();
            if (data.success) {
                // Passe à la section suivante si multi-step
                // showSection(currentIndex + 1); // selon ton code
            } else {
                alert(data.message || "Erreur lors de l'enregistrement.");
            }
        } catch (err) {
            console.error(err);
            alert("Erreur serveur, veuillez réessayer.");
        }
    });
});



// Récupère toutes les sections à afficher
const sections = [
    document.querySelector('.profil-picture-choice'),
    document.querySelector('.birthdate-choice'),
    document.querySelector('.phone-number'),
    document.querySelector('.english-level-section'),
    document.querySelector('.bio-section')
];

// Récupère l'index actuel depuis le localStorage ou démarre à 0
let currentIndex = parseInt(localStorage.getItem('currentSection')) || 0;

// Fonction pour afficher une section avec animation
function showSection(index, direction = 'none') {
    sections.forEach((sec, i) => {
        if (i === index) {
            sec.style.display = 'block';
            sec.style.opacity = 0;

            // Détermine la position de départ selon la direction
            if (direction === 'next') sec.style.transform = 'translateX(50px)';
            else if (direction === 'prev') sec.style.transform = 'translateX(-50px)';
            else sec.style.transform = 'translateX(0)';

            // Animation après un court délai
            setTimeout(() => {
                sec.style.transition = 'all 0.5s ease';
                sec.style.opacity = 1;
                sec.style.transform = 'translateX(0)';
            }, 50);
        } else {
            // Animation de sortie
            if (i === currentIndex) {
                sec.style.transition = 'all 0.5s ease';
                if (direction === 'next') sec.style.transform = 'translateX(-50px)';
                else if (direction === 'prev') sec.style.transform = 'translateX(50px)';
                sec.style.opacity = 0;

                // Cache après animation
                setTimeout(() => {
                    sec.style.display = 'none';
                }, 500);
            } else {
                sec.style.display = 'none';
            }
        }
    });

    // Sauvegarde l'index actuel
    localStorage.setItem('currentSection', index);
}

// Affiche la section courante au départ
showSection(currentIndex);

// Gestion des boutons "Suivant"
const nextButtons = document.querySelectorAll('.btn-submit');
nextButtons.forEach(button => {
    button.addEventListener('click', (e) => {
        e.preventDefault();
        if (currentIndex < sections.length - 1) {
            const prevIndex = currentIndex;
            currentIndex++;
            showSection(currentIndex, 'next');
        }
    });
});

// Ajout dynamique des boutons "Précédent"
sections.forEach((sec, index) => {
    if (index > 0) {
        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Précédent';
        prevBtn.classList.add('btn-prev');

        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentIndex > 0) {
                currentIndex--;
                showSection(currentIndex, 'prev');
            }
        });

        const nextDiv = sec.querySelector('.next');
        if (nextDiv) {
            nextDiv.prepend(prevBtn);
        }
    }
});


// ===== Partie date de naissance =====
document.addEventListener("DOMContentLoaded", () => {
    const birthdateInput = document.getElementById('birthdate');
    const nextBirthdateBtn = document.querySelector('.birthdate-choice .btn-submit-date');

    // Crée un élément pour afficher l'âge
    const ageDisplay = document.createElement('p');
    ageDisplay.style.marginTop = '10px';
    ageDisplay.style.fontWeight = '500';
    ageDisplay.style.color = '#333';
    birthdateInput.parentNode.appendChild(ageDisplay);

    // Années autorisées
    const minYear = 1900;
    const maxYear = new Date().getFullYear() - 10; // limite par rapport à ton max HTML

    // Fonction pour calculer l'âge
    function calculateAge(dateString) {
        const today = new Date();
        const birthDate = new Date(dateString);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

    // Fonction pour mettre à jour l'état du bouton et l'affichage de l'âge
    function updateBirthdateState() {
        const value = birthdateInput.value;
        if (value) {
            const birthYear = parseInt(value.split('-')[0], 10);
            if (birthYear >= minYear && birthYear <= maxYear) {
                // ageDisplay.textContent = `Vous avez ${calculateAge(value)} ans.`;
                nextBirthdateBtn.disabled = false; // active le bouton
            } else {
                // ageDisplay.textContent = 'Année non valide.';
                nextBirthdateBtn.disabled = true;
            }
        } else {
            ageDisplay.textContent = '';
            nextBirthdateBtn.disabled = true; // désactive le bouton
        }
    }

    // Vérifie la valeur au chargement
    updateBirthdateState();

    // Événement à la modification de la date
    birthdateInput.addEventListener('change', updateBirthdateState);
});


// Fonction pour vérifier si l'année est valide
function isValidYear(dateString) {
    const year = new Date(dateString).getFullYear();
    return year >= minYear && year <= maxYear;
}

// Événement à la sélection de la date
// birthdateInput.addEventListener('change', () => {
//     const selectedDate = birthdateInput.value;

//     if (selectedDate && isValidYear(selectedDate)) {
//         const age = calculateAge(selectedDate);
//         ageDisplay.textContent = `Vous avez ${age} ans.`;
//         nextBirthdateBtn.disabled = false; // Active le bouton
//     } else {
//         ageDisplay.textContent = ''; // Cache le message si date invalide
//         nextBirthdateBtn.disabled = true; // Désactive le bouton
//     }
// });

// Optionnel : limiter le champ input date pour aider l'utilisateur
birthdateInput.setAttribute('min', `${minYear}-01-01`);
birthdateInput.setAttribute('max', `${maxYear}-12-31`);
