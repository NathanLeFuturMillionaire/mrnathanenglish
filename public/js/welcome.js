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

            if (data.success) {
                console.log("Pays et photo mis à jour !");
            } else {
                console.error(data.message);
            }
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
        prevBtn.textContent = 'Retour';
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

// 🌍 Liste complète des pays ISO2 + indicatifs téléphoniques
const countryDialCodes = {
    "AF": "+93",
    "AL": "+355",
    "DZ": "+213",
    "AS": "+1684",
    "AD": "+376",
    "AO": "+244",
    "AI": "+1264",
    "AQ": "+672",
    "AG": "+1268",
    "AR": "+54",
    "AM": "+374",
    "AW": "+297",
    "AU": "+61",
    "AT": "+43",
    "AZ": "+994",
    "BS": "+1242",
    "BH": "+973",
    "BD": "+880",
    "BB": "+1246",
    "BY": "+375",
    "BE": "+32",
    "BZ": "+501",
    "BJ": "+229",
    "BM": "+1441",
    "BT": "+975",
    "BO": "+591",
    "BA": "+387",
    "BW": "+267",
    "BR": "+55",
    "IO": "+246",
    "BN": "+673",
    "BG": "+359",
    "BF": "+226",
    "BI": "+257",
    "KH": "+855",
    "CM": "+237",
    "CA": "+1",
    "CV": "+238",
    "KY": "+1345",
    "CF": "+236",
    "TD": "+235",
    "CL": "+56",
    "CN": "+86",
    "CX": "+61",
    "CC": "+61",
    "CO": "+57",
    "KM": "+269",
    "CG": "+242",
    "CD": "+243",
    "CK": "+682",
    "CR": "+506",
    "CI": "+225",
    "HR": "+385",
    "CU": "+53",
    "CY": "+357",
    "CZ": "+420",
    "DK": "+45",
    "DJ": "+253",
    "DM": "+1767",
    "DO": "+1809",
    "EC": "+593",
    "EG": "+20",
    "SV": "+503",
    "GQ": "+240",
    "ER": "+291",
    "EE": "+372",
    "ET": "+251",
    "FK": "+500",
    "FO": "+298",
    "FJ": "+679",
    "FI": "+358",
    "FR": "+33",
    "GF": "+594",
    "PF": "+689",
    "GA": "+241",
    "GM": "+220",
    "GE": "+995",
    "DE": "+49",
    "GH": "+233",
    "GI": "+350",
    "GR": "+30",
    "GL": "+299",
    "GD": "+1473",
    "GP": "+590",
    "GU": "+1671",
    "GT": "+502",
    "GN": "+224",
    "GW": "+245",
    "GY": "+592",
    "HT": "+509",
    "HN": "+504",
    "HK": "+852",
    "HU": "+36",
    "IS": "+354",
    "IN": "+91",
    "ID": "+62",
    "IR": "+98",
    "IQ": "+964",
    "IE": "+353",
    "IL": "+972",
    "IT": "+39",
    "JM": "+1876",
    "JP": "+81",
    "JO": "+962",
    "KZ": "+7",
    "KE": "+254",
    "KI": "+686",
    "KP": "+850",
    "KR": "+82",
    "KW": "+965",
    "KG": "+996",
    "LA": "+856",
    "LV": "+371",
    "LB": "+961",
    "LS": "+266",
    "LR": "+231",
    "LY": "+218",
    "LI": "+423",
    "LT": "+370",
    "LU": "+352",
    "MO": "+853",
    "MK": "+389",
    "MG": "+261",
    "MW": "+265",
    "MY": "+60",
    "MV": "+960",
    "ML": "+223",
    "MT": "+356",
    "MH": "+692",
    "MQ": "+596",
    "MR": "+222",
    "MU": "+230",
    "YT": "+262",
    "MX": "+52",
    "FM": "+691",
    "MD": "+373",
    "MC": "+377",
    "MN": "+976",
    "ME": "+382",
    "MS": "+1664",
    "MA": "+212",
    "MZ": "+258",
    "MM": "+95",
    "NA": "+264",
    "NR": "+674",
    "NP": "+977",
    "NL": "+31",
    "NC": "+687",
    "NZ": "+64",
    "NI": "+505",
    "NE": "+227",
    "NG": "+234",
    "NU": "+683",
    "NF": "+672",
    "MP": "+1670",
    "NO": "+47",
    "OM": "+968",
    "PK": "+92",
    "PW": "+680",
    "PS": "+970",
    "PA": "+507",
    "PG": "+675",
    "PY": "+595",
    "PE": "+51",
    "PH": "+63",
    "PL": "+48",
    "PT": "+351",
    "PR": "+1787",
    "QA": "+974",
    "RE": "+262",
    "RO": "+40",
    "RU": "+7",
    "RW": "+250",
    "BL": "+590",
    "SH": "+290",
    "KN": "+1869",
    "LC": "+1758",
    "MF": "+590",
    "PM": "+508",
    "VC": "+1784",
    "WS": "+685",
    "SM": "+378",
    "ST": "+239",
    "SA": "+966",
    "SN": "+221",
    "RS": "+381",
    "SC": "+248",
    "SL": "+232",
    "SG": "+65",
    "SX": "+1721",
    "SK": "+421",
    "SI": "+386",
    "SB": "+677",
    "SO": "+252",
    "ZA": "+27",
    "SS": "+211",
    "ES": "+34",
    "LK": "+94",
    "SD": "+249",
    "SR": "+597",
    "SZ": "+268",
    "SE": "+46",
    "CH": "+41",
    "SY": "+963",
    "TW": "+886",
    "TJ": "+992",
    "TZ": "+255",
    "TH": "+66",
    "TL": "+670",
    "TG": "+228",
    "TK": "+690",
    "TO": "+676",
    "TT": "+1868",
    "TN": "+216",
    "TR": "+90",
    "TM": "+993",
    "TC": "+1649",
    "TV": "+688",
    "UG": "+256",
    "UA": "+380",
    "AE": "+971",
    "GB": "+44",
    "US": "+1",
    "UY": "+598",
    "UZ": "+998",
    "VU": "+678",
    "VA": "+379",
    "VE": "+58",
    "VN": "+84",
    "VG": "+1284",
    "VI": "+1340",
    "WF": "+681",
    "EH": "+212",
    "YE": "+967",
    "ZM": "+260",
    "ZW": "+263"
};


async function detectCountry() {
    try {
        // Service de détection par IP
        const res = await fetch("https://ipapi.co/json/");
        const data = await res.json();

        const countryCode = data.country_code; // Ex: "FR"
        const countryDial = countryDialCodes[countryCode] || "";

        // Mise à jour DOM
        document.getElementById("flag").src = `https://flagcdn.com/w40/${countryCode.toLowerCase()}.png`;
        document.getElementById("dial-code").textContent = countryDial;

    } catch (err) {
        console.error("Erreur détection IP:", err);
    }
}

detectCountry();


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


