const profilePicInput = document.getElementById('profilePic');
const label = document.querySelector('.circle-upload');
const submitBtn = document.querySelector('.btn-submit');

// Bouton désactivé par défaut
submitBtn.disabled = true;

profilePicInput.addEventListener('change', function (event) {
    const file = event.target.files[0];

    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();

        reader.onload = function (e) {
            label.style.backgroundImage = `url('${e.target.result}')`;
            label.style.backgroundSize = 'cover';
            label.style.backgroundPosition = 'center';
            label.style.border = 'none';
            label.textContent = '';
        };

        reader.readAsDataURL(file);

        // Active le bouton
        submitBtn.disabled = false;
    } else {
        // Remet le style d'origine
        label.style.backgroundImage = '';
        label.style.border = '2px dashed #bbb';
        label.textContent = 'Cliquez pour choisir';

        // Désactive le bouton
        submitBtn.disabled = true;
    }
});

async function detectCountry() {
    try {
        // Récupérer les infos de localisation via l’IP
        const response = await fetch("https://ipapi.co/json/");
        const data = await response.json();

        // Extraire le code pays et l'indicatif
        const countryCode = data.country_code; // Exemple: "FR"
        const dialCode = getDialCode(countryCode);

        // Mettre à jour l’UI
        document.getElementById("flag").src = `https://flagcdn.com/w40/${countryCode.toLowerCase()}.png`;
        document.getElementById("dial-code").textContent = `+${dialCode}`;
    } catch (error) {
        console.error("Erreur lors de la détection du pays :", error);
    }
}

// Petit dictionnaire indicatif par code pays
function getDialCode(countryCode) {
    const dialCodes = {
        // Afrique
        DZ: 213, AO: 244, BJ: 229, BW: 267, BF: 226, BI: 257, CM: 237, CV: 238, CF: 236, TD: 235,
        KM: 269, CG: 242, CD: 243, CI: 225, DJ: 253, EG: 20, GQ: 240, ER: 291, ET: 251, GA: 241,
        GM: 220, GH: 233, GN: 224, GW: 245, KE: 254, LS: 266, LR: 231, LY: 218, MG: 261, MW: 265,
        ML: 223, MR: 222, MU: 230, MA: 212, MZ: 258, NA: 264, NE: 227, NG: 234, RW: 250, ST: 239,
        SN: 221, SC: 248, SL: 232, SO: 252, ZA: 27, SS: 211, SD: 249, SZ: 268, TZ: 255, TG: 228,
        TN: 216, UG: 256, ZM: 260, ZW: 263,

        // Amériques
        US: 1, CA: 1, MX: 52, BR: 55, AR: 54, CO: 57, VE: 58, CL: 56, PE: 51, EC: 593, GT: 502,
        CU: 53, BO: 591, DO: 1, HN: 504, PY: 595, NI: 505, CR: 506, PA: 507, UY: 598, JM: 1,
        HT: 509, SV: 503, BZ: 501, GF: 594, SR: 597, GY: 592,

        // Europe
        FR: 33, GB: 44, DE: 49, IT: 39, ES: 34, PT: 351, BE: 32, NL: 31, LU: 352, IE: 353, AT: 43,
        CH: 41, SE: 46, NO: 47, DK: 45, FI: 358, IS: 354, GR: 30, PL: 48, CZ: 420, SK: 421,
        HU: 36, RO: 40, BG: 359, HR: 385, SI: 386, BA: 387, ME: 382, RS: 381, MK: 389, AL: 355,
        MT: 356, CY: 357, EE: 372, LV: 371, LT: 370,

        // Asie
        CN: 86, JP: 81, IN: 91, PK: 92, BD: 880, RU: 7, SA: 966, AE: 971, IL: 972, IR: 98, KR: 82,
        SG: 65, MY: 60, TH: 66, VN: 84, ID: 62, PH: 63, HK: 852, TW: 886, KZ: 7, KG: 996, TJ: 992,
        TM: 993, UZ: 998, LK: 94, NP: 977, MM: 95, KH: 855, LA: 856, MN: 976, JO: 962, LB: 961, QA: 974,

        // Océanie
        AU: 61, NZ: 64, FJ: 679, PG: 675, SB: 677, VU: 678, NC: 687, PF: 689,

        // Moyen-Orient
        TR: 90, IQ: 964, SY: 963, YE: 967, OM: 968, KW: 965, BH: 973, QA: 974, SA: 966, AE: 971, JO: 962, LB: 961, PS: 970,

        // Autres îles et territoires
        IS: 354, GL: 299, FO: 298, PM: 508, RE: 262, YT: 262, CW: 599, AW: 297, BL: 590, MF: 590, SX: 1, GP: 590
    };

    return dialCodes[countryCode] || "";
}

// Exécution
detectCountry();

// Récupère toutes les sections à afficher une par une
const sections = [
    document.querySelector('.profil-picture-choice'),
    document.querySelector('.birthdate-choice'),
    document.querySelector('.phone-number'),
    document.querySelector('.english-level-section'),
    document.querySelector('.bio-section')
];

// Récupère l'index actuel depuis le localStorage ou démarre à 0
let currentIndex = parseInt(localStorage.getItem('currentSection')) || 0;

// Fonction pour montrer une section
function showSection(index) {
    sections.forEach((sec, i) => {
        if (i === index) {
            sec.style.display = 'block';
            sec.style.opacity = 0;
            sec.style.transform = 'translateX(50px)';
            setTimeout(() => {
                sec.style.transition = 'all 0.5s ease';
                sec.style.opacity = 1;
                sec.style.transform = 'translateX(0)';
            }, 50);
        } else {
            sec.style.display = 'none';
        }
    });
    // Sauvegarde l'index actuel dans le localStorage
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
            currentIndex++;
            showSection(currentIndex);
        }
    });
});

// Ajout dynamique du bouton "Précédent" à chaque section sauf la première
sections.forEach((sec, index) => {
    if (index > 0) {
        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Précédent';
        prevBtn.classList.add('btn-prev');
        prevBtn.addEventListener('click', () => {
            currentIndex--;
            showSection(currentIndex);
        });

        const nextDiv = sec.querySelector('.next');
        if (nextDiv) {
            nextDiv.prepend(prevBtn);
        }
    }
});

// ===== Partie date de naissance =====
const birthdateInput = document.getElementById('birthdate');
const nextBirthdateBtn = document.querySelector('.birthdate-choice .btn-submit');

// Crée un élément pour afficher l'âge
const ageDisplay = document.createElement('p');
ageDisplay.style.marginTop = '10px';
ageDisplay.style.fontWeight = '500';
ageDisplay.style.color = '#333';
birthdateInput.parentNode.appendChild(ageDisplay);

// Désactive le bouton par défaut
nextBirthdateBtn.disabled = true;

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

// Événement à la sélection de la date
birthdateInput.addEventListener('change', () => {
    if (birthdateInput.value) {
        const age = calculateAge(birthdateInput.value);
        ageDisplay.textContent = `Vous avez ${age} ans.`;
        nextBirthdateBtn.disabled = false; // Active le bouton
    } else {
        ageDisplay.textContent = '';
        nextBirthdateBtn.disabled = true; // Désactive le bouton
    }
});
