form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (confirm('Supprimer ce membre ?')) {
        const formData = new FormData();
        formData.append('member_id', memberId); // ID du membre
        fetch('./members/delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                window.location.reload();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => showToast('Erreur réseau.', 'error'));
    }
});