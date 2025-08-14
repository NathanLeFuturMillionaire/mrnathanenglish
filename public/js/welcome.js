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
